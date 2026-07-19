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
