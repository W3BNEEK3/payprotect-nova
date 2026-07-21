<?php ob_start(); ?>

<div style="max-width: 800px; margin: 0 auto; text-align: center; padding: var(--space-16) 0;">
    <h1 style="font-family: var(--font-display); font-size: 2.5rem; margin-bottom: var(--space-4);">Control Center</h1>
    <p style="color: var(--slate-500); font-size: 1.1rem;">This is the admin dashboard placeholder for Phase 8.</p>
</div>

<?php 
$content = ob_get_clean();
$sidebarBrand = 'Control Center';
$currentPath = '/admin/dashboard';
require __DIR__ . '/../layouts/admin.php'; 
?>
