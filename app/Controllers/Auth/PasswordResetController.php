<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Core\{Request, Session};
use App\Repositories\UserRepository;

class PasswordResetController extends BaseController
{
    public function showRequestForm(): void
    {
        $this->view('auth/forgot-password', [
            'pageTitle' => 'Reset Password — NovaTrust',
            'success' => Session::getFlash('reset_request_success'),
        ]);
    }

    public function submitRequest(): void
    {
        $request = new Request();
        $email = trim((string) $request->input('email', ''));

        $userRepo = new UserRepository();
        $user = $userRepo->findByEmail($email);

        // Always show the same success message whether or not the email
        // exists — confirming/denying an email's existence here is the same
        // class of leak as the login error message.
        if ($user !== null) {
            $token = bin2hex(random_bytes(32));
            $userRepo->setResetToken((int) $user['id'], $token);
            $this->attemptSendResetEmail($user['email'], $token);
        }

        Session::flash('reset_request_success', true);
        $this->redirect('/forgot-password');
    }

    public function showResetForm(string $token): void
    {
        $userRepo = new UserRepository();
        $user = $userRepo->findByResetToken($token);

        if ($user === null) {
            $this->view('auth/reset-password-invalid', [
                'pageTitle' => 'Link Expired — NovaTrust',
            ]);
            return;
        }

        $this->view('auth/reset-password', [
            'pageTitle' => 'Choose a New Password — NovaTrust',
            'token' => $token,
            'errors' => Session::getFlash('reset_errors', []),
        ]);
    }

    public function submitReset(string $token): void
    {
        $userRepo = new UserRepository();
        $user = $userRepo->findByResetToken($token);

        if ($user === null) {
            $this->redirect('/forgot-password');
            return;
        }

        $request = new Request();
        $password = (string) $request->input('password', '');

        if (strlen($password) < 8) {
            Session::flash('reset_errors', ['password' => 'Password must be at least 8 characters']);
            $this->redirect('/reset-password/' . $token);
            return;
        }

        // updatePassword() also clears reset_token — the token is single-use
        // by construction, not by a separate "mark as used" step.
        $userRepo->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));

        Session::flash('login_errors', []);
        $this->redirect('/login');
    }

    private function attemptSendResetEmail(string $email, string $token): void
    {
        try {
            $factory = new \App\Providers\Mail\MailProviderFactory(new \App\Repositories\MailSettingsRepository());
            $mailer = $factory->make();

            $appUrl = env('APP_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000'));
            $resetLink = rtrim($appUrl, '/') . '/reset-password/' . $token;
            
            $subject = 'NovaTrust - Password Reset Request';
            $body = \App\Helpers\MailTemplate::render('password_reset', [
                'subject' => $subject,
                'resetLink' => $resetLink
            ]);

            $mailer->send($email, $subject, $body);
        } catch (\Throwable $e) {
            // Intentionally swallowed — a mail failure must never block the
            // reset flow itself or reveal anything to the requester.
            error_log('Failed to send password reset email: ' . $e->getMessage());
        }
    }
}
