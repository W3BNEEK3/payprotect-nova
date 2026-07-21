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
