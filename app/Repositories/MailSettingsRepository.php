<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class MailSettingsRepository extends Repository
{
    protected static string $table = 'mail_settings';

    public function getActive(): ?array
    {
        $stmt = Database::connection()->query('SELECT * FROM mail_settings WHERE is_active = 1 LIMIT 1');
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
