<?php
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

$sidebarBrand = $sidebarBrand ?? 'Control Center';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin/dashboard', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Control Center') ?></title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0f766e">
<link rel="apple-touch-icon" href="/assets/images/icon-192.png">
<meta name="csrf-token" content="<?= \App\Middlewares\CsrfMiddleware::token() ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

<link rel="stylesheet" href="/assets/css/design-tokens.css">
<link rel="stylesheet" href="/assets/css/buttons.css">
<link rel="stylesheet" href="/assets/css/modal.css">
<link rel="stylesheet" href="/assets/css/toast.css">
<link rel="stylesheet" href="/assets/css/forms.css">
<link rel="stylesheet" href="/assets/css/public-layout.css">
<link rel="stylesheet" href="/assets/css/shell.css">
<style>
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
  body { margin: 0; padding: 0; background: var(--color-surface); font-family: var(--font-body); color: var(--color-ink); }
</style>
</head>
<body>

<div class="shell">
  <?php require __DIR__ . '/../components/navigation/_sidebar.php'; ?>
  
  <div class="shell-main">
    <header class="shell-topbar">
      <div class="shell-page-title"><?= htmlspecialchars($pageTitle ?? 'Control Center') ?></div>
      
      <div class="shell-topbar-right">
        <?php require __DIR__ . '/../components/notifications/_bell.php'; ?>
        
        <div class="shell-account">
          <button type="button" class="shell-account-trigger" aria-expanded="false">
             <span class="shell-account-avatar"><?= htmlspecialchars(substr(\App\Core\Session::get('admin_name', 'A'), 0, 1)) ?></span>
             <span class="shell-account-name"><?= htmlspecialchars(\App\Core\Session::get('admin_name', 'Admin')) ?></span>
             <span class="material-symbols-outlined">expand_more</span>
          </button>
          <div class="shell-account-menu">
            <form method="POST" action="/control-center/logout" style="margin:0;">
              <?= \App\Middlewares\CsrfMiddleware::field() ?>
              <button type="submit"><span class="material-symbols-outlined">logout</span> Log Out</button>
            </form>
          </div>
        </div>
        
        <button type="button" class="shell-mobile-menu-btn mobile-avatar-btn" aria-label="Menu" aria-expanded="false">
           <span class="shell-account-avatar"><?= htmlspecialchars(substr(\App\Core\Session::get('admin_name', 'A'), 0, 1)) ?></span>
        </button>
      </div>
    </header>

    <main class="shell-content">
      <?= $content ?? '' ?>
    </main>
    
    <?php require __DIR__ . '/../components/navigation/_bottom-nav.php'; ?>
  </div>
</div>

<div class="mobile-drawer-backdrop"></div>
<nav class="mobile-drawer" aria-label="Mobile navigation">
  <button type="button" class="mobile-drawer-close" aria-label="Close menu">
    <span class="material-symbols-outlined">close</span>
  </button>
  <ul class="mobile-drawer-links">
     <!-- Admin secondary links could go here -->
  </ul>
  <div class="mobile-drawer-actions">
     <form method="POST" action="/control-center/logout" style="margin:0;">
        <?= \App\Middlewares\CsrfMiddleware::field() ?>
        <button type="submit" class="btn btn-secondary" style="width:100%;">Log Out</button>
     </form>
  </div>
</nav>

<?php require __DIR__ . '/../components/ui/_confirm-modal.php'; ?>

<script src="/assets/js/modules/modal.js"></script>
<script src="/assets/js/toast.js"></script>
<script src="/assets/js/modules/shell.js"></script>
<script>
    window.PUSHER_KEY = "<?= htmlspecialchars(getenv('PUSHER_KEY') ?: '') ?>";
    window.PUSHER_CLUSTER = "<?= htmlspecialchars(getenv('PUSHER_CLUSTER') ?: 'mt1') ?>";
</script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<?= $extraScripts ?? '' ?>
<script src="/assets/js/modules/pwa.js"></script>
</body>
</html>
