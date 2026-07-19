<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class TransactionRepository extends Repository
{
    protected static string $table = 'transactions';

    public function findByUserId(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * The method the compliance auto-flag check (SRS BR-5/BR-12) depends on:
     * total credited to this user since a given point in time. Only counts
     * type = 'credit' — debits and withdrawals never count toward this sum.
     */
    public function sumCreditsForUserSince(int $userId, string $sinceDatetime): float
    {
        $stmt = Database::connection()->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = ? AND type = 'credit' AND created_at >= ?"
        );
        $stmt->execute([$userId, $sinceDatetime]);
        $row = $stmt->fetch();

        return (float) ($row['total'] ?? 0);
    }
}
