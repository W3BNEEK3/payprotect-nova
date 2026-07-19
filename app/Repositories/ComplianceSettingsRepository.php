<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ComplianceSettingsRepository extends Repository
{
    protected static string $table = 'compliance_settings';

    public function get(string $key, $default = null)
    {
        $stmt = Database::connection()->prepare(
            'SELECT setting_value FROM compliance_settings WHERE setting_key = ? LIMIT 1'
        );
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        return $row === false ? $default : $row['setting_value'];
    }

    public function set(string $key, string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO compliance_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([$key, $value]);
    }
}
