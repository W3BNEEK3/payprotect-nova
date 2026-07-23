<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? \App\Core\Site::name()) ?></title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0f766e">
<link rel="apple-touch-icon" href="<?= htmlspecialchars(\App\Core\Site::faviconUrl()) ?>">
<link rel="icon" href="<?= htmlspecialchars(\App\Core\Site::faviconUrl()) ?>">
<meta name="description" content="<?= htmlspecialchars($metaDescription ?? '') ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/material-symbols.css">

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
    <a href="/" class="site-logo"><?= htmlspecialchars(\App\Core\Site::name()) ?></a>

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
  <div class="footer-grid">
    
    <!-- Column 1: Brand & Socials -->
    <div class="footer-brand">
      <a href="/" class="site-logo"><?= htmlspecialchars(\App\Core\Site::name()) ?></a>
      <p>Digital banking that behaves exactly the way it says it will. A real account, a virtual card, and absolute financial transparency.</p>
      <div class="footer-socials">
        <a href="#" aria-label="Twitter"><span class="material-symbols-outlined">alternate_email</span></a>
        <a href="#" aria-label="LinkedIn"><span class="material-symbols-outlined">work</span></a>
        <a href="#" aria-label="GitHub"><span class="material-symbols-outlined">code</span></a>
      </div>
    </div>
    
    <!-- Column 2: Products -->
    <div class="footer-column">
      <h4 class="footer-heading">Products</h4>
      <ul class="footer-links">
        <li><a href="/cards">Virtual Cards</a></li>
        <li><a href="/accounts">Multi-Currency Accounts</a></li>
        <li><a href="/gateway">Payment Gateway API</a></li>
        <li><a href="/transfers">Automated Transfers</a></li>
      </ul>
    </div>

    <!-- Column 3: Resources -->
    <div class="footer-column">
      <h4 class="footer-heading">Resources</h4>
      <ul class="footer-links">
        <li><a href="/docs">Developer Documentation</a></li>
        <li><a href="/api-reference">API Reference</a></li>
        <li><a href="/help">Help Center</a></li>
        <li><a href="/community">Community Forum</a></li>
      </ul>
    </div>

    <!-- Column 4: Legal -->
    <div class="footer-column">
      <h4 class="footer-heading">Company</h4>
      <ul class="footer-links">
        <li><a href="/about">About Us</a></li>
        <li><a href="/careers">Careers</a></li>
        <li><a href="/privacy">Privacy Policy</a></li>
        <li><a href="/terms">Terms of Service</a></li>
      </ul>
    </div>

  </div>

  <!-- Bottom Row: Copyright & Status -->
  <div class="footer-bottom">
    <div class="footer-copyright">
      &copy; <?php echo date('Y'); ?> <?= htmlspecialchars(\App\Core\Site::name()) ?>. All rights reserved.
    </div>
    <a href="/status" class="footer-status">
      <span class="status-dot"></span>
      All systems operational
    </a>
  </div>
</footer>

<?php require __DIR__ . '/../components/ui/_confirm-modal.php'; ?>

<script src="/assets/js/modules/modal.js"></script>
<script src="/assets/js/toast.js"></script>
<script src="/assets/js/modules/header.js"></script>
<script src="/assets/js/modules/scroll-reveal.js"></script>
<?= $extraScripts ?? '' ?>
<script src="/assets/js/modules/pwa.js"></script>
</body>
</html>
