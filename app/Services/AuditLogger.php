<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Session;

class AuditLogger
{
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, array $details = []): void
    {
        $adminId = Session::get('admin_id');
        
        $db = Database::connection();
        $stmt = $db->prepare("
            INSERT INTO audit_log (admin_id, action, target_type, target_id, details)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $detailsJson = !empty($details) ? json_encode($details) : null;
        
        $stmt->execute([
            $adminId,
            $action,
            $targetType,
            $targetId,
            $detailsJson
        ]);
    }
}
