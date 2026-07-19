<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class VirtualCardRequestRepository extends Repository
{
    protected static string $table = 'virtual_card_requests';

    public function findPendingForUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM virtual_card_requests WHERE user_id = ? AND status = 'pending' LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findAllPending(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM virtual_card_requests WHERE status = 'pending' ORDER BY created_at ASC"
        );

        return $stmt->fetchAll();
    }
}
