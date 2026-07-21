<?php

namespace Database\Seeders;

use App\Models\ChatBotRule;

class ChatBotRuleSeeder
{
    public function run(): void
    {
        $rules = [
            [
                'trigger_keywords' => 'hello, hi, hey, greetings',
                'response' => 'Hello! How can I help you today?',
                'requires_human' => 0,
            ],
            [
                'trigger_keywords' => 'hours, time, open',
                'response' => 'Our support team is available Monday through Friday, 9 AM to 5 PM EST.',
                'requires_human' => 0,
            ],
            [
                'trigger_keywords' => 'card, virtual card, new card',
                'response' => 'You can request a new Virtual Card from the "Virtual Card" tab in your dashboard.',
                'requires_human' => 0,
            ],
            [
                'trigger_keywords' => 'history, transactions, past',
                'response' => 'You can view all your past transactions by navigating to the "Transactions" page from the main menu.',
                'requires_human' => 0,
            ],
            [
                'trigger_keywords' => 'lost, stolen, compromise',
                'response' => 'I understand your card might be compromised. I am transferring you to an agent immediately.',
                'requires_human' => 1,
            ],
        ];

        foreach ($rules as $rule) {
            ChatBotRule::create($rule);
        }

        echo "Seeded ChatBotRules.\n";
    }
}
