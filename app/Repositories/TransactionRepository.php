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

    public function create(array $data): bool
    {
        if (empty($data)) return false;

        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO transactions ({$fields}) VALUES ({$placeholders})";
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data)) return false;
        
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;

        $sql = "UPDATE transactions SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute($values);
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
             WHERE user_id = ? AND LOWER(type) IN ('credit', 'deposit') AND created_at >= ?"
        );
        $stmt->execute([$userId, $sinceDatetime]);
        $row = $stmt->fetch();

        return (float) ($row['total'] ?? 0);
    }

    public function getMonthlyIncome(int $userId): float
    {
        $startOfMonth = (new \DateTime('first day of this month'))->format('Y-m-d 00:00:00');
        
        $stmt = Database::connection()->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = ? AND LOWER(type) IN ('credit', 'deposit') AND created_at >= ?"
        );
        $stmt->execute([$userId, $startOfMonth]);
        $row = $stmt->fetch();

        return (float) ($row['total'] ?? 0);
    }

    public function getMonthlyIncomeTrend(int $userId): float
    {
        $startOfThisMonth = (new \DateTime('first day of this month'))->format('Y-m-d 00:00:00');
        $startOfLastMonth = (new \DateTime('first day of last month'))->format('Y-m-d 00:00:00');
        $endOfLastMonth = (new \DateTime('last day of last month'))->format('Y-m-d 23:59:59');

        // This month
        $thisMonth = $this->getMonthlyIncome($userId);

        // Last month
        $stmt = Database::connection()->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = ? AND LOWER(type) IN ('credit', 'deposit') AND created_at >= ? AND created_at <= ?"
        );
        $stmt->execute([$userId, $startOfLastMonth, $endOfLastMonth]);
        $row = $stmt->fetch();
        $lastMonth = (float) ($row['total'] ?? 0);

        if ($lastMonth === 0.0) {
            return $thisMonth > 0 ? 100.0 : 0.0;
        }

        return (($thisMonth - $lastMonth) / $lastMonth) * 100;
    }

    public function getSpendBreakdown(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT method, COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = ? AND LOWER(type) IN ('debit', 'withdrawal', 'transfer')
             GROUP BY method"
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $breakdown = [
            'transfers' => 0.0,
            'cards' => 0.0,
            'others' => 0.0
        ];

        foreach ($rows as $row) {
            $method = strtolower($row['method'] ?? '');
            $amount = (float)$row['total'];

            if (str_contains($method, 'transfer') || str_contains($method, 'wire') || str_contains($method, 'ach')) {
                $breakdown['transfers'] += $amount;
            } elseif (str_contains($method, 'card')) {
                $breakdown['cards'] += $amount;
            } else {
                $breakdown['others'] += $amount;
            }
        }

        $totalSpend = array_sum($breakdown);

        return [
            'breakdown' => $breakdown,
            'total' => $totalSpend
        ];
    }
}
