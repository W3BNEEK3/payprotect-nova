<?php ob_start(); 
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>

<div style="max-width: 800px; margin: 0 auto; text-align: center; padding: var(--space-16) 0;">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    
    <h1 style="font-family: var(--font-display); font-size: 2.5rem; margin-bottom: var(--space-4);">Welcome to NovaTrust</h1>
    <p style="color: var(--slate-500); font-size: 1.1rem;">This is the dashboard placeholder for Phase 8.</p>
</div>

<?php 
$content = ob_get_clean();
$sidebarBrand = 'NovaTrust';
$currentPath = '/dashboard';
require __DIR__ . '/../layouts/app.php'; 
?>
