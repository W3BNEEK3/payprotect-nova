<?php 
/** @var array $post */
ob_start(); 
?>

<section class="hero-section" style="min-height: 400px; padding-bottom: var(--space-8);">
  <div class="hero-inner" style="grid-template-columns: 1fr;">
    <div class="hero-copy" style="max-width: 800px; margin: 0 auto; text-align: center;">
      <div class="hero-eyebrow hero-load-item">ARTICLE • <?= htmlspecialchars(date('F j, Y', strtotime($post['published_at']))) ?></div>
      <h1 class="hero-load-item"><?= htmlspecialchars($post['title']) ?></h1>
      <p class="hero-subhead hero-load-item" style="max-width: 600px; margin: 0 auto;">
        <?= htmlspecialchars($post['excerpt']) ?>
      </p>
    </div>
  </div>
</section>

<section class="public-section">
  <div class="scroll-reveal" style="max-width: 720px; margin: 0 auto;">
    
    <article class="type-body-lg" style="color: var(--slate-900); line-height: 1.8;">
      <?php
        // Basic parser for paragraphs since the body is stored as plain text or simple markdown in the array
        // If it's a real CMS, this would be parsed markdown or raw HTML
        $paragraphs = explode("\n\n", $post['body']);
        foreach ($paragraphs as $p) {
            echo '<p style="margin-bottom: var(--space-6);">' . nl2br(htmlspecialchars(trim($p))) . '</p>';
        }
      ?>
    </article>

    <div style="margin-top: var(--space-12); padding-top: var(--space-8); border-top: 1px solid var(--slate-300);">
      <a href="/blog" class="btn btn-ghost" style="color: var(--color-teal);">
        <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span>
        Back to Blog
      </a>
    </div>

  </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../resources/layouts/public.php';
