<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatBotRule;
use App\Models\User;
use App\Core\Database;
use App\Core\Session;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\CsrfMiddleware;

class ChatController extends BaseController
{

    public function queue()
    {
        $db = Database::connection();
        
        $stmt = $db->query("
            SELECT c.*, u.firstname, u.lastname, u.email 
            FROM chat_conversations c
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'waiting_for_agent'
            ORDER BY c.updated_at ASC
        ");
        $waiting = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $adminId = Session::get('admin_id');
        $stmtActive = $db->prepare("
            SELECT c.*, u.firstname, u.lastname, u.email 
            FROM chat_conversations c
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'active' AND c.assigned_admin_id = ?
            ORDER BY c.updated_at DESC
        ");
        $stmtActive->execute([$adminId]);
        $active = $stmtActive->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('admin/chat/queue', [
            'pageTitle' => 'Chat Queue',
            'waiting' => $waiting,
            'active' => $active
        ]);
    }

    public function showConversation($id)
    {
        $db = Database::connection();
        $adminId = Session::get('admin_id');

        $stmt = $db->prepare("SELECT * FROM chat_conversations WHERE id = ?");
        $stmt->execute([$id]);
        $conversation = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$conversation) {
            return $this->redirect('/admin/chat/queue');
        }

        // Claim logic
        if ($conversation['status'] === 'waiting_for_agent') {
            $stmtClaim = $db->prepare("UPDATE chat_conversations SET status = 'active', assigned_admin_id = ?, updated_at = NOW() WHERE id = ? AND status = 'waiting_for_agent'");
            $stmtClaim->execute([$adminId, $id]);
            
            if ($stmtClaim->rowCount() > 0) {
                // We successfully claimed it. Send an automated agent message.
                $adminName = Session::get('admin_name') ?? 'An agent';
                ChatMessage::create([
                    'conversation_id' => $id,
                    'sender_type' => 'admin',
                    'sender_id' => $adminId,
                    'message' => "Hi, I'm {$adminName}. I'm reviewing your request now.",
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        $user = User::find($conversation['user_id']);
        
        $stmtMsgs = $db->prepare("SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY id ASC");
        $stmtMsgs->execute([$id]);
        $messages = $stmtMsgs->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('admin/chat/conversation', [
            'pageTitle' => 'Conversation #' . $id,
            'conversation' => $conversation,
            'user' => $user,
            'messages' => $messages
        ]);
    }

    public function botRules()
    {
        $rules = ChatBotRule::all();

        return $this->view('admin/chat/bot-rules', [
            'pageTitle' => 'Chat Bot Rules',
            'rules' => $rules
        ]);
    }

    public function closeConversation($id)
    {
        $conversation = ChatConversation::find($id);
        if ($conversation) {
            ChatConversation::update($id, [
                'status' => 'closed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            if (class_exists('\App\Services\PusherService')) {
                \App\Services\PusherService::broadcastStatusChange($id, 'closed');
            }
            Session::flash('success', 'Conversation closed.');
        }
        return $this->redirect('/admin/chat/queue');
    }
}
