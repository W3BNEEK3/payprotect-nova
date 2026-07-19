<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class VirtualCardRepository extends Repository
{
    protected static string $table = 'virtual_cards';

    /**
     * This is the exact check WithdrawalGate (Phase 11) runs first, per SADD
     * Section 6.0's documented gate sequence (approved card → KYC/upgrade →
     * open compliance flags, in order).
     */
    public function findApprovedForUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM virtual_cards WHERE user_id = ? AND is_virtual_card_approved = 1 LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM virtual_cards WHERE user_id = ?');
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /** Rows still holding plaintext-only card data (encrypted columns not yet backfilled). */
    public function findNeedingEncryptionBackfill(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM virtual_cards WHERE card_number IS NOT NULL AND card_number_encrypted IS NULL'
        );

        return $stmt->fetchAll();
    }
}
