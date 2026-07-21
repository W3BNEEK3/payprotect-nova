<?php

namespace App\Services;

use App\Models\ChatBotRule;
use App\Models\ChatConversation;
use App\Models\ChatMessage;

class ChatRuleEngine
{
    private array $excludedTopics = [
        'withdraw', 'withdrawal', 'payout', 'bank transfer', 'wire', 
        'compliance', 'kyc', 'limit', 'locked', 'suspension', 'suspended'
    ];

    public function processMessage(array $conversation, string $userMessage): void
    {
        $messageLower = strtolower($userMessage);

        // 1. Check excluded topics boundary first
        foreach ($this->excludedTopics as $topic) {
            if (strpos($messageLower, $topic) !== false) {
                $this->escalateToHuman($conversation, "I see you're asking about a sensitive topic. Let me transfer you to an agent who can help.");
                return;
            }
        }

        // 2. Check for explicit human request
        $humanRequests = ['agent', 'human', 'representative', 'real person'];
        foreach ($humanRequests as $hr) {
            if (strpos($messageLower, $hr) !== false) {
                $this->escalateToHuman($conversation, "I will transfer you to an agent now.");
                return;
            }
        }

        // 3. Match against seeded FAQ rules
        $rules = ChatBotRule::all();
        foreach ($rules as $rule) {
            $keywords = explode(',', strtolower($rule['trigger_keywords']));
            foreach ($keywords as $kw) {
                $kw = trim($kw);
                if (!empty($kw) && strpos($messageLower, $kw) !== false) {
                    // Match found
                    $this->sendBotReply($conversation, $rule['response']);

                    if ($rule['requires_human']) {
                        $this->escalateToHuman($conversation, "I'm passing this conversation to an agent for further review.");
                    }
                    return;
                }
            }
        }

        // 4. No rule matched
        $this->escalateToHuman($conversation, "I'm not sure how to answer that. Let me get a human agent to assist you.");
    }

    private function sendBotReply(array $conversation, string $text): void
    {
        ChatMessage::create([
            'conversation_id' => $conversation['id'],
            'sender_type' => 'bot',
            'message' => $text,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function escalateToHuman(array $conversation, string $handoffMessage): void
    {
        $this->sendBotReply($conversation, $handoffMessage);

        ChatConversation::update($conversation['id'], [
            'status' => 'waiting_for_agent',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
