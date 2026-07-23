<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Repositories\UserRepository;
use App\Core\Session;

class UserImpersonationController extends BaseController
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function impersonate(int $id): void
    {
        $user = $this->userRepo->find($id);

        if (!$user) {
            Session::flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Keep track of admin ID just in case
        $adminId = Session::get('admin_id') ?? Session::get('user_id');

        // Log in as user
        Session::put('user_id', $user['id']);
        Session::put('user_name', $user['fullname'] ?? 'User');
        Session::put('is_impersonating', true);
        Session::put('impersonator_id', $adminId);

        Session::flash('success', "You are now impersonating {$user['fullname']}.");
        $this->redirect('/dashboard');
    }
}
