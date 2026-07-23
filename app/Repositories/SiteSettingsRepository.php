<?php

namespace App\Repositories;

use App\Core\Database;

class SiteSettingsRepository
{
    private static ?array $cache = null;

    public function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $stmt = Database::connection()->query("SELECT setting_key, setting_value FROM site_settings");
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        self::$cache = $settings;
        return $settings;
    }

    public function get(string $key, $default = null)
    {
        $settings = $this->all();
        return $settings[$key] ?? $default;
    }

    public function set(string $key, string $value): bool
    {
        $stmt = Database::connection()->prepare("
            INSERT INTO site_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        
        $success = $stmt->execute([$key, $value, $value]);
        
        if ($success) {
            // Invalidate cache
            self::$cache = null;
        }

        return $success;
    }

    public function setMultiple(array $data): bool
    {
        Database::connection()->beginTransaction();
        try {
            foreach ($data as $key => $value) {
                $this->set($key, (string)$value);
            }
            Database::connection()->commit();
            return true;
        } catch (\Exception $e) {
            Database::connection()->rollBack();
            return false;
        }
    }
}
