# NovaTrust — Phase 0 Runbook: Decisions, Environment & Safety Net

**Version:** 1.0
**Prepared for:** Wynston
**Companion to:** `novatrust-implementation-plan.md` v1.3, Phase 0 (`P0.1`–`P0.19`)
**Scope:** step-by-step, copy-pasteable instructions for every Phase 0 task — the action items with real commands and code, and a structured decision brief for every item that's a decision rather than a build task.
**Verified against:** `github.com/W3BNEEK3/payprotect-nova` (live repo, direct inspection)

---

## How to Use This Document

Phase 0 has two kinds of tasks, and they need different things from this runbook:

- **Action tasks** (`P0.1`, `P0.2`, `P0.3`, `P0.16`, `P0.17`, `P0.18`, `P0.19`) get full step-by-step instructions with real code and exact file paths, ready to execute in order.
- **Decision tasks** (`P0.4`–`P0.15`) have no code to write — they're choices that block later phases. Each gets a short brief: what's being decided, why it matters, and the options, so whoever owns this can actually close it out in one sitting instead of re-deriving the context from three other documents first.

Do these roughly in the order below — `P0.1`–`P0.3` are urgent and independent of everything else; `P0.16`–`P0.19` need to happen before Phase 1 can start; the decisions in between can happen in parallel with either.

**One honest caveat before you start:** I verified the exact current contents of `database/db.php` and confirmed `config/config.php` matches the same connection pattern by variable-name inspection, but I did not print or work from the live credential values themselves — no secret values appear anywhere in this document, including in "before" code samples, which use placeholder text instead. I have **not** seen the full contents of `config/mail_config.php` beyond confirming it repeatedly assigns to a `$mail` variable (9 occurrences), which is consistent with a standard PHPMailer setup but isn't a guarantee of its exact structure. `P0.2`'s code below is a reasonable reconstruction of that pattern — reconcile it against the real file before replacing it, don't paste over it blind.

---

## P0.1 — Rotate DB Credentials

**Why urgent:** the live database password is currently plaintext, duplicated in two files, in a repository that may have been public. Every hour this sits unrotated is exposure time you don't get back.

### Step 1 — Generate new credentials at the host

This is a cPanel/CloudLinux host (`cll-lve` build, confirmed in the DB export header), so this happens in cPanel, not in code:

1. Log into cPanel → **MySQL Databases**.
2. Under **Current Users**, either reset the existing database user's password (simplest — keeps the same username, no privilege re-grant needed) or create a new user and grant it identical privileges on `harmony1_novar_DB`, then delete the old user once the cutover is confirmed working.
3. Generate a strong, random password via cPanel's built-in generator — don't hand-type one.
4. Write down the new host, database name, username, and password somewhere secure (a password manager, not a text file in the repo).

### Step 2 — Create `.env` at the project root

**File: `.env`** (project root, alongside `composer.json`)

```env
DB_HOST=localhost
DB_NAME=harmony1_novar_DB
DB_USER=your_new_db_username
DB_PASS=your_new_db_password
DB_CHARSET=utf8mb4
```

### Step 3 — Confirm `.env` is git-ignored

**File: `.gitignore`** (project root — check if this file exists at all first; it wasn't in the top-level listing confirmed during the consistency review, so it may need creating)

```gitignore
.env
vendor/
*.log
```

Run this immediately after creating/editing it, **before** the `.env` file itself is ever committed:

```bash
git status
# .env should NOT appear in the output. If it does, .gitignore isn't working yet — fix it
# before continuing to Step 4.
```

### Step 4 — Commit `.env.example`

**File: `.env.example`** (project root — this one *is* committed, with placeholder values only)

```env
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_username
DB_PASS=your_database_password
DB_CHARSET=utf8mb4
```

### Step 5 — Build the interim `.env` loader

This is deliberately minimal — a few lines, not a class. `Core/EnvLoader.php` (the real, permanent version) gets built properly in Phase 1 (`P1.2`); this file is retired the moment that lands.

**File: `bootstrap/env.php`** (new file — the `bootstrap/` directory doesn't exist yet either; create it)

```php
<?php
/**
 * bootstrap/env.php — interim .env loader, Phase 0 only.
 *
 * This is deliberately minimal. It exists solely to get plaintext credentials out of
 * source control immediately, without waiting on Phase 1's full framework build.
 *
 * SUPERSEDED BY: app/Core/EnvLoader.php (Implementation Plan P1.2).
 * Do not add features to this file — when Phase 1 lands, delete it and update the two
 * call sites below (config/config.php, config/mail_config.php) to use the real class.
 */

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                ".env file not found at {$path}. Copy .env.example to .env and fill in real values."
            );
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and blank lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip surrounding quotes if present
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value !== false && $value !== null ? $value : $default;
    }
}
```

### Step 6 — Patch `config/config.php`

**Before** (illustrative — placeholder value shown, not the real password):

```php
<?php
$host = 'localhost';
$db   = 'harmony1_novar_DB';
$user = 'harmony1_novar_DB';
$pass = 'PLACEHOLDER_DO_NOT_USE_REAL_VALUE_HERE';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die('Connection failed:' . $e->getMessage());
}
```

**After** — **File: `config/config.php`**

```php
<?php
require_once __DIR__ . '/../bootstrap/env.php';
loadEnv(__DIR__ . '/../.env');

$host    = env('DB_HOST');
$db      = env('DB_NAME');
$user    = env('DB_USER');
$pass    = env('DB_PASS');
$charset = env('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
```

### Step 7 — Collapse `database/db.php` into a shim

`database/db.php` currently duplicates `config/config.php`'s connection code with a second hardcoded copy of the same credentials. Rather than patch both files with the loader independently (which keeps the duplication, just with `.env` instead of hardcoded values), collapse it to a single include now. This is an interim de-duplication at the file level — `Core/Database.php` in Phase 1 (`P1.3`) is what formally establishes the single real connection *class*; this shim just stops the immediate bleeding.

**File: `database/db.php`** (replace entire contents)

```php
<?php
/**
 * database/db.php — temporary shim, Phase 0.
 *
 * This file previously duplicated config/config.php's PDO connection logic with a
 * second hardcoded copy of the live credentials. It now just includes the single
 * source of truth instead of maintaining two copies.
 *
 * SUPERSEDED BY: app/Core/Database.php (Implementation Plan P1.3).
 */

require_once __DIR__ . '/../config/config.php';
// $conn is now available here exactly as it was before — no call site elsewhere
// in admin/ or user/ needs to change for this step.
```

### Step 8 — Verify, then confirm nothing broke

```bash
# From the project root, start PHP's built-in server against the real files:
php -S localhost:8000

# In another terminal, hit a page that uses the DB connection:
curl -I http://localhost:8000/user/login.php
# Expect a normal HTTP response, not a 500 or a PDO connection error.
```

Then manually load `login.php` and `admin/admin_login.php` in a browser and confirm both render without a fatal error. If either fails, check `php -S`'s terminal output for the exact line — almost always a typo in a `.env` key name.

### Step 9 — Confirm the old credentials are dead

Back in cPanel, once the site is confirmed working against the new credentials, delete the *old* database user entirely (not just change its password) if you created a new one in Step 1, or confirm the reset password from Step 1 is the only one that works.

**Done-when:** `.env` holds the only copy of the credentials, `.env` is confirmed git-ignored, `config/config.php` and `database/db.php` both use it, the site loads correctly, and the old credentials no longer work against the database.

---

## P0.2 — Rotate SMTP Credentials

Same interim pattern as `P0.1`. **Adapt this to the actual current structure of `config/mail_config.php` before replacing it** — see the caveat at the top of this document.

### Step 1 — Generate new SMTP credentials

At your email/SMTP provider (or cPanel Email Accounts, if the mailbox is hosted there), reset the password on the mailbox `config/mail_config.php` currently authenticates as, or create a new mailbox and update the "from" address to match.

### Step 2 — Add SMTP values to `.env`

**File: `.env`** (append to the file created in `P0.1`)

```env
SMTP_HOST=your.smtp.host
SMTP_PORT=587
SMTP_USER=your_smtp_username
SMTP_PASS=your_new_smtp_password
SMTP_ENCRYPTION=tls
SMTP_FROM_ADDRESS=noreply@novatrust.example
SMTP_FROM_NAME=NovaTrust
```

**File: `.env.example`** (append matching placeholder keys)

```env
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=your_smtp_username
SMTP_PASS=your_smtp_password
SMTP_ENCRYPTION=tls
SMTP_FROM_ADDRESS=noreply@example.com
SMTP_FROM_NAME=Your App Name
```

### Step 3 — Patch `config/mail_config.php`

**Reconstructed pattern** (verify against the real file's actual structure before replacing — the `$mail` variable naming here is a best-effort match to what was confirmed present, not a byte-for-byte replica of the original):

```php
<?php
/**
 * config/mail_config.php
 *
 * SUPERSEDED BY: app/Providers/Mail/* + MailProviderFactory (Implementation Plan
 * P4.4–P4.5, wired into the admin UI in P14). Once that lands, these values move
 * again — out of .env entirely and into the mail_settings table.
 */

require_once __DIR__ . '/../bootstrap/env.php';
loadEnv(__DIR__ . '/../.env');
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = env('SMTP_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('SMTP_USER');
    $mail->Password   = env('SMTP_PASS');
    $mail->SMTPSecure = env('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
    $mail->Port       = (int) env('SMTP_PORT', 587);

    $mail->setFrom(env('SMTP_FROM_ADDRESS'), env('SMTP_FROM_NAME', 'NovaTrust'));

    return $mail;
}
```

**Important:** search the codebase for every place `mail_config.php` is currently included and how it's used, before assuming the `getMailer()` function signature above matches:

```bash
grep -rn "mail_config" --include="*.php" admin/ user/ | grep -v vendor
```

If existing call sites expect a bare `$mail` object already configured (rather than calling a function that returns one), keep that same calling convention — wrap the code above so the last few lines still produce a ready-to-use `$mail` variable in the including file's scope, rather than changing every call site in the same pass as this credential fix. Consolidating fully into the Provider pattern is Phase 4/14's job, not Phase 0's.

### Step 4 — Send a test email and verify

```bash
php -r '
require "config/mail_config.php";
$mail = getMailer();
$mail->addAddress("your-test-address@example.com");
$mail->Subject = "NovaTrust SMTP rotation test";
$mail->Body    = "If you receive this, the new SMTP credentials work.";
$mail->send() ? print("Sent.\n") : print("Failed: " . $mail->ErrorInfo . "\n");
'
```

**Done-when:** the test email arrives, `.env` holds the only copy of the SMTP credentials, and the old mailbox password no longer authenticates.

---

## P0.3 — Confirm Repository Exposure History

This is an investigation task — the output is a written finding, not code, but it needs real commands to produce.

### Step 1 — Check current visibility

```bash
curl -s https://api.github.com/repos/W3BNEEK3/payprotect-nova | grep -i '"private"'
# "private": false  → currently public
# "private": true   → currently private
```

### Step 2 — Check whether it was ever public, even if it's private now

GitHub doesn't expose a direct "was this ever public" API for a repo you don't own the audit log for. Practical approach:

```bash
# Check for any external forks or stars — these can only exist if the repo was public
# at some point, and forks retain their own copy of history even after the source
# goes private.
curl -s https://api.github.com/repos/W3BNEEK3/payprotect-nova | grep -E '"forks_count"|"stargazers_count"|"watchers_count"'
curl -s https://api.github.com/repos/W3BNEEK3/payprotect-nova/forks
```

If `forks_count` is greater than 0, or any forks are listed, **treat the credentials as permanently compromised regardless of what P0.1/P0.2 just did** — a fork made while the repo was public retains that commit history independently, outside your control, even after you rotate credentials and even after you make the source repo private.

### Step 3 — Search the commit history for exactly when the credentials were introduced

```bash
git log --all --full-history --oneline -- config/config.php
git log --all --full-history --oneline -- database/db.php
git log --all --full-history --oneline -- config/mail_config.php
```

This establishes the exposure window — useful context even if the repo was never public, since anyone with legitimate repo access during that window also saw the plaintext values.

### Step 4 — Record the finding

Write the outcome into `SECURITY_NOTES.md` at the project root (new file, committed):

```markdown
# Security Notes

## Credential Rotation — [date]

- Repository visibility at time of review: [public/private]
- Forks found: [count/list, or "none"]
- Exposure window (first commit of plaintext credentials → rotation date): [dates]
- Conclusion: [e.g. "Repo has always been private, no forks found — credentials
  rotated as a precaution per standard practice, not confirmed exposure" / "Repo
  was public between [dates] — treat old credentials as permanently compromised
  even though rotated"]
```

**Done-when:** `SECURITY_NOTES.md` exists with a dated, factual finding — not a guess.

---

## P0.4 – P0.15 — Decisions (No Code — Close These Out in One Sitting)

Each of these blocks a later phase and has no build work of its own. Answer them here, then update `novatrust-implementation-plan.md`'s Phase 0 table directly with the recorded decision so it isn't lost.

| ID | Decide | Why it matters | What blocks on it |
|---|---|---|---|
| **P0.4** | Hosting target: stay on shared/cPanel, or move to a VPS/cloud host? | Shared hosting caps what's realistic for polling intervals, background jobs (cron availability/frequency), and concurrent connections. A VPS removes those ceilings but adds server-management overhead you don't have today. | `P13.6`'s chat transport choice, `P13.5`'s cron mechanism, `P17.5`'s performance ceiling |
| **P0.5** | `virtual_cards.balance` — drop it entirely, or formalize it as a real, displayed feature? | If it's unused today, decide now rather than let it linger as a confusing half-feature. | `P9.5` |
| **P0.6** | Admin roles — split into Support/Compliance/Super Admin now, or keep one flat admin role? | Changes `AdminMiddleware`'s logic and the `admins` table schema. Cheaper to decide before `P1.18` builds the middleware than to retrofit after. | `P1.18`, `P8` |
| **P0.7** | Does `app/Core/Router.php` (once built in `P1.5`) need multi-route-file support, or is a prefix group inside `web.php` sufficient? | Since the router is being built from scratch in Phase 1, this is really "which pattern do you want built," not "check what exists." Decide the target shape now. | `P1.19` |
| **P0.8** | Alpine.js — adopt it, or stay pure vanilla JS throughout? | Affects how much of Phase 5's component work (modals, toasts, validation) leans on a library vs. hand-rolled JS. | `P5.10` and every component task in Phase 5 |
| **P0.9** | Blog: static hardcoded array (moved out of markup, but still static), or DB-driven with a small admin CMS? | Only matters if you expect to publish more than the current ~5 posts, or want non-technical staff editing them. | `P6.4` |
| **P0.10** | Keep the `CardIssuerInterface` seam for a hypothetical future real card issuer, or skip the abstraction and hard-code the simulated path? | Cheap to build now, awkward to retrofit later if you do eventually want a real issuer (Stripe Issuing, Marqeta). Costs almost nothing either way at this stage. | `P1.17`, `P4.1` |
| **P0.11** | "New account" threshold for compliance auto-flagging — 30 days (proposed default), or a different number? | Directly determines who gets auto-flagged. Should reflect actual fraud-pattern experience if you have any, not just a guess. | `P10.1` |
| **P0.12** | $7,000 auto-flag check: cumulative-while-new (proposed, safer default), or single-transaction only? | Cumulative catches structuring (e.g. $4k + $4k to dodge a single-transaction threshold); single-transaction is simpler but has an obvious gap. | `P10.1` |
| **P0.13** | Withdrawal completion: admin-release step (proposed default) or instant auto-debit once gates clear? | **The highest-impact decision in the whole plan** — changes `P11.4`'s entire data flow, not just a detail within it. Confirm this matches how payouts actually happen operationally before `P11` is built around the wrong assumption. | `P11` entirely |
| **P0.14** | Unclaimed-chat escalation threshold — 2 minutes (proposed), or different? | Too short and agents get spammed with email escalations for conversations they were about to claim anyway; too long and users wait needlessly. | `P13.5` |
| **P0.15** | Google Fonts / Material Symbols — CDN-linked, or self-hosted? | Self-hosting avoids a third-party request on every page load (helps performance and removes a Google dependency) but means manually updating font files when weights change. CDN is zero-maintenance but adds an external request. | `P5.1` |

---

## P0.16 — Staging Environment Bootstrap

### Step 1 — Provision a host matching production

Match the confirmed production signature as closely as your hosting provider allows: **PHP 8.1.30**, **MariaDB 10.6.20**, ideally a `cll-lve`/CloudLinux cPanel environment. If an exact match isn't available, get as close as possible on PHP and MariaDB major.minor versions specifically — those are the two most likely to introduce behavioral differences.

### Step 2 — Clone the repo

```bash
cd /path/to/staging/webroot
git clone https://github.com/W3BNEEK3/payprotect-nova.git .
```

### Step 3 — Install dependencies

```bash
composer install
```

### Step 4 — Set up `.env` for staging

```bash
cp .env.example .env
```

Then edit `.env` with the **staging** database credentials (never reuse production credentials on staging):

```env
DB_HOST=localhost
DB_NAME=novatrust_staging
DB_USER=staging_db_user
DB_PASS=staging_db_password
DB_CHARSET=utf8mb4

SMTP_HOST=your.smtp.host
SMTP_PORT=587
SMTP_USER=staging-or-shared-smtp-user
SMTP_PASS=staging_smtp_password
SMTP_ENCRYPTION=tls
SMTP_FROM_ADDRESS=noreply@staging.novatrust.example
SMTP_FROM_NAME=NovaTrust (Staging)
```

### Step 5 — Import the production export into staging

Using the export from `P0.17` (see below — do `P0.17` first if you haven't yet):

```bash
mysql -h localhost -u staging_db_user -p novatrust_staging < novatrust_backup_YYYYMMDD_HHMMSS.sql
```

### Step 6 — Load the site and verify it actually works

```bash
# If using PHP's built-in server for a quick check:
php -S localhost:8000

# Or configure the staging vhost/domain per your host's normal process, then:
curl -I https://staging.novatrust.example/user/login.php
```

Then manually, in a browser:
1. Load the homepage (`index.php`) — confirms basic PHP execution and DB connectivity.
2. Log in with a real (staging-copied) user account — confirms session handling and the `users` table query path.
3. Load the dashboard — confirms a page that reads multiple related tables.
4. Load at least one withdrawal method page (e.g. `user/withdraw_bank.php`) — confirms the deepest, most business-critical page renders without error.

**Done-when:** all four checks in Step 6 pass with no PHP errors or blank pages. Do not proceed to write any migration against this environment until this step is green.

---

## P0.17 — Production Backup + Documented Restore Procedure

### Step 1 — Take the export

```bash
mysqldump \
  -h localhost \
  -u your_admin_db_user \
  -p \
  --single-transaction \
  --quick \
  --lock-tables=false \
  harmony1_novar_DB > novatrust_backup_$(date +%Y%m%d_%H%M%S).sql
```

`--single-transaction` avoids locking live tables during the export — important since this runs against a database with real users actively transacting on it. `--quick` streams rows instead of buffering the whole result set in memory, which matters on shared hosting's memory limits.

### Step 2 — Compress and verify

```bash
gzip novatrust_backup_*.sql
ls -lh novatrust_backup_*.sql.gz

# Sanity-check the export isn't empty or truncated:
gunzip -c novatrust_backup_*.sql.gz | grep -c "INSERT INTO"
# Should return a healthy number (dozens+) — zero means the export failed silently.
```

### Step 3 — Store it outside the app server

```bash
# Example: copy to a separate, access-controlled machine
scp novatrust_backup_*.sql.gz your-user@backup-host:/secure/backups/novatrust/

# Or upload to private cloud storage — adjust to whatever you actually have available:
# aws s3 cp novatrust_backup_*.sql.gz s3://your-private-backup-bucket/novatrust/ --sse
```

### Step 4 — Write down the restore procedure now, before anyone needs it under pressure

**File: `RESTORE_PROCEDURE.md`** (project root, or wherever your ops docs live — committed)

```markdown
# NovaTrust — Database Restore Procedure

## When to use this
Production database corruption, a bad migration, or any incident requiring rollback
to a known-good state.

## Steps

1. Identify the most recent verified-good backup:
   `ls -lht /secure/backups/novatrust/`

2. Copy it to the target server:
   `scp your-user@backup-host:/secure/backups/novatrust/novatrust_backup_XXXXXXXX.sql.gz .`

3. Decompress:
   `gunzip novatrust_backup_XXXXXXXX.sql.gz`

4. **Stop the application** (maintenance mode / take the site offline) before restoring —
   restoring into a live database being actively written to will corrupt the restore.

5. Restore:
   `mysql -h localhost -u your_admin_db_user -p harmony1_novar_DB < novatrust_backup_XXXXXXXX.sql`

6. Verify: log in as a known test/admin user, check a known transaction total,
   confirm `migrate:status` (once Phase 1 lands) matches what's expected for this
   backup's point in time.

7. Bring the application back online.

## Who can do this
[Names/roles — fill in]

## Last tested
[Date — restore procedures that have never been tested against a real restore
should not be trusted; run this against staging at least once before relying on it]
```

**Done-when:** the backup exists in two places (app server export + off-server copy), and `RESTORE_PROCEDURE.md` is committed and has actually been tested once against staging, not just written.

---

## P0.18 — Git Branching Model

### Step 1 — Establish the convention

```bash
# main = production, exactly as it is today
git checkout main
git pull

# develop = integration branch, where phase work lands before going to main
git checkout -b develop
git push -u origin develop
```

### Step 2 — Feature branch naming, one per phase (or per task, for larger phases)

```bash
# Example for the first real piece of work after Phase 0:
git checkout develop
git checkout -b feature/p1-framework-core
# ... do the work for P1.1–P1.11 ...
git push -u origin feature/p1-framework-core
# Open a PR into develop, not main, for review before it merges
```

### Step 3 — Document the convention

**File: `CONTRIBUTING.md`** (project root, new file, committed)

```markdown
# Branching Convention

- `main` — production. Only merges from `develop`, only during a deployment window (Phase 18).
- `develop` — integration branch. Feature branches merge here first.
- `feature/<phase-or-task-id>-<short-description>` — one branch per phase or major task,
  e.g. `feature/p1-framework-core`, `feature/p10-compliance-engine`.
- No direct commits to `main` or `develop` — every change goes through a branch and a
  pull request, even for a single-person team, since it keeps a reviewable record of
  what changed and why.
```

**Done-when:** `develop` exists and is pushed, `CONTRIBUTING.md` is committed, and the first feature branch (`feature/p1-framework-core`, ready for Phase 1) exists off `develop`.

---

## P0.19 — Behavior Orientation

This is a reading task — read the real files below in order, in a browser and an editor side by side, then fill in the template at the end. The point isn't documentation for its own sake; it's making sure Phase 1's `Services/`/`Repositories/` design actually matches what the system does today, not an assumed version of it.

### Read in this order

1. **`database/db.php`** and **`config/config.php`** (post-`P0.1`) — confirm you understand the connection pattern before reading anything that uses it.
2. **`user/login.php`** → **`user/login_process.php`** — the simplest full request/response cycle in the app; a good warm-up before the more complex flows.
3. **`user/withdraw.php`** → **`user/process_withdraw.php`** — the real withdrawal gate sequence, end to end. Note every `if` that blocks progress (card status, KYC, compliance code) and the exact order they're checked in — this is what `WithdrawalGate` (`P11.1`) has to reproduce.
4. **`admin/admin_send_money.php`** — today's only funding path. Note exactly what it writes to `transactions` and `users.balance`, and in what order (does it update balance before or after logging the transaction row? — matters for `RefundRepository`/`TransactionRepository`'s design in Phase 3).
5. **`admin/approve_virtual_card.php`** vs. **`user/generate_card.php`** — the two-path bug named in the SADD's Section 0. Confirm which one is actually live/reachable from the UI today, and which is orphaned.
6. **`user/withdraw_bank.php`** (or any one other `withdraw_*.php` file) — confirms the pattern that all eight method-specific forms currently share, and exactly what they're missing (per the SRS, the destination fields are collected but never persisted).

### Fill in this template

**File: `BEHAVIOR_NOTES.md`** (project root, committed — this becomes Phase 1/3's reference alongside the SRS)

```markdown
# NovaTrust — Current Behavior Notes (Pre-Framework)

Written by: [name]
Date: [date]
Purpose: confirms the new Services/Repositories layer (Phase 3, Phase 11) matches
real current behavior, not an assumed version of it.

## Withdrawal gate sequence (from user/withdraw.php + process_withdraw.php)
1. [first check]
2. [second check]
3. [etc.]

## Crediting flow (from admin/admin_send_money.php)
- Balance updated: [before/after] the transaction row is written
- Fields written to `transactions`: [list]

## Virtual card issuance — which path is actually live?
- [ ] admin/approve_virtual_card.php is the real, reachable path
- [ ] user/generate_card.php is confirmed orphaned (not linked from any reachable UI)

## Anything else observed that isn't in the SRS
[Any behavior found while reading that the SRS doesn't mention — flag it back to that
document rather than silently building around it]
```

**Done-when:** `BEHAVIOR_NOTES.md` is committed and every section is actually filled in from reading the real files — not left as placeholders.

---

## Phase 0 Exit Checklist

Before Phase 1 (`P1.1`) starts, confirm every item below:

- [ ] `P0.1` — DB credentials rotated, `.env`-based, old credentials confirmed dead
- [ ] `P0.2` — SMTP credentials rotated, `.env`-based, old credentials confirmed dead
- [ ] `P0.3` — `SECURITY_NOTES.md` committed with a dated exposure finding
- [ ] `P0.4`–`P0.15` — every decision recorded (this document's table, copied into the Implementation Plan)
- [ ] `P0.16` — staging environment live, all four verification checks passing
- [ ] `P0.17` — backup taken, stored off-server, `RESTORE_PROCEDURE.md` committed and tested once
- [ ] `P0.18` — `develop` branch live, `CONTRIBUTING.md` committed
- [ ] `P0.19` — `BEHAVIOR_NOTES.md` committed and complete

---

*End of Phase 0 Runbook. Next: Implementation Plan Phase 1 (`P1.1`–`P1.19`) — the real framework scaffolding, built against the staging environment this runbook stood up.*
