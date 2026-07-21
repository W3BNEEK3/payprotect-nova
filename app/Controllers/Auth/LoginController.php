<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Core\{Request, Session};
use App\Repositories\UserRepository;

class LoginController extends BaseController
{
    public function index(): void
    {
        $this->view('auth/login', [
            'pageTitle' => 'Log In — NovaTrust',
            'errors' => Session::getFlash('login_errors', []),
            'old' => Session::getFlash('login_old', []),
        ]);
    }

    public function submit(): void
    {
        $request = new Request();
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $userRepo = new UserRepository();
        $user = $userRepo->findByEmail($email);

        // Deliberately identical error for "no such account" and "wrong
        // password" — distinguishing them tells an attacker which emails
        // are registered, which is itself a data leak.
        $genericError = 'Incorrect email or password.';

        if ($user === null || !password_verify($password, $user['password'] ?? '')) {
            Session::flash('login_errors', ['general' => $genericError]);
            Session::flash('login_old', ['email' => $email]);
            $this->redirect('/login');
            return;
        }

        if (($user['account_status'] ?? 'active') === 'suspended') {
            Session::flash('login_errors', ['general' => 'This account is suspended. Contact support for help.']);
            $this->redirect('/login');
            return;
        }

        Session::put('user_id', (int) $user['id']);
        Session::put('user_name', $user['fullname'] ?? $user['firstname'] ?? '');

        $redirectTo = Session::getFlash('redirect_after_login', '/dashboard');
        $this->redirect($redirectTo);
    }

    public function logout(): void
    {
        Session::forget('user_id');
        Session::forget('user_name');
        $this->redirect('/login');
    }
}
