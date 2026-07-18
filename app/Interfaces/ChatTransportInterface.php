<?php
namespace App\Interfaces;

interface ChatTransportInterface
{
    public function send(int $conversationId, string $senderType, string $message): void;
    public function poll(int $conversationId, int $sinceMessageId): array;
}
