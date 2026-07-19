<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ComplianceFlagRepository extends Repository
{
    protected static string $table = 'compliance_flags';

    public function hasOpenFlag(int $userId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) AS c FROM compliance_flags WHERE user_id = ? AND status = 'open'"
        );
        $stmt->execute([$userId]);

        return (int) $stmt->fetch()['c'] > 0;
    }

    public function findOpenForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM compliance_flags WHERE user_id = ? AND status = 'open' ORDER BY created_at ASC"
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function resolve(int $flagId): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE compliance_flags SET status = 'resolved', resolved_at = NOW() WHERE id = ?"
        );

        return $stmt->execute([$flagId]);
    }
}
