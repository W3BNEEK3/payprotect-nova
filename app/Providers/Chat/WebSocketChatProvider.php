<?php

namespace App\Providers\Chat;

use App\Interfaces\ChatTransportInterface;

/**
 * Documented future implementation — not built out. Build this only once P0.4's
 * hosting decision confirms a host that can run a persistent WebSocket process
 * (typical shared/cPanel hosting cannot; a VPS can). Until then,
 * PollingChatProvider is the real, working default — this stub exists so the
 * seam ChatTransportInterface provides is visible in the codebase, not just
 * described in a document.
 */
class WebSocketChatProvider implements ChatTransportInterface
{
    public function send(int $conversationId, string $senderType, string $message): void
    {
        throw new \RuntimeException('WebSocketChatProvider is not yet implemented — see class docblock.');
    }

    public function poll(int $conversationId, int $sinceMessageId): array
    {
        throw new \RuntimeException('WebSocketChatProvider is not yet implemented — see class docblock.');
    }
}
