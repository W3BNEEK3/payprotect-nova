<?php ob_start(); ?>

<!-- 1. Hero Section -->
<section class="hero-section">
  <!-- Premium Building Background -->
  <div class="hero-bg">
    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=2070&auto=format&fit=crop" alt="Modern Architecture">
  </div>

  <!-- Floating Elements -->
  <div class="floating-element float-1"><span class="material-symbols-outlined" style="font-size: 32px;">verified_user</span></div>
  <div class="floating-element float-2"><span class="material-symbols-outlined" style="font-size: 28px;">account_balance</span></div>
  <div class="floating-element float-3"><span class="material-symbols-outlined" style="font-size: 40px;">language</span></div>

  <div class="hero-inner">
    <div class="hero-copy">
      <div class="hero-eyebrow hero-load-item">DIGITAL BANKING, DONE PLAINLY</div>
      <h1 class="hero-load-item">Banking that behaves the way it says it will.</h1>
      <p class="hero-subhead hero-load-item">A virtual card, a real account, and withdrawals that don't disappear into a black box for three days. <?= htmlspecialchars(\App\Core\Site::name()) ?> tells you exactly where your money is and why, every time.</p>
      <div class="hero-cta-row hero-load-item">
        <a href="/register" class="btn btn-primary">Open an account</a>
        <a href="/about" class="btn btn-secondary" style="color: #fff; border-color: rgba(255,255,255,0.3);">How it works</a>
      </div>
    </div>

    <!-- Original Preserved SVG Animation -->
    <div class="hero-visual">
      <svg viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect class="draw-path" x="70" y="120" width="200" height="130" rx="14" stroke="var(--color-teal)" stroke-width="3" style="--path-length:660; animation-delay:0ms;"/>
        <line class="draw-path" x1="70" y1="165" x2="270" y2="165" stroke="var(--color-teal)" stroke-width="3" style="--path-length:200; animation-delay:150ms;"/>
        <path class="draw-path" d="M230 200 L290 220 L290 270 Q290 310 230 335 Q170 310 170 270 L170 220 Z" stroke="var(--color-ink)" stroke-width="3" style="--path-length:420; animation-delay:300ms;"/>
        <path class="draw-path" d="M210 265 L225 280 L255 245" stroke="var(--color-ink)" stroke-width="3" style="--path-length:90; animation-delay:600ms;"/>
        <line class="draw-path" x1="60" y1="290" x2="180" y2="290" stroke="var(--slate-300)" stroke-width="2" style="--path-length:120; animation-delay:450ms;"/>
        <line class="draw-path" x1="60" y1="305" x2="150" y2="305" stroke="var(--slate-300)" stroke-width="2" style="--path-length:90; animation-delay:500ms;"/>
        <line class="draw-path" x1="60" y1="320" x2="165" y2="320" stroke="var(--slate-300)" stroke-width="2" style="--path-length:105; animation-delay:550ms;"/>
      </svg>
    </div>
  </div>
</section>

<!-- 2. The Colorful Bento Grid -->
<section class="public-section">
  <div class="scroll-reveal" style="text-align:center; max-width: 600px; margin: 0 auto;">
    <h2 class="type-display-lg" style="margin-bottom: var(--space-3);">Everything you need, beautifully arranged.</h2>
    <p class="type-body-lg" style="color: var(--slate-700);">Not the marketing version. The real features.</p>
  </div>

  <div class="bento-grid scroll-reveal">
    <!-- Big Teal Card (Span 2x2) -->
    <div class="bento-card bento-teal">
      <div class="bento-icon"><span class="material-symbols-outlined" style="font-size:40px;">dashboard_customize</span></div>
      <h3>Total Financial Control</h3>
      <p>Issued instantly, approved within one business day. See the exact status on your dashboard — not "processing" forever.</p>
      <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop" class="bento-img-absolute" alt="Dashboard">
    </div>

    <!-- Wide Warning Card (Span 2x1) -->
    <div class="bento-card bento-warning">
      <div class="bento-icon"><span class="material-symbols-outlined" style="font-size:40px;">credit_card</span></div>
      <h3>Virtual Cards Instantly</h3>
      <p>Generate cards for secure online spending in one click.</p>
      <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?q=80&w=2070&auto=format&fit=crop" class="bento-img-absolute" style="width: 40%; right: 5%; bottom: -30%;" alt="Credit Card">
    </div>

    <!-- Small Danger Card (Span 1x1) -->
    <div class="bento-card bento-danger">
      <div class="bento-icon"><span class="material-symbols-outlined" style="font-size:40px;">sync_alt</span></div>
      <h3>Transparent Transfers</h3>
      <p>If a withdrawal needs review, we tell you exactly why.</p>
    </div>

    <!-- Small Success Card (Span 1x1) -->
    <div class="bento-card bento-success">
      <div class="bento-icon"><span class="material-symbols-outlined" style="font-size:40px;">support_agent</span></div>
      <h3>24/7 Real Support</h3>
      <p>Live chat routes to a human. No bot pretending otherwise.</p>
    </div>
  </div>
</section>

<!-- 3. Carousel Section -->
<section class="carousel-section">
  <div class="public-section scroll-reveal" style="padding-top: 0; padding-bottom: 0;">
    <h2 class="type-display-lg" style="text-align: center;">Built for modern life.</h2>
    
    <!-- Swiper -->
    <div class="swiper mySwiper">
      <div class="swiper-wrapper">
        <div class="swiper-slide">
          <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop" alt="Team">
          <div class="swiper-content">
            <h3 class="type-heading-md" style="margin-top: 0; margin-bottom: var(--space-2);">"Finally, a dashboard that makes sense."</h3>
            <p class="type-body-md" style="color: var(--slate-700); margin: 0;">— Sarah J., Entrepreneur</p>
          </div>
        </div>
        <div class="swiper-slide">
          <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?q=80&w=2070&auto=format&fit=crop" alt="Payment">
          <div class="swiper-content">
            <h3 class="type-heading-md" style="margin-top: 0; margin-bottom: var(--space-2);">"No hidden fees, no surprises."</h3>
            <p class="type-body-md" style="color: var(--slate-700); margin: 0;">— David M., Freelancer</p>
          </div>
        </div>
        <div class="swiper-slide">
          <img src="https://images.unsplash.com/photo-1573164713988-8665fc963095?q=80&w=2069&auto=format&fit=crop" alt="Tech">
          <div class="swiper-content">
            <h3 class="type-heading-md" style="margin-top: 0; margin-bottom: var(--space-2);">"The virtual cards are a lifesaver."</h3>
            <p class="type-body-md" style="color: var(--slate-700); margin: 0;">— Elena R., Designer</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 4. Premium CTA Section -->
<section class="public-section" style="max-width: 1280px;">
  <div class="cta-premium scroll-reveal">
    <div class="floating-element float-1"><span class="material-symbols-outlined">lock</span></div>
    <div class="floating-element float-3"><span class="material-symbols-outlined">payments</span></div>
    
    <h2 class="type-display-lg" style="margin-bottom: var(--space-4);">Ready to know where your money is?</h2>
    <p class="type-body-lg" style="color:var(--slate-700); margin-bottom: var(--space-8); max-width: 600px; margin-left: auto; margin-right: auto;">Every balance change traces back to something — a credit, a withdrawal, a refund. You can always see which.</p>
    <a href="/register" class="btn btn-primary" style="padding: 16px 32px; font-size: 18px;">Open an account in minutes</a>
  </div>
</section>

<?php
// Injecting Swiper JS into the layout's $extraScripts variable
$extraScripts = '
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    var swiper = new Swiper(".mySwiper", {
      slidesPerView: 1,
      spaceBetween: 24,
      grabCursor: true,
      loop: true,
      autoplay: {
        delay: 3500,
        disableOnInteraction: false,
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
          spaceBetween: 24,
        },
        1200: {
          slidesPerView: 3,
          spaceBetween: 32,
        },
      },
    });
  });
</script>
';

$content = ob_get_clean();
require __DIR__ . '/../layouts/public.php';