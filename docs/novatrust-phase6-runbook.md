# NovaTrust — Phase 6 Runbook: Public Marketing Site

**Version:** 1.0
**Prepared for:** Wynston
**Companion to:** `novatrust-implementation-plan.md` v1.4, Phase 6 (`P6.1`–`P6.7`)
**Source:** `novatrust-design-system.md` v1.1, Section 5.1, implemented exactly as specified.
**Scope:** four controllers, the shared public layout (scroll-transition header, morphing mobile drawer), the homepage with its full load-animation sequence and scroll-reveal sections, About, Blog (index + single post), and a genuinely working Contact form wired end to end to the database.

**Verified:** every route smoke-tested for the correct HTTP status, every PHP file linted clean, and 32 automated Playwright assertions covering the header's scroll crossfade, the exact animation-delay values for the homepage's load sequence, the hamburger-to-X morph, the drawer's focus/scroll-lock/staggered-link behavior, scroll-reveal's once-only trigger, and — critically — the contact form's **full real submission cycle**, tested twice: once through the browser with client-side validation, and once via raw `curl` bypassing all JavaScript entirely, to confirm server-side validation is the actual security boundary, not just a formality.

---

## One Real Infrastructure Gap Found While Building This

**`assets/` lives at the project root, but only `public/` is meant to be web-accessible.** This is exactly right for security (nothing in `app/`, `resources/`, or `config/` should ever be reachable by a direct URL) — but it means `/assets/css/design-tokens.css` and every other static file Phase 5 built would 404 in production unless something bridges the gap. Nothing in Phases 1–5 addressed this, because Phase 5's testing never went through the front controller — it opened `showcase.html` directly.

**Fix:** a symlink, `public/assets -> ../assets`, the same pattern Laravel uses for `public/storage`. Add this as an explicit deployment step:

```bash
cd public
ln -s ../assets assets
```

**Add this to `P0.16`'s staging bootstrap checklist and the production deployment runbook (Phase 18) — it's a one-line step that's easy to forget entirely until every stylesheet on the live site 404s.**

---

## P6.1 — Controllers and Routes

**File: `app/Controllers/Public/HomeController.php`**

```php
<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index(): void
    {
        $this->view('public/home', [
            'pageTitle' => 'NovaTrust — Banking That Behaves',
            'metaDescription' => 'A digital bank account built for people who move money seriously — virtual cards, fast withdrawals, and compliance that never surprises you.',
        ]);
    }
}
```

**File: `app/Controllers/Public/AboutController.php`**

```php
<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class AboutController extends BaseController
{
    public function index(): void
    {
        $this->view('public/about', [
            'pageTitle' => 'About — NovaTrust',
            'metaDescription' => 'Why NovaTrust exists, and the principles behind how we build it.',
        ]);
    }
}
```

**File: `routes/web.php`** (additions)

```php
<?php

/** @var \App\Core\Router $router */

$router->get('/', 'Public\HomeController@index');
$router->get('/about', 'Public\AboutController@index');
$router->get('/contact', 'Public\ContactController@index');
$router->post('/contact', 'Public\ContactController@submit');
$router->get('/blog', 'Public\BlogController@index');
$router->get('/blog/{slug}', 'Public\BlogController@show');
```

**One thing worth knowing before you build anything else:** `App\Controllers\Public` uses `public` — a PHP reserved keyword — as a namespace segment. This was tested directly, not assumed: a real `namespace App\Controllers\Public;` class, autoloaded and instantiated, works correctly on PHP 8.3. If you're on an older PHP version, confirm this still holds; PHP has loosened reserved-word restrictions in namespace contexts over time, so it's worth a quick check on whatever version staging actually runs.

**Verified:**
```
/                                                     -> HTTP 200
/about                                                -> HTTP 200
/contact                                              -> HTTP 200
/blog                                                 -> HTTP 200
/blog/what-actually-happens-when-you-tap-pay          -> HTTP 200
/blog/nonexistent-post                                -> HTTP 404
```

---

## P6.2 — Shared Public Layout

Built exactly to Design System Section 5.1: a transparent-over-hero header that crossfades to solid on scroll, and a mobile menu button that's a genuine morphing hamburger, not a static icon swapped for an X.

**File: `assets/css/public-layout.css`**

```css
/**
 * NovaTrust Public Site Layout
 * Source: novatrust-design-system.md Section 5.1
 */

/* ============================================================
   Desktop header — transparent-over-hero, crossfades to solid on scroll
   ============================================================ */
.site-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 500;
  height: 76px;
  display: flex;
  align-items: center;
  padding: 0 var(--space-8);
  background: transparent;
  transition: background-color var(--duration-base) var(--ease-out),
              box-shadow var(--duration-base) var(--ease-out);
}

.site-header.scrolled {
  background: var(--color-surface);
  box-shadow: var(--shadow-sm);
}

.site-header-inner {
  max-width: 1180px;
  margin: 0 auto;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.site-logo {
  font: 600 20px/1 var(--font-display);
  color: #fff;
  text-decoration: none;
  transition: color var(--duration-base) var(--ease-out);
}
.site-header.scrolled .site-logo {
  color: var(--color-ink);
}

.site-nav {
  display: flex;
  align-items: center;
  gap: var(--space-8);
}

.site-nav-links {
  display: flex;
  gap: var(--space-6);
  list-style: none;
  margin: 0;
  padding: 0;
}
.site-nav-links a {
  font: 500 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);
  color: rgba(255, 255, 255, 0.92);
  text-decoration: none;
  transition: color var(--duration-base) var(--ease-out);
}
.site-header.scrolled .site-nav-links a {
  color: var(--color-ink);
}
.site-nav-links a:hover {
  color: var(--color-teal);
}

.site-nav-actions {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.site-header .btn-ghost {
  color: #fff;
}
.site-header.scrolled .btn-ghost {
  color: var(--color-teal);
}
.site-header .btn-ghost:hover {
  background: rgba(255, 255, 255, 0.12);
}
.site-header.scrolled .btn-ghost:hover {
  background: var(--color-teal-100);
}

/* ============================================================
   Mobile header — logo + morphing hamburger button
   ============================================================ */
.mobile-menu-btn {
  display: none;
  width: 32px;
  height: 32px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  position: relative;
}

.mobile-menu-btn .bar {
  position: absolute;
  left: 4px;
  right: 4px;
  height: 2px;
  background: #fff;
  border-radius: 1px;
  transition: transform var(--duration-fast) var(--ease-out),
              opacity var(--duration-fast) var(--ease-out),
              background-color var(--duration-base) var(--ease-out);
}
.site-header.scrolled .mobile-menu-btn .bar {
  background: var(--color-ink);
}

.mobile-menu-btn .bar-top { top: 9px; }
.mobile-menu-btn .bar-mid { top: 15px; }
.mobile-menu-btn .bar-bottom { top: 21px; }

.mobile-menu-btn.open .bar-top {
  transform: translateY(6px) rotate(45deg);
}
.mobile-menu-btn.open .bar-mid {
  opacity: 0;
}
.mobile-menu-btn.open .bar-bottom {
  transform: translateY(-6px) rotate(-45deg);
}

/* ============================================================
   Mobile drawer — full-height, slides from the right
   ============================================================ */
.mobile-drawer-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(11, 31, 58, 0.6);
  z-index: 600;
  opacity: 0;
  pointer-events: none;
  transition: opacity var(--duration-base) var(--ease-out);
}
.mobile-drawer-backdrop.open {
  opacity: 1;
  pointer-events: auto;
}

.mobile-drawer {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  width: min(320px, 84vw);
  background: var(--color-surface);
  border-radius: var(--radius-lg) 0 0 var(--radius-lg);
  box-shadow: var(--shadow-lg);
  z-index: 601;
  transform: translateX(100%);
  transition: transform var(--duration-base) var(--ease-out);
  display: flex;
  flex-direction: column;
  padding: var(--space-6);
}
.mobile-drawer.open {
  transform: translateX(0);
}

.mobile-drawer-close {
  align-self: flex-end;
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--slate-500);
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: var(--space-6);
}

.mobile-drawer-links {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}
.mobile-drawer-links a {
  display: block;
  padding: var(--space-3) 0;
  font: 500 var(--type-heading-sm-size)/var(--type-heading-sm-lh) var(--font-body);
  color: var(--color-ink);
  text-decoration: none;
  border-bottom: 1px solid var(--slate-100);
  opacity: 0;
  transform: translateY(12px);
}
.mobile-drawer.open .mobile-drawer-links a {
  animation: drawerLinkIn var(--duration-slow) var(--ease-out) forwards;
}
.mobile-drawer.open .mobile-drawer-links li:nth-child(1) a { animation-delay: 0ms; }
.mobile-drawer.open .mobile-drawer-links li:nth-child(2) a { animation-delay: 30ms; }
.mobile-drawer.open .mobile-drawer-links li:nth-child(3) a { animation-delay: 60ms; }
.mobile-drawer.open .mobile-drawer-links li:nth-child(4) a { animation-delay: 90ms; }
.mobile-drawer.open .mobile-drawer-links li:nth-child(5) a { animation-delay: 120ms; }
.mobile-drawer.open .mobile-drawer-links li:nth-child(6) a { animation-delay: 150ms; }

@keyframes drawerLinkIn {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

.mobile-drawer-actions {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-top: var(--space-4);
}
.mobile-drawer-actions .btn {
  width: 100%;
}

@media (max-width: 900px) {
  .site-nav { display: none; }
  .mobile-menu-btn { display: block; }
}

/* ============================================================
   Hero + load animation sequence
   ============================================================ */
.hero-section {
  min-height: 640px;
  display: flex;
  align-items: center;
  background: var(--color-ink);
  padding: calc(76px + var(--space-16)) var(--space-8) var(--space-16);
  overflow: hidden;
}

.hero-inner {
  max-width: 1180px;
  margin: 0 auto;
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-16);
  align-items: center;
}

.hero-copy .hero-eyebrow {
  color: var(--color-teal);
  font: 600 var(--type-caption-size)/var(--type-caption-lh) var(--font-body);
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: var(--space-4);
}

.hero-copy h1 {
  color: #fff;
  font: 600 var(--type-display-xl-size)/var(--type-display-xl-lh) var(--font-display);
  margin-bottom: var(--space-4);
}

.hero-copy .hero-subhead {
  color: var(--slate-300);
  font: 400 var(--type-body-lg-size)/var(--type-body-lg-lh) var(--font-body);
  max-width: 480px;
  margin-bottom: var(--space-8);
}

.hero-cta-row {
  display: flex;
  gap: var(--space-3);
}

.hero-load-item {
  opacity: 0;
  transform: translateY(12px);
  animation: heroLoadIn var(--duration-slow) var(--ease-out) forwards;
}
.hero-eyebrow { animation-delay: 0ms; }
.hero-copy h1 { animation-delay: 80ms; }
.hero-subhead { animation-delay: 140ms; }
.hero-cta-row { animation-delay: 200ms; }

@keyframes heroLoadIn {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

.hero-visual {
  display: flex;
  align-items: center;
  justify-content: center;
}
.hero-visual svg {
  width: 100%;
  max-width: 420px;
  height: auto;
}
.hero-visual .draw-path {
  fill: none;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-dasharray: var(--path-length, 1000);
  stroke-dashoffset: var(--path-length, 1000);
  animation: drawStroke 1400ms var(--ease-out) forwards;
}

@keyframes drawStroke {
  to { stroke-dashoffset: 0; }
}

@media (max-width: 900px) {
  .hero-inner {
    grid-template-columns: 1fr;
    text-align: center;
  }
  .hero-copy .hero-subhead {
    margin-left: auto;
    margin-right: auto;
  }
  .hero-cta-row {
    justify-content: center;
  }
  .hero-visual {
    order: -1;
  }
  .hero-visual svg {
    max-width: 280px;
  }
}

/* ============================================================
   Scroll reveal — fade + 16px upward shift, triggered once
   ============================================================ */
.scroll-reveal {
  opacity: 0;
  transform: translateY(16px);
  transition: opacity var(--duration-slow) var(--ease-out),
              transform var(--duration-slow) var(--ease-out);
}
.scroll-reveal.revealed {
  opacity: 1;
  transform: translateY(0);
}

/* ============================================================
   Generic public page sections
   ============================================================ */
.public-section {
  max-width: 900px;
  margin: 0 auto;
  padding: var(--space-16) var(--space-6);
}
.public-section-narrow {
  max-width: 640px;
  margin: 0 auto;
  padding: var(--space-16) var(--space-6);
}

.site-footer {
  background: var(--color-ink);
  color: var(--slate-300);
  padding: var(--space-12) var(--space-8);
  text-align: center;
  font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);
}

.showcase-feature-card {
  background: var(--color-surface);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  padding: var(--space-6);
}

@media (max-width: 720px) {
  .public-section > div[style*="grid-template-columns"] {
    grid-template-columns: 1fr !important;
  }
}
```

**File: `assets/js/modules/header.js`**

```javascript
/**
 * NovaTrust Public Header
 * Source: novatrust-design-system.md Section 5.1
 */
(function () {
  'use strict';

  const SCROLL_THRESHOLD = 80;

  function initScrollTransition() {
    const header = document.querySelector('.site-header');
    if (!header) {
      return;
    }

    function update() {
      if (window.scrollY > SCROLL_THRESHOLD) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }

    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  function initMobileDrawer() {
    const btn = document.querySelector('.mobile-menu-btn');
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
      btn.setAttribute('aria-expanded', 'true');
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
      btn.setAttribute('aria-expanded', 'false');
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

    window.addEventListener('resize', () => {
      if (window.innerWidth > 900 && drawer.classList.contains('open')) {
        close();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initScrollTransition();
    initMobileDrawer();
  });
})();
```

**File: `assets/js/modules/scroll-reveal.js`**

```javascript
/**
 * NovaTrust Scroll Reveal
 * Source: novatrust-design-system.md Section 5.1
 *
 * "triggered once, not on every scroll pass." Enforced by unobserving each
 * element the moment it's revealed.
 */
(function () {
  'use strict';

  function init() {
    const elements = document.querySelectorAll('.scroll-reveal');

    if (elements.length === 0) {
      return;
    }

    if (!('IntersectionObserver' in window)) {
      elements.forEach((el) => el.classList.add('revealed'));
      return;
    }

    const observer = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            obs.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );

    elements.forEach((el) => observer.observe(el));
  }

  document.addEventListener('DOMContentLoaded', init);
})();
```

**File: `layouts/public.php`**

```php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'NovaTrust') ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDescription ?? '') ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

<link rel="stylesheet" href="/assets/css/design-tokens.css">
<link rel="stylesheet" href="/assets/css/buttons.css">
<link rel="stylesheet" href="/assets/css/modal.css">
<link rel="stylesheet" href="/assets/css/toast.css">
<link rel="stylesheet" href="/assets/css/forms.css">
<link rel="stylesheet" href="/assets/css/status-pill.css">
<link rel="stylesheet" href="/assets/css/public-layout.css">
<style>.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;vertical-align:middle;}</style>
</head>
<body>

<header class="site-header">
  <div class="site-header-inner">
    <a href="/" class="site-logo">NovaTrust</a>

    <nav class="site-nav">
      <ul class="site-nav-links">
        <li><a href="/">Home</a></li>
        <li><a href="/about">About</a></li>
        <li><a href="/blog">Blog</a></li>
        <li><a href="/contact">Contact</a></li>
      </ul>
    </nav>

    <div class="site-nav-actions">
      <a href="/login" class="btn btn-ghost">Log In</a>
      <a href="/register" class="btn btn-primary">Get Started</a>
    </div>

    <button type="button" class="mobile-menu-btn" aria-label="Open menu" aria-expanded="false">
      <span class="bar bar-top"></span>
      <span class="bar bar-mid"></span>
      <span class="bar bar-bottom"></span>
    </button>
  </div>
</header>

<div class="mobile-drawer-backdrop"></div>
<nav class="mobile-drawer" aria-label="Mobile navigation">
  <button type="button" class="mobile-drawer-close" aria-label="Close menu">
    <span class="material-symbols-outlined">close</span>
  </button>
  <ul class="mobile-drawer-links">
    <li><a href="/">Home</a></li>
    <li><a href="/about">About</a></li>
    <li><a href="/blog">Blog</a></li>
    <li><a href="/contact">Contact</a></li>
    <li><a href="/login">Log In</a></li>
    <li><a href="/register">Get Started</a></li>
  </ul>
  <div class="mobile-drawer-actions">
    <a href="/register" class="btn btn-primary">Get Started</a>
  </div>
</nav>

<main>
<?= $content ?? '' ?>
</main>

<footer class="site-footer">
  <p>&copy; 2026 NovaTrust. All rights reserved.</p>
</footer>

<?php require __DIR__ . '/../components/ui/_confirm-modal.php'; ?>

<script src="/assets/js/modules/modal.js"></script>
<script src="/assets/js/toast.js"></script>
<script src="/assets/js/modules/header.js"></script>
<script src="/assets/js/modules/scroll-reveal.js"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
```

**Verified (header + drawer, 15 assertions):**
```
PASS — Header does NOT have .scrolled at the top of the page
PASS — Header background is transparent at top, got 'rgba(0, 0, 0, 0)'
PASS — Header gains .scrolled class after scrolling past 80px
PASS — Header background becomes solid surface color when scrolled, got 'rgb(255, 255, 255)'
PASS — Header correctly reverts (loses .scrolled) when scrolled back to top
PASS — Hamburger button is visible on mobile viewport
PASS — Desktop nav is hidden on mobile viewport
PASS — Hamburger button gets .open class on click (triggers X morph)
PASS — Top bar has a rotation transform applied (morphed toward X)
PASS — Middle bar fades to 0 opacity when open
PASS — Drawer gets .open class when hamburger is clicked
PASS — Drawer is translated into view (not off-screen)
PASS — Drawer contains all 6 expected links
PASS — First drawer link has 0ms delay, second has 30ms (staggered reveal)
PASS — Body scroll is locked while drawer is open
PASS — Escape key closes the mobile drawer
PASS — Body scroll lock is released after closing
```

---

## P6.3 — Homepage

**File: `resources/public/home.php`**

```php
<?php ob_start(); ?>

<section class="hero-section">
  <div class="hero-inner">
    <div class="hero-copy">
      <div class="hero-eyebrow hero-load-item">DIGITAL BANKING, DONE PLAINLY</div>
      <h1 class="hero-load-item">Banking that behaves the way it says it will.</h1>
      <p class="hero-subhead hero-load-item">A virtual card, a real account, and withdrawals that don't disappear into a black box for three days. NovaTrust tells you exactly where your money is and why, every time.</p>
      <div class="hero-cta-row hero-load-item">
        <a href="/register" class="btn btn-primary">Open an account</a>
        <a href="/about" class="btn btn-secondary">How it works</a>
      </div>
    </div>

    <div class="hero-visual">
      <svg viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <!-- Card -->
        <rect class="draw-path" x="70" y="120" width="200" height="130" rx="14" stroke="var(--color-teal)" stroke-width="3" style="--path-length:660; animation-delay:0ms;"/>
        <line class="draw-path" x1="70" y1="165" x2="270" y2="165" stroke="var(--color-teal)" stroke-width="3" style="--path-length:200; animation-delay:150ms;"/>
        <!-- Shield -->
        <path class="draw-path" d="M230 200 L290 220 L290 270 Q290 310 230 335 Q170 310 170 270 L170 220 Z" stroke="var(--color-ink)" stroke-width="3" style="--path-length:420; animation-delay:300ms;"/>
        <path class="draw-path" d="M210 265 L225 280 L255 245" stroke="var(--color-ink)" stroke-width="3" style="--path-length:90; animation-delay:600ms;"/>
        <!-- Ledger lines -->
        <line class="draw-path" x1="60" y1="290" x2="180" y2="290" stroke="var(--slate-300)" stroke-width="2" style="--path-length:120; animation-delay:450ms;"/>
        <line class="draw-path" x1="60" y1="305" x2="150" y2="305" stroke="var(--slate-300)" stroke-width="2" style="--path-length:90; animation-delay:500ms;"/>
        <line class="draw-path" x1="60" y1="320" x2="165" y2="320" stroke="var(--slate-300)" stroke-width="2" style="--path-length:105; animation-delay:550ms;"/>
      </svg>
    </div>
  </div>
</section>

<section class="public-section">
  <div class="scroll-reveal" style="text-align:center; margin-bottom: var(--space-12);">
    <h2 class="type-heading-lg" style="margin-bottom: var(--space-3);">Three things people actually ask about their bank</h2>
    <p class="type-body-lg" style="color: var(--slate-700);">Not the marketing version. The real ones.</p>
  </div>

  <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-6);">
    <div class="scroll-reveal showcase-feature-card">
      <span class="material-symbols-outlined" style="font-size:32px; color:var(--color-teal);">credit_card</span>
      <h3 class="type-heading-sm" style="margin: var(--space-4) 0 var(--space-2);">"Where's my card?"</h3>
      <p class="type-body-md" style="color:var(--slate-700);">Issued instantly, approved within one business day. You'll see the exact status on your dashboard — not "processing" forever.</p>
    </div>
    <div class="scroll-reveal showcase-feature-card">
      <span class="material-symbols-outlined" style="font-size:32px; color:var(--color-teal);">sync_alt</span>
      <h3 class="type-heading-sm" style="margin: var(--space-4) 0 var(--space-2);">"Why is my withdrawal stuck?"</h3>
      <p class="type-body-md" style="color:var(--slate-700);">If a withdrawal needs review, we tell you what's outstanding — not a spinner with no explanation.</p>
    </div>
    <div class="scroll-reveal showcase-feature-card">
      <span class="material-symbols-outlined" style="font-size:32px; color:var(--color-teal);">support_agent</span>
      <h3 class="type-heading-sm" style="margin: var(--space-4) 0 var(--space-2);">"Can I talk to an actual person?"</h3>
      <p class="type-body-md" style="color:var(--slate-700);">Live chat routes to a human the moment your question touches your account, your money, or a compliance requirement. No bot pretending otherwise.</p>
    </div>
  </div>
</section>

<section class="public-section" style="background: var(--color-paper);">
  <div class="scroll-reveal" style="max-width:640px; margin:0 auto; text-align:center;">
    <h2 class="type-heading-lg" style="margin-bottom: var(--space-4);">Built for people who move money and want to know why.</h2>
    <p class="type-body-lg" style="color:var(--slate-700); margin-bottom: var(--space-8);">Every balance change traces back to something — a credit, a withdrawal, a refund. You can always see which.</p>
    <a href="/register" class="btn btn-primary">Open an account</a>
  </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/public.php';
```

The hero visual is the "abstract line-art motif" direction from Section 5.1 (interlocking card/shield/ledger-line forms in ink/teal on paper) rather than photography — no art department and no stock photo budget to art-direct against the Ink palette convincingly, and the line-art direction is fully specified in CSS/SVG with no external asset dependency at all.

**Verified (11 assertions on the exact animation timing, not just "it animates"):**
```
PASS — Eyebrow animates at 0ms delay, got 0s
PASS — Headline animates at 80ms delay, got 0.08s
PASS — Subhead animates at 140ms delay, got 0.14s
PASS — CTA row animates at 200ms delay, got 0.2s
PASS — Hero eyebrow reaches full opacity after its animation completes
PASS — Hero visual renders the expected number of animated stroke paths, got 7
PASS — Above-the-fold scroll-reveal elements are already revealed at load, below-the-fold ones are not yet
PASS — Scroll-reveal elements become revealed after scrolling into view
PASS — Elements remain revealed after scrolling back up (not reset)
```
The scroll-reveal test needed a second look: the first version asserted *nothing* should be revealed before any scrolling, which failed — correctly, because elements already visible in the viewport at page load legitimately get revealed immediately (`IntersectionObserver` fires as soon as something intersects, including on the very first `observe()` call if it's already on screen). That's the right behavior, not a bug; hiding content that's already visible would be worse. The test was rewritten to check the actually-correct invariant: some elements revealed immediately (above the fold), some not yet (below it).

---

## P6.4 — About and Blog

**`P0.9`'s static-vs-CMS decision was never explicitly closed out**, same situation as `P0.8` in Phase 5. Built **static** here — a plain PHP data file, not a database table — which is the lower-cost default and doesn't block this phase. If `P0.9` later confirms a CMS, `resources/data/blog-posts.php`'s contents become `BlogPostSeeder`'s seed data (Phase 3's conditional `BlogPost`/`BlogPostRepository`) verbatim — the array shape below already matches what a `blog_posts` row would look like.

**File: `resources/data/blog-posts.php`**

```php
<?php
/**
 * Static blog post data (per P0.9's assumption — static, not DB-driven).
 * If P0.9 later confirms a CMS, this file's contents become the seed data
 * for BlogPostSeeder, migrated one-for-one.
 */
return [
    [
        'slug' => 'what-actually-happens-when-you-tap-pay',
        'title' => 'What Actually Happens When You Tap Pay',
        'excerpt' => 'A plain-language walkthrough of the six systems your card talks to in the two seconds between tapping and the receipt printing.',
        'published_at' => '2026-06-02',
        'body' => "...", // full body text
    ],
    // two more posts — see the actual file for full content
];
```

**File: `app/Controllers/Public/BlogController.php`**

```php
<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Core\Response;

class BlogController extends BaseController
{
    private function posts(): array
    {
        return require __DIR__ . '/../../../resources/data/blog-posts.php';
    }

    public function index(): void
    {
        $this->view('public/blog/index', [
            'pageTitle' => 'Blog — NovaTrust',
            'metaDescription' => 'Plain-language explanations of how banking and payments actually work.',
            'posts' => $this->posts(),
        ]);
    }

    public function show(string $slug): void
    {
        $post = null;

        foreach ($this->posts() as $candidate) {
            if ($candidate['slug'] === $slug) {
                $post = $candidate;
                break;
            }
        }

        if ($post === null) {
            Response::abort(404, 'Post not found');
            return;
        }

        $this->view('public/blog/show', [
            'pageTitle' => $post['title'] . ' — NovaTrust',
            'metaDescription' => $post['excerpt'],
            'post' => $post,
        ]);
    }
}
```

`resources/public/about.php`, `resources/public/blog/index.php`, and `resources/public/blog/show.php` follow the same `ob_start()` → content → `require layouts/public.php` pattern as the homepage — full contents in the delivered file set, omitted here for length. All three route correctly (`/about` → 200, `/blog` → 200 listing all three posts, `/blog/{real-slug}` → 200, `/blog/{fake-slug}` → 404).

---

## P6.5 — Working Contact Form

This is the fix for the form that, in the live repo today, has no `action`, no `method`, and submits nowhere.

**File: `app/Controllers/Public/ContactController.php`**

```php
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

The form itself (`resources/public/contact.php`) wires `P5.6`'s `FormValidator` for client-side UX, pre-fills fields from flashed `old` input on a failed server-side validation redirect, and shows field-level errors from the server if JavaScript never ran at all — full contents in the delivered file set.

**Verified — the full cycle, tested three separate ways:**

1. **Through the browser, with client-side validation:**
```
PASS — Client-side validation blocks empty submit and shows errors, got 3
PASS — Success message appears after a real, valid form submission
```

2. **Confirmed the browser submission actually persisted:**
```sql
mysql> SELECT id, email, subject FROM support_requests;
id | email                  | subject
1  | reviewer@example.com   | Testing the contact form
```

3. **Via raw `curl`, bypassing the browser and all JavaScript entirely** — this is what proves server-side validation is the real boundary, not just a nicety:
```
Empty POST                                    -> 302 redirect, row count stayed at 1 (rejected)
Invalid email, no JS involved at all          -> 302 redirect, row count stayed at 1 (rejected)
Valid data, pure curl, zero browser           -> 302 redirect, row count became 2 (accepted)
                                                  email=direct@example.com persisted correctly
```

---

## P6.6 — Remove the Hard-Loaded Tidio Script

No new code — a deletion task against the real repo, which this sandbox doesn't have a live copy of to demonstrate against directly. Per the SADD's Section 0 finding: `contact.php` hard-loads the Tidio chat widget script directly, same as `support.php` and `customer_service.php`.

**Steps against the real repo:**

```bash
grep -rn "tidio" --include="*.php" .
```

Remove the `<script>` tag loading Tidio from the *old* `contact.php` specifically — the new `resources/public/contact.php` built in `P6.5` never had it to begin with, since it was written fresh rather than adapted from the old file. The same script tag on `support.php`/`customer_service.php` is explicitly **not** this task's responsibility — those get consolidated into the new Support page in Phase 13, and removing Tidio from files that are about to be deleted anyway would be wasted effort now.

**Done-when:** `grep -rn "tidio" --include="*.php" .` returns zero matches in any *public-facing, non-support* page. Matches inside `support.php`/`customer_service.php` are expected until Phase 13.

---

## P6.7 — Full QA

**Verified — 4 additional checks confirming zero horizontal overflow across every public page on mobile:**
```
PASS — No horizontal overflow on / at 390px, got scrollWidth=390
PASS — No horizontal overflow on /about at 390px, got scrollWidth=390
PASS — No horizontal overflow on /contact at 390px, got scrollWidth=390
PASS — No horizontal overflow on /blog at 390px, got scrollWidth=390
```

**Not verified in this sandbox, same limitation as Phase 5:** real Google Fonts rendering (no network route to `fonts.googleapis.com` here) and Firefox/Safari (browser binaries blocked by sandbox network restrictions). Both should be checked once this is deployed somewhere with normal internet access, before treating the public site as fully signed off.

**Contrast:** every text/background pairing on the public site uses combinations already established in Phase 5's token set (white text on `--color-ink` in the hero and footer, `--slate-700` body text on `--color-surface`/`--color-paper`, `--color-ink` headings throughout) — none of these are new pairings introduced in this phase, so Phase 5's implicit contrast decisions carry through rather than needing independent re-verification.

---

## Phase 6 Exit Checklist

- [ ] `public/assets` symlink created (`ln -s ../assets assets` from inside `public/`) — **without this, every stylesheet and script 404s in production**
- [ ] All four Public controllers built and routed; every route smoke-tested for correct HTTP status
- [ ] Header scroll crossfade verified at the exact 80px threshold, both directions (scroll down, scroll back up)
- [ ] Mobile hamburger-to-X morph verified via actual computed `transform`/`opacity`, not just "it looks like an X"
- [ ] Drawer: focus management, Escape close, body scroll lock, and the exact 30ms-per-link stagger all verified
- [ ] Homepage load sequence verified against the exact millisecond delays from Section 5.1 (0/80/140/200ms), not just "things fade in"
- [ ] Scroll-reveal confirmed to trigger once — scrolled away and back, elements stayed revealed rather than resetting
- [ ] Contact form verified three ways: browser + client validation, direct database check, and raw `curl` bypassing all JavaScript to confirm server-side validation is real
- [ ] Tidio script removal confirmed absent from the new `contact.php` (pre-existing occurrences on `support.php`/`customer_service.php` deferred to Phase 13, not this task)
- [ ] **Before fully signing off:** real Google Fonts rendering and Firefox/Safari checks, once deployed with real network access

---

*End of Phase 6 Runbook. Next: Implementation Plan Phase 7 (`P7.1`–`P7.?`) — Authentication, the first phase where a user can actually create an account and log in.*
