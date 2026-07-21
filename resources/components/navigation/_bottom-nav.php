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
