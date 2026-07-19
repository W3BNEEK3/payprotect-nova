<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class NotificationRepository extends Repository
{
    protected static string $table = 'notifications';

    /**
     * Matches SRS FR-7.2 exactly: the bell preview dropdown shows the latest 5.
     */
    public function latestForUser(int $userId, int $limit = 5): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function unreadCountForUser(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);

        return (int) $stmt->fetch()['c'];
    }

    public function markRead(int $notificationId): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?'
        );

        return $stmt->execute([$notificationId]);
    }

    public function markAllReadForUser(int $userId): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0'
        );

        return $stmt->execute([$userId]);
    }
}
