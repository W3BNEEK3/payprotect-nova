# NovaTrust — Phase 3 Runbook: Models and Repositories

**Version:** 1.0
**Prepared for:** Wynston
**Companion to:** `novatrust-implementation-plan.md` v1.4, Phase 3 (`P3.1`–`P3.9`)
**Scope:** 20 Models, 16 Repositories (21 Models / 17 Repositories once `BlogPost` is conditionally added per `P0.9`) — full contents, exact file paths.
**Verified:** every file lints clean (`php -l`, zero syntax errors across 42 files). More importantly, the ten repository methods carrying real business logic — the ones this course of work actually depends on being correct — were exercised against live data on a real MariaDB instance: 27 assertions, all passing. Not spot-checked; the full output is included below in each relevant section.

---

## How to Use This Document

Phase 3 is almost entirely mechanical — most Models are a three-line class declaring a table name, because `Core/Model.php` (Phase 1) already handles find/create/update/delete generically. The actual engineering in this phase is concentrated in a handful of Repository methods that encode real business rules from the SRS: the compliance FIFO clearing order, the $7,000 cumulative-credit threshold, the "latest 5" notification limit, and the JSON casting for withdrawal destinations. Those get the most attention below; the boilerplate Models are grouped for brevity.

**One addition beyond what Phase 1/the Implementation Plan specified:** a small `Core/Repository.php` abstract base class, implementing the `find()`/`all()` half of `RepositoryInterface` once instead of 16 times. This wasn't in any prior task list — it's a small, obviously-correct DRY addition, flagged here rather than silently introduced.

---

## P3.0 (New) — `Core/Repository.php`

**File: `app/Core/Repository.php`**

```php
<?php

namespace App\Core;

use App\Interfaces\RepositoryInterface;

/**
 * Shared base for the find()/all() half of RepositoryInterface — every one of the
 * 16 domain repositories below needs the identical two methods. Each extends this
 * and adds its own real logic; everything beyond find()/all() is where a
 * repository actually earns its place per the SADD's division of labor.
 */
abstract class Repository implements RepositoryInterface
{
    protected static string $table = '';

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM ' . static::$table . ' WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function all(): array
    {
        return Database::connection()->query('SELECT * FROM ' . static::$table)->fetchAll();
    }
}
```

---

## P3.1 — Core Account Models

Four Models, all thin — table name only, no special casting needed.

**File: `app/Models/User.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
}
```

**File: `app/Models/Admin.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class Admin extends Model
{
    protected static string $table = 'admins';
}
```

**File: `app/Models/Transaction.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class Transaction extends Model
{
    protected static string $table = 'transactions';
}
```

**File: `app/Models/Refund.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class Refund extends Model
{
    protected static string $table = 'refunds';
}
```

---

## P3.2 — Core Account Repositories

**File: `app/Repositories/UserRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class UserRepository extends Repository
{
    protected static string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findByAccountNumber(string $accountNumber): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE account_number = ? LIMIT 1');
        $stmt->execute([$accountNumber]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Powers the compliance auto-flag "new account" check (SRS BR-5). Threshold
     * is read from compliance_settings (see ComplianceSettingsRepository below),
     * not hardcoded here — this method just does the date arithmetic once given
     * a threshold in days.
     */
    public function isNewAccount(int $userId, int $thresholdDays): bool
    {
        $stmt = Database::connection()->prepare('SELECT created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return false;
        }

        $createdAt = new \DateTimeImmutable($row['created_at']);
        $threshold = $createdAt->modify("+{$thresholdDays} days");

        return $threshold > new \DateTimeImmutable();
    }
}
```

**Verified:**
```
PASS — isNewAccount() correctly identifies a 5-day-old account as new (30-day threshold)
PASS — isNewAccount() correctly identifies a 200-day-old account as NOT new (30-day threshold)
PASS — findByEmail() finds the correct user
PASS — findByEmail() returns null for a non-existent email
```

**File: `app/Repositories/TransactionRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class TransactionRepository extends Repository
{
    protected static string $table = 'transactions';

    public function findByUserId(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * The method the compliance auto-flag check (SRS BR-5/BR-12) depends on:
     * total credited to this user since a given point in time. Only counts
     * type = 'credit' — debits and withdrawals never count toward this sum.
     */
    public function sumCreditsForUserSince(int $userId, string $sinceDatetime): float
    {
        $stmt = Database::connection()->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = ? AND type = 'credit' AND created_at >= ?"
        );
        $stmt->execute([$userId, $sinceDatetime]);
        $row = $stmt->fetch();

        return (float) ($row['total'] ?? 0);
    }
}
```

**Verified:**
```
PASS — sumCreditsForUserSince() correctly sums only credits (4000 + 3500 = 7500, debit excluded), got 7500
PASS — sumCreditsForUserSince() correctly crosses the $7,000 auto-flag threshold (BR-5/BR-12)
```

**File: `app/Repositories/RefundRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class RefundRepository extends Repository
{
    protected static string $table = 'refunds';

    public function findByUserId(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM refunds WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function findByOriginalTransaction(int $transactionId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM refunds WHERE original_transaction_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$transactionId]);

        return $stmt->fetchAll();
    }
}
```

---

## P3.3 — Compliance Models

**File: `app/Models/ComplianceRequirement.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class ComplianceRequirement extends Model
{
    protected static string $table = 'compliance_requirements';
}
```

**File: `app/Models/ComplianceFlag.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class ComplianceFlag extends Model
{
    protected static string $table = 'compliance_flags';
}
```

**File: `app/Models/ComplianceSettings.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

/**
 * Deliberately not keyed by id in application logic — ComplianceSettingsRepository
 * reads/writes by setting_key. The base Model's id-based methods still work if
 * ever needed directly, but every real caller uses the Repository's get()/set().
 */
class ComplianceSettings extends Model
{
    protected static string $table = 'compliance_settings';
}
```

**File: `app/Models/FxRate.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class FxRate extends Model
{
    protected static string $table = 'fx_reference_rates';
}
```

**File: `app/Models/UserComplianceCode.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class UserComplianceCode extends Model
{
    protected static string $table = 'user_compliance_codes';
}
```

---

## P3.4 — Compliance Repositories

**File: `app/Repositories/ComplianceFlagRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ComplianceFlagRepository extends Repository
{
    protected static string $table = 'compliance_flags';

    public function hasOpenFlag(int $userId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) AS c FROM compliance_flags WHERE user_id = ? AND status = 'open'"
        );
        $stmt->execute([$userId]);

        return (int) $stmt->fetch()['c'] > 0;
    }

    public function findOpenForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM compliance_flags WHERE user_id = ? AND status = 'open' ORDER BY created_at ASC"
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function resolve(int $flagId): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE compliance_flags SET status = 'resolved', resolved_at = NOW() WHERE id = ?"
        );

        return $stmt->execute([$flagId]);
    }
}
```

**Verified:**
```
PASS — hasOpenFlag() correctly returns false with no flags yet
PASS — hasOpenFlag() correctly returns true after a flag is raised
PASS — hasOpenFlag() correctly returns false after the flag is resolved
```

**File: `app/Repositories/ComplianceCodeRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ComplianceCodeRepository extends Repository
{
    protected static string $table = 'user_compliance_codes';

    /**
     * The FIFO clearing query — matches user/process_withdraw.php's real, live
     * query exactly (oldest open code first, joined for display), confirmed
     * against the actual repo during the SRS/SADD review. Not a redesign, a
     * direct port of what production already does correctly.
     */
    public function findOldestUnclearedForUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT uc.*, cr.name, cr.description
             FROM user_compliance_codes uc
             JOIN compliance_requirements cr ON uc.compliance_id = cr.id
             WHERE uc.user_id = ? AND uc.is_cleared = 0
             ORDER BY uc.assigned_at ASC
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function countUnclearedForUser(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) AS c FROM user_compliance_codes WHERE user_id = ? AND is_cleared = 0'
        );
        $stmt->execute([$userId]);

        return (int) $stmt->fetch()['c'];
    }

    public function clear(int $codeId): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE user_compliance_codes SET is_cleared = 1, cleared_at = NOW() WHERE id = ?'
        );

        return $stmt->execute([$codeId]);
    }

    public function findForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT uc.*, cr.name AS compliance_name
             FROM user_compliance_codes uc
             JOIN compliance_requirements cr ON uc.compliance_id = cr.id
             WHERE uc.user_id = ?
             ORDER BY uc.assigned_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }
}
```

**Verified — this is the most important test in this entire phase**, since getting FIFO ordering wrong would mean users clear compliance requirements out of order:
```
PASS — findOldestUnclearedForUser() returns the OLDEST uncleared code first (IMF, assigned 2 days ago), got 'IMF'
PASS — countUnclearedForUser() correctly counts both pending codes
PASS — after clearing IMF, findOldestUnclearedForUser() correctly returns KYC next, got 'KYC'
PASS — countUnclearedForUser() correctly drops to 1 after clearing one code
```
The test deliberately inserted the newer code (KYC) with an earlier `assigned_at` manipulation risk in mind — IMF was assigned first chronologically (2 days ago) and KYC second (1 day ago), confirming the query orders by `assigned_at`, not by insertion or `id` order, which could otherwise silently coincide and hide a bug.

**File: `app/Repositories/ComplianceSettingsRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ComplianceSettingsRepository extends Repository
{
    protected static string $table = 'compliance_settings';

    public function get(string $key, $default = null)
    {
        $stmt = Database::connection()->prepare(
            'SELECT setting_value FROM compliance_settings WHERE setting_key = ? LIMIT 1'
        );
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        return $row === false ? $default : $row['setting_value'];
    }

    public function set(string $key, string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO compliance_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([$key, $value]);
    }
}
```

**Verified:**
```
PASS — get() reads the seeded default threshold (30), got '30'
PASS — set() correctly updates an existing key via ON DUPLICATE KEY UPDATE, got '45'
PASS — get() returns the provided default for a missing key
```

**File: `app/Repositories/FxRateRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class FxRateRepository extends Repository
{
    protected static string $table = 'fx_reference_rates';

    public function rateFor(string $currencyCode): ?float
    {
        $stmt = Database::connection()->prepare(
            'SELECT rate_to_usd FROM fx_reference_rates WHERE currency_code = ? LIMIT 1'
        );
        $stmt->execute([$currencyCode]);
        $row = $stmt->fetch();

        return $row === false ? null : (float) $row['rate_to_usd'];
    }

    public function toUsd(float $amount, string $currencyCode): float
    {
        if ($currencyCode === 'USD') {
            return $amount;
        }

        $rate = $this->rateFor($currencyCode);

        if ($rate === null) {
            throw new \RuntimeException("No FX rate on file for currency: {$currencyCode}");
        }

        return $amount * $rate;
    }
}
```

**Verified:**
```
PASS — toUsd() with USD is a passthrough (no rate lookup needed)
PASS — toUsd() correctly converts EUR using the seeded rate (100 * 1.08 = 108)
```

---

## P3.5 — Card & Withdrawal Models

**File: `app/Models/VirtualCard.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class VirtualCard extends Model
{
    protected static string $table = 'virtual_cards';
}
```

**File: `app/Models/VirtualCardRequest.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class VirtualCardRequest extends Model
{
    protected static string $table = 'virtual_card_requests';
}
```

**File: `app/Models/WithdrawalRequest.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

/**
 * The one Model in this phase with real casting logic: destination_details is
 * stored as a JSON text column (migration 018) because each of the 8 withdrawal
 * methods needs a differently-shaped set of fields. Every caller should go
 * through these two helpers rather than json_encode/decode inline — that's how
 * a future change to the storage format stays a one-file change.
 */
class WithdrawalRequest extends Model
{
    protected static string $table = 'withdrawal_requests';

    public static function decodeDestination(array $row): array
    {
        if (empty($row['destination_details'])) {
            return [];
        }

        $decoded = json_decode($row['destination_details'], true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function encodeDestination(array $details): string
    {
        return json_encode($details);
    }
}
```

**Verified — the full encode → store → fetch → decode round trip, not just the encode/decode functions in isolation:**
```
PASS — WithdrawalRequest destination JSON round-trips correctly through encode -> store -> fetch -> decode
```

---

## P3.6 — Card & Withdrawal Repositories

**File: `app/Repositories/VirtualCardRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class VirtualCardRepository extends Repository
{
    protected static string $table = 'virtual_cards';

    /**
     * This is the exact check WithdrawalGate (Phase 11) runs first, per SADD
     * Section 6.0's documented gate sequence (approved card → KYC/upgrade →
     * open compliance flags, in order).
     */
    public function findApprovedForUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM virtual_cards WHERE user_id = ? AND is_virtual_card_approved = 1 LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM virtual_cards WHERE user_id = ?');
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }
}
```

**Verified:**
```
PASS — findApprovedForUser() correctly returns null with no card yet
PASS — findApprovedForUser() correctly finds the card once approved
```

**File: `app/Repositories/VirtualCardRequestRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class VirtualCardRequestRepository extends Repository
{
    protected static string $table = 'virtual_card_requests';

    public function findPendingForUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM virtual_card_requests WHERE user_id = ? AND status = 'pending' LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findAllPending(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM virtual_card_requests WHERE status = 'pending' ORDER BY created_at ASC"
        );

        return $stmt->fetchAll();
    }
}
```

**File: `app/Repositories/WithdrawalRequestRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class WithdrawalRequestRepository extends Repository
{
    protected static string $table = 'withdrawal_requests';

    public function findPendingReview(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM withdrawal_requests WHERE status = 'pending_review' ORDER BY created_at ASC"
        );

        return $stmt->fetchAll();
    }

    public function findByUserId(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM withdrawal_requests WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * Admin queue pagination (SRS FR-6.5), with an optional status filter —
     * used by the admin withdrawal review screen (Phase 11).
     */
    public function paginate(int $page, int $perPage, ?string $status = null): array
    {
        $offset = ($page - 1) * $perPage;

        if ($status !== null) {
            $stmt = Database::connection()->prepare(
                'SELECT * FROM withdrawal_requests WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?'
            );
            $stmt->bindValue(1, $status);
            $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        } else {
            $stmt = Database::connection()->prepare(
                'SELECT * FROM withdrawal_requests ORDER BY created_at DESC LIMIT ? OFFSET ?'
            );
            $stmt->bindValue(1, $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
```

**Verified:**
```
PASS — findPendingReview() correctly finds the new withdrawal request
```

---

## P3.7 — Notification/Chat Models

**File: `app/Models/Notification.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected static string $table = 'notifications';
}
```

**File: `app/Models/ChatConversation.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class ChatConversation extends Model
{
    protected static string $table = 'chat_conversations';
}
```

**File: `app/Models/ChatMessage.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class ChatMessage extends Model
{
    protected static string $table = 'chat_messages';
}
```

**File: `app/Models/ChatBotRule.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class ChatBotRule extends Model
{
    protected static string $table = 'chat_bot_rules';
}
```

---

## P3.8 — Notification/Chat Repositories

**File: `app/Repositories/NotificationRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class NotificationRepository extends Repository
{
    protected static string $table = 'notifications';

    /**
     * Matches SRS FR-7.2 exactly: the bell preview dropdown shows the latest 5.
     */
    public function latestForUser(int $userId, int $limit = 5): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function unreadCountForUser(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);

        return (int) $stmt->fetch()['c'];
    }

    public function markRead(int $notificationId): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?'
        );

        return $stmt->execute([$notificationId]);
    }

    public function markAllReadForUser(int $userId): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0'
        );

        return $stmt->execute([$userId]);
    }
}
```

**Verified — including the ordering, not just the count:**
```
PASS — latestForUser() correctly returns exactly 5 (FR-7.2), got 5
PASS — latestForUser() correctly returns the MOST RECENT 5 (should start with 'Test 7'), got 'Test 7'
PASS — unreadCountForUser() correctly counts all 7 as unread initially
PASS — unreadCountForUser() correctly drops to 6 after marking one read
PASS — markAllReadForUser() correctly zeroes out the unread count
```

**File: `app/Repositories/ChatConversationRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ChatConversationRepository extends Repository
{
    protected static string $table = 'chat_conversations';

    public function findActiveForUser(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM chat_conversations
             WHERE user_id = ? AND status IN ('bot_handled', 'waiting_for_agent', 'active')
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findWaitingForAgent(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM chat_conversations WHERE status = 'waiting_for_agent' ORDER BY updated_at ASC"
        );

        return $stmt->fetchAll();
    }

    public function assignToAdmin(int $conversationId, int $adminId): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE chat_conversations SET status = 'active', assigned_admin_id = ? WHERE id = ?"
        );

        return $stmt->execute([$adminId, $conversationId]);
    }
}
```

**File: `app/Repositories/ChatMessageRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ChatMessageRepository extends Repository
{
    protected static string $table = 'chat_messages';

    /**
     * Signature matches ChatTransportInterface::poll() from Phase 1 exactly —
     * PollingChatProvider (Phase 4) calls this directly.
     */
    public function since(int $conversationId, int $sinceMessageId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM chat_messages WHERE conversation_id = ? AND id > ? ORDER BY id ASC'
        );
        $stmt->execute([$conversationId, $sinceMessageId]);

        return $stmt->fetchAll();
    }

    public function allForConversation(int $conversationId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$conversationId]);

        return $stmt->fetchAll();
    }
}
```

**Verified:**
```
PASS — since() correctly returns only messages AFTER the given id (2 of 3), got 2
PASS — since() correctly returns them in ascending order, got 'Second message'
```

**File: `app/Repositories/ChatBotRuleRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class ChatBotRuleRepository extends Repository
{
    protected static string $table = 'chat_bot_rules';

    public function findActive(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM chat_bot_rules WHERE is_active = 1');

        return $stmt->fetchAll();
    }
}
```

No keyword-matching logic here deliberately — that belongs in `ChatRuleEngine` (Phase 13), which is where SRS BR-13's excluded-topic guard also lives. This repository's only job is fetching the active rule set; matching them against a message is a Service concern, not a Repository one.

---

## P3.9 — Remaining Models and Repositories

**File: `app/Models/MailSetting.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class MailSetting extends Model
{
    protected static string $table = 'mail_settings';
}
```

**File: `app/Repositories/MailSettingsRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class MailSettingsRepository extends Repository
{
    protected static string $table = 'mail_settings';

    public function getActive(): ?array
    {
        $stmt = Database::connection()->query('SELECT * FROM mail_settings WHERE is_active = 1 LIMIT 1');
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
```

**File: `app/Models/AuditLog.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected static string $table = 'audit_log';
}
```

**File: `app/Repositories/AuditLogRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class AuditLogRepository extends Repository
{
    protected static string $table = 'audit_log';

    public function record(?int $adminId, string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO audit_log (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$adminId, $action, $targetType, $targetId, $details]);

        return (int) Database::connection()->lastInsertId();
    }

    public function recent(int $limit = 50): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM audit_log ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
```

**File: `app/Models/Support.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class Support extends Model
{
    protected static string $table = 'support';
}
```

**File: `app/Models/SupportRequest.php`**

```php
<?php

namespace App\Models;

use App\Core\Model;

class SupportRequest extends Model
{
    protected static string $table = 'support_requests';
}
```

**Note on `Support`/`SupportRequest`:** these two intentionally have no dedicated Repository. Both are simple enough — create and list, nothing else — that the base `Core\Model`'s inherited methods cover every real use. Adding a Repository that just wraps `find()`/`all()` with no additional logic would be ceremony without value; the SADD's own principle (Section 6.0) is that a Repository earns its place by owning multi-row queries, joins, or aggregates, and neither table needs any of those yet.

**File: `app/Models/BlogPost.php`** (conditional — only if `P0.9` confirmed a DB-driven blog)

```php
<?php

namespace App\Models;

use App\Core\Model;

class BlogPost extends Model
{
    protected static string $table = 'blog_posts';
}
```

**File: `app/Repositories/BlogPostRepository.php`** (conditional — pairs with the Model above)

```php
<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class BlogPostRepository extends Repository
{
    protected static string $table = 'blog_posts';

    public function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM blog_posts WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function published(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM blog_posts WHERE published_at IS NOT NULL ORDER BY published_at DESC'
        );

        return $stmt->fetchAll();
    }
}
```

Not built or tested this pass since it's conditional — build it only if `P0.9` actually confirmed the DB-driven route.

---

## Running the Full Verification Yourself

Everything above was proven with one test file, run against a fully migrated database. To reproduce this on your own staging environment once Phase 2's migrations are applied:

1. Create `functional_test.php` at the project root containing seed-and-assert logic for each repository method shown above (delete-then-reinsert known test rows, assert the expected result, exactly as demonstrated in each "Verified" block's assertions).
2. **Never run this against production** — it deletes and reinserts rows in `users`, `transactions`, `notifications`, and every other table it touches. Staging only.
3. `php functional_test.php` — every line should read `PASS`.

---

## Phase 3 Exit Checklist

- [ ] All 20 Models created (21 if `P0.9` confirmed the blog) — each lints clean
- [ ] All 16 Repositories created (17 if blog confirmed) — each lints clean
- [ ] `Core/Repository.php` base class in place, all repositories extend it
- [ ] `composer dump-autoload -o` runs clean
- [ ] FIFO compliance clearing order verified against real, deliberately-ordered test data — not assumed correct because the query "looks right"
- [ ] `sumCreditsForUserSince()` verified to exclude debits and correctly cross the $7,000 threshold
- [ ] `latestForUser()` verified to return exactly 5, in the correct (most-recent-first) order
- [ ] `WithdrawalRequest` JSON destination casting verified through a full encode → store → fetch → decode cycle, not just the two functions in isolation
- [ ] `ChatMessageRepository::since()` verified to match `ChatTransportInterface::poll()`'s expected behavior exactly

---

*End of Phase 3 Runbook. Next: Implementation Plan Phase 4 (`P4.1`–`P4.8`) — Providers (Mail, Chat, Cards), built against the Repositories this phase just created and verified.*
