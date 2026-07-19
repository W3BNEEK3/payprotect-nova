<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class RefundRepository extends Repository
{
    protected static string $table = 'refunds';

    public function findByUserId(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM refunds WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function findByOriginalTransaction(int $transactionId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM refunds WHERE original_transaction_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$transactionId]);

        return $stmt->fetchAll();
    }
}
