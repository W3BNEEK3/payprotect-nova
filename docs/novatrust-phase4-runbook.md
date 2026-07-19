# NovaTrust — Phase 4 Runbook: Providers

**Version:** 1.0
**Prepared for:** Wynston
**Companion to:** `novatrust-implementation-plan.md` v1.4, Phase 4 (`P4.1`–`P4.7`)
**Scope:** Card, Mail, and Chat providers, plus the one-time card-encryption backfill script.
**Verified:** every file lints clean. 32 assertions run against a real MariaDB instance and, for the backfill script specifically, an actual subprocess execution (not a function call — the real `php database/backfill_card_encryption.php` command, exactly as it would run in production) — all passing. Coverage includes a deliberate tampering test against the encryption (confirms corruption is detected, not silently decrypted into garbage) and a full encrypt → store → fetch → decrypt round trip for both new-card issuance and the backfill path.

---

## A Gap Found While Building This

`Helpers/Crypto.php` is referenced by `P4.1`, `P4.3`, and `P4.5` — but no task in Phases 1–3 ever built it. The SADD's Section 4 directory tree always listed it (`Helpers/ [NEW — Crypto, Date, Html, Str, Url, Validator, Money]`), and `Money.php` got its own explicit task (`P1.15`), but `Crypto.php` never did. This phase builds it as part of `P4.1`, since that's the first task that actually needs it — flagged here rather than silently added, the same treatment `Core/Repository.php` got in the Phase 3 runbook.

---

## P4.1 — `Helpers/Crypto.php` + `Providers/Cards/SimulatedCardProvider.php`

### `Helpers/Crypto.php` (new, closing the gap above)

**File: `app/Helpers/Crypto.php`**

```php
<?php

namespace App\Helpers;

/**
 * AES-256-GCM authenticated encryption. GCM mode is deliberate over CBC — it
 * detects tampering (a corrupted or truncated ciphertext fails to decrypt
 * loudly, rather than silently producing garbage plaintext), which matters
 * for card data specifically.
 */
class Crypto
{
    private const CIPHER = 'aes-256-gcm';

    private static function key(): string
    {
        $key = env('APP_ENCRYPTION_KEY');

        if (!$key) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY is not set in .env');
        }

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (strlen($key) !== 32) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY must decode to exactly 32 bytes for AES-256');
        }

        return $key;
    }

    /**
     * Returns raw binary (iv + auth tag + ciphertext, concatenated) — safe to
     * store directly in a VARBINARY column via a prepared statement. Do not
     * treat the return value as a UTF-8 string.
     */
    public static function encrypt(string $plaintext): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);
        $tag = '';

        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return $iv . $tag . $ciphertext;
    }

    public static function decrypt(string $binary): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($binary, 0, $ivLength);
        $tag = substr($binary, $ivLength, 16);
        $ciphertext = substr($binary, $ivLength + 16);

        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed — data may be corrupted, truncated, or the key is wrong');
        }

        return $plaintext;
    }
}
```

**Setup required — add to `.env` (and `.env.example` with a placeholder) before this runs:**

```env
APP_ENCRYPTION_KEY=base64:REPLACE_WITH_A_REAL_GENERATED_KEY
```

Generate a real key with:

```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

**This key is as sensitive as the database password.** Losing it means every encrypted card number and CVV becomes permanently unrecoverable — there is no "forgot my key" recovery path with authenticated encryption, by design. Back it up somewhere as secure as the credentials from `P0.1`, separately from the `.env` file itself.

**Verified — including a deliberate tampering test:**
```
PASS — Crypto round-trips correctly for '4000123456789012'
PASS — Crypto round-trips correctly for '000'
PASS — Crypto round-trips correctly for 'a'
PASS — Crypto round-trips correctly for a 200-char string
PASS — Crypto round-trips correctly for ''
PASS — Crypto ciphertext is never equal to the plaintext (actually encrypted, not passthrough)
PASS — Crypto detects tampering and throws rather than returning corrupted plaintext
```
The tampering test flips a single bit in a real ciphertext and confirms `decrypt()` throws rather than silently returning corrupted data — this is GCM's authentication tag doing its job, and it's the reason GCM was chosen over CBC.

### `Providers/Cards/SimulatedCardProvider.php`

**File: `app/Providers/Cards/SimulatedCardProvider.php`**

```php
<?php

namespace App\Providers\Cards;

use App\Interfaces\CardIssuerInterface;
use App\Helpers\Crypto;
use App\Models\VirtualCard;

/**
 * Consolidates the two disagreeing card-issuance paths found during the SADD
 * review (admin/approve_virtual_card.php vs the orphaned user/generate_card.php)
 * into one path (P4.2). New cards write ONLY to the encrypted columns — the
 * plaintext card_number/cvv columns are left NULL going forward. Existing cards
 * with plaintext-only data get caught up by the one-time backfill script (P4.3),
 * not by this class.
 */
class SimulatedCardProvider implements CardIssuerInterface
{
    public function issue(int $userId): array
    {
        $cardNumber = $this->generateCardNumber();
        $expiry = $this->generateExpiry();
        $cvv = $this->generateCvv();

        VirtualCard::create([
            'user_id' => $userId,
            'card_number_encrypted' => Crypto::encrypt($cardNumber),
            'cvv_encrypted' => Crypto::encrypt($cvv),
            'expiry_date' => $expiry,
            'status' => 'active',
            'is_virtual_card_approved' => 0, // still requires admin approval, FR-4.2
        ]);

        return [
            'number' => $cardNumber,
            'expiry' => $expiry,
            'cvv' => $cvv,
        ];
    }

    private function generateCardNumber(): string
    {
        // Simulated card — a Luhn-valid 16-digit PAN using a prefix that is
        // deliberately NOT a real issuer BIN range, so it can never be mistaken
        // for or accidentally processed as a genuine card.
        $prefix = '4000';
        $rest = '';

        for ($i = 0; $i < 11; $i++) {
            $rest .= random_int(0, 9);
        }

        $partial = $prefix . $rest;

        return $partial . $this->luhnCheckDigit($partial);
    }

    private function luhnCheckDigit(string $number): int
    {
        $sum = 0;
        $alternate = true;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];

            if ($alternate) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }

            $sum += $n;
            $alternate = !$alternate;
        }

        return (10 - ($sum % 10)) % 10;
    }

    private function generateExpiry(): string
    {
        return (new \DateTimeImmutable())->modify('+3 years')->format('m/y');
    }

    private function generateCvv(): string
    {
        return str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
    }
}
```

A Luhn check digit wasn't strictly required by any SRS requirement, but a simulated card that fails basic Luhn validation would look broken in any UI component or third-party validation library that checks it — cheap to get right, so it's included.

**Verified — the full path, not just number generation in isolation:**
```
PASS — issue() returns a 16-digit card number
PASS — issue() returns an expiry in mm/yy format
PASS — issue() returns a 3-digit CVV
PASS — Generated card number passes Luhn validation
PASS — New card row has NULL plaintext card_number (encrypted-only for new cards)
PASS — New card row has encrypted columns populated
PASS — Decrypting the stored encrypted card number matches what issue() returned
PASS — Decrypting the stored encrypted CVV matches what issue() returned
PASS — New card is NOT auto-approved (still requires admin approval per FR-4.2)
```

---

## P4.2 — Consolidate the Two Card-Issuance Paths

No new code — this is a deletion task. Per the SADD's Section 0 grounding note, two disagreeing paths currently exist:

- `admin/approve_virtual_card.php` — the real, reachable path
- `user/generate_card.php` — orphaned, confirmed not linked from any reachable UI during the SRS/SADD review

**Steps:**

```bash
git rm user/generate_card.php
```

`admin/approve_virtual_card.php`'s responsibility moves into `SimulatedCardProvider::issue()` (above) plus the new `VirtualCardController` built in Phase 9 — after Phase 9 lands, `admin/approve_virtual_card.php` itself also gets retired (tracked under `P19.4`'s final cleanup, not this task, since Phase 9 doesn't exist yet at this point in the plan).

**Done-when:** `git log --all --full-history -- user/generate_card.php` still shows the file's history (never rewrite git history to hide it — the record that this bug existed and was fixed is worth keeping), but the file itself is gone from the working tree, and nothing in the codebase references it.

---

## P4.3 — One-Time Card Encryption Backfill

**File: `database/backfill_card_encryption.php`**

```php
<?php
/**
 * One-time backfill: encrypts existing plaintext card_number/cvv values into the
 * encrypted columns added by migration 017. Safe to run more than once — only
 * touches rows where the encrypted columns are still NULL, so a second run finds
 * nothing left to do.
 */
require __DIR__ . '/../vendor/autoload.php';

use App\Core\{EnvLoader, Database};
use App\Repositories\VirtualCardRepository;
use App\Helpers\Crypto;

EnvLoader::load(__DIR__ . '/../.env');

$repo = new VirtualCardRepository();
$rows = $repo->findNeedingEncryptionBackfill();

echo "Found " . count($rows) . " card(s) needing backfill.\n";

$pdo = Database::connection();
$updated = 0;

foreach ($rows as $row) {
    $encryptedNumber = Crypto::encrypt($row['card_number']);
    $encryptedCvv = Crypto::encrypt($row['cvv']);

    $stmt = $pdo->prepare('UPDATE virtual_cards SET card_number_encrypted = ?, cvv_encrypted = ? WHERE id = ?');
    $stmt->execute([$encryptedNumber, $encryptedCvv, $row['id']]);
    $updated++;
}

echo "Backfilled {$updated} card(s).\n";
```

This depends on one repository method not built in Phase 3 (added here since it's specific to this backfill's needs):

**Add to `app/Repositories/VirtualCardRepository.php`:**

```php
    /** Rows still holding plaintext-only card data (encrypted columns not yet backfilled). */
    public function findNeedingEncryptionBackfill(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM virtual_cards WHERE card_number IS NOT NULL AND card_number_encrypted IS NULL'
        );

        return $stmt->fetchAll();
    }
```

**Run it — staging first, always:**

```bash
php database/backfill_card_encryption.php
```

**Verified — run as an actual subprocess (`exec()` calling the real command), not a function call, and run twice to confirm idempotency:**
```
PASS — Before backfill: exactly one card needs it (the simulated old row)
PASS — Backfill script exits successfully
PASS — Backfill script reports backfilling exactly 1 card
PASS — After backfill: encrypted columns are now populated
PASS — After backfill: decrypting matches the ORIGINAL plaintext value exactly
PASS — After backfill: original plaintext column is UNTOUCHED (not deleted by this script)
PASS — Second backfill run finds 0 cards needing it (idempotent)
```

**On dropping the plaintext columns:** this script deliberately does not delete the original `card_number`/`cvv` values after encrypting them. Don't drop those columns until you've confirmed, against a full copy of production data, that every single row has a corresponding encrypted value — run `migrate:status`-style verification (`SELECT COUNT(*) FROM virtual_cards WHERE card_number IS NOT NULL AND card_number_encrypted IS NULL` should return `0`) before ever writing a migration to drop them. That drop is intentionally not part of this phase.

---

## P4.4 — Mail Providers

**File: `app/Exceptions/ProviderException.php`** (built in Phase 1's task list, restated here since both mail providers throw it)

```php
<?php

namespace App\Exceptions;

class ProviderException extends AppException
{
    private string $provider;

    public function __construct(string $message, string $provider = '')
    {
        parent::__construct($message);
        $this->provider = $provider;
    }

    public function provider(): string
    {
        return $this->provider;
    }
}
```

**File: `app/Providers/Mail/SmtpMailProvider.php`**

```php
<?php

namespace App\Providers\Mail;

use App\Interfaces\MailProviderInterface;
use App\Exceptions\ProviderException;

class SmtpMailProvider implements MailProviderInterface
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password']; // decrypted by MailProviderFactory before this
            $mail->SMTPSecure = $this->config['encryption'] ?? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) ($this->config['port'] ?? 587);
            $mail->setFrom($this->config['from_address'], $this->config['from_name'] ?? 'NovaTrust');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (\Exception $e) {
            throw new ProviderException('SMTP send failed: ' . $e->getMessage(), 'smtp');
        }
    }
}
```

**File: `app/Providers/Mail/ResendMailProvider.php`**

```php
<?php

namespace App\Providers\Mail;

use App\Interfaces\MailProviderInterface;
use App\Exceptions\ProviderException;

class ResendMailProvider implements MailProviderInterface
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->config['api_key'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($this->buildPayload($to, $subject, $body)),
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        }

        throw new ProviderException("Resend send failed (HTTP {$statusCode}): {$response}", 'resend');
    }

    /**
     * Payload construction split out from send() specifically so it can be unit
     * tested without making a real network call to api.resend.com.
     */
    public function buildPayload(string $to, string $subject, string $body): array
    {
        return [
            'from' => ($this->config['from_name'] ?? 'NovaTrust') . ' <' . $this->config['from_address'] . '>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $body,
        ];
    }
}
```

**Note on `ResendMailProvider`:** no Composer dependency was added for this (no `resend/resend-php` package) — it's a direct `curl` call to Resend's REST API instead. This keeps the dependency footprint identical to what's already there (`composer.json` still only lists PHPMailer) and avoids adding a package purely for one HTTP call this simple. `buildPayload()` is separated from `send()` specifically so the payload shape could be tested without a live network call — verified below — but the actual `send()` → `api.resend.com` call itself was **not** tested against the real Resend API in this sandbox, since that domain isn't reachable from here. Test this specifically against a real Resend sandbox/test API key before considering it production-ready.

**Verified (payload construction only, per the note above):**
```
PASS — ResendMailProvider builds the correct payload shape
PASS — ResendMailProvider payload "from" field is correctly formatted
```

---

## P4.5 — `MailProviderFactory`

**File: `app/Providers/Mail/MailProviderFactory.php`**

```php
<?php

namespace App\Providers\Mail;

use App\Repositories\MailSettingsRepository;
use App\Helpers\Crypto;
use App\Interfaces\MailProviderInterface;

class MailProviderFactory
{
    public function __construct(private MailSettingsRepository $repo)
    {
    }

    /**
     * config JSON holds plaintext operational fields (host, port, from address)
     * plus one base64-encoded encrypted field (secret_encrypted) for the SMTP
     * password or Resend API key — never a plaintext secret at rest.
     */
    public function make(): MailProviderInterface
    {
        $settings = $this->repo->getActive();

        if ($settings === null) {
            throw new \RuntimeException('No active mail driver configured in mail_settings.');
        }

        $config = json_decode($settings['config'], true) ?: [];

        if (isset($config['secret_encrypted'])) {
            $secretBinary = base64_decode($config['secret_encrypted']);
            $decrypted = Crypto::decrypt($secretBinary);
            $config['password'] = $decrypted;
            $config['api_key'] = $decrypted;
        }

        return match ($settings['driver']) {
            'smtp' => new SmtpMailProvider($config),
            'resend' => new ResendMailProvider($config),
            default => throw new \RuntimeException("Unknown mail driver: {$settings['driver']}"),
        };
    }
}
```

**Why the secret is base64-encoded on top of encryption:** `Crypto::encrypt()` returns raw binary, which isn't valid inside a JSON string. The `config` column is `TEXT` holding JSON (chosen in Phase 2 so SMTP and Resend, which need entirely different fields, don't need separate columns) — base64 is just the encoding that lets encrypted binary survive being embedded in JSON text. When Phase 14 builds the admin mail-settings screen, saving a new secret means: `base64_encode(Crypto::encrypt($secret))` into `config['secret_encrypted']`, mirroring this factory's reverse operation exactly.

**Verified — decryption round-trip through the full factory, plus confirming the switch between drivers actually returns different classes:**
```
PASS — MailProviderFactory returns an SmtpMailProvider when driver = smtp
PASS — MailProviderFactory correctly decrypted the SMTP password before handing it to the provider
PASS — Plaintext secret is never in mail_settings.config directly
PASS — MailProviderFactory returns a ResendMailProvider when driver = resend
```

---

## P4.6 — `PollingChatProvider`

**File: `app/Providers/Chat/PollingChatProvider.php`**

```php
<?php

namespace App\Providers\Chat;

use App\Interfaces\ChatTransportInterface;
use App\Repositories\ChatMessageRepository;
use App\Models\ChatMessage;

class PollingChatProvider implements ChatTransportInterface
{
    public function __construct(private ChatMessageRepository $messages)
    {
    }

    public function send(int $conversationId, string $senderType, string $message): void
    {
        ChatMessage::create([
            'conversation_id' => $conversationId,
            'sender_type' => $senderType,
            'message' => $message,
        ]);
    }

    public function poll(int $conversationId, int $sinceMessageId): array
    {
        return $this->messages->since($conversationId, $sinceMessageId);
    }
}
```

This is almost entirely a thin wrapper — `ChatMessageRepository::since()` (Phase 3) already did the real work and was already verified there. What's new here is confirming `send()` followed by `poll()` behaves correctly as a pair, end to end.

**Verified:**
```
PASS — PollingChatProvider: send() + poll() from 0 returns both messages
PASS — PollingChatProvider: messages are in correct order
PASS — PollingChatProvider: poll() since firstId correctly excludes it, returns 2 newer messages
```

---

## P4.7 — `WebSocketChatProvider` (Stub)

**File: `app/Providers/Chat/WebSocketChatProvider.php`**

```php
<?php

namespace App\Providers\Chat;

use App\Interfaces\ChatTransportInterface;

/**
 * Documented future implementation — not built out. Build this only once P0.4's
 * hosting decision confirms a host that can run a persistent WebSocket process
 * (typical shared/cPanel hosting cannot; a VPS can). Until then,
 * PollingChatProvider is the real, working default — this stub exists so the
 * seam ChatTransportInterface provides is visible in the codebase, not just
 * described in a document.
 */
class WebSocketChatProvider implements ChatTransportInterface
{
    public function send(int $conversationId, string $senderType, string $message): void
    {
        throw new \RuntimeException('WebSocketChatProvider is not yet implemented — see class docblock.');
    }

    public function poll(int $conversationId, int $sinceMessageId): array
    {
        throw new \RuntimeException('WebSocketChatProvider is not yet implemented — see class docblock.');
    }
}
```

**Verified — confirms it correctly fails loud rather than silently doing nothing, which matters since it implements the same interface `PollingChatProvider` does and could otherwise be swapped in by mistake:**
```
PASS — WebSocketChatProvider correctly throws (documented stub, not implemented)
```

---

## Phase 4 Exit Checklist

- [ ] `APP_ENCRYPTION_KEY` generated and added to `.env` (and a placeholder to `.env.example`) — a real, freshly generated key, not a copy-pasted example value
- [ ] `APP_ENCRYPTION_KEY` backed up somewhere as secure as the database credentials — losing it makes existing encrypted data permanently unrecoverable
- [ ] `Helpers/Crypto.php` in place, tampering-detection behavior confirmed
- [ ] `SimulatedCardProvider` issuing cards with encrypted-only columns (plaintext columns NULL for new cards)
- [ ] `user/generate_card.php` deleted; `admin/approve_virtual_card.php` scheduled for retirement once Phase 9 lands
- [ ] Backfill script run successfully against staging, confirmed idempotent
- [ ] Both mail providers built; `MailProviderFactory` correctly switches between them and correctly decrypts the stored secret
- [ ] `ResendMailProvider::send()` tested against a **real** Resend sandbox key before going live — this phase only verified payload construction, not the actual network call
- [ ] `PollingChatProvider` verified end to end (send → poll)
- [ ] `WebSocketChatProvider` stub in place, confirmed to fail loudly rather than silently

---

*End of Phase 4 Runbook. Next: Implementation Plan Phase 5 (`P5.1`–`P5.9`+) — Design System Implementation, the frontend foundation every feature page from Phase 6 onward consumes.*
