<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class AuditLogRepository extends Repository
{
    protected static string $table = 'audit_log';

    public function record(?int $adminId, string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO audit_log (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$adminId, $action, $targetType, $targetId, $details]);

        return (int) Database::connection()->lastInsertId();
    }

    public function recent(int $limit = 50): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM audit_log ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
