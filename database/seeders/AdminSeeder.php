<?php

namespace Database\Seeders;

use App\Core\Database;

class AdminSeeder
{
    public function run(): void
    {
        $db = Database::connection();
        
        $email = 'admin@novatrust.example';
        $password = password_hash('change-me-immediately', PASSWORD_DEFAULT);
        
        $stmt = $db->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return; // Already exists
        }
        
        $stmt = $db->prepare('INSERT INTO admins (fullname, email, password) VALUES (?, ?, ?)');
        $stmt->execute(['System Administrator', $email, $password]);
    }
}
