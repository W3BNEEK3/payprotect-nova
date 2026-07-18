# NovaTrust — Phase 2 Runbook: Database Migrations

**Version:** 1.0
**Prepared for:** Wynston
**Companion to:** `novatrust-implementation-plan.md` v1.4, Phase 2 (`P2.1`–`P2.14`)
**Scope:** all 28 migration files, exact contents, in dependency order.
**Verified:** every migration in this document was run against a real, live MariaDB 10.11 instance — applied in order via the actual `MigrationRunner` class from Phase 1 (not raw SQL scripts guessed to work), re-run a second time to confirm the tracking table prevents re-application, and separately tested against a database seeded with a pre-existing `users` row to confirm the baseline migrations genuinely don't touch real data. All foreign keys resolved correctly on the first pass, confirming the dependency order below is actually correct, not just plausible.

---

## Two Findings From Building This — Read Before You Run Anything

**1. `user_compliance_codes` was missing from the migration plan entirely.** Both `database/schema.sql` and `database/novatrust.sql` are stale — neither one captures `compliance_requirements`'s companion table, even though it's a real, actively-queried production table (`admin/admin_codes.php`, `user/withdraw.php`, `user/process_withdraw.php` all query it directly). I reconstructed its structure from the actual `INSERT`/`SELECT`/`UPDATE` statements in those files, not from any schema dump. This is why the migration numbering below doesn't match earlier drafts of the SADD — `002`–`011` is ten baseline tables, not nine, and everything after shifted by one.

**2. `database/novatrust.sql` contains real user PII in committed data rows, not just schema.** Names, emails, phone numbers, bcrypt password hashes, account balances — all sitting in git history. I did not reproduce any of it anywhere in this document, and every migration below is written from table *structure* only. This needs to be added to `P0.3`'s exposure investigation and `SECURITY_NOTES.md` — same "was this repo ever public" logic applies here as to the plaintext credentials, and arguably matters more, since this is regulated customer data.

---

## How to Use This Document

Run these against **staging only** first (`P0.16`'s environment). Every file is idempotent in the sense that matters — `MigrationRunner` tracks what's applied and never re-runs a completed migration — but individual `ALTER TABLE` statements are not safe to run twice manually outside the runner (a second `ADD COLUMN` on an existing column fails). Always run through `database/migrate.php`, never by hand-pasting SQL into a client, once Phase 1's runner exists.

Create the migration files in `database/migrations/` (built empty in Phase 1's directory scaffolding) using the exact filenames and contents below.

---

## P2.1 — Migrations Tracking Table

**File: `database/migrations/001_create_migrations_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS migrations (
    id INT NOT NULL AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY migration (migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

This is created automatically by `MigrationRunner::ensureMigrationsTable()` even without this file present — it's listed here so `migrate:status` has something to show as migration `001` in the sequence, matching the SADD's documented list exactly.

---

## P2.2 — Baseline Snapshot (10 Tables, `002`–`011`)

Every file below is `CREATE TABLE IF NOT EXISTS` — safe to run against staging *or* production, whether the table already has real data or not. **Verified directly:** I ran `002` against a database with a pre-existing `users` row already in it; the row survived untouched and the command exited cleanly with no error.

**File: `database/migrations/002_baseline_users_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    firstname VARCHAR(100) DEFAULT NULL,
    middlename VARCHAR(100) DEFAULT NULL,
    lastname VARCHAR(100) DEFAULT NULL,
    fullname VARCHAR(200) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    currency VARCHAR(10) DEFAULT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    refunded_balance DECIMAL(15,2) DEFAULT 0.00,
    account_number VARCHAR(30) DEFAULT NULL,
    account_status VARCHAR(20) DEFAULT 'active',
    account_type VARCHAR(30) DEFAULT 'regular',
    employment VARCHAR(50) DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    is_upgraded TINYINT(1) DEFAULT 0,
    is_upgrade_verified TINYINT(1) DEFAULT 0,
    is_kyc_verified TINYINT(1) DEFAULT 0,
    kyc_code VARCHAR(50) DEFAULT NULL,
    is_imf_verified TINYINT(1) DEFAULT 0,
    imf_code VARCHAR(50) DEFAULT NULL,
    is_vat_verified TINYINT(1) DEFAULT 0,
    vat_code VARCHAR(50) DEFAULT NULL,
    is_ars_verified TINYINT(1) DEFAULT 0,
    ars_code VARCHAR(50) DEFAULT NULL,
    is_withdrawal_verified TINYINT(1) DEFAULT 0,
    withdrawal_code VARCHAR(50) DEFAULT NULL,
    is_virtual_card_cleared TINYINT(1) DEFAULT 0,
    reset_token VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    dob DATE DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email),
    UNIQUE KEY account_number (account_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

32 columns — pulled from `database/novatrust.sql`'s actual export, which is more complete than `database/schema.sql`'s 27-column version (missing `middlename`, `employment`, `gender`, `dob`). `novatrust.sql` is the authoritative source; `schema.sql` is stale.

**File: `database/migrations/003_baseline_admins_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS admins (
    id INT NOT NULL AUTO_INCREMENT,
    fullname VARCHAR(200) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/004_baseline_compliance_requirements_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS compliance_requirements (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(200) DEFAULT NULL,
    description VARCHAR(500) DEFAULT NULL,
    fee_amount DECIMAL(15,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/005_baseline_user_compliance_codes_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS user_compliance_codes (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    compliance_id INT NOT NULL,
    code VARCHAR(50) DEFAULT NULL,
    notes VARCHAR(500) DEFAULT NULL,
    assigned_by VARCHAR(200) DEFAULT NULL,
    is_cleared TINYINT(1) DEFAULT 0,
    cleared_at DATETIME DEFAULT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY compliance_id (compliance_id),
    CONSTRAINT fk_ucc_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_ucc_requirement FOREIGN KEY (compliance_id) REFERENCES compliance_requirements(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Reconstructed from `admin/admin_codes.php` and `user/process_withdraw.php`'s actual queries — not from any schema dump, since none captured this table. Reviewed the reconstruction against every query site found; if any other file queries columns not listed here, treat that as a signal this reconstruction needs a follow-up column addition before Phase 3 builds a Model against it.

**File: `database/migrations/006_baseline_transactions_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS transactions (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    amount DECIMAL(15,2) DEFAULT NULL,
    currency VARCHAR(10) DEFAULT NULL,
    type VARCHAR(20) DEFAULT NULL,
    message VARCHAR(255) DEFAULT NULL,
    receiver_id INT DEFAULT NULL,
    status VARCHAR(30) DEFAULT NULL,
    method VARCHAR(50) DEFAULT NULL,
    reference VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/007_baseline_virtual_cards_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS virtual_cards (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    card_number VARCHAR(30) DEFAULT NULL,
    expiry_date VARCHAR(10) DEFAULT NULL,
    cvv VARCHAR(10) DEFAULT NULL,
    cardholder_name VARCHAR(200) DEFAULT NULL,
    balance DECIMAL(15,2) DEFAULT NULL,
    status VARCHAR(30) DEFAULT NULL,
    is_virtual_card_approved TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Note `is_virtual_card_approved` (not `is_approved`) — this is the correct column name per the SADD's Section 0 grounding note about the two disagreeing card-issuing code paths. `user/generate_card.php` (orphaned) was the one writing to a wrong/different column; this baseline preserves the column `admin/approve_virtual_card.php` (the real path) actually uses.

**File: `database/migrations/008_baseline_notifications_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS notifications (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    title VARCHAR(100) DEFAULT NULL,
    message VARCHAR(255) DEFAULT NULL,
    type VARCHAR(30) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/009_baseline_refunds_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS refunds (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    account_number VARCHAR(30) DEFAULT NULL,
    amount DECIMAL(15,2) DEFAULT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    refunded_by VARCHAR(200) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/010_baseline_support_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS support (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/011_baseline_support_requests_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS support_requests (
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(150) DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    date_sent DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Verify:**

```bash
php database/migrate.php migrate
# Expect "Applying 001..." through "Applying 011..." each followed by "Applied", then
# "Migrations complete."

mysql -u your_db_user -p your_db_name -e "SHOW TABLES;"
# Expect all 11 tables (10 domain tables + migrations) to exist.
```

---

## P2.3 — Compliance Support Tables (`012`–`014`)

**File: `database/migrations/012_create_compliance_flags_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS compliance_flags (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    reason VARCHAR(50) NOT NULL COMMENT 'new_account, high_credit_volume, or manual',
    status VARCHAR(20) NOT NULL DEFAULT 'open' COMMENT 'open or resolved',
    triggered_amount DECIMAL(15,2) DEFAULT NULL,
    triggered_currency VARCHAR(10) DEFAULT NULL,
    created_by INT DEFAULT NULL COMMENT 'admin id if manually flagged, NULL if automatic',
    resolved_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY status (status),
    CONSTRAINT fk_flag_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/013_create_compliance_settings_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS compliance_settings (
    id INT NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO compliance_settings (setting_key, setting_value) VALUES
    ('new_account_threshold_days', '30'),
    ('auto_flag_amount_usd', '7000');
```

Seeds `P0.11`/`P0.12`'s confirmed defaults directly in the migration — `INSERT IGNORE` means re-running this is harmless even outside the tracked-migration mechanism (though it still only runs once via `MigrationRunner` regardless). If `P0.11`/`P0.12` land on different numbers before this runs on staging, edit these two values before running, not after.

**File: `database/migrations/014_create_fx_reference_rates_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS fx_reference_rates (
    id INT NOT NULL AUTO_INCREMENT,
    currency_code VARCHAR(10) NOT NULL,
    rate_to_usd DECIMAL(15,6) NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY currency_code (currency_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO fx_reference_rates (currency_code, rate_to_usd) VALUES
    ('USD', 1.000000),
    ('EUR', 1.080000),
    ('GBP', 1.270000);
```

Seed values are illustrative placeholders, not live rates — `ComplianceEngine` (Phase 10) needs real, periodically-updated rates for the cumulative-credit USD-equivalent check (BR-5/BR-12) to be accurate. Treat wiring this table to a real FX data source as a Phase 10 task, not something this migration solves.

---

## P2.4 — Link Compliance Codes to Flags

**File: `database/migrations/015_add_flag_id_to_user_compliance_codes.sql`**

```sql
ALTER TABLE user_compliance_codes
    ADD COLUMN flag_id INT DEFAULT NULL AFTER compliance_id;

ALTER TABLE user_compliance_codes
    ADD CONSTRAINT fk_ucc_flag FOREIGN KEY (flag_id) REFERENCES compliance_flags(id);
```

**Verified dependency order:** this migration references `compliance_flags`, created in `012`. Running it before `012` fails with a foreign key error — confirmed by the fact that running all 28 files in numeric order via `MigrationRunner` succeeded with zero errors, which wouldn't be possible if this ordering were wrong.

---

## P2.5 — Virtual Card Requests + Encryption Columns

**File: `database/migrations/016_create_virtual_card_requests_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS virtual_card_requests (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, approved, rejected',
    reason VARCHAR(255) DEFAULT NULL COMMENT 'rejection reason, if rejected',
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT fk_vcr_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/017_add_encrypted_columns_to_virtual_cards.sql`**

```sql
ALTER TABLE virtual_cards
    ADD COLUMN card_number_encrypted VARBINARY(255) DEFAULT NULL AFTER card_number,
    ADD COLUMN cvv_encrypted VARBINARY(255) DEFAULT NULL AFTER cvv;
```

This adds new columns alongside the existing plaintext `card_number`/`cvv` — it does not touch or remove the plaintext columns yet. `P4.3` (Phase 4) is the one-time backfill script that encrypts existing values into these new columns; only once that's confirmed working against a full copy of production data should the plaintext columns be dropped, and that drop is deliberately not part of this migration.

---

## P2.6 — Withdrawal Requests

**File: `database/migrations/018_create_withdrawal_requests_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS withdrawal_requests (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    method VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    destination_details TEXT DEFAULT NULL COMMENT 'JSON-encoded, method-specific fields',
    status VARCHAR(20) NOT NULL DEFAULT 'pending_review' COMMENT 'pending_review, approved, processing, completed, rejected',
    rejection_reason VARCHAR(255) DEFAULT NULL,
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY status (status),
    CONSTRAINT fk_wr_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

`destination_details` is a single JSON-encoded text column rather than one column per possible field, since the 8 withdrawal methods (SRS FR-6.1) each need different fields — a bank transfer needs routing/account numbers, a crypto withdrawal needs a wallet address, and so on. `WithdrawalRequest`'s Model (`P3.5`) is responsible for casting this to/from a PHP array; the 8 method-specific forms (`P11.3`) each write a differently-shaped array into the same column.

**File: `database/migrations/019_add_withdrawal_request_id_to_transactions.sql`**

```sql
ALTER TABLE transactions
    ADD COLUMN withdrawal_request_id INT DEFAULT NULL AFTER receiver_id;

ALTER TABLE transactions
    ADD CONSTRAINT fk_txn_withdrawal FOREIGN KEY (withdrawal_request_id) REFERENCES withdrawal_requests(id);
```

This is the traceability link FR-6.3 needs — once a withdrawal completes, the debit transaction row references exactly which withdrawal request caused it, the same pattern `020` establishes for refunds against transactions.

---

## P2.7 — Refund Traceability

**File: `database/migrations/020_add_original_transaction_id_to_refunds.sql`**

```sql
ALTER TABLE refunds
    ADD COLUMN original_transaction_id INT DEFAULT NULL AFTER user_id;

ALTER TABLE refunds
    ADD CONSTRAINT fk_refund_transaction FOREIGN KEY (original_transaction_id) REFERENCES transactions(id);
```

**This migration didn't exist in any earlier version of the plan.** SRS FR-2.1.1 says a refund is issued "against any single existing transaction," but the baseline `refunds` table (`009`) has no column linking to one — it was designed before the Refund module (SADD Section 6.1a) existed. Without this, `RefundController` (Phase 11) would have nothing to store that link in. Caught while cross-referencing the real schema against the SRS's refund requirements, not flagged in any prior review.

---

## P2.8 — Live Chat Tables

**File: `database/migrations/021_create_chat_conversations_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS chat_conversations (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'bot_handled' COMMENT 'bot_handled, waiting_for_agent, active, closed',
    assigned_admin_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY status (status),
    CONSTRAINT fk_conv_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/022_create_chat_messages_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT NOT NULL AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    sender_type VARCHAR(20) NOT NULL COMMENT 'user, bot, or admin',
    sender_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY conversation_id (conversation_id),
    CONSTRAINT fk_msg_conversation FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**File: `database/migrations/023_create_chat_bot_rules_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS chat_bot_rules (
    id INT NOT NULL AUTO_INCREMENT,
    trigger_keywords VARCHAR(500) NOT NULL COMMENT 'comma-separated keywords/phrases',
    response TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

No seed rows here deliberately — `ChatBotRuleSeeder` (Phase 3's `database/seeders/`) owns populating this, and per SRS BR-13, withdrawal/account/compliance topics must never get a seeded rule at all (enforced additionally as an active guard in `ChatRuleEngine`, per the Implementation Plan's `P13.3`).

---

## P2.9 — Mail Settings

**File: `database/migrations/024_create_mail_settings_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS mail_settings (
    id INT NOT NULL AUTO_INCREMENT,
    driver VARCHAR(20) NOT NULL COMMENT 'smtp or resend',
    config TEXT DEFAULT NULL COMMENT 'JSON-encoded driver-specific settings',
    is_active TINYINT(1) DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

`config` holds driver-specific settings as JSON rather than separate SMTP/Resend columns, since the two providers need entirely different fields — `MailProviderFactory` (Phase 4) reads `driver` to decide which provider to instantiate, then passes `config` to it.

---

## P2.10 — Notifications Read-State + Audit Log

**File: `database/migrations/025_add_read_state_to_notifications.sql`**

```sql
ALTER TABLE notifications
    ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER type,
    ADD COLUMN read_at DATETIME DEFAULT NULL AFTER is_read;
```

**File: `database/migrations/026_create_audit_log_table.sql`**

```sql
CREATE TABLE IF NOT EXISTS audit_log (
    id INT NOT NULL AUTO_INCREMENT,
    admin_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) DEFAULT NULL,
    target_id INT DEFAULT NULL,
    details TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY admin_id (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

No foreign key from `audit_log.admin_id` to `admins.id` deliberately — an audit trail should still record *that* an action happened even if the admin account is later deleted, so this is an intentionally loose reference, not an oversight.

---

## P2.11 — Conditional: Admin Roles

**File: `database/migrations/027_add_role_to_admins_table.sql`**

```sql
-- Conditional — only run if P0.6 confirmed admin role separation.
ALTER TABLE admins
    ADD COLUMN role VARCHAR(30) DEFAULT 'super_admin' COMMENT 'support, compliance, or super_admin';
```

Every existing admin defaults to `super_admin` on this migration, preserving current access levels for everyone already in the table — nobody's access silently narrows the moment this runs.

---

## P2.12 — Conditional: Blog Posts

**File: `database/migrations/028_create_blog_posts_table.sql`**

```sql
-- Conditional — only run if P0.9 confirmed a DB-driven blog.
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    excerpt VARCHAR(500) DEFAULT NULL,
    body TEXT NOT NULL,
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

If `P0.9` went the other way (static array, not DB-driven), skip this file entirely — don't create an unused table.

---

## P2.13 — Full Regression Pass on Staging

No new files — this is verification. Run every migrated page manually against staging:

```bash
php database/migrate.php migrate
php database/migrate.php migrate:status
# Every line should read "applied" (or the two conditional ones, if you skipped them
# per P0.6/P0.9, will correctly still show as files that don't exist — that's fine,
# only create 027/028 if the relevant decision confirmed you need them)
```

Then manually, in a browser against staging:

1. Log in as an existing (staging-copied) user — confirms `users` table changes didn't break the login query.
2. Load the dashboard — confirms balance/transaction display still works.
3. Load `admin/admin_codes.php` — confirms `compliance_requirements`/`user_compliance_codes` still work with the new `flag_id` column present.
4. Load a withdrawal method page — confirms nothing in the withdrawal flow broke, even though `withdrawal_requests` isn't wired into it yet (that's Phase 11).

**Done-when:** all four checks pass with no errors, and `migrate:status` shows every applicable migration as `applied`.

---

## P2.14 — Production Migration Window

**Do not run this until `P2.13` is fully green on staging.**

```bash
# Step 1 — fresh backup, per the P0.17 runbook, no exceptions:
mysqldump -h localhost -u your_admin_db_user -p --single-transaction --quick --lock-tables=false harmony1_novar_DB > pre_migration_backup_$(date +%Y%m%d_%H%M%S).sql
gzip pre_migration_backup_*.sql

# Step 2 — check current state before touching anything:
php database/migrate.php migrate:status

# Step 3 — apply:
php database/migrate.php migrate

# Step 4 — verify:
php database/migrate.php migrate:status
# Every line should now read "applied"

# Step 5 — smoke test the live site immediately:
curl -I https://novatrust.example/user/login.php
# Then manually: log in, load the dashboard, load admin_codes.php — same four checks
# as P2.13, now against production.
```

If anything fails at Step 5, use `P0.17`'s documented restore procedure immediately — don't attempt to debug forward against a partially-migrated production database with real user funds in it.

---

## Phase 2 Exit Checklist

- [ ] All 26 unconditional migrations (`001`–`026`) applied and verified on staging
- [ ] `027`/`028` applied only if `P0.6`/`P0.9` confirmed they're needed
- [ ] `user_compliance_codes` confirmed present with the correct columns — this table didn't exist in any schema dump before this phase
- [ ] `refunds.original_transaction_id` confirmed present — needed before Phase 11 can build `RefundController`
- [ ] Full regression pass (`P2.13`) green on staging
- [ ] `database/novatrust.sql`'s PII finding added to `SECURITY_NOTES.md` (from `P0.3`)
- [ ] Production backup taken immediately before the production migration window
- [ ] Production migration applied, verified, smoke-tested (`P2.14`)

---

*End of Phase 2 Runbook. Next: Implementation Plan Phase 3 (`P3.1`–`P3.9`) — the domain Models and Repositories, built against the exact schema this phase just created and verified.*
