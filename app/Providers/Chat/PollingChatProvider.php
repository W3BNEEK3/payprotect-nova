<?php

namespace App\Providers\Chat;

use App\Interfaces\ChatTransportInterface;
use App\Repositories\ChatMessageRepository;
use App\Models\ChatMessage;

class PollingChatProvider implements ChatTransportInterface
{
    public function __construct(private ChatMessageRepository $messages)
    {
    }

    public function send(int $conversationId, string $senderType, string $message): void
    {
        ChatMessage::create([
            'conversation_id' => $conversationId,
            'sender_type' => $senderType,
            'message' => $message,
        ]);
    }

    public function poll(int $conversationId, int $sinceMessageId): array
    {
        return $this->messages->since($conversationId, $sinceMessageId);
    }
}
