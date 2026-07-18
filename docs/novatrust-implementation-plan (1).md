# NovaTrust — Implementation Plan

**Version:** 1.4 — Phase 2 renumbered to match SADD v2.4's corrected migration list (`user_compliance_codes` baseline added, `refunds` linkage column added); downstream references to the old `P2.13` "production migration window" milestone updated to `P2.14`.
**Prepared for:** Wynston
**Synthesizes:** `novatrust-business-logic-srs.md` v1.2, `novatrust-sadd.md` v2.4, `novatrust-design-system.md` v1.1
**Scope:** every task required to take the current live codebase to the fully upgraded system described in those three documents, in dependency order.
**v1.4 changelog:** Phase 2's task table renumbered `P2.1`–`P2.14` (was `P2.1`–`P2.13`) to match the corrected migration file list — see SADD v2.4 for why. Every downstream task that depended on the old `P2.13` (`P3.1`, `P3.3`, `P3.5`, `P3.7`, `P3.9`, `P4.3`) now correctly points at `P2.14`, the actual "production migration window" milestone those tasks meant to depend on.

---

## 0. How to Read This Plan

- **Task IDs** (`P0.1`, `P4.3`, etc.) are stable references you can turn directly into tickets.
- **Depends on** points to the task(s) that must be *done*, not just started, before that task can begin. Tasks within the same phase are usually parallelizable across people unless a dependency says otherwise.
- **Sizing** is relative, not calendar time — I have no basis for estimating days/weeks without knowing team size and how much of this is genuinely new to whoever builds it, and a fabricated time estimate would be less useful than an honest one. `S` = a focused, single-sitting task. `M` = a feature touching several files/layers that needs coordination. `L` = cross-cutting work touching most of the app at once.
- **Phases are ordered by real dependency**, not by importance — Phase 6 (marketing site) comes before Phase 11 (withdrawals) because it's genuinely safer to prove out the design system and routing patterns on lower-stakes pages first, not because it matters more.
- Every task traces back to a specific section of one of the three source documents — that section reference is your source of truth if this plan and the source document ever seem to disagree.

---

## Phase 0 — Decisions, Environment & Safety Net (blocking)

**Before anything else: this is a from-scratch framework build, not an extension.** Verified directly against `github.com/W3BNEEK3/payprotect-nova`: the live repo has **no framework at all** — 14 flat top-level items, zero namespaced/class-based PHP anywhere, no router, no front controller, every page a standalone procedural script. The Emirates directory structure this plan follows is a **reference pattern**, not code that already exists in NovaTrust — an earlier version of this plan and the SADD both got that backwards. Phase 1 below is now real scaffolding — building `Core/App.php`, `Router.php`, `Database.php`, and the rest from nothing — not "extensions" to something already running. What genuinely *is* already there and stays exactly as valuable: the live database (~1,312 real users) and the actual business behavior the SRS documented by reading the real procedural files directly. `P0.19` has whoever's building read those real files before Phase 1 designs Models around them.

Nothing in Phase 1 onward should start until this phase closes. Most of these are decisions, not build work, and several are urgent independent of the rest of the project.

| ID | Task | Depends on | Output |
|---|---|---|---|
| P0.1 | Rotate the live DB credentials currently duplicated in plaintext across `config/config.php` **and** `database/db.php` (Section 0 of the SADD — it's both files, not one): (a) generate new DB credentials with the host, (b) add `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS` to a new `.env` file at the project root, (c) confirm `.env` is listed in `.gitignore` — add it if it isn't, (d) point both `config/config.php` and `database/db.php` at a minimal inline `.env` parser for now — a few lines, not a full class; `Core/EnvLoader.php` doesn't exist until `P1.2`, and this credential leak is too urgent to wait on Phase 1 — (e) delete the plaintext values from both files entirely, don't just move them, (f) commit `.env.example` (same keys, placeholder values) so the pattern is documented for the next person. `P1.3` later formally supersedes this interim parser with `Core/Database.php` as the single real connection point, consolidating what are still two separate files even after this task | — | New credentials issued, old ones revoked, `.env` is now the only place they live, neither `config/config.php` nor `database/db.php` has plaintext credentials left |
| P0.2 | Rotate the live SMTP credentials currently committed in plaintext in `config/mail_config.php`, using the identical interim pattern as `P0.1`: new credentials → `.env` (`SMTP_HOST`/`SMTP_PORT`/`SMTP_USER`/`SMTP_PASS`) → the same minimal inline `.env` parser (not `EnvLoader`, not yet built) patched into the existing `config/mail_config.php` → plaintext deleted from that file. This is interim scaffolding twice over: `P1.2`'s real `EnvLoader` supersedes the inline parser, and then `P14` supersedes `.env` itself for SMTP specifically — these values move again, out of `.env` and into the `mail_settings` table via `MailProviderFactory`, so `.env` no longer needs SMTP credentials at all past that point | — | New credentials issued, old ones revoked, no plaintext SMTP credentials in source |
| P0.3 | Confirm the repo's git history/visibility — if it has ever been public with these credentials in it, treat them as permanently compromised, not just rotate-and-move-on | — | Written confirmation of repo exposure status |
| P0.4 | Decide: hosting target for the upgraded system (stay shared/cPanel vs move to VPS/cloud) — SADD Open Q1 | — | Decision recorded; determines P13.6's transport choice and P16.1's cron mechanism |
| P0.5 | Decide: fate of `virtual_cards.balance` — drop from use vs formalize as a real feature — SADD Open Q2 | — | Decision recorded; blocks P9 |
| P0.6 | Decide: admin role separation now (Support/Compliance/Super Admin) or keep flat — SADD Open Q3 / SRS Open Q4 | — | Decision recorded; blocks P1.18, P8 |
| P0.7 | Check `app/Core/Router.php` for multi-route-file or prefix-group support — SADD Open Q4 | — | Decision recorded: `routes/api.php` as a separate file, or a prefix block inside `web.php`; blocks P1.19 |
| P0.8 | Decide: adopt Alpine.js or pure vanilla JS throughout — SADD Open Q5 / Design System | — | Decision recorded; blocks P5 |
| P0.9 | Decide: blog stays a static array vs becomes `blog_posts`-backed with a small admin CMS — SADD Open Q6 | — | Decision recorded; blocks P6.4 |
| P0.10 | Decide: keep `CardIssuerInterface` seam for a possible future real card issuer, or skip it and hard-code the simulated path — SADD Open Q7 | — | Decision recorded; blocks P4.1 |
| P0.11 | Decide: new-account threshold for compliance auto-flagging (default proposed: 30 days) — SRS Open Q1 | — | Decision recorded; blocks P10 |
| P0.12 | Decide: cumulative vs single-transaction check for the $7,000 auto-flag rule — SRS Open Q2 | — | Decision recorded; blocks P10 |
| P0.13 | Decide: withdrawal completion model — instant auto-debit vs admin-release step (default proposed: admin-release) — SRS Open Q3. **This is the highest-impact open decision in the whole plan** — it changes P11's data flow, not just a detail within it. | — | Decision recorded; blocks P11 |
| P0.14 | Decide: unclaimed-chat escalation threshold (default proposed: 2 minutes) — SRS Open Q6 | — | Decision recorded; blocks P13.5 |
| P0.15 | Decide: Google Fonts/Material Symbols — CDN-linked vs self-hosted | — | Decision recorded; blocks P5.1 |
| P0.16 | Stand up a staging environment matching production — step by step: (a) provision a host matching the production signature exactly (PHP 8.1.30, MariaDB 10.6.20, `cll-lve`/CloudLinux if you can get it, or the closest available equivalent), (b) clone the repo onto it, (c) run `composer install`, (d) copy `.env.example` (from P0.1) to `.env` and fill in the staging DB credentials, (e) import the P0.17 production export into the staging database, (f) point `config/database.php` at staging and load the site in a browser — confirm login, dashboard, and at least one existing withdrawal method page all render without error *before* any new code is written against this environment. No migration in P2 ever runs against production first | P0.1 | Working staging environment, verified against a real page load, not just "the server is up" |
| P0.17 | Take a full production database export and store it somewhere outside the app server; write down the exact restore procedure before anyone needs it under pressure | — | Verified backup + documented restore steps |
| P0.18 | Confirm the git branching model (e.g. `main` = production, feature branches per phase below) | — | Written convention, first branch created |
| P0.19 | **Behavior orientation** — before Phase 1 designs a single class, whoever's building should read (not skim) the real procedural files that encode today's actual behavior: `database/db.php` + `config/config.php` (the duplicated connection pattern), `user/withdraw.php` → `user/process_withdraw.php` (the real gate sequence, end to end), `admin/admin_send_money.php` (today's only funding path), `admin/approve_virtual_card.php` vs the orphaned `user/generate_card.php` (the two-path bug named in SADD Section 0). This is a reading task, not a build task — its output is a working understanding of what the new `Services/`/`Repositories/` layer actually needs to reproduce, since the SRS's business rules were derived from these exact files | P0.16 (needs a running instance to read against) | Written 1-page internal note — the real request/response flow for withdrawal and crediting, in your own words — cheap insurance that Phase 3's Models and Phase 11's `WithdrawalGate` match what the system actually does today, not an assumed version of it |

---

## Phase 1 — Framework Core (Built From Scratch)

*Corrected in v1.3 — this phase was previously titled "Framework Core Extensions" and assumed most of this already existed. It doesn't (Section 0 / `P0.19`). `P1.1`–`P1.11` below are genuinely new: nothing in this list exists in the live repo today. `P1.12` onward are the tasks originally in this phase, renumbered, which still hold — they just now depend on `P1.1`–`P1.11` existing first instead of assuming a framework core to attach to.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P1.1 | Add a PSR-4 autoload block to `composer.json` (`"App\\": "app/"`), run `composer dump-autoload` — today's `composer.json` has exactly one dependency (PHPMailer) and no autoloading beyond it | P0.19 | Working class autoloading |
| P1.2 | Build `Core/EnvLoader.php` — parses `.env`, exposes an `env($key, $default)` helper | P1.1 | `.env` values readable from PHP |
| P1.3 | Build `Core/Database.php` — the single PDO connection point, reading credentials via `EnvLoader`; this replaces the connection logic currently duplicated in `config/config.php` and `database/db.php` (Section 0 of the SADD) | P1.2 | One connection path instead of two |
| P1.4 | Build `Core/Request.php` and `Core/Response.php` — thin wrappers around superglobals and HTTP output | P1.1 | Request/response abstractions |
| P1.5 | Build `Core/Router.php` — route registration (`GET`/`POST` + path, with parameters) and dispatch to a Controller method | P1.4 | Working router, no routes registered yet |
| P1.6 | Build `Core/Session.php`, `Core/ErrorHandler.php`, `Core/Logger.php` | P1.1 | Session/error/log wrappers, replacing today's bare `session_start()` calls scattered across every file |
| P1.7 | Build `bootstrap/app.php` and `Core/App.php` — wires `Database`, `Router`, `Session`, `ErrorHandler` together into one bootstrap sequence | P1.3, P1.5, P1.6 | A single bootstrap entry point |
| P1.8 | Build `public/index.php` (the real front controller) and `public/.htaccess` (URL rewriting so every request funnels through it) — today's `index.php` sits at the repo root and is just the flat marketing homepage, not a front controller; that page's content becomes `resources/public/home.php` per Section 4 | P1.7 | Every request now enters through one file |
| P1.9 | Build `Core/Model.php` (base Model — table-name convention, attribute casting, single-row persistence) and `Controllers/BaseController.php` | P1.7 | Base classes every domain Model/Controller extends |
| P1.10 | Build the base `Exceptions/` set (`AppException`, `AuthException`, `NotFoundException`, `ProviderException`, `StorageException`, `ValidationException`) and the `Middlewares/` pipeline mechanism plus `AuthMiddleware`, `CsrfMiddleware`, `GuestMiddleware` | P1.7 | Exception hierarchy + working middleware pipeline |
| P1.11 | **Smoke test:** register one throwaway route, dispatch it through `Router` → a test Controller → `Response`, confirm it renders in a browser against the `P0.16` staging environment, before any domain-specific code is written on top of this | P1.1–P1.10 | Proof the full chain works end to end |
| P1.12 | Build `Core/MigrationRunner.php` — scans `database/migrations/*.sql` in order, checks the `migrations` table, applies pending ones, records each, exposes `status()` | P1.3, P1.11 | `app/Core/MigrationRunner.php` |
| P1.13 | Build `Core/SeedRunner.php` — runs all or one named seeder | P1.11 | `app/Core/SeedRunner.php` |
| P1.14 | Build `database/migrate.php` as a thin CLI dispatcher (`migrate`, `migrate:status`, `seed`) — this file doesn't exist today (Section 4 of the SADD) | P1.12, P1.13 | New `database/migrate.php` |
| P1.15 | Build `Helpers/Money.php` — decimal-safe currency arithmetic (add/subtract/compare without float error), formatting helper that pairs with the design system's `--font-data` display convention | P1.11 | `app/Helpers/Money.php` |
| P1.16 | Build `Exceptions/ComplianceException.php` and `Exceptions/InsufficientFundsException.php` | P1.10 | Two additional exception classes |
| P1.17 | Build `Interfaces/RepositoryInterface.php`, `Interfaces/MailProviderInterface.php`, `Interfaces/ChatTransportInterface.php`, `Interfaces/CardIssuerInterface.php` (flat in `Interfaces/`, following the Emirates naming convention) | P0.10, P1.11 | Four interfaces |
| P1.18 | Build `Middlewares/AdminMiddleware.php` — role-aware if `P0.6` confirmed role separation, otherwise a flat admin-session check | P0.6, P1.10 | Working admin middleware |
| P1.19 | Add `routes/api.php` (or a prefix group inside `web.php`, per `P0.7`'s finding) and confirm the router dispatches it correctly with a throwaway test route | P0.7, P1.11 | Working second route surface for `Api/` controllers |

---

## Phase 2 — Database Migrations

*Implements SADD Section 5, in the exact order Section 5.4 specifies. Every file is written and run against staging first; production only sees a migration after it's been verified there. Renumbered in this revision — see SADD v2.4's changelog for why `user_compliance_codes` needed its own baseline migration.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P2.1 | Write and run `001_create_migrations_table.sql` on staging | P1.12 | `migrations` table exists on staging |
| P2.2 | Write and run the baseline snapshot migrations `002`–`011` (idempotent `CREATE TABLE IF NOT EXISTS` for `users`, `admins`, `compliance_requirements`, `user_compliance_codes`, `transactions`, `virtual_cards`, `notifications`, `refunds`, `support`, `support_requests` — 10 tables, not 9; `user_compliance_codes` was missing from every prior schema dump) on staging | P2.1 | Baseline schema reproducible from migrations |
| P2.3 | Write and run `012_create_compliance_flags_table.sql`, `013_create_compliance_settings_table.sql`, `014_create_fx_reference_rates_table.sql` on staging | P2.2 | Three new tables |
| P2.4 | Write and run `015_add_flag_id_to_user_compliance_codes.sql` on staging | P2.3 | `user_compliance_codes.flag_id` column + FK |
| P2.5 | Write and run `016_create_virtual_card_requests_table.sql`, `017_add_encrypted_columns_to_virtual_cards.sql` on staging | P2.2 | New table + new encrypted columns (plaintext columns untouched for now) |
| P2.6 | Write and run `018_create_withdrawal_requests_table.sql`, `019_add_withdrawal_request_id_to_transactions.sql` on staging | P2.2 | New table + traceability column |
| P2.7 | Write and run `020_add_original_transaction_id_to_refunds.sql` on staging | P2.2 | `refunds.original_transaction_id` column + FK — closes the SADD 6.1a gap needed for `RefundController` (Phase 11) |
| P2.8 | Write and run `021_create_chat_conversations_table.sql`, `022_create_chat_messages_table.sql`, `023_create_chat_bot_rules_table.sql` on staging | P2.2 | Three new tables |
| P2.9 | Write and run `024_create_mail_settings_table.sql` on staging | P2.2 | New table |
| P2.10 | Write and run `025_add_read_state_to_notifications.sql`, `026_create_audit_log_table.sql` on staging | P2.2 | Column additions + new table |
| P2.11 | If P0.6 confirmed role separation: write and run `027_add_role_to_admins_table.sql` on staging | P0.6 | `admins.role` column |
| P2.12 | If P0.9 confirmed a DB-driven blog: write and run `028_create_blog_posts_table.sql` on staging | P0.9 | `blog_posts` table |
| P2.13 | Full regression pass on staging: confirm every existing page still works against the migrated schema before touching production | P2.1–P2.12 | Signed-off staging environment |
| P2.14 | **Production migration window:** take a fresh backup (per P0.17's procedure), run `migrate:status`, apply `001`–`028` (as applicable) in order, verify with `migrate:status` again, smoke-test the live site | P2.13, P0.17 | Production schema matches staging |

---

## Phase 3 — Domain Layer: Models & Repositories

*Implements SADD Section 6.0 and the `Models/`, `Repositories/` rows of Section 4. Grouped by business domain rather than one row per file — each group is one coherent sitting of similar work.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P3.1 | Build core account Models: `User`, `Admin`, `Transaction`, `Refund` | P2.14 | 4 Models |
| P3.2 | Build core account Repositories: `UserRepository`, `TransactionRepository` (incl. `sumCreditsForUserSince()`, the method P10 depends on), `RefundRepository` | P3.1 | 3 Repositories |
| P3.3 | Build compliance Models: `ComplianceRequirement`, `ComplianceFlag`, `ComplianceSettings`, `FxRate`, `UserComplianceCode` | P2.14 | 5 Models |
| P3.4 | Build compliance Repositories: `ComplianceFlagRepository` (incl. `oldestOpenForUser()`), `ComplianceCodeRepository` (incl. FIFO clearing query), `ComplianceSettingsRepository`, `FxRateRepository` | P3.3 | 4 Repositories |
| P3.5 | Build card & withdrawal Models: `VirtualCard`, `VirtualCardRequest`, `WithdrawalRequest` | P2.14 | 3 Models — `WithdrawalRequest` casts `destination_details` JSON to/from array |
| P3.6 | Build card & withdrawal Repositories: `VirtualCardRepository`, `VirtualCardRequestRepository`, `WithdrawalRequestRepository` | P3.5 | 3 Repositories |
| P3.7 | Build notification/chat Models: `Notification`, `ChatConversation`, `ChatMessage`, `ChatBotRule` | P2.14 | 4 Models |
| P3.8 | Build notification/chat Repositories: `NotificationRepository`, `ChatConversationRepository` (incl. unassigned-queue and unclaimed-past-threshold queries), `ChatMessageRepository`, `ChatBotRuleRepository` | P3.7 | 4 Repositories |
| P3.9 | Build remaining Models/Repositories: `MailSetting`/`MailSettingsRepository`, `AuditLog`/`AuditLogRepository`, `Support`, `SupportRequest`, and `BlogPost`/`BlogPostRepository` if P0.9 confirmed | P2.14 | Remaining Models + Repositories |
| P3.10 | Unit tests for every Repository method that carries real business risk if wrong: `ComplianceFlagRepository::oldestOpenForUser`, `TransactionRepository::sumCreditsForUserSince`, `ChatConversationRepository`'s unclaimed-past-threshold query | P3.2, P3.4, P3.8 | Test suite covering the FIFO and threshold logic the SRS depends on |

---

## Phase 4 — Providers

*Implements SADD Section 6.0/6.2/6.5/6.6 and the `Providers/` row of Section 4.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P4.1 | Build `Providers/Cards/SimulatedCardProvider.php` implementing `CardIssuerInterface` — `random_bytes()`/`random_int()` generation, writes to the new encrypted columns via `Helpers/Crypto.php` | P1.17, P3.6, P0.10 | Working, hardened card issuance path |
| P4.2 | Consolidate: delete/retire `user/generate_card.php`'s logic entirely; `admin/approve_virtual_card.php`'s responsibility moves into P4.1 + the new `VirtualCardController` (P9) so there is exactly one path that creates a `virtual_cards` row | P4.1 | One consolidated card-issuance path |
| P4.3 | Write and run the one-time backfill script that encrypts existing plaintext `card_number`/`cvv` values into the new encrypted columns for all existing cards | P2.14, P4.1 | Backfill script, run once against production |
| P4.4 | Build `Providers/Mail/SmtpMailProvider.php`, `Providers/Mail/ResendMailProvider.php`, both implementing `MailProviderInterface` | P1.17 | Two mail providers |
| P4.5 | Build `Providers/Mail/MailProviderFactory.php` — reads `mail_settings` via `MailSettingsRepository`, decrypts credentials via `Helpers/Crypto.php`, returns the active provider | P4.4, P3.9 | Working factory |
| P4.6 | Build `Providers/Chat/PollingChatProvider.php` implementing `ChatTransportInterface` — `send()` is an immediate `chat_messages` insert, `poll()` is a `since`-id query | P1.17, P3.8 | Working default chat transport |
| P4.7 | Stub `Providers/Chat/WebSocketChatProvider.php` as a documented future implementation of the same interface — not built out, just scaffolded so the seam is visible in the codebase, contingent on P0.4's hosting decision | P4.6, P0.4 | Stub file + a short doc comment explaining when to build it out |

---

## Phase 5 — Design System Implementation (Frontend Foundation)

*Implements `novatrust-design-system.md` end to end at the token/component level, before any feature page consumes it. Nothing in Phase 6 onward should hand-roll a button, toast, modal, or form-validation pattern — it consumes what's built here.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P5.1 | Load Google Fonts (Space Grotesk, IBM Plex Sans, IBM Plex Mono) and Material Symbols Outlined/Rounded, per P0.15's CDN-vs-self-hosted decision | P0.15 | Fonts/icons loading on a test page |
| P5.2 | Write `assets/css/design-tokens.css` — every custom property in Design System Section 1, verbatim | P5.1 | Token stylesheet, imported everywhere |
| P5.3 | Build the button component CSS (Design System Section 2) — all five tiers plus Ghost, disabled state, press micro-interaction | P5.2 | `.btn-*` classes |
| P5.4 | Build the confirmation modal component: markup structure, CSS, and `assets/js/modules/modal.js` — a single reusable controller (open/close, focus trap, Escape/backdrop = Cancel, mobile bottom-sheet behavior) that every "are you sure" moment in the app calls into. This is what replaces `window.confirm` everywhere. | P5.2 | `components/ui/_confirm-modal.php` + `modal.js` |
| P5.5 | Build the toast component: markup, CSS, and rewrite the existing `assets/js/toast.js` to match Design System Section 4 exactly (variants, stacking, progress bar, error-persists-until-dismissed rule) | P5.2 | Updated `toast.js` + toast markup |
| P5.6 | Build the generic client-side validation engine (`assets/js/modules/validation.js`) implementing Design System Section 6.1's philosophy (validate on blur, live re-validate after first error, shake + focus first invalid field on submit) | P5.2 | Reusable validation engine |
| P5.7 | Build the field-type-specific handlers from Design System Section 6.2 as small, composable validators/formatters (email, phone, amount, card number, CVV, IBAN, crypto address, OTP/compliance code, password strength) on top of P5.6 | P5.6 | One handler per field type, each independently testable |
| P5.8 | Build the responsive table pattern (Design System Section 7) as a reusable CSS pattern plus a small `data-label` attribute convention documented for every future table markup | P5.2 | `assets/css/tables.css` + convention doc comment |
| P5.9 | Build the chart container styling/tooltip component (Design System Section 8), ready for whichever specific charts P10/P12 need | P5.2 | Reusable chart CSS/JS wrapper |
| P5.10 | If P0.8 confirmed Alpine.js: add it to `assets/js/`, wire it into the layout `<head>`; if not, confirm the vanilla-only patterns in P5.4–P5.7 cover the same interactions without it | P0.8 | Alpine loaded, or vanilla-only decision confirmed in writing |
| P5.11 | Cross-browser/breakpoint QA of every component built in this phase, in isolation, before any real page uses them | P5.3–P5.9 | Signed-off component library |

---

## Phase 6 — Public Marketing Site

*Implements SADD Section 6.8 and Design System Section 5.1. Chosen to go first among feature work because it's the lowest-stakes place to prove out routing, layouts, and the design system on real pages.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P6.1 | Build `Controllers/Public/HomeController.php`, `AboutController.php`, `ContactController.php`, `BlogController.php` and their routes | P1.11 | 4 controllers, routed |
| P6.2 | Build `layouts/public.php` — desktop scroll-transition header, mobile morphing-hamburger drawer, per Design System Section 5.1 | P5.11 | Shared public layout |
| P6.3 | Build `resources/public/home.php` — hero, headline, the load animation sequence, section-by-section scroll reveals — replacing the current root `index.php` | P6.2 | New homepage |
| P6.4 | Build `resources/public/about.php` and `resources/public/blog/{index,show}.php`, per P0.9's static-vs-CMS decision (if CMS: wire to `BlogPost`/`BlogPostRepository` and run `BlogPostSeeder` to migrate the ~5 existing hardcoded posts) | P6.2, P0.9, (P3.9 if CMS) | About + blog pages |
| P6.5 | Build `resources/public/contact.php` wired to `ContactController` → `support_requests` insert via a repository call, using the P5.6/P5.7 validation engine — this is the fix for the form that currently submits nowhere | P6.1, P5.6 | Working contact form |
| P6.6 | Remove the hard-loaded Tidio script from `contact.php` (and note the same removal is still owed on `support.php`/`customer_service.php` in Phase 13) | P6.5 | No third-party chat script on the public site |
| P6.7 | Full QA of the public site against the design system (motion, responsive header/drawer, contrast) | P6.3–P6.6 | Signed-off public site |

---

## Phase 7 — Authentication

| ID | Task | Depends on | Output |
|---|---|---|---|
| P7.1 | Build `Controllers/Auth/LoginController.php`, `RegisterController.php`, `PasswordResetController.php` | P1.11, P3.2 | 3 controllers |
| P7.2 | Rebuild `resources/auth/*` views against the design system (form inputs, password strength meter, buttons) | P5.11 | Updated auth pages |
| P7.3 | Confirm `AuthMiddleware`/`GuestMiddleware`/`CsrfMiddleware` are correctly applied to every auth route | P7.1 | Verified middleware coverage |

---

## Phase 8 — Authenticated Shell (User & Admin Layout)

*Implements Design System Section 5.2. Built once, before any individual feature page, since every user/admin page depends on this shell existing.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P8.1 | Build `layouts/app.php` — desktop collapsible sidebar, top bar with page title + notification bell placeholder + account dropdown | P5.11, P7.3 | User shell |
| P8.2 | Build `layouts/admin.php` — same pattern, gated by `AdminMiddleware` | P5.11, P1.18 | Admin shell |
| P8.3 | Build `components/navigation/_sidebar.php` (adapt the existing framework component of the same name), `_bottom-nav.php` for mobile, and the shared morphing menu-button JS from P6.2 reused here | P8.1, P8.2 | Shared nav components |
| P8.4 | Mobile QA: bottom tab bar + top drawer split behaves correctly across both user and admin shells | P8.3 | Signed-off responsive shell |

---

## Phase 9 — Virtual Card Module

*Implements SRS Section 4 (FR-4.1–4.4) and SADD Section 6.2.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P9.1 | Build `Controllers/User/VirtualCardController.php` — self-service request flow into `virtual_card_requests` | P3.6, P8.1 | User-side request flow |
| P9.2 | Build `Controllers/Admin/VirtualCardReviewController.php` — pending queue, approve (calls `Providers/Cards/SimulatedCardProvider`) / reject with reason | P4.1, P8.2 | Admin review queue |
| P9.3 | Build `resources/user/virtual-card/*` — request form, status page, approved-card display (decrypted, masked per Design System's card-number field spec) | P9.1, P5.7 | User-facing card pages |
| P9.4 | Wire notifications on approval/rejection (depends on P12 existing, or build the notification insert now and let P12 build the *display* side) | P9.2 | Notification fired on card decision |
| P9.5 | Apply P0.5's decision on `virtual_cards.balance` — either remove all remaining references or formalize it as a displayed feature | P9.3 | Resolved, no dangling references either way |

---

## Phase 10 — Compliance Engine

*Implements SRS Section 5 in full (BR-5–BR-8, FR-5.1–5.5) and SADD Section 6.3. This is the most business-critical module in the plan — get P3.10's tests solid before building on top of them.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P10.1 | Build `Services/ComplianceEngine.php` — `evaluateCredit()`, using P0.11/P0.12's confirmed threshold and cumulative-vs-single rule | P3.4, P0.11, P0.12 | Working auto-flag evaluation |
| P10.2 | Hook `ComplianceEngine::evaluateCredit()` into the admin "send money" credit action (the same hook point applies unchanged if a real deposit rail is ever added) | P10.1 | Every credit is evaluated automatically |
| P10.3 | Build the manual-flag admin action (any account, any reason, admin discretion) writing to the same `compliance_flags` table with `flag_type = 'manual'` | P3.4 | Manual flagging UI |
| P10.4 | Build `resources/admin/compliance/settings.php` — admin-configurable `compliance_settings` (new-account days, USD threshold, cumulative toggle) and `fx_reference_rates` management | P3.4 | Admin settings screens |
| P10.5 | Build `resources/admin/compliance/{flags,assign-code,requirements}.php` — flags queue, code assignment (writes `user_compliance_codes` linked via `flag_id`), and the existing `compliance_requirements` catalog UI, refined | P3.4 | Admin compliance workflow, end to end |
| P10.6 | Build `Controllers/User/ComplianceController.php` and `resources/user/compliance/{verify-code,pending}.php` — oldest-open-first code entry using the OTP-style segmented input from Design System 6.2, and the "review in progress, contact Support" fallback state when a flag has no code yet | P3.4, P5.7 | User-facing compliance flow |
| P10.7 | Add compliance-code rate limiting/lockout after repeated failed attempts (flagged in SADD Section 8, not previously in the SRS — fold back into both docs once built) | P10.6 | Brute-force protection on code entry |
| P10.8 | End-to-end test: seed a new account, credit it over threshold, confirm a flag raises automatically and withdrawal blocks; manually flag a separate account and confirm the same downstream behavior | P10.2, P10.3, P11 (for the withdrawal-blocking check) | Verified compliance gate behavior |

---

## Phase 11 — Withdrawal & Refund Module

*Implements SRS Section 6 (BR-9, FR-6.1–6.5) and SADD Section 6.1 (withdrawal), plus SRS Section 3.1 (BR-2.1, FR-2.1.1–2.1.4) and SADD Section 6.1a (refund) — grouped together because both are the two remaining ways a balance can change outside an admin credit, and both reuse the same `Helpers/Money.php` arithmetic and `transactions`-linkage pattern. P0.13's decision on the completion model determines the shape of P11.4 specifically; refunds (P11.10–P11.13) have no equivalent open decision and aren't blocked by it.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P11.1 | Build `Services/WithdrawalGate.php` — checks approved card → KYC/upgrade → open compliance flags, in order, returns the next required step | P3.4, P3.6, P9 | Gate-sequencing service |
| P11.2 | Build `Controllers/User/WithdrawalController.php` — initiate step, calling `WithdrawalGate` at entry and again just before final submission | P11.1 | Withdrawal entry flow |
| P11.3 | Build the 8 method-specific destination forms (`resources/user/withdraw/method/*.php`) per the exact field table in SRS Section 6/FR-6.1, using the P5.7 field validators (card number, IBAN, crypto address formatting/validation) | P5.7, P11.2 | Bank / Crypto / PayPal / Wise / Skrill / Western Union / Google Pay / Payoneer forms, all actually persisting destination data |
| P11.4 | Build the review & confirm screen and submission handler, creating a `withdrawal_requests` row per P0.13's confirmed completion model | P11.3, P0.13 | Real, trackable withdrawal requests replacing the current instant-debit-only path |
| P11.5 | Build `resources/user/withdraw/success.php` — the actual working final page, replacing the currently-orphaned `withdrawal_success.php` | P11.4 | Working confirmation page |
| P11.6 | Build `Controllers/Admin/WithdrawalReviewController.php` — pending-review queue with full unmasked destination details, approve/reject/mark-complete actions | P11.4 | Admin review workflow |
| P11.7 | Wire the completion action (per P0.13) to insert the real `transactions` row and debit `users.balance`, linked back via `transactions.withdrawal_request_id` | P11.6, P4.5 (Money helper) | Balance-affecting completion, auditable end to end |
| P11.8 | Wire notifications at every status transition (submitted, approved, completed, rejected) | P11.4, P11.6, P12 | Notifications fire on each state change |
| P11.9 | End-to-end test across all 8 methods, both a clean-gate user and a compliance-blocked user | P11.1–P11.8 | Verified withdrawal flow |
| P11.10 | Build `Controllers/Admin/RefundController.php` — issue a refund against a specific transaction (amount, required reason), calling `RefundRepository::create()` per SADD 6.1a | P3.2, P4.5 (Money helper) | Working refund creation |
| P11.11 | Add a "Refund" action to the admin transaction-history view, opening the same confirmation-modal component (`P5.4`) as every other discretionary admin action, tier per Design System Section 2 | P11.10, P5.4 | Refund entry point in the existing admin UI — no new screen needed |
| P11.12 | Wire notification-on-issue (FR-2.1.3) and the `audit_log` write (FR-2.1.4) | P11.10, P12, P16.1 | Refund fully traceable, same as a withdrawal completion |
| P11.13 | End-to-end test: full refund, partial refund, confirm the linked `transactions` row and audit entry are both correct | P11.10–P11.12 | Verified refund flow |

---

## Phase 12 — Notifications

*Implements SRS Section 7 and SADD Section 6.4.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P12.1 | Build `Controllers/Api/NotificationApiController.php` — unread count + latest N, mark-read, mark-all-read | P3.8, P1.19 | JSON notification API |
| P12.2 | Build `components/notifications/_bell.php` and `_preview-dropdown.php`, wired into `layouts/app.php`/`admin.php` | P8.1, P8.2, P12.1 | Header bell + preview, live everywhere |
| P12.3 | Build `assets/js/modules/notifications.js` — polling loop (proposed 45s default per SADD 6.4), mark-read on open | P12.1 | Working live-updating bell |
| P12.4 | Rebuild `resources/user/notifications/index.php` — full page with header/nav (currently missing entirely), All/Unread filter, mark-all-read, pagination | P8.1, P5.8 | Rebuilt notifications page |

---

## Phase 13 — Support: Tickets & First-Party Live Chat

*Implements SRS Section 8 in full and SADD Section 6.5. The largest single module in this plan — sequenced last among the user-facing features because it depends on the notification/agent-alerting pattern established in P12.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P13.1 | Build `Controllers/User/SupportController.php` and `resources/user/support/index.php` — the two-option page (Open a Ticket / Live Chat), consolidating `support.php` + `customer_service.php` | P8.1 | Unified support entry point |
| P13.2 | Build the ticket path: form → `support` table insert (existing table, existing pattern, just moved into the new page) | P13.1, P5.6 | Working ticket submission |
| P13.3 | Build `Services/ChatRuleEngine.php` — an excluded-topic guard (withdrawal/account/compliance keyword/phrase list) runs **first, unconditionally**, and hands off to a human immediately on any match; only messages that clear the guard proceed to keyword matching against `chat_bot_rules`. This is the active check SRS BR-13 requires ("regardless of whether a rule would technically match") — not seeding rules for those topics is necessary but not sufficient on its own, since an unrelated seeded rule could still keyword-collide with a forbidden topic | P3.8, P4.6 | Bot response engine with a verifiable, testable exclusion boundary |
| P13.4 | Seed initial FAQ-only bot rules via `ChatBotRuleSeeder` (support hours, card request how-to, navigation help) | P13.3 | Initial rule catalog |
| P13.5 | Build `Services/ChatEscalationChecker.php` using P0.14's confirmed threshold, and the cron entry point that invokes it (mechanism per P0.4's hosting decision — cPanel cron hitting a CLI script, or a token-protected URL) | P0.14, P0.4, P3.8 | Scheduled unclaimed-chat detection |
| P13.6 | Build `Controllers/Api/ChatApiController.php` — send/poll endpoints, backed by `Providers/Chat/PollingChatProvider` | P4.6, P1.19 | Chat JSON API |
| P13.7 | Build the user-side chat widget: `components/chat/{_widget,_bubble}.php`, `assets/js/modules/chat.js` — queue/claim conversation states, mobile full-screen behavior per Design System, on-demand load only (never auto-loads on any page, including the support page itself before "Live Chat" is clicked) | P13.6, P5.11 | Working first-party chat, user side |
| P13.8 | Build the admin-side chat: `resources/admin/chat/{queue,conversation,bot-rules}.php` — unassigned queue with claim mechanic, live conversation view, bot rule management UI | P13.3, P13.6 | Admin chat workspace |
| P13.9 | Build agent notification, two layers: live dashboard update (same polling channel) plus the fallback — Web Push (VAPID, no third-party account) for opted-in agents, email via `MailProviderFactory` if still unclaimed past P0.14's threshold | P13.5, P4.5 | Two-layer agent alerting |
| P13.10 | Context handoff: pass user name/email/account context automatically into any conversation reaching an agent | P13.8 | Agents never need the user to re-introduce themselves |
| P13.11 | Remove every remaining hard-loaded Tidio script reference sitewide (`support.php`, `customer_service.php`, plus the one already handled in P6.6) | P13.1 | Zero third-party chat widget code left in the app |
| P13.12 | End-to-end test: bot-handled FAQ question, explicit human handoff phrase, unmatched message, unclaimed-timeout escalation, mobile full-screen rendering (per Design System Section 5.3), **and an adversarial case — a message that keyword-collides with a seeded FAQ rule while also referencing an excluded topic (e.g. "where's my withdrawal history"), confirming the topic guard wins and the conversation still escalates to a human** | P13.1–P13.11 | Verified chat system, including the BR-13 exclusion boundary specifically |

---

## Phase 14 — Mail Driver Management

*Implements SRS Section 10 and SADD Section 6.6.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P14.1 | Build `Controllers/Admin/MailSettingsController.php` and `resources/admin/mail-settings/index.php` — driver switch (SMTP/Resend), credential fields, "send test email" action | P4.5, P8.2 | Admin mail configuration screen |
| P14.2 | Audit every existing mail-sending call site in the codebase (verification emails, password reset, any admin notification) and replace direct/hardcoded sending with a call through `MailProviderFactory` | P4.5 | No hardcoded mail credentials anywhere in source |
| P14.3 | Confirm `config/mail_config.php`'s old hardcoded values are fully removed once P14.2 is verified working | P14.2, P0.2 | Closed exposure from Phase 0 |

---

## Phase 15 — Progressive Web App

*Implements SADD Section 6.7.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P15.1 | Generate the icon set (multiple sizes) from the existing NovaTrust logo | — | Icon assets |
| P15.2 | Write `public/manifest.json` | P15.1 | Manifest |
| P15.3 | Write `public/service-worker.js` — static-asset caching only, explicit network-only handling for every authenticated route (never cache balances/transactions/notifications/chat) | — | Service worker |
| P15.4 | Build `assets/js/modules/pwa.js` — registration, called from every page's base layout | P15.3 | Registered service worker across the app |
| P15.5 | Test install flow (Add to Home Screen) on both Android/Chrome and iOS/Safari, and confirm offline behavior fails safely (no stale financial data ever shown) | P15.2–P15.4 | Verified installable PWA |
| P15.6 | If P0.4 confirmed intent to pursue Google Play (optional, per SADD BR-15): package via TWA/Bubblewrap, set up Digital Asset Links | P15.5, P0.4 | Play Store-ready package, if pursued |

---

## Phase 16 — Security & Audit Hardening

*Implements SADD Section 8, consolidating security work that was noted throughout earlier phases but deserves a dedicated closing pass.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P16.1 | Confirm the `audit_log` table is being written to by every admin discretionary action: crediting, approving/rejecting withdrawals and cards, issuing refunds, assigning compliance codes, suspending accounts | P9–P11 (each admin action point) | Full audit coverage |
| P16.2 | CSRF audit — confirm `CsrfMiddleware` covers every new state-changing route added across P6–P15 | All prior phases | Verified CSRF coverage |
| P16.3 | Verify the P4.3 card-data encryption backfill is complete and correct on production, then run the follow-up migration dropping the old plaintext `card_number`/`cvv` columns — deliberately a separate, later migration, never combined with P2.5 | P4.3, production soak time | Plaintext card columns removed |
| P16.4 | Composer dependency audit (`composer audit` or equivalent) across everything pulled in during P4/P13/P14 (mail SDKs, etc.) | P4, P13, P14 | Clean dependency audit |

---

## Phase 17 — Cross-Cutting QA

*Nothing here is phase-specific — it's a full pass across everything built in P5–P16, done once the app is feature-complete.*

| ID | Task | Depends on | Output |
|---|---|---|---|
| P17.1 | Design-system compliance audit: grep for hardcoded hex/px values outside `design-tokens.css`, confirm zero `window.alert`/`confirm`/`prompt` calls anywhere, confirm zero emoji anywhere in UI strings | P5–P16 | Clean audit report |
| P17.2 | Accessibility pass: keyboard focus visibility, color-not-the-only-signal check on every status pill/toast, contrast check, `prefers-reduced-motion` respected including the homepage sequence | P5–P16 | Signed-off accessibility pass |
| P17.3 | Full responsive QA at defined breakpoints, every page, both the marketing site and the authenticated app | P5–P16 | Signed-off responsive behavior |
| P17.4 | Cross-browser check (the browsers your actual user base uses — worth pulling analytics on this before deciding scope) | P17.3 | Browser compatibility confirmed |
| P17.5 | Performance check under the constraints implied by P0.4's hosting decision (shared hosting has real resource ceilings — confirm polling intervals and asset sizes are reasonable under it) | P0.4, P17.3 | Verified acceptable performance |

---

## Phase 18 — Deployment & Production Rollout

| ID | Task | Depends on | Output |
|---|---|---|---|
| P18.1 | Final production backup immediately before cutover (fresh, not the one from P0.17) | P17.1–P17.5 | Verified fresh backup |
| P18.2 | Deploy during a defined low-traffic window; run any remaining migrations per Phase 2's discipline | P18.1 | Code + schema live |
| P18.3 | Smoke test every module directly on production: login, withdrawal (at least one method end to end), card request, compliance code entry (test account), notification bell, support ticket, live chat, PWA install | P18.2 | Verified production behavior |
| P18.4 | Rollback plan on standby: since migrations are forward-only by design (SADD 6.5), a rollback means restoring P18.1's backup and reverting the code deploy — confirm this is understood and rehearsed *before* cutover, not discovered during an incident | P18.1 | Documented, ready rollback path |

---

## Phase 19 — Post-Launch

| ID | Task | Depends on | Output |
|---|---|---|---|
| P19.1 | Monitor the compliance auto-flag engine against real transactions for the first stretch post-launch — confirm P0.11/P0.12's thresholds behave as intended against real account activity, not just test data | P18.3 | Validated real-world compliance behavior |
| P19.2 | Confirm the chat escalation cron (P13.5) is actually firing on schedule in production, not just in staging | P18.3 | Verified scheduled job |
| P19.3 | Review `audit_log` entries after the first week of real admin activity to confirm the schema captures what's actually useful, adjust if not | P16.1, P18.3 | Confirmed or refined audit logging |
| P19.4 | Retire the old root-level files (`index.php`, `about.php`, `blog.php`, `blog-post.php`, `contact.php`, and the `user`/`admin` files replaced by their `Controllers`/`resources` equivalents) once their replacements have been live and stable | P18.3 | Clean, single-source-of-truth codebase — no orphaned legacy files left sitting alongside their replacements |

---

## Appendix — Dependency Map (Phase Level)

```
P0 (decisions/safety net)
 └─▶ P1 (framework core)
      └─▶ P2 (migrations)
           └─▶ P3 (models & repositories)
                ├─▶ P4 (providers)
                └─▶ P5 (design system implementation)
                     └─▶ P6 (public site)
                          └─▶ P7 (auth)
                               └─▶ P8 (authenticated shell)
                                    ├─▶ P9  (virtual card)     ──────┬─▶ P11 (withdrawal & refund)
                                    ├─▶ P10 (compliance) ─────────────┘        ▲
                                    ├─▶ P12 (notifications) ────────────────────┤ (P11.8, P11.12 wire notifications)
                                    └─▶ P13 (support/chat, needs P4 + P12) ─┐  │
                                                                             │  │
                                                                             └──┴─▶ P14 (mail) ─▶ P15 (PWA)
                                                                                                  └─▶ P16 (security hardening)
                                                                                                       └─▶ P17 (cross-cutting QA)
                                                                                                            └─▶ P18 (deployment)
                                                                                                                 └─▶ P19 (post-launch)
```

This corrects the previous version of this diagram, which drew `P9` and `P12` as flowing *through* `P10` on their way to `P11`. They don't — check each task's own "Depends on" column: `P11.1` depends on `P9` directly (it needs an approved-card check), and `P11.8`/`P11.12` depend on `P12` directly (notification wiring), independent of whichever compliance tasks happen to be in flight. `P10` and `P11` are still tightly coupled to each other — most of `P10` must exist before `P11.1`'s gate logic can check compliance flags — but that coupling runs `P10 → P11`, not `P9/P12 → P10 → P11`.

One dependency worth naming explicitly since it doesn't fit a strictly top-down diagram: `P10.8` (compliance's own end-to-end test) depends on `P11` being built, because verifying "withdrawal actually blocks on an open flag" requires the withdrawal flow to exist. So while most of `P10` must be done before `P11.1` can be built, `P10`'s *last* task can't close out until `P11` is done — plan for `P10` and `P11` to be finished by the same close-out pass, not treated as two cleanly sequential phases.

P9, P12, and the early parts of P13 can run in parallel once P8 closes, if you have more than one person building. P10 and P11 are the two modules with the tightest real coupling to each other and to P0.13's decision specifically — I'd staff those together rather than splitting them across two people working independently.

---

*This plan is only as good as Phase 0's decisions being made early — nine of Phase 0's eighteen tasks are pure decisions with no build work attached, and most of the rest of the plan is blocked behind at least one of them. That phase is worth closing out completely before anyone opens an editor.*
