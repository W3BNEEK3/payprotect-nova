<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? \App\Core\Site::name()) ?></title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0f766e">
<link rel="apple-touch-icon" href="<?= htmlspecialchars(\App\Core\Site::faviconUrl()) ?>">
<link rel="icon" href="<?= htmlspecialchars(\App\Core\Site::faviconUrl()) ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap">
<link rel="stylesheet" href="/assets/css/material-symbols.css">
<link rel="stylesheet" href="/assets/css/design-tokens.css">
<link rel="stylesheet" href="/assets/css/buttons.css">
<link rel="stylesheet" href="/assets/css/forms.css">
<style>
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
  body { margin: 0; padding: 0; background: var(--color-surface); font-family: var(--font-body); color: var(--color-ink); }
  
  .auth-split-layout { display: flex; min-height: 100vh; }
  
  .auth-split-visual {
      flex: 1;
      background: var(--color-ink);
      color: white;
      display: flex;
      flex-direction: column;
      padding: var(--space-12);
      position: relative;
      overflow: hidden;
  }
  
  .auth-split-visual::before {
      content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
      background: radial-gradient(circle at 50% 50%, rgba(20, 184, 166, 0.08) 0%, transparent 60%);
      pointer-events: none;
  }
  
  .auth-visual-content {
      position: relative;
      z-index: 1;
      max-width: 520px;
      margin: auto auto;
      display: flex;
      flex-direction: column;
      height: 100%;
  }

  .auth-logo-inverse {
      font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: white; text-decoration: none;
      display: inline-block; margin-bottom: auto;
  }

  /* Abstract UI Mockup Graphic */
  .auth-mockup {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-lg);
      padding: var(--space-6);
      margin: var(--space-12) 0;
      box-shadow: 0 20px 40px rgba(0,0,0,0.2);
      position: relative;
  }
  
  .auth-mockup-card {
      background: linear-gradient(135deg, var(--color-teal) 0%, #0d9488 100%);
      border-radius: var(--radius-md);
      padding: var(--space-4);
      color: white;
      margin-bottom: var(--space-4);
      transform: rotate(-2deg);
      box-shadow: 0 10px 20px rgba(20, 184, 166, 0.2);
  }

  .auth-mockup-row {
      display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-3);
  }

  .auth-mockup-label { font-size: 0.75rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.05em; }
  .auth-mockup-value { font-size: 1.5rem; font-weight: 600; font-family: var(--font-mono); }
  
  .auth-mockup-item {
      background: rgba(255,255,255,0.05); border-radius: var(--radius-sm); padding: var(--space-3);
      display: flex; align-items: center; gap: var(--space-3); margin-top: var(--space-2);
  }
  .auth-mockup-icon {
      width: 32px; height: 32px; border-radius: 50%; background: rgba(20, 184, 166, 0.2);
      display: flex; align-items: center; justify-content: center; color: var(--color-teal);
  }

  .auth-visual-text-block {
      margin-top: auto;
  }
  
  .auth-visual-title {
      font-family: var(--font-display); font-size: 2.5rem; font-weight: 600; line-height: 1.2; margin-bottom: var(--space-4); margin-top: 0; color: white;
  }
  
  .auth-visual-text {
      color: var(--slate-300); font-size: 1.1rem; line-height: 1.6; margin: 0;
  }
  
  .auth-split-form {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: var(--space-8);
      background: var(--color-surface);
  }
  
  .auth-form-container {
      width: 100%;
      max-width: 440px;
  }
  
  .auth-logo-mobile { display: none; }
  
  .auth-header { margin-bottom: var(--space-8); }
  .auth-title { font-family: var(--font-display); font-size: 2rem; color: var(--color-ink); margin-bottom: var(--space-2); margin-top: 0; }
  .auth-subtitle { color: var(--slate-500); font-size: 1rem; margin: 0; }
  .auth-footer { margin-top: var(--space-6); font-size: 0.95rem; color: var(--slate-600); }
  .auth-footer a { color: var(--color-teal); text-decoration: none; font-weight: 600; }
  .auth-footer a:hover { text-decoration: underline; }
  
  .generic-error { background: var(--color-danger-100); color: var(--color-danger); padding: var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-6); display: flex; align-items: center; gap: var(--space-2); font-size: 0.95rem; font-weight: 500; }
  .generic-success { background: var(--color-success-100); color: var(--color-success); padding: var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-6); display: flex; align-items: center; gap: var(--space-2); font-size: 0.95rem; font-weight: 500; }

  @media (max-width: 900px) {
      .auth-split-layout { flex-direction: column; }
      .auth-split-visual { 
          flex: none; 
          padding: var(--space-8) var(--space-6);
          min-height: 180px;
      }
      .auth-split-visual::before {
          content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
          width: auto; height: auto;
          background: 
              radial-gradient(circle at 50% 50%, rgba(20, 184, 166, 0.15) 0%, transparent 70%),
              linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
              linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
          background-size: 100% 100%, 24px 24px, 24px 24px;
          background-position: center;
      }
      .auth-visual-content { align-items: center; text-align: center; justify-content: center; }
      .auth-logo-inverse { margin: 0; font-size: 2rem; }
      .auth-mockup { display: none; }
      .auth-visual-text-block { display: none; } /* Hide text entirely on mobile to save space */
      
      .auth-split-form { padding: var(--space-8) var(--space-6); justify-content: flex-start; }
      .auth-header { text-align: center; }
      .auth-footer { text-align: center; }
  }
</style>
</head>
<body>
  <div class="auth-split-layout">
      <!-- Left Visual Side -->
      <div class="auth-split-visual">
          <div class="auth-visual-content">
              <a href="/" class="auth-logo-inverse"><?= htmlspecialchars(\App\Core\Site::name()) ?></a>
              
              <div class="auth-mockup">
                  <div class="auth-mockup-card">
                      <div class="auth-mockup-row">
                          <span class="auth-mockup-label">Virtual Card</span>
                          <span class="material-symbols-outlined" style="opacity: 0.8;">contactless</span>
                      </div>
                      <div class="auth-mockup-value">**** **** **** 4920</div>
                  </div>
                  <div class="auth-mockup-item">
                      <div class="auth-mockup-icon"><span class="material-symbols-outlined" style="font-size: 18px;">check</span></div>
                      <div style="flex: 1;">
                          <div style="font-size: 0.85rem; font-weight: 600; color: white;">Payment Received</div>
                          <div style="font-size: 0.75rem; color: var(--slate-400);">Just now</div>
                      </div>
                      <div style="font-size: 0.9rem; font-weight: 600; color: var(--color-teal);">+$1,250.00</div>
                  </div>
              </div>

              <div class="auth-visual-text-block">
                  <h2 class="auth-visual-title">Speedy, Easy and Fast</h2>
                  <p class="auth-visual-text">Join thousands of users who trust <?= htmlspecialchars(\App\Core\Site::name()) ?> to manage their finances securely. Plain-language banking designed for you.</p>
              </div>
          </div>
      </div>

      <!-- Right Form Side -->
      <div class="auth-split-form">
          <div class="auth-form-container">
              <?= $content ?? '' ?>
          </div>
      </div>
  </div>
  
  <?= $extraScripts ?? '' ?>
<script src="/assets/js/modules/pwa.js"></script>
</body>
</html>
