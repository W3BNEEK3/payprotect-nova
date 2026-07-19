<?php ob_start(); ?>

<section class="hero-section">
  <div class="hero-inner">
    <div class="hero-copy">
      <div class="hero-eyebrow hero-load-item">DIGITAL BANKING, DONE PLAINLY</div>
      <h1 class="hero-load-item">Banking that behaves the way it says it will.</h1>
      <p class="hero-subhead hero-load-item">A virtual card, a real account, and withdrawals that don't disappear into a black box for three days. NovaTrust tells you exactly where your money is and why, every time.</p>
      <div class="hero-cta-row hero-load-item">
        <a href="/register" class="btn btn-primary">Open an account</a>
        <a href="/about" class="btn btn-secondary">How it works</a>
      </div>
    </div>

    <div class="hero-visual">
      <svg viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <!-- Card -->
        <rect class="draw-path" x="70" y="120" width="200" height="130" rx="14" stroke="var(--color-teal)" stroke-width="3" style="--path-length:660; animation-delay:0ms;"/>
        <line class="draw-path" x1="70" y1="165" x2="270" y2="165" stroke="var(--color-teal)" stroke-width="3" style="--path-length:200; animation-delay:150ms;"/>
        <!-- Shield -->
        <path class="draw-path" d="M230 200 L290 220 L290 270 Q290 310 230 335 Q170 310 170 270 L170 220 Z" stroke="var(--color-ink)" stroke-width="3" style="--path-length:420; animation-delay:300ms;"/>
        <path class="draw-path" d="M210 265 L225 280 L255 245" stroke="var(--color-ink)" stroke-width="3" style="--path-length:90; animation-delay:600ms;"/>
        <!-- Ledger lines -->
        <line class="draw-path" x1="60" y1="290" x2="180" y2="290" stroke="var(--slate-300)" stroke-width="2" style="--path-length:120; animation-delay:450ms;"/>
        <line class="draw-path" x1="60" y1="305" x2="150" y2="305" stroke="var(--slate-300)" stroke-width="2" style="--path-length:90; animation-delay:500ms;"/>
        <line class="draw-path" x1="60" y1="320" x2="165" y2="320" stroke="var(--slate-300)" stroke-width="2" style="--path-length:105; animation-delay:550ms;"/>
      </svg>
    </div>
  </div>
</section>

<section class="public-section">
  <div class="scroll-reveal" style="text-align:center; margin-bottom: var(--space-12);">
    <h2 class="type-heading-lg" style="margin-bottom: var(--space-3);">Three things people actually ask about their bank</h2>
    <p class="type-body-lg" style="color: var(--slate-700);">Not the marketing version. The real ones.</p>
  </div>

  <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-6);">
    <div class="scroll-reveal showcase-feature-card">
      <span class="material-symbols-outlined" style="font-size:32px; color:var(--color-teal);">credit_card</span>
      <h3 class="type-heading-sm" style="margin: var(--space-4) 0 var(--space-2);">"Where's my card?"</h3>
      <p class="type-body-md" style="color:var(--slate-700);">Issued instantly, approved within one business day. You'll see the exact status on your dashboard — not "processing" forever.</p>
    </div>
    <div class="scroll-reveal showcase-feature-card">
      <span class="material-symbols-outlined" style="font-size:32px; color:var(--color-teal);">sync_alt</span>
      <h3 class="type-heading-sm" style="margin: var(--space-4) 0 var(--space-2);">"Why is my withdrawal stuck?"</h3>
      <p class="type-body-md" style="color:var(--slate-700);">If a withdrawal needs review, we tell you what's outstanding — not a spinner with no explanation.</p>
    </div>
    <div class="scroll-reveal showcase-feature-card">
      <span class="material-symbols-outlined" style="font-size:32px; color:var(--color-teal);">support_agent</span>
      <h3 class="type-heading-sm" style="margin: var(--space-4) 0 var(--space-2);">"Can I talk to an actual person?"</h3>
      <p class="type-body-md" style="color:var(--slate-700);">Live chat routes to a human the moment your question touches your account, your money, or a compliance requirement. No bot pretending otherwise.</p>
    </div>
  </div>
</section>

<section class="public-section" style="background: var(--color-paper);">
  <div class="scroll-reveal" style="max-width:640px; margin:0 auto; text-align:center;">
    <h2 class="type-heading-lg" style="margin-bottom: var(--space-4);">Built for people who move money and want to know why.</h2>
    <p class="type-body-lg" style="color:var(--slate-700); margin-bottom: var(--space-8);">Every balance change traces back to something — a credit, a withdrawal, a refund. You can always see which.</p>
    <a href="/register" class="btn btn-primary">Open an account</a>
  </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/public.php';
