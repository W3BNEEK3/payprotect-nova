<?php

require_once __DIR__ . '/../../bootstrap/app.php';

use App\Core\Database;

try {
    $db = Database::connection();
    
    // Create the site_settings table
    $db->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(255) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Seed default data
    $defaultSettings = [
        'site_name' => 'NovaTrust',
        'site_logo_url' => '', // Empty defaults to just text or a fallback
        'site_favicon_url' => '/assets/images/icon-192.png',
        'site_address' => '123 Nova Way, Financial District, NY 10004',
        'site_support_email' => 'support@novatrust.com',
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    
    foreach ($defaultSettings as $key => $value) {
        $stmt->execute([$key, $value]);
    }

    echo "Successfully created and seeded site_settings table.\n";
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
