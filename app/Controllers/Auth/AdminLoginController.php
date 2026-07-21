<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Core\{Request, Session, Database};

class AdminLoginController extends BaseController
{
    public function index(): void
    {
        $this->view('auth/admin-login', [
            'pageTitle' => 'Control Center — NovaTrust',
            'errors' => Session::getFlash('login_errors', []),
            'old' => Session::getFlash('login_old', []),
        ]);
    }

    public function submit(): void
    {
        $request = new Request();
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $stmt = Database::connection()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        $genericError = 'Incorrect email or password.';

        if ($admin === false || !password_verify($password, $admin['password'] ?? '')) {
            Session::flash('login_errors', ['general' => $genericError]);
            Session::flash('login_old', ['email' => $email]);
            $this->redirect('/control-center/auth');
            return;
        }

        Session::put('admin_id', (int) $admin['id']);
        Session::put('admin_name', $admin['fullname'] ?? '');

        $redirectTo = Session::getFlash('redirect_after_login', '/admin/dashboard');
        $this->redirect($redirectTo);
    }

    public function logout(): void
    {
        Session::forget('admin_id');
        Session::forget('admin_name');
        $this->redirect('/control-center/auth');
    }
}
