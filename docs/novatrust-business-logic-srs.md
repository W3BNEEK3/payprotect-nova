# NovaTrust (PayProtect Nova) — Business Logic & System Requirements Document

**Version:** 1.2 (Refund workflow added, BR-13 enforcement hardened — closes gaps found in a cross-document consistency review against the SADD and Implementation Plan)
**Prepared for:** Wynston
**Scope of this document:** Refined business logic and functional requirements only. Architecture, tech stack, and technical design will follow in a separate SADD (System Architecture & Design Document) once reviewed.
**Source reviewed:** `github.com/W3BNEEK3/payprotect-nova` (current codebase, as of this review)
**v1.2 changelog:** (1) Added Section 3.1 — a refund workflow was implied by BR-2(b) but never actually specified; the SADD and Implementation Plan had, correctly, nothing to build against. (2) Strengthened BR-13's wording so the bot-exclusion rule reads as a mandatory active check, not a curation guideline the bot-rule catalog happens to follow.

---

## 0. Grounding Notes — What the Current System Actually Does

Before restating the refined logic, this section records what was verified directly in the repository, so the requirements below are anchored to real behavior rather than assumptions. This also surfaces a few things that need attention regardless of the upgrade.

**⚠️ Immediate security flag (not part of the redesign, but urgent):** `config/config.php` and `config/mail_config.php` contain a live database username/password and an SMTP mailbox password committed in plaintext to the repository. If this repo is public, or has ever been public, those credentials should be treated as compromised and rotated immediately, independent of anything else in this document.

Confirmed current behavior:
- **Funding:** There is no self-service deposit. Balances only move when an admin manually credits a user via `admin_send_money.php`. This is effectively today's "deposit" mechanism.
- **Currency:** Each user has one currency set at registration. There is no conversion, multi-wallet, or multi-currency handling anywhere.
- **Compliance codes already exist, partially:** There is a `compliance_requirements` catalog (admin-defined types) and a `user_compliance_codes` table (per-user assignments with a code and `is_cleared` flag), gated in `process_withdraw.php` and checked in `withdraw.php`. This is a real foundation — it is not being invented from scratch, it's being formalized and automated.
- **Withdrawal methods are UI shells:** `withdraw_bank.php`, `withdraw_crypto.php`, `withdraw_paypal.php`, `withdraw_skrill.php`, `withdraw_transferwise.php`, `withdraw_westernunion.php`, `withdraw_googlepay.php`, and `withdraw_payoneer.php` all collect method-specific fields in the form but post straight to `process_withdraw.php` (the compliance code page). None of the destination details (bank account number, wallet address, PayPal email, etc.) are ever saved.
- **The withdrawal flow is orphaned at the end:** `withdrawal_success.php` exists but nothing in the codebase sets the session variable it depends on or redirects to it. The only "success" path currently is an inline HTML block printed directly inside `withdraw.php` on the instant-approval branch. This matches what you described — the flow doesn't follow through to a real last page.
- **Virtual card is a hard gate for withdrawal**, but there's no working self-service request flow — `request_virtual_card.php` inserts into the wrong column (`is_approved` instead of `is_virtual_card_approved`) and loads the wrong config file, so it doesn't actually work today. The "Request Virtual Card" button on `virtual_card.php` just links to Customer Service instead.
- **Notifications** are a flat table with no read/unread state, rendered on a standalone page with no header/nav, and there is no bell icon or preview anywhere in the UI — only a sidebar link.
- **Support is split across two disconnected pages** (`support.php` — a ticket form, and `customer_service.php` — a live-chat landing page), and both hard-load a third-party Tidio chat script unconditionally in the page `<script>` tag, meaning the widget currently loads on whichever of those two pages is visited, with no user choice and no control over where it appears. This is being replaced entirely with a first-party live chat (Section 8).
- **Mail sending is hardcoded** to a single SMTP mailbox in `config/mail_config.php`. There's no driver concept, no admin UI, and no Resend support.
- **Compliance flagging today is entirely manual** — an admin marks an account `suspended` by hand. There is no automatic detection of new-account-plus-large-credit patterns.
- **No PWA foundation exists** — there's no web app manifest or service worker anywhere in the repo, so PWA support (Section 9) is a greenfield addition, not a refinement of something partially built.

These findings inform the "why" behind several rules below.

---

## 1. Actors

| Actor | Description |
|---|---|
| **User (customer)** | Registered account holder. Single currency, single balance, may hold one virtual card. |
| **Admin / Support Agent** | Staff who manage users, credit accounts, review/approve withdrawals and cards, define compliance requirement types, assign compliance codes, manage mail driver settings, and handle support tickets and live chats. (Today there's one undifferentiated admin role — see Open Questions on whether role separation, e.g. Support vs Compliance Officer, is wanted later.) |
| **System (automated)** | Evaluates every inbound credit against the auto-flagging rule and raises compliance flags without human intervention; also runs the rule-based first-response chat handler (Section 8). |

---

## 2. Explicit Non-Goals for This Phase

Per your direction, the following are **deliberately excluded** and should not be designed around, only left extensible for later:

- **Multi-currency support.** Each account keeps a single fixed currency, as today.
- **Self-service deposits / deposit rails.** Funding remains admin-initiated (the existing "send money" mechanism), refined but not replaced with a real payment gateway.
- **iOS App Store submission.** The platform will be a fully installable PWA (Section 9), but getting into Apple's App Store specifically requires a genuinely native/hybrid shell, which is a separate, heavier initiative than this upgrade — see Section 9 for the detail behind this call.

Anything below that touches "currency equivalence" for compliance purposes (Section 5) is an internal risk-calculation detail, not a user-facing multicurrency feature — flagged explicitly where relevant.

---

## 3. Account & Balance Model (Refined)

**BR-1.** Each user has exactly one balance, denominated in the currency selected at registration. No wallets, no conversion.

**BR-2.** Balance only changes via: (a) an admin-issued credit, (b) an approved refund, (c) a completed withdrawal debit. No other pathway may alter balance.

**BR-3.** `account_status` (active / suspended) continues to control login/account access and is a fully separate concept from compliance flags (Section 5). A user can be **active** and still have withdrawals blocked by an open compliance requirement — suspension is reserved for admin discretion (e.g., confirmed fraud, policy violation), not for routine compliance holds.

### 3.1 Refund Workflow (New Detail)

BR-2(b) has always named "an approved refund" as one of exactly three ways a balance can change, but until now nothing defined what that approval process actually looks like — the `refunds` table already exists in the live database (Section 0), but the workflow around it was never specified. This closes that gap, mirroring the same request → review → outcome shape already established for withdrawals and virtual cards, sized down to match how much simpler a refund actually is.

**BR-2.1.** A refund is always **admin-initiated against a specific original transaction** — a user cannot self-initiate a refund request from their side; this is consistent with funding remaining admin-controlled per the Section 2 non-goals (no self-service deposit rail) and keeps the balance-mutation surface small and auditable.

**FR-2.1.1 — Creation.** An admin, viewing a user's transaction history, can issue a refund against any single existing transaction: amount (defaulting to, but not required to equal, the original transaction amount — partial refunds are allowed), and a required reason.

**FR-2.1.2 — Effect.** Creating a refund immediately credits the user's balance by the refund amount and writes a linked `transactions` row (type `refund`, referencing the original transaction), so the balance change is traceable back to its cause the same way a withdrawal debit is traceable via `transactions.withdrawal_request_id`.

**FR-2.1.3 — Notification.** The user is notified (Section 7) the moment a refund is issued, showing the amount, the reason, and which original transaction it relates to.

**FR-2.1.4 — Audit.** Every refund is written to `audit_log` (Section 8 of the SADD) — this is a discretionary admin action moving real balance, the same category of action as a manual compliance flag or an account suspension.

---

## 4. Virtual Card Requirement

**BR-4.** A virtual card remains mandatory before a user can withdraw or transfer funds — this is unchanged from current intent, but the flow is refined to actually work end to end.

**FR-4.1 — Self-service request.** A user with no existing card can submit a card request from `virtual_card.php` (reason field optional). This creates a card record in `pending` state. One card per user; a second request is blocked while one is pending or already approved.

**FR-4.2 — Admin review.** Admin sees pending card requests in a dedicated queue, and can **Approve** (system generates card number/expiry/CVV/cardholder name and sets status to `approved`) or **Reject** (with a reason).

**FR-4.3 — User notification.** Either outcome fires a notification (Section 7) to the user. Approval also reveals the card details on `virtual_card.php`; rejection shows the reason and a path back to Support.

**FR-4.4 — Withdrawal gate.** A withdrawal cannot proceed past the initiation step unless the user's card status is `approved`.

---

## 5. Compliance & Risk Flagging

This is the most important refinement, so it's laid out in full.

### 5.1 Two ways a compliance requirement gets attached to a user

**BR-5 (Automatic).** The system automatically opens a compliance flag when **both** of the following are true at the moment of an inbound credit:
1. The account is still "new" — proposed default: **created within the last 30 days**. *(This threshold is an assumption on my part — flag it for confirmation; it should be an admin-configurable setting, not hardcoded, either way.)*
2. The credit amount, evaluated in USD, is **≥ $7,000** — either as a single credit, or as the **cumulative sum of credits** received since account creation while the account is still "new" (this cumulative check exists so an admin crediting a new user in smaller increments — e.g. $4,000 then $4,000 — still gets caught; recommend this as the safer default, open to confirmation if you'd rather it check single-transaction only).

Because multicurrency isn't in scope as a product feature, currency-equivalence for this check only needs a small **admin-maintained reference rate table** (e.g., "1 EUR = 1.08 USD"), used purely internally to evaluate the $7,000 threshold for accounts not denominated in USD. This is not exposed to users and is not a conversion feature — it's a risk-calculation input.

**BR-6 (Manual).** An admin may flag any account as requiring compliance review at their own discretion, regardless of age or amount, when they judge the account genuinely suspicious for reasons the automatic rule doesn't cover. This is the explicit fallback so admins are never blocked by the automated rule's boundaries.

**BR-7.** Automatic flags never require an admin to manually decide to flag the user — the system raises them by itself the moment the condition is met. Admins only get involved *after* a flag exists, to run the (off-system) compliance process and eventually issue a code.

### 5.2 What happens after a flag is raised

**FR-5.1.** Raising a flag (auto or manual) does **not** suspend the account and does **not** immediately assign a code. It:
- Records the flag (type: `auto_new_account_large_credit` or `manual`, reason, related transaction if applicable, timestamp).
- Notifies the user that a compliance review is required and that they should contact Support to begin the process.
- Blocks withdrawal from that point forward until the flag is resolved.

**FR-5.2.** The actual compliance process (document checks, verification calls, whatever it involves) happens **outside this system**, between the user and Support/third-party tooling, exactly as you described.

**FR-5.3.** Once that off-system process is complete, an admin resolves the flag **from inside the system** by generating a code and assigning it to the user against a compliance requirement type (drawn from the existing `compliance_requirements` catalog — e.g., KYC, IMF, VAT, ARS, or a general "Enhanced Due Diligence" type for the auto-triggered case). The code is then communicated to the user out-of-band by Support.

**FR-5.4.** The user enters the code on the compliance verification screen. A correct match clears that specific assignment. An incorrect code shows an error and does not reveal the correct value.

**BR-8 (Sequential, multiple processes).** A user can accumulate more than one open compliance requirement over time (e.g., an auto-flag now, a manual flag six months later). Each is tracked as its own record. They are presented and cleared **one at a time, oldest first** — a user only ever sees and enters the code for the single oldest unresolved item; once cleared, the next oldest (if any) appears. Withdrawal remains blocked until **all** open items are cleared. This preserves the FIFO pattern already present in the codebase, just formalized.

**FR-5.5.** If a user has an open flag with no code assigned yet (admin hasn't finished the process), the withdrawal flow shows a clear "compliance review in progress, contact Support" state rather than an empty code box — this already exists as a fallback state in the current code and should be kept.

### 5.3 Summary table

| Trigger | Who raises it | What it blocks | How it clears |
|---|---|---|---|
| New account + ≥$7,000 credit (single or cumulative) | System, automatically | Withdrawal only | Admin issues code after off-system process → user enters code |
| Admin judgment (any account) | Admin, manually | Withdrawal only | Same as above |
| Policy violation / confirmed fraud | Admin, manually | Full account (suspension) | Admin reactivates directly — separate from compliance codes |

---

## 6. Withdrawal & Transfer Flow (Refined End-to-End)

**BR-9.** Withdrawal requires, in order: an approved virtual card → existing KYC/account-upgrade verification (unchanged for now) → all compliance requirements cleared (Section 5) → method-specific destination details → review & confirm → submission.

**FR-6.1 — Method-specific data capture.** Each withdrawal method must actually collect and **persist** its destination details, not just display a styled form. Minimum fields per method:

| Method | Required fields |
|---|---|
| Bank Transfer | Bank name, account holder name, account number/IBAN, routing/SWIFT/BIC, bank country |
| Cryptocurrency | Network/chain, wallet address, coin/token |
| PayPal | PayPal email |
| Wise (Transferwise) | Recipient name, Wise account email, payout currency |
| Skrill | Skrill email |
| Western Union | Recipient full name, country, phone number |
| Google Pay | Linked email or phone |
| Payoneer | Payoneer email / account ID |

**FR-6.2 — Review & Confirm.** After destination details are captured, the user sees a single summary screen: amount, method, masked destination details, and any applicable notice, before final confirmation.

**FR-6.3 — Submission creates a real, trackable withdrawal request** with its own status lifecycle: `pending_review` → `approved`/`processing` → `completed`, or `rejected` (with reason). *(Recommendation, flagged for your confirmation: since real payout to a bank/crypto/PayPal destination is currently a manual, off-system action performed by the operator — the same way crediting is — it makes sense for withdrawals to require an admin "release" step rather than instantly auto-debiting the balance the moment gates are cleared, as happens today. If you'd rather keep instant auto-completion once all gates pass, that's a one-line change to this rule — flagging it because it's a real behavior change from what exists now.)*

**FR-6.4 — A working final page.** On submission, the user is routed to an actual confirmation/success page (replacing the currently-orphaned `withdrawal_success.php`) showing the request reference, amount, method, and current status, with a link into transaction history. A notification (Section 7) fires at submission, and again at each subsequent status change (approved / completed / rejected).

**FR-6.5 — Admin visibility.** Admins get a queue of pending withdrawal requests showing the full destination details (unmasked, for legitimate processing) so payouts can actually be carried out off-system and then marked complete.

---

## 7. Notifications (Refined)

**BR-10.** Notifications gain a read/unread state (`is_read`, `read_at`) in addition to their existing title/message/type/timestamp.

**FR-7.1 — Header bell icon.** Every authenticated page shows a bell icon in the header with an unread-count badge.

**FR-7.2 — Preview.** Clicking the bell opens a lightweight preview (dropdown on desktop, modal-style on mobile) listing the most recent notifications (e.g., latest 5) with title, a short excerpt, and relative time. Opening an item from the preview marks it read and links into the full notification on the Notifications page. The preview includes a "View all" link to the full page.

**FR-7.3 — Full Notifications page.** Rebuilt with the current header/nav shell (it currently renders standalone with no navigation at all), grouped by date, filterable by All/Unread, with a "mark all as read" action and pagination for history.

---

## 8. Support (Refined) — First-Party Ticketing & Live Chat

**BR-11.** `support.php` and `customer_service.php` are consolidated into a single Support page with two clear options:
1. **Open a Ticket** — the existing subject/message form, stored against the user.
2. **Live Chat** — a button that, when clicked, opens the chat interface.

**BR-12.** Live chat is a **first-party, custom-built feature** — no third-party embed (this replaces the Tidio scripts noted in Section 0). Message **persistence** and message **delivery** are two separate concerns and must each be handled directly rather than one standing in for the other:
- **Persistence:** every message, in either direction, is written to the database at the moment it's sent. There is no batching, no idle-timeout deferral, and no reliance on client-side storage as the system of record. For a financial platform, a support conversation can end up mattering for a dispute or compliance record later, so messages must not be able to disappear because a tab was closed before a deferred save ran.
- **Delivery:** a live channel between the user's and agent's screens (e.g. a WebSocket connection, or a short-interval poll as a simpler fallback — the exact mechanism is a SADD-level decision once the stack is confirmed) pushes each message to the other party the moment it's sent, independent of and in parallel with persistence. This is what actually makes the chat feel real-time — not deferred saving.
- **Client-side storage's only legitimate role** is holding an in-progress draft (so a refresh doesn't lose unsent typing) and optimistically rendering a message the instant it's sent, before server confirmation arrives. It is never the primary delivery or storage mechanism.

**FR-8.1 — Conversation & queue model.** Each live chat becomes a conversation record tied to the user, and — once claimed — to the assigned agent. New conversations land in an "Unassigned" queue visible to all agents; the first agent to open one claims it, so two agents can't collide on the same user. Conversation states: `bot_handled` → `waiting_for_agent` → `active` → `closed`.

**FR-8.2 — Agent notification, two layers so nothing is missed:**
1. **Agent watching the dashboard:** the same live channel used for message delivery pushes new/updated conversations into the admin panel in real time, with a visual badge and sound alert.
2. **No agent currently watching:** a fallback that doesn't depend on someone staring at a screen — a browser push notification to agents who've opted in, and if a conversation stays unclaimed past a defined threshold (proposed default: **2 minutes**, admin-configurable), an email alert to the support inbox.

**FR-8.3 — Mobile full-screen behavior.** On mobile viewports, the chat interface occupies the full screen once opened, so it reads as part of the app rather than a floating bubble. Because this is now first-party rather than a third-party SDK, this is a direct CSS/layout requirement rather than a vendor-capability question — the uncertainty flagged earlier about third-party widget behavior no longer applies.

**FR-8.4 — Rule-based first responder.** An admin-managed catalog of trigger phrases/keywords mapped to canned responses (the same catalog pattern already used for `compliance_requirements`, so it's a familiar admin experience to build and use). It runs automatically on each incoming message while a conversation is `bot_handled`; the first matching rule sends the reply.

**FR-8.5 — Mandatory handoff conditions.** The bot immediately hands off to the human queue (conversation → `waiting_for_agent`) when **any** of the following is true:
- No rule matches the incoming message.
- The user explicitly asks for a human (e.g. "agent", "human", "representative" — matched via the same rule catalog).
- The matched rule is itself tagged `requires_human`.

**BR-13.** The bot is scoped to **general/FAQ topics only** — support hours, how to request a card, where to find transaction history, general navigation. Anything account-specific, anything about a withdrawal, and anything compliance-related is explicitly excluded from bot handling and routes straight to a human regardless of whether a rule would technically match. This is a deliberate trust and liability boundary for a financial platform, not a coverage gap to close later.

**Implementation note (added in this revision):** "regardless of whether a rule would technically match" means this must be enforced as an **active check that runs before rule matching**, not satisfied merely by never seeding a rule for those topics. A seeded FAQ rule (e.g. "where's my transaction history") can still contain keyword overlap with a forbidden topic ("where's my withdrawal") and incorrectly return a bot answer if nothing is actively blocking it. The excluded-topic list must be checked against the incoming message first; only messages that clear that check are handed to the rule catalog at all.

**FR-8.6 — Context handoff.** Whenever a conversation reaches an agent — whether escalated from the bot or opened directly — the agent sees the user's name, email, and account context automatically, without the user re-introducing themselves.

**FR-8.7 — Ticket path unchanged.** "Open a Ticket" remains the existing subject/message form, stored against the user, for non-real-time inquiries.

---

## 9. Platform & Distribution — Progressive Web App

**BR-14.** The platform will also ship as an installable Progressive Web App. A user can install it directly from their browser (Add to Home Screen) on both Android and iOS — this requires no app-store submission, no review process, and no developer account on either platform to work.

**FR-9.1 — Core PWA requirements.** A web app manifest, a service worker, an HTTPS-only origin (already required), and a standard icon set. This is a light addition once the responsive UI rebuild covered elsewhere in this document is in place — realistically a small, self-contained piece of work, not a rebuild.

**FR-9.2 — No offline caching of financial data.** The service worker's scope is limited to installability and static asset caching. Balances, transaction history, notifications, and any other authenticated financial data use a network-first (or network-only) strategy and are never served from cache while offline. Users should never see a stale balance and mistake it for current — offline should mean "can't reach your account right now," not "here's what we last saw."

**FR-9.3 — In-app notifications are unaffected.** The header bell/preview/full-page notification system (Section 7) is in-page and works identically regardless of PWA install status. Real OS-level push notifications (device alerts while the browser is closed) are a distinct, optional future capability — they require the user to have installed the PWA to their home screen, and iOS support is materially newer and more limited than Android's. Not part of this phase; noted here only so it isn't confused with the notification bell requirement.

**BR-15.** **Google Play listing is optional, and realistic if wanted.** Android supports PWAs directly via Trusted Web Activity (TWA) — the PWA is packaged (e.g. with Bubblewrap), verified as belonging to the same developer via Digital Asset Links, and listed with standard Play Store assets. This can be added on top of a properly built PWA without a separate codebase.

**BR-16.** **Apple App Store listing is out of scope for this phase.** A plain installable PWA does not pass Apple's App Store review — Apple's guidelines explicitly reject "repackaged websites," and a PWA wrapped for submission falls into that category. Reaching the App Store would require a genuinely native or hybrid shell (native navigation, platform-only features like biometric login or native push) layered on top, which is a separate, heavier initiative than this upgrade, not a checkbox on this one. Users on iOS are still fully served via home-screen installation from Safari (BR-14) — they just won't find it by searching the App Store.

---

## 10. Mail Driver Management (New Capability)

**BR-17.** Outbound mail configuration moves out of hardcoded source code into an admin-managed setting.

**FR-10.1.** Admin settings screen to configure and switch between mail drivers:
- **SMTP** — host, port, username, password, encryption mode, from name/address.
- **Resend** — API key, from name/address.

**FR-10.2.** One driver is "active" at a time; switching is immediate for all subsequent outbound mail (verification emails, notifications, etc.).

**FR-10.3.** A "send test email" action lets an admin verify a configuration works before relying on it.

**FR-10.4.** No mail credentials remain in source code after this is implemented — this directly resolves the exposure noted in Section 0.

---

## 11. Traceability Summary

| Area | Business Rules | Functional Requirements |
|---|---|---|
| Account & Balance | BR-1 – BR-3 | — |
| Refund Workflow | BR-2.1 | FR-2.1.1 – FR-2.1.4 |
| Virtual Card | BR-4 | FR-4.1 – FR-4.4 |
| Compliance & Risk | BR-5 – BR-8 | FR-5.1 – FR-5.5 |
| Withdrawal Flow | BR-9 | FR-6.1 – FR-6.5 |
| Notifications | BR-10 | FR-7.1 – FR-7.3 |
| Support (Ticketing & Live Chat) | BR-11 – BR-13 | FR-8.1 – FR-8.7 |
| Platform & Distribution (PWA) | BR-14 – BR-16 | FR-9.1 – FR-9.3 |
| Mail Driver | BR-17 | FR-10.1 – FR-10.4 |

---

## 12. Open Questions / Assumptions to Confirm

These were flagged inline above and are collected here for a fast pass:

1. **"New account" threshold** — proposed 30 days, admin-configurable. Confirm or change.
2. **Cumulative vs single-transaction** for the $7,000 auto-flag check — proposed cumulative-while-new. Confirm.
3. **Withdrawal completion model** — proposed admin-release step before a withdrawal is marked complete (rather than instant auto-debit). Confirm this matches how payouts actually happen operationally.
4. **Admin roles** — currently one undifferentiated admin role. Worth splitting into e.g. Support Agent / Compliance Officer / Super Admin as part of this upgrade, or keep flat for now?
5. **Live chat delivery mechanism** — WebSocket vs. short-interval polling fallback is left open pending the tech stack discussion; either satisfies BR-12, but it affects what infrastructure is needed.
6. **Unclaimed-chat escalation threshold** — proposed 2 minutes before the email fallback fires. Confirm or change.
7. **Bot scope boundary** — proposed: FAQ/navigation only, with account-specific, withdrawal, and compliance topics always excluded regardless of rule match. Confirm this matches your risk tolerance.

---

*Next step: once this is reviewed and adjusted, share the architecture and tech stack you have in mind and I'll produce the SADD covering data model, module structure, and implementation approach for each item above.*
