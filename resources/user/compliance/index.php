<?php ob_start();
/** @var array|null $pendingCode */
/** @var bool $hasOpenFlag */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>

<?php if ($flashError): ?>
<div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>
<?php if ($flashSuccess): ?>
<div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>

<div style="max-width: 560px; margin: 0 auto;">

  <?php if ($pendingCode): ?>
    <!-- ── State 2: Code assigned — user enters it now (FR-5.4) ── -->
    <div style="background: var(--color-surface); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); padding: var(--space-8); border: 1px solid var(--slate-100);">

      <div style="display:flex; align-items:center; gap: var(--space-4); margin-bottom: var(--space-6);">
        <div style="width:56px; height:56px; border-radius:50%; background: var(--color-teal-100); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <span class="material-symbols-outlined" style="font-size:28px; color: var(--color-teal);">verified_user</span>
        </div>
        <div>
          <h2 style="margin:0 0 var(--space-1); font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">Enter Your Clearance Code</h2>
          <p style="margin:0; font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-500);">
            Requirement: <strong><?= htmlspecialchars($pendingCode['name'] ?? 'Compliance') ?></strong>
          </p>
        </div>
      </div>

      <p style="font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-700); margin: 0 0 var(--space-6);">
        Support has completed your compliance review and communicated a clearance code to you. Enter that code below to restore your withdrawal access.
      </p>

      <form action="/compliance/verify" method="POST" id="complianceForm">
        <?= \App\Middlewares\CsrfMiddleware::field() ?>

        <div style="margin-bottom: var(--space-6);">
          <label style="display:block; font: 500 var(--type-caption-size)/1 var(--font-body); color: var(--slate-500); text-transform:uppercase; letter-spacing:.05em; margin-bottom: var(--space-3);">Clearance Code</label>
          <!-- Design System 6.2: segmented OTP-style boxes, --font-data, Rounded icon variant -->
          <div class="otp-group" id="otpGroup">
            <?php for ($i = 0; $i < 6; $i++): ?>
              <input type="text"
                     name="code_box[]"
                     class="otp-box"
                     maxlength="1"
                     inputmode="text"
                     autocomplete="one-time-code"
                     id="otp<?= $i ?>"
                     aria-label="Code character <?= $i + 1 ?>">
            <?php endfor; ?>
          </div>
          <p style="font: 400 var(--type-caption-size)/var(--type-caption-lh) var(--font-body); color: var(--slate-500); margin: var(--space-2) 0 0;">
            If the code is incorrect, contact Support — we will not reveal the correct value.
          </p>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">Submit Code</button>
      </form>
    </div>

  <?php else: ?>
    <!-- ── State 1: Flag exists, no code yet — contact Support (FR-5.5) ── -->
    <div style="background: var(--color-surface); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); padding: var(--space-8); border: 1px solid var(--color-warning-100); text-align:center;">

      <div style="width:72px; height:72px; border-radius:50%; background: var(--color-warning-100); display:flex; align-items:center; justify-content:center; margin: 0 auto var(--space-5);">
        <span class="material-symbols-outlined" style="font-size:36px; color: var(--color-warning);">gavel</span>
      </div>

      <h2 style="margin:0 0 var(--space-3); font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">Account Under Compliance Review</h2>

      <p style="font: 400 var(--type-body-md-size)/1.6 var(--font-body); color: var(--slate-700); max-width: 440px; margin: 0 auto var(--space-6);">
        Your account has been flagged for a compliance review in accordance with our financial policies. Your withdrawal access is temporarily paused until this review is complete.
      </p>

      <div style="background: var(--color-warning-100); border-radius: var(--radius-sm); padding: var(--space-4) var(--space-5); margin-bottom: var(--space-6); text-align:left;">
        <div style="display:flex; align-items:flex-start; gap: var(--space-3);">
          <span class="material-symbols-outlined" style="color: var(--color-warning); font-size:20px; margin-top:1px; flex-shrink:0;">info</span>
          <div>
            <p style="margin:0 0 var(--space-1); font: 600 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--color-warning);">What to do next</p>
            <p style="margin:0; font: 400 var(--type-body-md-size)/1.5 var(--font-body); color: var(--slate-700);">
              Contact our Support team to begin the off-system review process. Once complete, Support will issue you a clearance code to enter here to restore your withdrawal access. All other account features remain available.
            </p>
          </div>
        </div>
      </div>

      <a href="/support" class="btn btn-primary" style="display:inline-flex; align-items:center; gap: var(--space-2); text-decoration:none;">
        <span class="material-symbols-outlined" style="font-size:18px;">support_agent</span>
        Contact Support
      </a>

      <p style="font: 400 var(--type-caption-size)/var(--type-caption-lh) var(--font-body); color: var(--slate-500); margin: var(--space-4) 0 0;">
        Your other account features — Virtual Card, Transactions, Notifications — are unaffected.
      </p>
    </div>
  <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
$currentPath = '/compliance';
$extraScripts = <<<'JS'
<script>
// OTP segmented input — Design System 6.2
// Rounded icon style referenced (chat bubble / compliance-code inputs use Rounded variant)
(function () {
  var boxes = document.querySelectorAll('#otpGroup .otp-box');
  if (!boxes.length) return;

  boxes.forEach(function (box, i) {
    box.addEventListener('input', function () {
      this.value = this.value.toUpperCase().slice(-1);
      if (this.value && i < boxes.length - 1) boxes[i + 1].focus();
    });
    box.addEventListener('keydown', function (e) {
      if (e.key === 'Backspace' && !this.value && i > 0) boxes[i - 1].focus();
    });
    box.addEventListener('paste', function (e) {
      e.preventDefault();
      var pasted = (e.clipboardData || window.clipboardData).getData('text').toUpperCase().replace(/\s/g, '');
      pasted.split('').slice(0, boxes.length).forEach(function (ch, j) {
        if (boxes[i + j]) boxes[i + j].value = ch;
      });
      var next = Math.min(i + pasted.length, boxes.length - 1);
      boxes[next].focus();
    });
  });
})();
</script>
JS;
require __DIR__ . '/../../layouts/app.php';
?>
