<?php ob_start(); 
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="support-page" style="max-width: 800px; margin: 0 auto; padding-bottom: var(--space-8);">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6);">
        
        <!-- Live Chat Card -->
        <div style="background: var(--color-surface); border-radius: var(--radius-lg); padding: var(--space-6); border: 1px solid var(--slate-100); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; text-align: center;">
            <div style="width: 64px; height: 64px; background: var(--teal-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-4); color: var(--color-primary);">
                <span class="material-symbols-outlined" style="font-size: 32px;">forum</span>
            </div>
            <h2 style="font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); margin: 0 0 var(--space-2); color: var(--color-ink);">Live Chat</h2>
            <p style="color: var(--slate-500); font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); margin: 0 0 var(--space-6); flex-grow: 1;">
                Get immediate help from our automated assistant or speak directly with an agent.
            </p>
            <button id="open-live-chat" class="btn btn-primary" style="width: 100%;">Start Chat</button>
        </div>

        <!-- Ticket Card -->
        <div style="background: var(--color-surface); border-radius: var(--radius-lg); padding: var(--space-6); border: 1px solid var(--slate-100); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-4);">
                <div style="width: 48px; height: 48px; background: var(--slate-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--slate-600);">
                    <span class="material-symbols-outlined" style="font-size: 24px;">mail</span>
                </div>
                <h2 style="font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); margin: 0; color: var(--color-ink);">Open a Ticket</h2>
            </div>
            
            <form action="/support/ticket" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                
                <div class="field-group">
                    <label for="subject" class="label">Subject</label>
                    <input type="text" id="subject" name="subject" class="input" required>
                </div>
                
                <div class="field-group">
                    <label for="message" class="label">Message</label>
                    <textarea id="message" name="message" class="input" rows="4" required style="resize: vertical;"></textarea>
                </div>
                
                <button type="submit" class="btn btn-secondary" style="width: 100%;">Submit Ticket</button>
            </form>
        </div>

    </div>
</div>
<script src="/assets/js/modules/chat.js" type="module"></script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
