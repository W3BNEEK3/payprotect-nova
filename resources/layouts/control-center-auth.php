<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Control Center — ' . \App\Core\Site::name()) ?></title>
<meta name="robots" content="noindex, nofollow">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/material-symbols.css">

<link rel="stylesheet" href="/assets/css/design-tokens.css">
<link rel="stylesheet" href="/assets/css/buttons.css">
<link rel="stylesheet" href="/assets/css/forms.css">
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
  <a href="/" class="cc-footer-link">&larr; Return to <?= htmlspecialchars(\App\Core\Site::name()) ?></a>
</div>

<script src="/assets/js/modules/validation.js"></script>
<script src="/assets/js/modules/field-handlers.js"></script>
<script src="/assets/js/toast.js"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
