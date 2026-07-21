<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Core\{Request, Session};
use App\Models\User;
use App\Repositories\UserRepository;

class RegisterController extends BaseController
{
    public function index(): void
    {
        $this->view('auth/register', [
            'pageTitle' => 'Create Your Account — NovaTrust',
            'errors' => Session::getFlash('register_errors', []),
            'old' => Session::getFlash('register_old', []),
        ]);
    }

    public function submit(): void
    {
        $request = new Request();
        $firstname = trim((string) $request->input('firstname', ''));
        $lastname = trim((string) $request->input('lastname', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $userRepo = new UserRepository();
        $errors = $this->validate($firstname, $lastname, $email, $password, $userRepo);

        if (!empty($errors)) {
            Session::flash('register_errors', $errors);
            Session::flash('register_old', ['firstname' => $firstname, 'lastname' => $lastname, 'email' => $email]);
            $this->redirect('/register');
            return;
        }

        $accountNumber = $userRepo->generateUniqueAccountNumber();

        $userId = User::create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'fullname' => trim("{$firstname} {$lastname}"),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'account_number' => $accountNumber,
            'account_status' => 'active',
            'balance' => 0.00,
        ]);

        // Auto-login immediately after registration — no separate "check your
        // email to activate" step in this pass. If email verification is
        // wanted later, it's an additive step here, not a rebuild.
        Session::put('user_id', $userId);
        Session::put('user_name', trim("{$firstname} {$lastname}"));

        $this->redirect('/dashboard');
    }

    /**
     * Server-side validation — mirrors whatever P5.6/P5.7 checks client-side,
     * but this is the actual boundary. A duplicate-email check specifically
     * cannot happen client-side at all without leaking which emails exist,
     * so this is the only place that check can correctly live.
     */
    private function validate(string $firstname, string $lastname, string $email, string $password, UserRepository $userRepo): array
    {
        $errors = [];

        if ($firstname === '') {
            $errors['firstname'] = 'Enter your first name';
        }

        if ($lastname === '') {
            $errors['lastname'] = 'Enter your last name';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address';
        } elseif ($userRepo->findByEmail($email) !== null) {
            $errors['email'] = 'An account with this email already exists';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        return $errors;
    }
}
