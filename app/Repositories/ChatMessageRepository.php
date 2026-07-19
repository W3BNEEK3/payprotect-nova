<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ChatMessageRepository extends Repository
{
    protected static string $table = 'chat_messages';

    /**
     * Signature matches ChatTransportInterface::poll() from Phase 1 exactly —
     * PollingChatProvider (Phase 4) calls this directly.
     */
    public function since(int $conversationId, int $sinceMessageId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM chat_messages WHERE conversation_id = ? AND id > ? ORDER BY id ASC'
        );
        $stmt->execute([$conversationId, $sinceMessageId]);

        return $stmt->fetchAll();
    }

    public function allForConversation(int $conversationId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$conversationId]);

        return $stmt->fetchAll();
    }
}
