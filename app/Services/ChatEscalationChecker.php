<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Core\Database;
use App\Providers\Mail\MailProviderFactory;
use App\Repositories\MailSettingsRepository;

class ChatEscalationChecker
{
    private MailProviderFactory $mailFactory;

    public function __construct()
    {
        $this->mailFactory = new MailProviderFactory(new MailSettingsRepository());
    }

    public function checkAndEscalate(): void
    {
        $db = Database::connection();
        
        // Find conversations waiting_for_agent updated > 2 mins ago
        $twoMinsAgo = date('Y-m-d H:i:s', strtotime('-2 minutes'));
        
        $stmt = $db->prepare("SELECT * FROM chat_conversations WHERE status = 'waiting_for_agent' AND updated_at < ?");
        $stmt->execute([$twoMinsAgo]);
        $conversations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($conversations as $conversation) {
            $user = User::find($conversation['user_id']);
            
            // Mark as 'escalated_to_email' so we don't alert again if it stays unassigned?
            // Or just alert once and record an internal bot message "Admin has been notified".
            
            // Let's send an email via MailProviderFactory
            try {
                $mailer = $this->mailFactory->make();
                $subject = "URGENT: Unclaimed Chat from " . ($user['firstname'] ?? 'User');
                $body = "A user chat has been waiting for an agent for over 2 minutes. \nUser: {$user['email']}\nLog in to the admin panel to assist them.";
                
                // Assuming admin email is in config, but for now we'll just log or use a placeholder
                $adminEmail = "admin@payprotect-nova.com"; 
                $mailer->send($adminEmail, $subject, $body);

                // Add an internal note that an email was sent
                ChatMessage::create([
                    'conversation_id' => $conversation['id'],
                    'sender_type' => 'bot',
                    'message' => 'An email alert has been sent to our support team to notify them of your request.',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Update updated_at so it doesn't trigger repeatedly every minute
                ChatConversation::update($conversation['id'], [
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // If mail is not configured yet, just log and skip
                error_log("Escalation email failed: " . $e->getMessage());
            }
        }
    }
}
