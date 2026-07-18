# NovaTrust — UI/UX Design System

**Version:** 1.1 — reclassifies "Reject Withdrawal" severity tier (was inconsistent with this document's own reversibility rule), adds the mobile full-screen live-chat spec referenced by the SRS and Implementation Plan but never actually written
**Prepared for:** Wynston
**Applies to:** every surface in the SADD — marketing site, user dashboard, admin panel, PWA
**v1.1 changelog:** (1) Section 2 — "Reject Withdrawal" moves from Destructive to Caution; per the SRS's admin-release withdrawal model, rejecting a withdrawal never touches the user's balance, so it doesn't meet this document's own bar for red ("genuinely cannot take back"). (2) Section 5.3 (new) — the mobile full-screen chat layout that SRS FR-8.3 and the Implementation Plan both cite "the Design System" for, but which this document never actually specified until now.

---

## 0. Direction

**The thesis:** NovaTrust should feel like *precise, calm competence* — the visual equivalent of a bank teller who's seen every kind of transaction and is never rattled by yours. Not playful, not cold. The kind of interface that makes a $7,000 withdrawal feel as routine and controlled as checking a balance, because the design never raises its voice except when something genuinely needs your attention.

**Register:** Serious, minimal, precision over decoration. Confidence expressed through restraint and exactness — tight alignment, deliberate spacing, no ornamental flourishes — not through bold color or playful motion. The one place the system allows itself real expression is the marketing homepage (Section 5.1); everywhere money and account data live, the design gets quieter, not louder.

**The signature:** Two typefaces do two different jobs and never swap. Prose (labels, navigation, body copy, headings) is set in a humanist sans. Every number that means something — balances, transaction amounts, account numbers, card numbers, references, timestamps in tables — is set in a monospace face with tabular figures. This isn't decoration; it's how you'd typeset a real ledger, and it makes financial data instantly recognizable as *data* anywhere it appears in the product, at a glance, before you've even read it.

**Icons and fonts — non-negotiable:** No emoji, anywhere, in any surface, including toasts, empty states, and admin tooling. Icons are **Google Material Symbols** (Outlined by default). Type is **Google Fonts** only. Both are specified exactly in Section 1.

---

## 1. Foundations (Design Tokens)

Every value below should exist exactly once, as a CSS custom property, and nowhere else as a hardcoded hex/px value in the codebase. This is the enforcement mechanism for Section 7's consistency rules.

### 1.1 Color

| Token | Hex | Role |
|---|---|---|
| `--color-ink` | `#0B1F3A` | Primary structural color — headers, nav, dark surfaces, primary text |
| `--color-ink-700` | `#16304F` | Ink hover/pressed state |
| `--color-teal` | `#0E7C7B` | **Primary interactive accent** — the one color that means "click me" |
| `--color-teal-600` | `#0B6564` | Teal hover/pressed state |
| `--color-teal-100` | `#E3F2F1` | Teal tint — active nav backgrounds, selected states, low-emphasis highlights |
| `--color-paper` | `#F7F8FA` | App/page background |
| `--color-surface` | `#FFFFFF` | Cards, inputs, modals, table rows — anything sitting on `--color-paper` |
| `--color-success` | `#1B8A5A` | Positive completion — approvals, cleared status |
| `--color-success-100` | `#E4F5EC` | Success tint background (badges, toast body) |
| `--color-warning` | `#B7791F` | Caution — reversible-but-notable states |
| `--color-warning-100` | `#FBF0DF` | Warning tint background |
| `--color-danger` | `#C0392B` | Destructive / high-risk / errors |
| `--color-danger-100` | `#FBE8E6` | Danger tint background |
| `--slate-900` | `#111827` | Primary body text |
| `--slate-700` | `#374151` | Secondary text |
| `--slate-500` | `#6B7280` | Placeholder text, disabled, captions |
| `--slate-300` | `#D1D5DB` | Borders, dividers |
| `--slate-100` | `#F1F3F5` | Hairline rules, chart gridlines, skeleton base |

**Rule that gives buttons meaning (Section 4.1 depends on this):** Teal, Success, Warning, and Danger are never used interchangeably. Teal means *forward/neutral progress*. Green means *this completed successfully or was approved*. Amber means *pause and reconsider, but it's reversible*. Red means *this cannot be undone*. A withdrawal debiting a balance is not, by itself, a "danger" — it's a normal Teal/Success flow. Red is reserved for things a user or admin genuinely cannot take back.

### 1.2 Typography

```css
--font-display: 'Space Grotesk', sans-serif;   /* headings, hero, page titles */
--font-body:    'IBM Plex Sans', sans-serif;   /* everything else: labels, body, nav, buttons */
--font-data:    'IBM Plex Mono', monospace;    /* the signature — see Section 0 */
```

All three load from Google Fonts. Weights needed: Space Grotesk 500/600/700, IBM Plex Sans 400/500/600, IBM Plex Mono 400/500/600.

| Token | Face | Size / Line-height | Weight | Use |
|---|---|---|---|---|
| `--type-display-xl` | display | 48px / 56px | 600 | Marketing hero headline only |
| `--type-display-lg` | display | 36px / 44px | 600 | Marketing section headers |
| `--type-heading-lg` | display | 28px / 36px | 600 | Dashboard/admin page titles |
| `--type-heading-md` | display | 22px / 30px | 500 | Card/section headers, modal titles |
| `--type-heading-sm` | body | 18px / 26px | 600 | Sub-section labels |
| `--type-body-lg` | body | 16px / 24px | 400 | Primary body copy, form labels |
| `--type-body-md` | body | 14px / 20px | 400 | Table cells, secondary UI text |
| `--type-caption` | body | 12px / 16px | 500, +0.02em tracking | Timestamps, helper text, badges |
| `--type-data-lg` | data | 32px / 38px | 600, tabular-nums | Balance displays, hero stats |
| `--type-data-md` | data | 14px / 20px | 500, tabular-nums | Table amounts, account/card numbers, references |

`font-feature-settings: "tnum" 1;` (tabular figures) is mandatory wherever `--font-data` is used, so digits never shift width as they change — this matters for anything that updates live (a balance ticking, a table re-sorting).

### 1.3 Iconography

**Google Material Symbols, Outlined, weight 400, grade 0, optical size 24** as the default everywhere. Two deliberate, functional exceptions:
- **Filled** variant for an icon's *active/selected* state only — e.g. the notification bell fills when there are unread items, the current bottom-nav tab icon fills while inactive tabs stay outlined. This gives the fill axis real meaning instead of using it decoratively.
- **Rounded** variant, not Outlined, inside chat bubbles and the compliance-code/OTP inputs specifically — a marginally softer geometry in the two spots where the product is having a direct conversation with the user, versus the precise Outlined style everywhere data-dense or transactional.

Never mix in emoji, never mix in a different icon set. If a concept has no clean Material Symbols match, that's a signal to rethink the UI copy, not to reach for an emoji.

### 1.4 Spacing, Radius, Elevation

```css
/* Spacing — 4px base unit */
--space-1: 4px;  --space-2: 8px;  --space-3: 12px; --space-4: 16px;
--space-6: 24px; --space-8: 32px; --space-12: 48px; --space-16: 64px; --space-24: 96px;

/* Radius */
--radius-sm: 6px;    /* inputs, chips, OTP boxes, small buttons */
--radius-md: 10px;   /* buttons, cards, table containers */
--radius-lg: 16px;   /* modals, large panels, the mobile menu drawer */
--radius-pill: 999px;/* avatars, status pills, the mobile bottom-nav active pill */

/* Elevation */
--shadow-sm: 0 1px 2px rgba(11,31,58,0.06);              /* resting cards */
--shadow-md: 0 4px 12px rgba(11,31,58,0.08);              /* dropdowns, notification preview */
--shadow-lg: 0 16px 40px rgba(11,31,58,0.16);             /* modals, the mobile drawer */
```

### 1.5 Motion

```css
--ease-out: cubic-bezier(0.16, 1, 0.3, 1);   /* entrances — confident, slightly overshoots then settles */
--ease-in:  cubic-bezier(0.7, 0, 0.84, 0);   /* exits — quick departure */
--duration-fast: 120ms;   /* micro-interactions: button press, checkbox toggle */
--duration-base: 200ms;   /* toasts, dropdowns, modals */
--duration-slow: 360ms;   /* orchestrated homepage sequences only */
```

`prefers-reduced-motion: reduce` must collapse every transition/animation in this document to an instant state change or a 1-frame opacity crossfade — no exceptions, including the homepage.

---

## 2. Buttons — The Action Weight Scale

The word you were reaching for is **severity**, or equivalently **risk weight** — how much does it cost the user if this action turns out to be a mistake. Five tiers, each with exactly one color, so the color alone tells you what kind of action you're about to take before you read the label:

| Tier | Color | Fill | Use for | Example |
|---|---|---|---|---|
| **Primary** | `--color-teal` | Solid | The one forward action on a screen | "Continue," "Submit Ticket," "Send Message" |
| **Secondary** | `--color-ink` | Outline, transparent fill | Alternative/parallel action, not the main path | "Back," "Save Draft" |
| **Success** | `--color-success` | Solid | Confirms a positive, often admin-side, completion | "Approve Withdrawal," "Clear Compliance Code," "Approve Card" |
| **Caution** | `--color-warning` | Solid | Reversible but consequential | "Suspend Account," "Reject with Review," "Flag for Compliance," "Reject Withdrawal" |
| **Destructive** | `--color-danger` | Solid | Irreversible or high-risk | "Delete," "Permanently Block Card" |
| **Ghost** | `--color-teal` text, no fill/border | Text-only | Low-emphasis, tertiary | "Learn more," inline links, "Skip" |

**Rule:** a screen never has more than one Primary button visible at once — if two actions both feel "primary," one of them is actually Secondary or Success and needs re-classifying, not two teal buttons side by side.

**Why "Reject Withdrawal" is Caution, not Destructive:** the test for red is Section 1's own rule — "genuinely cannot take back." Under the SRS's admin-release withdrawal model, a rejected withdrawal never debits the user's balance; the user simply resubmits. That's the same shape of consequence as rejecting a virtual card request, which this table already treats as Caution ("Reject with Review"). Reserve Destructive for actions with no path back — blocking a card permanently, deleting a record — not for "no, try again."

```css
.btn {
  font: 600 var(--type-body-md)/1 var(--font-body);
  padding: var(--space-3) var(--space-6);
  border-radius: var(--radius-md);
  transition: transform var(--duration-fast) var(--ease-out),
              background-color var(--duration-fast) var(--ease-out);
}
.btn:active { transform: scale(0.97); }
.btn-primary     { background: var(--color-teal);    color: #fff; }
.btn-primary:hover { background: var(--color-teal-600); }
.btn-secondary   { background: transparent; color: var(--color-ink); border: 1.5px solid var(--slate-300); }
.btn-success     { background: var(--color-success); color: #fff; }
.btn-caution     { background: var(--color-warning); color: #fff; }
.btn-destructive { background: var(--color-danger);  color: #fff; }
.btn-ghost       { background: transparent; color: var(--color-teal); padding-inline: var(--space-2); }
.btn:disabled    { opacity: 0.45; cursor: not-allowed; transform: none; }
```

---

## 3. Confirmation Modals — No Browser Alerts, Anywhere

**Hard rule:** `window.alert`, `window.confirm`, and `window.prompt` are banned from this codebase entirely. Every "are you sure" moment — rejecting a withdrawal, blocking a card, suspending a user, closing a chat, deleting a compliance rule — uses the same modal component. This is one of the most load-bearing consistency rules in this whole system: a user should never see two different visual languages for "confirm this action" depending on which page they're on.

**Structure:**
```
┌─────────────────────────────────────┐
│  [icon badge]                    [×]  │  ← icon color matches the action's tier
│                                        │
│  Title — plain language, states the   │
│  action, not "Are you sure?"          │
│                                        │
│  One or two sentences: what happens,  │
│  and whether it can be undone.        │
│                                        │
│              [Cancel]  [Confirm Tier] │  ← Cancel is always Secondary/Ghost,
└─────────────────────────────────────┘     Confirm takes the action's real tier color
```

Concretely, for a destructive action: icon badge is a `--color-danger-100` circle containing an Outlined `warning` symbol in `--color-danger`. Title: "Block this card?" Body: "The cardholder won't be able to use it for any transaction. This can be reversed by re-approving a new card, but the blocked card number is permanently retired." Buttons: `Cancel` (Secondary) / `Block Card` (Destructive) — **the confirm button is always labeled with the actual verb, never "Confirm" or "OK."**

```css
.modal-overlay {
  background: rgba(11,31,58,0.6);
  backdrop-filter: blur(2px);
  animation: overlayIn var(--duration-base) var(--ease-out);
}
.modal-dialog {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  max-width: 440px;
  animation: dialogIn var(--duration-base) var(--ease-out);
}
@keyframes overlayIn { from { opacity: 0 } to { opacity: 1 } }
@keyframes dialogIn  { from { opacity: 0; transform: scale(0.96) translateY(8px) }
                        to   { opacity: 1; transform: scale(1) translateY(0) } }
```

Behavior: `Escape` and backdrop click both act as Cancel (never as Confirm — so an accidental dismiss is always the safe outcome). Focus traps inside the dialog and returns to the triggering element on close. On mobile, the dialog anchors to the bottom of the viewport as a sheet (rounds only the top corners) rather than floating centered, so it's reachable with a thumb.

---

## 4. Toasts

**Position:** top-right on desktop (clear of the sidebar/content, doesn't block the primary action area), top-center on mobile, respecting safe-area insets so it never sits under a device notch or the browser chrome.

**Anatomy:** left-edge 3px color bar (tier color) + Outlined icon in tier color + message in `--type-body-md` + close button. Background is always `--color-surface`, never the tint color — the tier reads through the edge bar and icon, not a wash of color across the whole toast (keeps a stack of toasts calm rather than a wall of colored blocks).

| Variant | Color | Icon | Default duration |
|---|---|---|---|
| Success | `--color-success` | `check_circle` | 4s |
| Info | `--color-teal` | `info` | 4s |
| Warning | `--color-warning` | `warning` | 6s |
| Error | `--color-danger` | `error` | **Manual dismiss only** — errors on a financial platform don't get to disappear before you've read them |

**Stacking:** newest toast enters at the top of the stack; maximum 3 visible at once, older ones beyond that are dismissed early rather than piling up. Each toast shows a thin progress bar along its bottom edge counting down to auto-dismiss (paused on hover) — except Error toasts, which show no progress bar since they don't auto-dismiss.

```css
@keyframes toastInDesktop  { from { opacity:0; transform: translateX(24px) } to { opacity:1; transform: translateX(0) } }
@keyframes toastInMobile   { from { opacity:0; transform: translateY(-16px) } to { opacity:1; transform: translateY(0) } }
@keyframes toastOut        { from { opacity:1 } to { opacity:0; transform: scale(0.98) } }
.toast { animation: toastInDesktop var(--duration-base) var(--ease-out); }
.toast.leaving { animation: toastOut var(--duration-fast) var(--ease-in) forwards; }
```

**Voice:** matches the action verb exactly (Section 0's writing principle) — a "Block Card" confirm produces a toast that says "Card blocked," never "Success!" or "Done!"

---

## 5. Layout Patterns

### 5.1 Marketing Home Page

**Desktop header:** Fixed to top. On page load, transparent with white/Ink text over the hero; on scroll past the hero (~80px), it crossfades to a solid `--color-surface` background with `--shadow-sm` and Ink text, over `var(--duration-base)`. Logo left. Nav center-right: Home / About / Blog / Contact, `--type-body-md` weight 500. Far right: `Log In` as Ghost button, `Get Started` as Primary button. This scroll transition is a small, deliberate detail — the header should feel like it belongs to the hero at first, then becomes a normal navigation tool once you've moved past it.

**Mobile header:** Logo left, single custom menu button right — not a static hamburger. A three-line icon that morphs into an X on tap (the top and bottom lines rotate 45° and meet in the middle, the center line fades out, all in `var(--duration-fast)`), opening a full-height drawer sliding in from the right (`--radius-lg` on its leading edge, `--shadow-lg`) with `Home / About / Blog / Contact / Log In / Get Started` revealed as a staggered fade-up (each link delayed 30ms after the last) rather than appearing all at once.

**Hero imagery direction:** Not generic fintech stock photography (no laptop-on-a-desk, no diverse-group-shaking-hands cliché). Two acceptable directions, either works with the token system:
1. **Abstract line-art motif** — interlocking geometric forms suggesting a card, a shield, and ledger lines, rendered in `--color-ink` and `--color-teal` on `--color-paper`, animated on load as a sequence: the shapes draw themselves in stroke-by-stroke (SVG `stroke-dashoffset` animation), settling into the final mark as the headline fades up beside it. Ties directly into the "ledger" signature.
2. **Real photography, tightly art-directed** — if genuine photography is preferred, it should show a specific, confident moment (someone glancing at their phone mid-stride, a hand tapping a card reader) graded toward the Ink palette (desaturated, cool, slightly underexposed shadows) rather than the bright, oversaturated look most stock banking photography uses.

**Homepage motion sequence (page load):** eyebrow label fades up (0ms) → headline fades up (80ms delay) → subhead fades up (140ms) → CTA buttons fade up (200ms) → hero visual resolves (throughout, per above). Each element moves 12px upward while fading in, `var(--duration-slow)`, `var(--ease-out)`. Below the fold: each section's content reveals on scroll intersection (fade + 16px upward shift, triggered once, not on every scroll pass — repeated reveal-on-every-scroll reads as gimmicky rather than polished).

### 5.2 Dashboard — Header & Sidebar

**Desktop sidebar:** 240px fixed left rail, `--color-surface` background (not dark — a light, precise sidebar suits "banking tool" better than a dark-mode SaaS cliché), `--slate-300` 1px right border. Each nav item: Outlined icon + label, `--type-body-md`. Active item gets a `--color-teal-100` background, `--color-teal` left border (3px), and the icon switches to its Filled variant — one item can carry the fill treatment at a time, which is exactly what makes it legible as "current location" rather than decoration. Collapsible to a 64px icon-only rail via a toggle at the sidebar's bottom edge (icon persists, label disappears, tooltip on hover shows the label).

**Desktop top bar:** sits beside the sidebar, `--color-surface`, `--shadow-sm` on scroll only (flat when at the top of a page). Left: current page title (`--type-heading-md`). Right: notification bell (Section 6 of the SRS — Filled when unread, badge count in `--color-danger` if count > 0) → admin/user avatar + name → chevron opening an account dropdown (Profile / Settings / Log Out).

**Mobile:** sidebar disappears entirely, replaced by two coordinated pieces:
- **Bottom tab bar**, fixed, `--color-surface`, `--shadow-lg` (casts upward), 4–5 primary destinations (Dashboard / Withdraw / Cards / Support, admin equivalent for the admin panel) as Outlined icon + micro-label, active tab gets the Filled icon plus a small `--color-teal` pill (`--radius-pill`) behind the icon — this is the same "confident but not showy" active-state language as desktop, just relocated.
- **Top bar** stays for anything that doesn't fit the bottom tab bar: page title left, the same morphing hamburger-to-X button from the marketing site (Section 5.1) on the right, opening a drawer with notifications, settings, profile, and log out — secondary items only, since the primary ones already live in the thumb-reachable bottom bar.

This split (bottom tabs for the handful of things you do constantly, top drawer for everything else) is deliberate, not arbitrary — it's the same pattern established consumer banking apps converge on because it keeps the most frequent actions in thumb range without cramming a full nav into a bottom bar.

---

### 5.3 Live Chat — Mobile Full-Screen Behavior

Referenced by SRS FR-8.3 and built by Implementation Plan `P13.7`, specified here for the first time.

**Trigger:** tapping "Live Chat" on the Support page (or the persistent chat bubble, once a conversation is active) opens the interface. On mobile, it does **not** open as a floating widget or bottom sheet — it takes the full viewport, so it reads as a screen within the app rather than a bubble layered on top of one.

**Entry/exit animation:** slides up from the bottom over `var(--duration-base)`, `var(--ease-out)` on open; reverses with `var(--ease-in)` on close. This is deliberately the same directional language as a native app pushing a new screen, not a modal fading in place — it should feel like *navigating to* chat, not *popping open* chat.

**Structure, top to bottom:**
- **Header** (fixed): back arrow (`arrow_back`, Outlined) on the left — returns to the Support page, does not end the conversation — center-aligned title ("Live Chat" while `bot_handled`/`waiting_for_agent`, or the agent's name once `active`), and a subtle status dot (Section 7's semantic colors: `--color-warning` while waiting, `--color-success` once an agent has joined) beside the title.
- **Message thread** (scrollable, fills remaining height): standard chat-bubble pattern — user messages right-aligned in `--color-teal-100` fill with `--color-ink` text, agent/bot messages left-aligned in `--color-surface` with a `--slate-300` border. Bubble corners use `--radius-md`, with the Rounded icon variant (Section 1.3) for any inline icons (attachment, delivery check) — this is one of the two deliberate Rounded exceptions already named in Section 1.3.
- **Composer** (fixed to bottom, above the safe-area inset): text input + send button, matching the standard `.input` treatment from Section 6, disabled with a brief inline note ("Connecting…") only in the rare case the transport layer hasn't established yet — never blocking the user from typing a draft regardless of connection state, per the SRS's client-side-draft allowance (BR-12).

**Desktop contrast, stated for clarity:** the same full-screen behavior does not apply above the responsive breakpoint — desktop keeps the conversation in a docked panel/bubble as is conventional for that viewport size; full-screen is a mobile-only behavior specifically because there's no room to dock anything smaller on a phone without it feeling cramped.

**Reduced motion:** per Section 1.5, the slide becomes an instant state change with `prefers-reduced-motion: reduce` — no exception for chat.

---

## 6. Forms

### 6.1 Validation Philosophy

- **Never validate on every keystroke.** A field is left alone until the user leaves it (`blur`) for the first time, or the form is submitted — validating a mid-typed email as invalid on keystroke 3 punishes the user for not being finished yet.
- **After a field has shown an error once, it re-validates live** as the user types the fix — so the error clears the moment it's actually resolved, rather than requiring another blur.
- **Submitting a form with errors** never silently fails — it scrolls to and focuses the first invalid field, gives it a brief horizontal shake (`--duration-fast`, ±4px, 2 cycles), and shows every field's inline error simultaneously so the user isn't fixing one at a time.
- **Errors render inline, below the field**, in `--color-danger`, `--type-caption`, with a small `error` Outlined icon — never as a toast (a toast for a single field's format error is noise; toasts are reserved for whole-form or whole-action outcomes) and never as a browser-native validation bubble.

```css
.field-error {
  color: var(--color-danger);
  font: 500 var(--type-caption)/1.3 var(--font-body);
  display: flex; align-items: center; gap: var(--space-1);
  margin-top: var(--space-1);
}
.input.has-error { border-color: var(--color-danger); }
@keyframes shake { 25% { transform: translateX(-4px) } 75% { transform: translateX(4px) } }
.shake { animation: shake var(--duration-fast) var(--ease-out) 2; }
```

### 6.2 Field Behavior by Data Type

| Field | While typing | Formatting | Error trigger & message |
|---|---|---|---|
| **Email** | Free text | None | On blur: format regex check → "Enter a valid email address" |
| **Phone** | Digits auto-grouped as typed | International grouping mask | On blur: length check for detected pattern → "Enter a valid phone number" |
| **Amount (withdrawal)** | Numeric only, decimal to 2 places | Thousands separator applied on blur, currency symbol prefixed (fixed, from account currency) | Live, non-blocking: if entered amount exceeds available balance, inline warning appears immediately ("Exceeds available balance") and Continue disables — but keystrokes are never blocked |
| **Card number (bank withdrawal / display)** | Digits only | Grouped in 4s, rendered in `--font-data` | On blur: length check per method |
| **CVV** | Digits only, masked (dots) | 3–4 digit max, `--font-data` | On blur: length check |
| **IBAN / account number** | Alphanumeric, auto-uppercased | Grouped in 4s, `--font-data` | On blur: length/pattern check per country format |
| **Crypto wallet address** | Free paste, case preserved exactly (never auto-transformed) | `--font-data`, and truncated with a copy icon (`0x71C7…6F3E` + `content_copy`) anywhere it's shown read-only | On blur: basic length/charset check for the selected network only — never "corrected" |
| **Compliance / OTP code** | Segmented boxes, one character per box, auto-advances focus, supports paste-and-distribute across boxes | Uppercase if alphanumeric, `--font-data`, Rounded icon style per Section 1.3 | On submit only: "Incorrect code — check with Support and try again" (never reveals what the correct value would have been) |
| **Password** | Free text, masked | Show/hide toggle using `visibility`/`visibility_off` Outlined icons | Live strength indicator (bar in danger→warning→success color as strength increases), blur validates minimum requirements |
| **Method selector (withdrawal)** | N/A — selection, not typing | Visually distinct icon + label per method (bank/crypto/PayPal/etc.), selected state matches the sidebar's active-item treatment (teal tint + left border) | N/A |

---

## 7. Tables

### 7.1 Desktop — Horizontal

Standard table, sticky header row (`--color-surface`, `--slate-300` bottom border, stays pinned on scroll within the table's container). ID/reference/amount columns use `--font-data`; everything else `--font-body`. Row hover: `--color-paper` background shift, `var(--duration-fast)`; if the row is clickable, cursor becomes a pointer and the hover also nudges a trailing chevron icon 2px rightward. No zebra striping — hairline `--slate-100` row dividers only, which reads calmer and more "ledger" than alternating shading. Sortable headers show a Material Symbols `arrow_upward`/`arrow_downward` beside the label, appearing on hover and staying visible once that column is the active sort.

**New rows arriving live** (e.g. a transaction appearing via the notification/chat polling described in the SADD): fade+slide in from the top over `var(--duration-base)`, with a brief `--color-teal-100` background flash that fades to transparent over 1.2s — draws the eye without being jarring.

**Loading state:** skeleton rows — `--slate-100` blocks in place of text, animated with a slow left-to-right shimmer gradient. **Empty state:** centered Outlined icon relevant to the table's content (e.g. `receipt_long` for an empty transaction table), one line of plain-language explanation, and a Primary or Ghost CTA if there's a relevant action ("Make your first withdrawal").

### 7.2 Mobile — Vertical Stacked Cards

Below the responsive breakpoint, the table restructures into a list of cards — one per row — using the standard label/value stacking technique so it stays pure CSS/HTML with no JS reflow required:

```css
@media (max-width: 720px) {
  table thead { display: none; }
  table, tbody, tr, td { display: block; width: 100%; }
  tr {
    background: var(--color-surface);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    margin-bottom: var(--space-3);
    padding: var(--space-4);
  }
  td {
    display: flex; justify-content: space-between; align-items: center;
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--slate-100);
  }
  td:last-child { border-bottom: none; }
  td::before {
    content: attr(data-label);
    font: 500 var(--type-caption)/1 var(--font-body);
    color: var(--slate-500);
  }
}
```

The row's most identifying information (typically amount + status) is pulled to the top of the card and rendered larger (`--type-data-md` for the amount, a status pill beside it) rather than treated as just another label/value pair — so scanning a stack of cards works the same way scanning a table's first two columns would on desktop.

---

## 8. Charts

Minimal line and bar charts only — no 3D, no gradient fills, no drop shadows on data elements. Gridlines are `--slate-100`, 1px, horizontal only (vertical gridlines add clutter without adding information in most of this product's charts). Data labels and axis figures use `--font-data`. 

**Color mapping follows Section 1's semantic rule strictly:** a line showing transaction volume over time uses `--color-teal` — it's neutral, informational data, not a judgment. Reserve `--color-success`/`--color-danger` in charts for genuinely evaluative moments (e.g. a compliance dashboard showing cleared vs. flagged accounts) rather than using green/red for ordinary credits/debits, which would wrongly imply a debit is "bad."

Tooltips on hover match the toast/card visual language: `--color-surface` background, `--shadow-md`, `--font-data` for the value, `--type-caption` for the label — never a dark tooltip that breaks from the rest of the palette. Chart empty states match Section 7.1's table empty state exactly, for consistency.

---

## 9. Do's and Don'ts

**Do:**
- Reuse the exact token values in Section 1 everywhere — a hardcoded hex or px value outside this file is a bug, not a style choice.
- Use the same confirmation modal component for every "are you sure" moment, regardless of which part of the app it's in.
- Match a button's color tier to the actual severity of the action, every time — never pick a color because it "looks nice" for that screen.
- Keep the numeric typeface (`--font-data`) applied consistently to every amount, ID, and reference across the entire product, including inside charts, toasts, and modals.
- Let the marketing homepage be the one place motion gets expressive; keep the dashboard/admin motion functional and quiet.

**Don't:**
- Don't use `window.alert`, `window.confirm`, or `window.prompt` anywhere, for any reason.
- Don't use emoji as icons, status indicators, or decoration anywhere in the product.
- Don't introduce a second icon set, a second font family, or a one-off color "just for this page."
- Don't use red for a routine debit/withdrawal — red is reserved for genuinely irreversible or erroneous states.
- Don't validate a field's format while the user is still mid-keystroke on their first pass through it.
- Don't let more than one Primary (teal) button appear on the same screen at the same time.
- Don't repeat scroll-triggered reveal animations every time an element re-enters the viewport — once per element, per page load.

---

## 10. Accessibility Baseline

Not explicitly asked for, but part of the quality floor for a product handling people's money: every interactive element has a visible keyboard focus ring (`2px solid var(--color-teal)`, offset 2px — never `outline: none` without a replacement). Color is never the only signal — every status pill and toast pairs its color with an icon and a text label, so the system remains legible for color-blind users. All body text maintains WCAG AA contrast against its background (`--slate-900` on `--color-paper`/`--color-surface` clears this comfortably; double-check any text placed directly on `--color-teal` or `--color-danger` fills, which need white text, not `--color-ink`). `prefers-reduced-motion` is respected everywhere per Section 1.5, including the homepage's load sequence.

---

*This document governs every page referenced in the SADD. If a new page or component doesn't clearly map to something in Sections 1–8, that's a sign to extend this system deliberately — via a follow-up to this document — rather than improvising a one-off treatment in the code.*
