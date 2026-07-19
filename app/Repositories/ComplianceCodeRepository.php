<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ComplianceCodeRepository extends Repository
{
    protected static string $table = 'user_compliance_codes';

    /**
     * The FIFO clearing query — matches user/process_withdraw.php's real, live
     * query exactly (oldest open code first, joined for display), confirmed
     * against the actual repo during the SRS/SADD review. Not a redesign, a
     * direct port of what production already does correctly.
     */
    public function findOldestUnclearedForUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT uc.*, cr.name, cr.description
             FROM user_compliance_codes uc
             JOIN compliance_requirements cr ON uc.compliance_id = cr.id
             WHERE uc.user_id = ? AND uc.is_cleared = 0
             ORDER BY uc.assigned_at ASC
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function countUnclearedForUser(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) AS c FROM user_compliance_codes WHERE user_id = ? AND is_cleared = 0'
        );
        $stmt->execute([$userId]);

        return (int) $stmt->fetch()['c'];
    }

    public function clear(int $codeId): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE user_compliance_codes SET is_cleared = 1, cleared_at = NOW() WHERE id = ?'
        );

        return $stmt->execute([$codeId]);
    }

    public function findForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT uc.*, cr.name AS compliance_name
             FROM user_compliance_codes uc
             JOIN compliance_requirements cr ON uc.compliance_id = cr.id
             WHERE uc.user_id = ?
             ORDER BY uc.assigned_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }
}
