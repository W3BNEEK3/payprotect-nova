# NovaTrust — Phase 8 Runbook: Authenticated Shell (User & Admin Layout)

**Version:** 1.0
**Prepared for:** Wynston
**Companion to:** `novatrust-implementation-plan.md` v1.4, Phase 8 (`P8.1`–`P8.4`)
**Source:** `novatrust-design-system.md` v1.1, Section 5.2, implemented exactly as specified.
**Scope:** the desktop collapsible sidebar, the top bar (page title, notification bell, account dropdown), the mobile bottom tab bar + secondary drawer split, and both the user and admin shells that share all of it.

**Verified:** every PHP file lints clean. 25 automated checks — a mix of real HTTP session testing (admin login/gating, via Python's `requests`) and real browser interaction (via Playwright: computed styles for the active nav state, sidebar collapse with `localStorage` persistence, account dropdown open/close/outside-click, mobile bottom nav, and the secondary drawer) — all passing. One genuine layout bug was found and fixed; one test assertion was wrong and got corrected instead of the component.

---

## A Real Gap Found Before Any Code Was Written

**No phase anywhere in the Implementation Plan builds admin authentication.** `AdminMiddleware` (Phase 1) checks `Session::has('admin_id')`, and this phase's `layouts/admin.php` is explicitly "gated by `AdminMiddleware`" — but nothing in Phases 1–7 ever sets `admin_id`. Confirmed by grepping the entire plan for "admin login" and "AdminLoginController": zero matches. Without fixing this, the admin shell would have been completely untestable — `AdminMiddleware` would just redirect forever with nowhere to actually log in.

Filled in as part of this phase, since it's the first one that actually needs a working admin session:

- **`app/Controllers/Auth/AdminLoginController.php`** — mirrors `LoginController` exactly, including the same no-account-enumeration generic error.
- **`app/Middlewares/AdminGuestMiddleware.php`** — the admin-side equivalent of `GuestMiddleware`, keeping a logged-in admin off `/admin/login`.
- **`database/seeders/AdminSeeder.php`** — also never built despite being named in the SADD's Section 4 directory tree since the beginning. Seeds one default admin (`admin@novatrust.example` / `change-me-immediately`) so there's a real way to test the admin shell at all. **Change this password immediately in any real environment** — it's a seed value, not a production credential.
- `routes/web.php` gained `/admin/login`, `/admin/logout`, and `/admin/dashboard`.

---

## P8.1 / P8.2 — The Two Shell Layouts

**File: `assets/css/shell.css`** (includes the mobile-overflow fix described below)

```css
/**
 * NovaTrust Authenticated Shell
 * Source: novatrust-design-system.md Section 5.2
 */

.shell {
  display: flex;
  min-height: 100vh;
}

/* ============================================================
   Desktop sidebar — 240px, collapsible to 64px
   ============================================================ */
.shell-sidebar {
  width: 240px;
  flex-shrink: 0;
  background: var(--color-surface);
  border-right: 1px solid var(--slate-300);
  display: flex;
  flex-direction: column;
  transition: width var(--duration-base) var(--ease-out);
  position: sticky;
  top: 0;
  height: 100vh;
}
.shell-sidebar.collapsed {
  width: 64px;
}

.shell-sidebar-brand {
  padding: var(--space-6) var(--space-4);
  font: 600 20px/1 var(--font-display);
  color: var(--color-ink);
  text-decoration: none;
  white-space: nowrap;
  overflow: hidden;
}

.shell-nav {
  flex: 1;
  padding: var(--space-2);
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.shell-nav-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3);
  border-radius: var(--radius-sm);
  border-left: 3px solid transparent;
  color: var(--slate-700);
  text-decoration: none;
  font: 500 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);
  white-space: nowrap;
  overflow: hidden;
  position: relative;
  transition: background-color var(--duration-fast) var(--ease-out);
}
.shell-nav-item:hover {
  background: var(--color-paper);
}
.shell-nav-item.active {
  background: var(--color-teal-100);
  border-left-color: var(--color-teal);
  color: var(--color-ink);
}
.shell-nav-item .material-symbols-outlined {
  font-size: 22px;
  flex-shrink: 0;
}
/* Active item's icon switches to Filled — one item carries the fill
   treatment at a time (Section 5.2). */
.shell-nav-item.active .material-symbols-outlined {
  font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

.shell-sidebar.collapsed .shell-nav-item span.label,
.shell-sidebar.collapsed .shell-sidebar-brand span.full {
  display: none;
}
.shell-sidebar.collapsed .shell-nav-item {
  justify-content: center;
  padding-left: 0;
  padding-right: 0;
}

.shell-sidebar.collapsed .shell-nav-item::after {
  content: attr(data-label);
  position: absolute;
  left: calc(100% + var(--space-2));
  top: 50%;
  transform: translateY(-50%);
  background: var(--color-ink);
  color: #fff;
  padding: var(--space-1) var(--space-3);
  border-radius: var(--radius-sm);
  font: 500 var(--type-caption-size)/var(--type-caption-lh) var(--font-body);
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity var(--duration-fast) var(--ease-out);
  z-index: 50;
}
.shell-sidebar.collapsed .shell-nav-item:hover::after {
  opacity: 1;
}

.shell-sidebar-collapse-toggle {
  border-top: 1px solid var(--slate-100);
  padding: var(--space-3);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  background: transparent;
  border-left: none;
  border-right: none;
  border-bottom: none;
  color: var(--slate-500);
  width: 100%;
}
.shell-sidebar-collapse-toggle:hover {
  background: var(--color-paper);
  color: var(--color-ink);
}
.shell-sidebar.collapsed .shell-sidebar-collapse-toggle .material-symbols-outlined {
  transform: rotate(180deg);
}

/* ============================================================
   Desktop top bar
   ============================================================ */
.shell-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.shell-topbar {
  height: 68px;
  background: var(--color-surface);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--space-6);
  position: sticky;
  top: 0;
  z-index: 100;
  transition: box-shadow var(--duration-base) var(--ease-out);
}
.shell-topbar.scrolled {
  box-shadow: var(--shadow-sm);
}

.shell-page-title {
  font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display);
  color: var(--color-ink);
}

.shell-topbar-right {
  display: flex;
  align-items: center;
  gap: var(--space-4);
}

.shell-bell {
  position: relative;
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--slate-700);
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-pill);
}
.shell-bell:hover {
  background: var(--color-paper);
}
.shell-bell .material-symbols-outlined {
  font-size: 24px;
}
.shell-bell.has-unread .material-symbols-outlined {
  font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
  color: var(--color-teal);
}
.shell-bell-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: var(--color-danger);
  color: #fff;
  font: 600 10px/1 var(--font-data);
  min-width: 16px;
  height: 16px;
  border-radius: var(--radius-pill);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 3px;
}

.shell-account {
  position: relative;
}
.shell-account-trigger {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  background: transparent;
  border: none;
  cursor: pointer;
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
}
.shell-account-trigger:hover {
  background: var(--color-paper);
}
.shell-account-avatar {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-pill);
  background: var(--color-teal-100);
  color: var(--color-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  font: 600 13px/1 var(--font-display);
}
.shell-account-name {
  font: 500 var(--type-body-md-size)/1 var(--font-body);
  color: var(--color-ink);
}
.shell-account-trigger .material-symbols-outlined {
  font-size: 18px;
  color: var(--slate-500);
  transition: transform var(--duration-fast) var(--ease-out);
}
.shell-account.open .shell-account-trigger .material-symbols-outlined {
  transform: rotate(180deg);
}

.shell-account-menu {
  position: absolute;
  top: calc(100% + var(--space-2));
  right: 0;
  background: var(--color-surface);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  min-width: 180px;
  padding: var(--space-2);
  opacity: 0;
  transform: translateY(-8px);
  pointer-events: none;
  transition: opacity var(--duration-fast) var(--ease-out), transform var(--duration-fast) var(--ease-out);
  z-index: 200;
}
.shell-account.open .shell-account-menu {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}
.shell-account-menu a,
.shell-account-menu button {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  width: 100%;
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-sm);
  color: var(--slate-700);
  text-decoration: none;
  font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);
  background: transparent;
  border: none;
  cursor: pointer;
  text-align: left;
}
.shell-account-menu a:hover,
.shell-account-menu button:hover {
  background: var(--color-paper);
}
.shell-account-menu .material-symbols-outlined {
  font-size: 18px;
  color: var(--slate-500);
}

.shell-content {
  flex: 1;
  padding: var(--space-6);
}

/* ============================================================
   Mobile — sidebar disappears, bottom tab bar + top drawer instead
   ============================================================ */
.shell-bottom-nav {
  display: none;
}

@media (max-width: 900px) {
  .shell-sidebar {
    display: none;
  }

  .shell-topbar {
    padding: 0 var(--space-4);
  }

  /* Account name/dropdown is redundant on mobile — Profile/Settings/Log Out
     already live in the secondary drawer, and showing the full account name
     next to the hamburger was overflowing the topbar on narrow viewports. */
  .shell-account {
    display: none;
  }

  .shell-content {
    padding: var(--space-4);
    padding-bottom: calc(64px + var(--space-4));
  }

  .shell-bottom-nav {
    display: flex;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 64px;
    background: var(--color-surface);
    box-shadow: 0 -4px 12px rgba(11, 31, 58, 0.08);
    z-index: 300;
    padding-bottom: env(safe-area-inset-bottom, 0px);
  }

  .shell-bottom-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    text-decoration: none;
    color: var(--slate-500);
  }

  .shell-bottom-nav-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 24px;
    border-radius: var(--radius-pill);
  }
  .shell-bottom-nav-item.active .shell-bottom-nav-icon-wrap {
    background: var(--color-teal);
  }
  .shell-bottom-nav-item.active .material-symbols-outlined {
    color: #fff;
    font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
  }
  .shell-bottom-nav-item.active .shell-bottom-nav-label {
    color: var(--color-ink);
    font-weight: 600;
  }

  .shell-bottom-nav-label {
    font: 500 10px/1 var(--font-body);
  }
}

@media (min-width: 901px) {
  .shell-mobile-menu-btn {
    display: none;
  }
}
```

### The one real bug this phase found

**The account dropdown trigger (avatar + full name + chevron, 207px wide) had no mobile treatment**, so on a 390px viewport the top bar's flex row — bell + account trigger + hamburger — didn't fit and pushed the hamburger button 49px past the right edge of the screen, causing horizontal scroll on every single authenticated page. Caught by the automated overflow check, not by eye (visually the page looked fine at first glance; the hamburger was just slightly off-screen, easy to miss without measuring). Fixed by hiding `.shell-account` entirely below 900px — its functions (Profile/Settings/Log Out) already live in the secondary drawer, so nothing is lost, just de-duplicated.

**File: `assets/js/modules/shell.js`**

```javascript
/**
 * NovaTrust Authenticated Shell
 * Source: novatrust-design-system.md Section 5.2
 */
(function () {
  'use strict';

  const COLLAPSE_STORAGE_KEY = 'novatrust_sidebar_collapsed';

  function initSidebarCollapse() {
    const sidebar = document.querySelector('.shell-sidebar');
    const toggle = document.querySelector('.shell-sidebar-collapse-toggle');

    if (!sidebar || !toggle) {
      return;
    }

    if (localStorage.getItem(COLLAPSE_STORAGE_KEY) === 'true') {
      sidebar.classList.add('collapsed');
    }

    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      localStorage.setItem(COLLAPSE_STORAGE_KEY, sidebar.classList.contains('collapsed'));
    });
  }

  function initTopbarScrollShadow() {
    const topbar = document.querySelector('.shell-topbar');
    if (!topbar) {
      return;
    }

    function update() {
      if (window.scrollY > 4) {
        topbar.classList.add('scrolled');
      } else {
        topbar.classList.remove('scrolled');
      }
    }

    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  function initAccountDropdown() {
    const account = document.querySelector('.shell-account');
    const trigger = document.querySelector('.shell-account-trigger');

    if (!account || !trigger) {
      return;
    }

    function close() {
      account.classList.remove('open');
      document.removeEventListener('click', onOutsideClick);
      document.removeEventListener('keydown', onEscape);
    }

    function onOutsideClick(e) {
      if (!account.contains(e.target)) {
        close();
      }
    }

    function onEscape(e) {
      if (e.key === 'Escape') {
        close();
        trigger.focus();
      }
    }

    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = account.classList.toggle('open');
      if (isOpen) {
        document.addEventListener('click', onOutsideClick);
        document.addEventListener('keydown', onEscape);
      } else {
        close();
      }
    });
  }

  /**
   * Mobile secondary drawer — same morphing hamburger-to-X mechanism as the
   * marketing site (Section 5.1 / P6.2's header.js), reused here per P8.3,
   * but opening notifications/settings/profile/logout instead of nav links.
   */
  function initMobileDrawer() {
    const btn = document.querySelector('.shell-mobile-menu-btn');
    const drawer = document.querySelector('.mobile-drawer');
    const backdrop = document.querySelector('.mobile-drawer-backdrop');
    const closeBtn = document.querySelector('.mobile-drawer-close');

    if (!btn || !drawer || !backdrop) {
      return;
    }

    let lastFocused = null;

    function open() {
      lastFocused = document.activeElement;
      btn.classList.add('open');
      drawer.classList.add('open');
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', onKeydown);
      const firstLink = drawer.querySelector('a, button');
      if (firstLink) {
        requestAnimationFrame(() => firstLink.focus());
      }
    }

    function close() {
      btn.classList.remove('open');
      drawer.classList.remove('open');
      backdrop.classList.remove('open');
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onKeydown);
      if (lastFocused) {
        lastFocused.focus();
      }
    }

    function onKeydown(e) {
      if (e.key === 'Escape') {
        close();
      }
    }

    btn.addEventListener('click', () => {
      if (drawer.classList.contains('open')) {
        close();
      } else {
        open();
      }
    });

    closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);
  }

  document.addEventListener('DOMContentLoaded', () => {
    initSidebarCollapse();
    initTopbarScrollShadow();
    initAccountDropdown();
    initMobileDrawer();
  });
})();
```

**File: `resources/layouts/app.php`** (user shell) — assembles the sidebar (via `resources/components/navigation/_sidebar.php`), top bar, bottom nav, and secondary mobile drawer around `$content`. Nav items:

```php
$sidebarItems = [
    ['href' => '/dashboard', 'icon' => 'space_dashboard', 'label' => 'Dashboard'],
    ['href' => '/withdraw', 'icon' => 'sync_alt', 'label' => 'Withdraw'],
    ['href' => '/virtual-card', 'icon' => 'credit_card', 'label' => 'Virtual Card'],
    ['href' => '/transactions', 'icon' => 'receipt_long', 'label' => 'Transactions'],
    ['href' => '/notifications', 'icon' => 'notifications', 'label' => 'Notifications'],
    ['href' => '/support', 'icon' => 'support_agent', 'label' => 'Support'],
];

// The bottom tab bar is a curated 4-item subset, per Section 5.2's exact
// named list: "Dashboard / Withdraw / Cards / Support".
$bottomNavItems = [
    ['href' => '/dashboard', 'icon' => 'space_dashboard', 'label' => 'Home'],
    ['href' => '/withdraw', 'icon' => 'sync_alt', 'label' => 'Withdraw'],
    ['href' => '/virtual-card', 'icon' => 'credit_card', 'label' => 'Cards'],
    ['href' => '/support', 'icon' => 'support_agent', 'label' => 'Support'],
];
```

**File: `resources/layouts/admin.php`** — identical structure, admin nav items:

```php
$sidebarItems = [
    ['href' => '/admin/dashboard', 'icon' => 'space_dashboard', 'label' => 'Dashboard'],
    ['href' => '/admin/users', 'icon' => 'group', 'label' => 'Users'],
    ['href' => '/admin/withdrawals', 'icon' => 'sync_alt', 'label' => 'Withdrawals'],
    ['href' => '/admin/virtual-cards', 'icon' => 'credit_card', 'label' => 'Virtual Cards'],
    ['href' => '/admin/compliance', 'icon' => 'verified_user', 'label' => 'Compliance'],
    ['href' => '/admin/chat', 'icon' => 'forum', 'label' => 'Live Chat'],
    ['href' => '/admin/mail-settings', 'icon' => 'mail', 'label' => 'Mail Settings'],
];

$bottomNavItems = [
    ['href' => '/admin/dashboard', 'icon' => 'space_dashboard', 'label' => 'Home'],
    ['href' => '/admin/users', 'icon' => 'group', 'label' => 'Users'],
    ['href' => '/admin/withdrawals', 'icon' => 'sync_alt', 'label' => 'Withdraw'],
    ['href' => '/admin/chat', 'icon' => 'forum', 'label' => 'Chat'],
];
```

**Verified — the sidebar, top bar, and their interactions:**
```
PASS — Exactly one sidebar item has the active class
PASS — Active nav item background is exactly --color-teal-100, got rgb(227, 242, 241)
PASS — Active nav item left border is exactly --color-teal, got rgb(14, 124, 123)
PASS — Active nav item's icon uses the Filled variant
PASS — Sidebar collapses from 240px toward 64px, got 64px
PASS — Sidebar collapse state persists across a reload (localStorage), got 64px
PASS — Account dropdown starts closed
PASS — Account dropdown opens on trigger click
PASS — Account dropdown closes on outside click
PASS — Notification bell shows the unread badge (placeholder count=3)
```

---

## P8.3 — Shared Navigation Components

**File: `resources/components/navigation/_sidebar.php`**

```php
<?php
/**
 * resources/components/navigation/_sidebar.php
 *
 * Expects, from the including layout:
 *   $sidebarBrand   (string)
 *   $sidebarItems   (array) — [['href' => '/dashboard', 'icon' => 'space_dashboard', 'label' => 'Dashboard'], ...]
 *   $currentPath    (string) — for active-state matching
 */
?>
<aside class="shell-sidebar" id="shellSidebar">
  <a href="<?= $sidebarItems[0]['href'] ?? '/' ?>" class="shell-sidebar-brand">
    <span class="full"><?= htmlspecialchars($sidebarBrand) ?></span>
  </a>

  <nav class="shell-nav">
    <?php foreach ($sidebarItems as $item): ?>
      <?php $isActive = $currentPath === $item['href'] || str_starts_with($currentPath, $item['href'] . '/'); ?>
      <a href="<?= htmlspecialchars($item['href']) ?>"
         class="shell-nav-item<?= $isActive ? ' active' : '' ?>"
         data-label="<?= htmlspecialchars($item['label']) ?>">
        <span class="material-symbols-outlined"><?= htmlspecialchars($item['icon']) ?></span>
        <span class="label"><?= htmlspecialchars($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <button type="button" class="shell-sidebar-collapse-toggle" aria-label="Collapse sidebar">
    <span class="material-symbols-outlined">chevron_left</span>
  </button>
</aside>
```

**File: `resources/components/navigation/_bottom-nav.php`**

```php
<?php
/**
 * resources/components/navigation/_bottom-nav.php
 *
 * Expects:
 *   $bottomNavItems (array) — the 4-5 primary destinations
 *   $currentPath    (string)
 */
?>
<nav class="shell-bottom-nav" aria-label="Primary">
  <?php foreach ($bottomNavItems as $item): ?>
    <?php $isActive = $currentPath === $item['href'] || str_starts_with($currentPath, $item['href'] . '/'); ?>
    <a href="<?= htmlspecialchars($item['href']) ?>" class="shell-bottom-nav-item<?= $isActive ? ' active' : '' ?>">
      <span class="shell-bottom-nav-icon-wrap">
        <span class="material-symbols-outlined"><?= htmlspecialchars($item['icon']) ?></span>
      </span>
      <span class="shell-bottom-nav-label"><?= htmlspecialchars($item['label']) ?></span>
    </a>
  <?php endforeach; ?>
</nav>
```

Active-state matching uses `str_starts_with($currentPath, $item['href'] . '/')` alongside an exact match — so `/withdraw/bank` (a sub-page that doesn't exist yet, built in Phase 11) will correctly keep "Withdraw" highlighted rather than only matching the exact `/withdraw` path.

**The mobile secondary drawer reuses the exact hamburger-to-X morph CSS from Phase 6** (`P6.2`'s `.mobile-menu-btn`/`.bar-top`/`.bar-mid`/`.bar-bottom` classes) rather than a new pattern — same visual language, different content (notifications/settings/support/logout instead of marketing nav links).

**Verified:**
```
PASS — Sidebar is hidden on mobile viewport
PASS — Bottom nav is visible on mobile viewport
PASS — Bottom nav has exactly 4 items (Section 5.2's named list), got 4
PASS — The correct bottom nav item (Home/Dashboard) is active, got 'Home'
PASS — Secondary drawer opens from the mobile top bar hamburger
PASS — Secondary drawer contains the expected secondary links, got 4
```

---

## P8.4 — Mobile QA + Admin Gating

**Verified — the admin authentication gap-fill, tested as thoroughly as Phase 7's user auth:**
```
PASS — A regular logged-in user CANNOT reach /admin/dashboard (AdminMiddleware)
PASS — Regular user hitting /admin/dashboard lands on the ADMIN login page specifically
PASS — Admin login page loads (200)
PASS — Wrong admin password rejected with generic error
PASS — Correct admin credentials log in and reach the admin dashboard
PASS — Logged-in admin is redirected away from /admin/login (AdminGuestMiddleware)
PASS — An ADMIN-only session is still blocked from /dashboard (AuthMiddleware checks user_id, not admin_id)
```

That last check matters more than it looks — it confirms `user_id` and `admin_id` are genuinely independent session keys, not a shared "is this someone logged in" flag. An admin session should never accidentally grant access to user-only routes, and vice versa; this was verified directly rather than assumed from reading the middleware code.

**Verified — final mobile pass, post-fix:**
```
PASS — No horizontal overflow on the dashboard shell at 390px, got scrollWidth=390
```

---

## Phase 8 Exit Checklist

- [ ] `AdminSeeder` run against staging, default admin password changed immediately after first login
- [ ] Sidebar active-state, collapse toggle (with `localStorage` persistence), and tooltip-on-hover-when-collapsed all verified
- [ ] Account dropdown open/close/outside-click/Escape all verified
- [ ] Mobile: sidebar hidden, bottom nav showing the correct 4 items, account dropdown hidden (moved to drawer) — **no horizontal overflow anywhere**
- [ ] Admin gating verified both directions: regular users blocked from `/admin/*`, and an admin-only session confirmed NOT to grant `/dashboard` access
- [ ] `AdminGuestMiddleware` confirmed to keep a logged-in admin off `/admin/login`
- [ ] Placeholder `DashboardController`/`AdminDashboardController` clearly marked as temporary — real content starts arriving in Phase 9

---

## Addendum (post-delivery): Admin Auth Moved to `/control-center/auth`, Redesigned

Two changes made after this runbook's original delivery, per direct feedback:

**1. URL obscurity.** `/admin/login` and `/admin/logout` are now `/control-center/auth` and `/control-center/logout` — `/admin/login` returns a real `404`, confirmed directly. This is a legitimate hardening pattern: predictable admin paths (`/admin`, `/admin/login`, `/wp-admin`, etc.) are the first thing automated scanners and credential-stuffing bots probe for. `/control-center/auth` doesn't guess.

**Open question worth deciding explicitly, not silently:** `AdminMiddleware` still redirects an unauthenticated visit to `/admin/dashboard` (or any other `/admin/*` route, once Phases 9+ build them) to `/control-center/auth` — which means scanning `/admin/dashboard` still reveals that an admin system exists and where its login lives, even though the login URL itself is no longer guessable. Full obscurity would mean moving the *entire* admin route namespace under `/control-center/*` (e.g. `/control-center/dashboard`, `/control-center/users`), not just the auth routes. Only the auth routes were changed here, matching exactly what was asked — flagging the rest as a deliberate choice to make later, not an oversight now.

**2. Visual redesign.** The admin login previously reused the consumer login's exact layout with swapped labels — same light Paper background, same warm consumer-facing card. Rebuilt as a genuinely distinct page:

- Dark `--color-ink` background instead of light `--color-paper`
- A faint ambient grid backdrop (low-opacity, an abstracted echo of the marketing hero's ledger-line motif from Section 5.1, as texture rather than illustration)
- A teal shield-lock badge and a tracked-out "CONTROL CENTER" eyebrow label — visual registers borrowed from the design system's existing vocabulary (Section 5.1's eyebrow pattern, Section 1's teal-on-dark treatment), not invented from scratch
- Dark-mode form field overrides scoped specifically to this page (`.cc-card .input`, etc.) rather than a second global input theme
- `<meta name="robots" content="noindex, nofollow">` — this page has no reason to ever appear in a search index

**File: `resources/layouts/control-center-auth.php`**

```php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Control Center — NovaTrust') ?></title>
<meta name="robots" content="noindex, nofollow">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

<link rel="stylesheet" href="/assets/css/design-tokens.css">
<link rel="stylesheet" href="/assets/css/buttons.css">
<link rel="stylesheet" href="/assets/css/toast.css">
<link rel="stylesheet" href="/assets/css/control-center-auth.css">
<style>.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;vertical-align:middle;}</style>
</head>
<body class="cc-body">

<div class="cc-grid-backdrop" aria-hidden="true"></div>

<div class="cc-card">
  <div class="cc-badge">
    <span class="material-symbols-outlined">shield_lock</span>
  </div>
  <div class="cc-eyebrow">CONTROL CENTER</div>
<?= $content ?? '' ?>
  <a href="/" class="cc-footer-link">&larr; Return to novatrust.example</a>
</div>

<script src="/assets/js/modules/validation.js"></script>
<script src="/assets/js/modules/field-handlers.js"></script>
<script src="/assets/js/toast.js"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
```

**File: `assets/css/control-center-auth.css`**

```css
/**
 * NovaTrust Control Center — Admin Auth
 *
 * Deliberately distinct from the consumer auth pages (resources/layouts/auth.php):
 * dark Ink background instead of Paper, a technical/restricted-access register
 * (eyebrow label, shield badge, subtle grid backdrop) rather than the warm,
 * approachable consumer login. Same design tokens throughout — this is a
 * different composition of the same system, not a different system.
 */

.cc-body {
  margin: 0;
  min-height: 100vh;
  background: var(--color-ink);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-6);
  position: relative;
  overflow: hidden;
}

.cc-grid-backdrop {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
  background-size: 40px 40px;
  mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
  pointer-events: none;
}

.cc-card {
  position: relative;
  z-index: 1;
  background: var(--color-ink-700);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  padding: var(--space-8);
  max-width: 400px;
  width: 100%;
}

.cc-badge {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-pill);
  background: rgba(14, 124, 123, 0.15);
  border: 1px solid rgba(14, 124, 123, 0.35);
  color: var(--color-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto var(--space-4);
}
.cc-badge .material-symbols-outlined {
  font-size: 26px;
}

.cc-eyebrow {
  text-align: center;
  color: var(--color-teal);
  font: 600 11px/1 var(--font-data);
  letter-spacing: 0.14em;
  margin-bottom: var(--space-6);
}

.cc-card .auth-title { color: #fff; }
.cc-card .auth-subtitle { color: var(--slate-300); }
.cc-card .field-label { color: var(--slate-300); }
.cc-card .field-hint { color: rgba(255, 255, 255, 0.4); }
.cc-card .input {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.15);
  color: #fff;
}
.cc-card .input::placeholder { color: rgba(255, 255, 255, 0.32); }
.cc-card .input:focus {
  border-color: var(--color-teal);
  box-shadow: 0 0 0 3px rgba(14, 124, 123, 0.28);
}
.cc-card .input.has-error { border-color: var(--color-danger); }
.cc-card .password-toggle { color: rgba(255, 255, 255, 0.45); }
.cc-card .password-toggle:hover { color: #fff; }
.cc-card .auth-error-banner {
  background: rgba(192, 57, 43, 0.15);
  border: 1px solid rgba(192, 57, 43, 0.35);
}
.cc-card .auth-error-banner .type-body-md { color: #fff; }
.cc-card .field-error { color: #ff9a8c; }

.cc-footer-link {
  display: block;
  text-align: center;
  margin-top: var(--space-6);
  font: 400 var(--type-caption-size)/var(--type-caption-lh) var(--font-body);
  color: rgba(255, 255, 255, 0.4);
  text-decoration: none;
}
.cc-footer-link:hover { color: rgba(255, 255, 255, 0.7); }

.cc-card .auth-form .btn { width: 100%; }
```

**Verified — 10 checks, functional and visual:**
```
PASS — Control Center auth page loads at the new URL
PASS — Page title reflects Control Center, not generic admin
PASS — Page has noindex/nofollow (shouldn't be crawlable)
PASS — Wrong password still rejected correctly at the new URL
PASS — Correct login at the new URL reaches the admin dashboard
PASS — Logout at the new URL correctly clears the admin session
PASS — Body background is dark Ink, not the light consumer Paper, got rgb(11, 31, 58)
PASS — Shield badge icon present (visual distinction from consumer login)
PASS — CONTROL CENTER eyebrow label present, got 'CONTROL CENTER'
PASS — Consumer login background is DIFFERENT from Control Center (Paper vs Ink), got rgb(247, 248, 250)
```

Every other Phase 8 deliverable (sidebar, top bar, bottom nav, both shells' content areas) is unaffected — this addendum only touches the two admin auth routes and their page.


---

*End of Phase 8 Runbook. Next: Implementation Plan Phase 9 (`P9.1`–`P9.5`) — the Virtual Card Module, the first phase to build real content inside the shell this phase just delivered.*
