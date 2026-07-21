# NovaTrust — Phase 7 Runbook: Authentication

**Version:** 1.0
**Prepared for:** Wynston
**Companion to:** `novatrust-implementation-plan.md` v1.4, Phase 7 (`P7.1`–`P7.3`)
**Scope:** three controllers (Login, Register, PasswordReset), the `resources/auth/` layout and pages, and the middleware wiring that gates them.

**Convention change starting this phase:** `layouts/` and `components/` now live inside `resources/` (`resources/layouts/`, `resources/components/`), matching the SADD's original directory tree. Phases 5 and 6 had drifted from this without it being caught — confirmed directly against your `develop` branch, and the three Phase 6 pages that hadn't been migrated yet (`about.php`, `contact.php`, `blog/index.php`, `blog/show.php`) were fixed and verified against your actual checked-out branch before this phase started.

**Verified:** every PHP file lints clean. 23 automated checks run against a live server using real HTTP sessions (via Python's `requests`, which handles cookies naturally across multi-step flows) — covering registration, duplicate-email rejection, login with correct/incorrect/nonexistent credentials, `GuestMiddleware`/`AuthMiddleware` redirects, CSRF rejection with missing and forged tokens, logout, and the complete password-reset lifecycle including confirming the reset token is genuinely single-use. All 23 passed on the first run — no bugs found in the auth logic itself, which is a better outcome than Phases 5/6 but not one to get complacent about; it means the design was right, not that testing was unnecessary.

---

## P7.1 — Auth Controllers

**File: `app/Controllers/Auth/LoginController.php`**

```php
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
```

**Verified:**
```
PASS — Wrong password is rejected with the GENERIC error (doesn't reveal account exists)
PASS — Nonexistent email gets the SAME generic error as wrong password (no account enumeration)
PASS — Correct password logs in successfully and reaches the dashboard
```

**File: `app/Controllers/Auth/RegisterController.php`**

```php
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
```

**Verified:**
```
PASS — Registration with valid data redirects to dashboard (placeholder)
PASS — Auto-login after registration puts user_id in session (dashboard shows it)
PASS — Duplicate email is rejected, redirected back to /register with an error
PASS — Stored password is a bcrypt/argon hash, not plaintext (starts with $2y$ or $argon2)
PASS — Stored password hash does NOT contain the actual plaintext password anywhere
```

**File: `app/Controllers/Auth/PasswordResetController.php`**

```php
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
            // Real wiring lands once MailProviderFactory has an active driver
            // configured (Phase 14). For now this is a documented no-op if
            // nothing is configured yet, rather than a hard failure.
        } catch (\Throwable $e) {
            // Intentionally swallowed — a mail failure must never block the
            // reset flow itself or reveal anything to the requester.
        }
    }
}
```

**`attemptSendResetEmail()` is a deliberate no-op right now** — real sending wires up once Phase 14 gives `MailProviderFactory` an actual active driver to call. Wrapping it in a swallowed try/catch from the start means the reset flow itself never breaks because email delivery isn't configured yet in early development, and nothing about the calling code changes when Phase 14 fills it in.

**Verified — the full lifecycle, including the part most auth implementations get subtly wrong (token reuse):**
```
PASS — Password reset request shows the generic success message
PASS — A real reset token was generated and stored, got a 64-char token
PASS — Reset page loads successfully for a valid token
PASS — Password reset submission redirects to /login
PASS — OLD password no longer works after reset
PASS — NEW password works after reset
PASS — Reset token is single-use — reusing it shows the expired-link page
```

---

## Supporting Pieces Built Alongside the Controllers

**`app/Repositories/UserRepository.php`** gained four methods this phase: `findByResetToken()`, `updatePassword()` (clears the token in the same query), `setResetToken()`, and `generateUniqueAccountNumber()` (retries on the rare collision rather than trusting randomness alone).

**`app/Middlewares/CsrfMiddleware.php`** — built in Phase 1's task list but never actually wired into a real form until now:

```php
<?php

namespace App\Middlewares;

use App\Core\{Request, Response, Session};
use App\Interfaces\MiddlewareInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!$request->isPost()) {
            return;
        }

        $token = $request->input('_csrf');

        if (!$token || !Session::has('_csrf_token') || !hash_equals(Session::get('_csrf_token'), $token)) {
            Response::abort(419, 'Your session expired. Please refresh the page and try again.');
        }
    }

    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::put('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token()) . '">';
    }
}
```

Note `hash_equals()` rather than `===` for the token comparison — a plain string comparison is vulnerable to a timing attack that can (slowly) leak the correct token one byte at a time; `hash_equals()` runs in constant time regardless of where the strings first differ.

**Verified:**
```
PASS — POST with NO CSRF token is rejected (419)
PASS — POST with WRONG CSRF token is rejected (419)
```

**`app/Middlewares/GuestMiddleware.php`** — the inverse of `AuthMiddleware`, keeping a logged-in session off `/login` and `/register`:

```php
<?php

namespace App\Middlewares;

use App\Core\{Request, Response, Session};
use App\Interfaces\MiddlewareInterface;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (Session::has('user_id')) {
            Response::redirect('/dashboard');
        }
    }
}
```

**`AuthMiddleware.php` gained one behavior beyond its Phase 1 version:** it now remembers where the user was trying to go before being bounced to `/login`, and `LoginController::submit()` redirects back there after a successful login instead of always landing on `/dashboard`:

```php
public function handle(Request $request): void
{
    if (!Session::has('user_id')) {
        Session::flash('redirect_after_login', $request->path());
        Response::redirect('/login');
    }
}
```

**Verified:**
```
PASS — Logged-in session is redirected AWAY from /login (GuestMiddleware)
PASS — Logged-in session is redirected AWAY from /register (GuestMiddleware)
PASS — Logged-out session is redirected to /login when hitting /dashboard (AuthMiddleware)
PASS — After logout, hitting /dashboard again redirects to /login (session cleared)
```

**`app/Core/Session.php` was missing `forget()` and `destroy()`** — present in the original Phase 1 spec but silently absent from the copy this build started from. Added back:

```php
public static function forget(string $key): void { unset($_SESSION[$key]); }
public static function destroy(): void {
    if (PHP_SAPI === 'cli') return;
    session_unset();
    session_destroy();
}
```

**`routes/web.php`** — full auth routing with middleware applied per route:

```php
<?php

/** @var \App\Core\Router $router */

use App\Middlewares\{AuthMiddleware, GuestMiddleware, CsrfMiddleware};

// ---- Public marketing site ----
$router->get('/', 'Public\HomeController@index');
$router->get('/about', 'Public\AboutController@index');
$router->get('/contact', 'Public\ContactController@index');
$router->post('/contact', 'Public\ContactController@submit', [CsrfMiddleware::class]);
$router->get('/blog', 'Public\BlogController@index');
$router->get('/blog/{slug}', 'Public\BlogController@show');

// ---- Authentication ----
$router->get('/login', 'Auth\LoginController@index', [GuestMiddleware::class]);
$router->post('/login', 'Auth\LoginController@submit', [GuestMiddleware::class, CsrfMiddleware::class]);
$router->post('/logout', 'Auth\LoginController@logout', [AuthMiddleware::class]);

$router->get('/register', 'Auth\RegisterController@index', [GuestMiddleware::class]);
$router->post('/register', 'Auth\RegisterController@submit', [GuestMiddleware::class, CsrfMiddleware::class]);

$router->get('/forgot-password', 'Auth\PasswordResetController@showRequestForm', [GuestMiddleware::class]);
$router->post('/forgot-password', 'Auth\PasswordResetController@submitRequest', [GuestMiddleware::class, CsrfMiddleware::class]);
$router->get('/reset-password/{token}', 'Auth\PasswordResetController@showResetForm', [GuestMiddleware::class]);
$router->post('/reset-password/{token}', 'Auth\PasswordResetController@submitReset', [GuestMiddleware::class, CsrfMiddleware::class]);

// ---- Authenticated (placeholder until Phase 8 builds the real shell) ----
$router->get('/dashboard', 'User\DashboardController@index', [AuthMiddleware::class]);
```

**Note the contact form's route also picked up `CsrfMiddleware` this phase** — it didn't have it in Phase 6, and should have from the start; fixed here since this was the first time `CsrfMiddleware` actually got built out enough to apply.

**A minimal placeholder `app/Controllers/User/DashboardController.php` was added purely so `AuthMiddleware`/`GuestMiddleware` redirects have somewhere real to land during testing** — it's explicitly not real, and gets replaced entirely once Phase 8 builds the actual authenticated shell.

---

## P7.2 — Auth Pages

**File: `resources/layouts/auth.php`** — a simple centered-card layout, no marketing nav, built fresh this phase since nothing like it existed before.

**Files: `resources/auth/login.php`, `register.php`, `forgot-password.php`, `reset-password.php`, `reset-password-invalid.php`** — all follow the established `ob_start()` → content → `require '../layouts/auth.php'` pattern, each wiring `P5.6`'s `FormValidator` client-side and rendering server-flashed errors when JavaScript never ran at all. Full contents in the delivered file set.

**One thing worth calling out in the register page specifically:** the password strength meter from `P5.7` is wired in for real here, not just demonstrated in isolation:

```
Weak password ('weak')                    -> .password-strength-fill.weak
Strong password ('Str0ng!Pass99Long')     -> .password-strength-fill.strong
Password visibility toggle                -> input type switches password -> text
No horizontal overflow at 480px viewport  -> scrollWidth stayed at 480
```

---

## P7.3 — Middleware Coverage Confirmed

Every auth route's middleware assignment was verified by actually exercising it, not just reading the route table:

| Route | Middleware | Verified behavior |
|---|---|---|
| `GET /login`, `GET /register` | `GuestMiddleware` | A logged-in session is redirected to `/dashboard` |
| `POST /login`, `POST /register`, `POST /forgot-password`, `POST /reset-password/{token}` | `GuestMiddleware` + `CsrfMiddleware` | Both a missing and a forged token return `419`; a logged-in session can't reach these either |
| `POST /logout` | `AuthMiddleware` | Only reachable while logged in |
| `GET /dashboard` | `AuthMiddleware` | A logged-out session is redirected to `/login`, and back to `/dashboard` automatically after logging in |

---

## Phase 7 Exit Checklist

- [ ] `resources/layouts/` and `resources/components/` convention confirmed and matched — no drift back to project-root `layouts/`/`components/` in future phases
- [ ] Registration creates a real bcrypt-hashed password, a unique account number, and auto-logs the user in
- [ ] Login rejects wrong password and nonexistent email with the **identical** generic message — verified, not just implemented
- [ ] `GuestMiddleware` and `AuthMiddleware` both verified via real redirect behavior, not just code review
- [ ] CSRF verified to reject both a missing and a forged token
- [ ] Password reset verified end to end: request → real token generated → reset page loads → password updates → old password fails → new password works → **token confirmed single-use**
- [ ] `Session::forget()`/`destroy()` restored — missing from the copy this build started from
- [ ] Contact form (`P6.5`) retroactively gained `CsrfMiddleware` — it should have had it from Phase 6, fixed here
- [ ] Placeholder `DashboardController` clearly marked as temporary, to be fully replaced by `P8.1`

---

*End of Phase 7 Runbook. Next: Implementation Plan Phase 8 (`P8.1`–`P8.4`) — the Authenticated Shell, which replaces today's placeholder dashboard with the real desktop sidebar / mobile bottom-tab layout every user and admin page depends on.*
