<?php ob_start(); ?>

<section class="hero-section" style="min-height: 400px;">
  <div class="hero-inner" style="grid-template-columns: 1fr;">
    <div class="hero-copy" style="max-width: 800px; margin: 0 auto; text-align: center;">
      <div class="hero-eyebrow hero-load-item">BLOG</div>
      <h1 class="hero-load-item">Bank Smarter: Tips, News & Security</h1>
      <p class="hero-subhead hero-load-item" style="max-width: 600px; margin: 0 auto;">
        Welcome to the <?= htmlspecialchars(\App\Core\Site::name()) ?> Blog – your trusted source for online banking tips, security best practices, and financial wellness advice.
      </p>
    </div>
  </div>
</section>

<section class="public-section">
  <div class="scroll-reveal" style="max-width: 1180px; margin: 0 auto;">
    
    <?php if (empty($posts)): ?>
      <div style="text-align: center; padding: var(--space-16) 0;">
        <p class="type-body-lg" style="color: var(--slate-500);">No posts available at this time.</p>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-8);">
        <?php foreach ($posts as $post): ?>
          <div class="showcase-feature-card" style="padding: var(--space-6); background: var(--color-surface); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); display: flex; flex-direction: column;">
            <div style="font: 500 var(--type-caption-size)/var(--type-caption-lh) var(--font-body); color: var(--color-teal); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-2);">
              <?= htmlspecialchars(date('F j, Y', strtotime($post['published_at']))) ?>
            </div>
            <h2 class="type-heading-sm" style="color: var(--color-ink); margin-bottom: var(--space-3);">
              <?= htmlspecialchars($post['title']) ?>
            </h2>
            <p class="type-body-md" style="color: var(--slate-700); margin-bottom: var(--space-6); flex: 1;">
              <?= htmlspecialchars($post['excerpt']) ?>
            </p>
            <a href="/blog/<?= urlencode($post['slug']) ?>" class="btn btn-secondary" style="align-self: flex-start;">Read Article</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/public.php';
