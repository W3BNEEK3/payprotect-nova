<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ChatConversationRepository extends Repository
{
    protected static string $table = 'chat_conversations';

    public function findActiveForUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM chat_conversations
             WHERE user_id = ? AND status IN ('bot_handled', 'waiting_for_agent', 'active')
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findWaitingForAgent(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM chat_conversations WHERE status = 'waiting_for_agent' ORDER BY updated_at ASC"
        );

        return $stmt->fetchAll();
    }

    public function assignToAdmin(int $conversationId, int $adminId): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE chat_conversations SET status = 'active', assigned_admin_id = ? WHERE id = ?"
        );

        return $stmt->execute([$adminId, $conversationId]);
    }
}
