<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Core\{Request, Session};
use App\Models\SupportRequest;

class ContactController extends BaseController
{
    public function index(): void
    {
        $this->view('public/contact', [
            'pageTitle' => 'Contact — NovaTrust',
            'metaDescription' => 'Get in touch with the NovaTrust team.',
            'success' => Session::getFlash('contact_success'),
            'old' => Session::getFlash('contact_old', []),
            'errors' => Session::getFlash('contact_errors', []),
        ]);
    }

    public function submit(): void
    {
        $request = new Request();

        $email = trim((string) $request->input('email', ''));
        $subject = trim((string) $request->input('subject', ''));
        $message = trim((string) $request->input('message', ''));

        $errors = $this->validate($email, $subject, $message);

        if (!empty($errors)) {
            Session::flash('contact_errors', $errors);
            Session::flash('contact_old', ['email' => $email, 'subject' => $subject, 'message' => $message]);
            $this->redirect('/contact');
            return;
        }

        SupportRequest::create([
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
        ]);

        Session::flash('contact_success', true);
        $this->redirect('/contact');
    }

    /**
     * Server-side validation — the client-side validation engine (P5.6/P5.7)
     * is a UX convenience, never the actual security/data-integrity boundary.
     * A determined user can submit this form with JavaScript disabled
     * entirely, so every rule the client checks gets checked again here.
     */
    private function validate(string $email, string $subject, string $message): array
    {
        $errors = [];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address';
        }

        if ($subject === '') {
            $errors['subject'] = 'Enter a subject';
        }

        if (strlen($message) < 10) {
            $errors['message'] = 'Message must be at least 10 characters';
        }

        return $errors;
    }
}
