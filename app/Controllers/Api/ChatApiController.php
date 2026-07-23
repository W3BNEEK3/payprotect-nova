<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Core\Session;
use App\Services\ChatRuleEngine;
use App\Core\Database;

class ChatApiController extends BaseController
{
    private ChatRuleEngine $ruleEngine;

    public function __construct()
    {
        $this->ruleEngine = new ChatRuleEngine();
    }

    public function status()
    {
        $userId = Session::get('user_id');
        
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM chat_conversations WHERE user_id = ? AND status != 'closed' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $conversation = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($conversation) {
            return $this->json(['status' => 'success', 'conversation' => $conversation]);
        }

        return $this->json(['status' => 'success', 'conversation' => null]);
    }

    public function messages(string $id)
    {
        $userId = Session::get('user_id');
        
        $conversation = ChatConversation::find($id);
        if (!$conversation || (int)$conversation['user_id'] !== (int)$userId) {
            return $this->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $after = $_GET['after'] ?? 0;
        
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM chat_messages WHERE conversation_id = ? AND id > ? ORDER BY id ASC");
        $stmt->execute([$id, $after]);
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->json(['status' => 'success', 'messages' => $messages, 'conversation_status' => $conversation['status']]);
    }

    public function sendMessage()
    {
        $userId = Session::get('user_id');
        $text = trim($_POST['message'] ?? '');

        if (empty($text)) {
            return $this->json(['status' => 'error', 'message' => 'Empty message'], 400);
        }

        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM chat_conversations WHERE user_id = ? AND status != 'closed' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $conversation = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$conversation) {
            ChatConversation::create([
                'user_id' => $userId,
                'status' => 'bot_handled',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $conversationId = $db->lastInsertId();
            $conversation = ChatConversation::find($conversationId);
        } else {
            $conversationId = $conversation['id'];
        }

        ChatMessage::create([
            'conversation_id' => $conversationId,
            'sender_type' => 'user',
            'sender_id' => $userId,
            'message' => $text,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($conversation['status'] === 'bot_handled') {
            $this->ruleEngine->processMessage($conversation, $text);
        }

        return $this->json(['status' => 'success', 'conversation_id' => $conversationId]);
    }

    public function adminMessages(string $id)
    {
        $conversation = ChatConversation::find($id);
        if (!$conversation) {
            return $this->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $after = $_GET['after'] ?? 0;
        
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM chat_messages WHERE conversation_id = ? AND id > ? ORDER BY id ASC");
        $stmt->execute([$id, $after]);
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->json(['status' => 'success', 'messages' => $messages, 'conversation_status' => $conversation['status']]);
    }

    public function sendMessageAdmin(string $id)
    {
        $adminId = Session::get('admin_id');
        $text = trim($_POST['message'] ?? '');

        if (empty($text)) {
            return $this->json(['status' => 'error', 'message' => 'Empty message'], 400);
        }

        $conversation = ChatConversation::find($id);
        if (!$conversation || $conversation['status'] === 'closed') {
            return $this->json(['status' => 'error', 'message' => 'Conversation closed or not found'], 400);
        }

        ChatMessage::create([
            'conversation_id' => $id,
            'sender_type' => 'admin',
            'sender_id' => $adminId,
            'message' => $text,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->json(['status' => 'success']);
    }

    public function pusherAuth()
    {
        $userId = \App\Core\Session::get('user_id');
        $socketId = $_POST['socket_id'] ?? '';
        $channelName = $_POST['channel_name'] ?? '';

        if (!$socketId || !$channelName) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }

        // Validate channel name
        if (preg_match('/^private-chat-(\d+)$/', $channelName, $matches)) {
            $conversationId = (int)$matches[1];
            $conversation = \App\Models\ChatConversation::find($conversationId);
            
            if ($conversation && (int)$conversation['user_id'] === (int)$userId) {
                if (class_exists('\App\Services\PusherService')) {
                    $pusher = \App\Services\PusherService::getPusher();
                    echo $pusher->socket_auth($channelName, $socketId);
                    exit;
                }
            }
        }
        
        header('HTTP/1.1 403 Forbidden');
        echo 'Forbidden';
        exit;
    }

    public function pusherAuthAdmin()
    {
        $socketId = $_POST['socket_id'] ?? '';
        $channelName = $_POST['channel_name'] ?? '';

        if (!$socketId || !$channelName) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }

        // Admins can authenticate to any private chat channel
        if (preg_match('/^private-chat-(\d+)$/', $channelName, $matches)) {
            if (class_exists('\App\Services\PusherService')) {
                $pusher = \App\Services\PusherService::getPusher();
                echo $pusher->socket_auth($channelName, $socketId);
                exit;
            }
        }
        
        header('HTTP/1.1 403 Forbidden');
        echo 'Forbidden';
        exit;
    }
}
