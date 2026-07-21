<?php

namespace App\Models;

use App\Core\Model;

class ChatMessage extends Model
{
    protected static string $table = 'chat_messages';

    public static function create(array $data): int
    {
        $id = parent::create($data);
        if ($id) {
            $message = parent::find($id);
            if ($message && class_exists('\App\Services\PusherService')) {
                \App\Services\PusherService::broadcastMessage((int)$message['conversation_id'], $message);
            }
        }
        return $id;
    }
}
