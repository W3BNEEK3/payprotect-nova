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

    /**
     * All open flags across all users — admin compliance queue view.
     * Joins users table so admin can see name + email alongside the flag.
     */
    public function findAllOpen(): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT cf.*, u.fullname, u.email, u.account_number,
                    ucc.code AS assigned_code, cr.name AS requirement_name
             FROM compliance_flags cf
             JOIN users u ON cf.user_id = u.id
             LEFT JOIN user_compliance_codes ucc ON ucc.flag_id = cf.id
             LEFT JOIN compliance_requirements cr ON ucc.compliance_id = cr.id
             WHERE cf.status = 'open'
             ORDER BY cf.created_at ASC"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
