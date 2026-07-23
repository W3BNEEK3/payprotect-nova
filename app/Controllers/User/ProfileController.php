<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Repositories\UserRepository;

class ProfileController extends BaseController
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function index()
    {
        $user = Session::get('user');
        // Fetch fresh user to get settings
        $freshUser = (object)$this->userRepo->find(Session::get('user_id'));
        
        $this->view('user/profile', [
            'title' => 'My Profile',
            'user' => $freshUser
        ]);
    }

    public function updatePassword()
    {
        $user = Session::get('user');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            Session::flash('error', 'All fields are required.');
            $this->redirect('/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New passwords do not match.');
            $this->redirect('/profile');
            return;
        }

        $freshUser = $this->userRepo->findByEmail($user['email']);

        if (!password_verify($currentPassword, $freshUser['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            $this->redirect('/profile');
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $pdo = \App\Core\Database::connection();
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user->id]);

        Session::flash('success', 'Password updated successfully.');
        $this->redirect('/profile');
    }
}
