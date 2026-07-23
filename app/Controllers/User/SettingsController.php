<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Core\Database;

class SettingsController extends BaseController
{
    public function index()
    {
        $userId = Session::get('user_id');
        
        // Fetch fresh user to get settings
        $pdo = Database::connection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $freshUser = $stmt->fetch(\PDO::FETCH_OBJ);
        
        $this->view('user/settings', [
            'title' => 'Account Settings',
            'user' => $freshUser
        ]);
    }

    public function update()
    {
        $userId = Session::get('user_id');
        
        $emailNotif = isset($_POST['settings_email_notifications']) ? 1 : 0;
        $darkMode = isset($_POST['settings_dark_mode']) ? 1 : 0;
        $twoFa = isset($_POST['settings_2fa']) ? 1 : 0;

        $pdo = Database::connection();
        $stmt = $pdo->prepare("UPDATE users SET settings_email_notifications = ?, settings_dark_mode = ?, settings_2fa = ? WHERE id = ?");
        $stmt->execute([$emailNotif, $darkMode, $twoFa, $userId]);

        Session::flash('success', 'Settings updated successfully.');
        $this->redirect('/settings');
    }
}
