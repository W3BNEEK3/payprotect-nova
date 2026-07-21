<?php

namespace App\Services;

use Pusher\Pusher;

class PusherService
{
    private static ?Pusher $pusher = null;

    public static function getPusher(): Pusher
    {
        if (self::$pusher === null) {
            $options = [
                'cluster' => getenv('PUSHER_CLUSTER') ?: 'mt1',
                'useTLS' => true,
            ];
            $client = new \GuzzleHttp\Client(['verify' => false]);
            self::$pusher = new Pusher(
                getenv('PUSHER_KEY'),
                getenv('PUSHER_SECRET'),
                getenv('PUSHER_APP_ID'),
                $options,
                $client
            );
        }
        return self::$pusher;
    }

    public static function broadcastMessage(int $conversationId, array $messageData): void
    {
        try {
            self::getPusher()->trigger('private-chat-' . $conversationId, 'new-message', $messageData);
        } catch (\Exception $e) {
            error_log('Pusher Broadcast Error: ' . $e->getMessage());
        }
    }

    public static function broadcastStatusChange(int $conversationId, string $status): void
    {
        try {
            self::getPusher()->trigger('private-chat-' . $conversationId, 'status-change', ['status' => $status]);
        } catch (\Exception $e) {
            error_log('Pusher Broadcast Error: ' . $e->getMessage());
        }
    }
}
