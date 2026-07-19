<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class WithdrawalRequestRepository extends Repository
{
    protected static string $table = 'withdrawal_requests';

    public function findPendingReview(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM withdrawal_requests WHERE status = 'pending_review' ORDER BY created_at ASC"
        );

        return $stmt->fetchAll();
    }

    public function findByUserId(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM withdrawal_requests WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * Admin queue pagination (SRS FR-6.5), with an optional status filter —
     * used by the admin withdrawal review screen (Phase 11).
     */
    public function paginate(int $page, int $perPage, ?string $status = null): array
    {
        $offset = ($page - 1) * $perPage;

        if ($status !== null) {
            $stmt = Database::connection()->prepare(
                'SELECT * FROM withdrawal_requests WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?'
            );
            $stmt->bindValue(1, $status);
            $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        } else {
            $stmt = Database::connection()->prepare(
                'SELECT * FROM withdrawal_requests ORDER BY created_at DESC LIMIT ? OFFSET ?'
            );
            $stmt->bindValue(1, $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
