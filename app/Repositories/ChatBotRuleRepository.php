<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ChatBotRuleRepository extends Repository
{
    protected static string $table = 'chat_bot_rules';

    public function findActive(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM chat_bot_rules WHERE is_active = 1');

        return $stmt->fetchAll();
    }
}
