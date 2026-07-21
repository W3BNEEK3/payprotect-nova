<?php ob_start(); ?>

<div class="admin-header">
    <h1 class="admin-title">Mail Driver Settings</h1>
    <p class="admin-subtitle">Configure outbound email delivery via SMTP or Resend API.</p>
</div>

<?php if ($success = \App\Core\Session::getFlash('settings_success')): ?>
    <div class="alert alert-success" style="margin-bottom: var(--space-6);">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error = \App\Core\Session::getFlash('settings_error')): ?>
    <div class="alert alert-error" style="margin-bottom: var(--space-6);">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<style>
    .mail-settings-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: var(--space-8);
    }
    .form-row-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-4);
    }
    @media (max-width: 768px) {
        .mail-settings-grid {
            grid-template-columns: 1fr;
        }
        .form-row-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="mail-settings-grid">
    
    <!-- Settings Form -->
    <div class="card">
        <form action="/admin/mail-settings" method="POST">
            <?= \App\Middlewares\CsrfMiddleware::field() ?>

            <div class="field-group">
                <label class="label">Mail Driver</label>
                <select name="driver" id="driver-select" class="input" onchange="toggleDriverFields()">
                    <option value="smtp" <?= $driver === 'smtp' ? 'selected' : '' ?>>SMTP Server</option>
                    <option value="resend" <?= $driver === 'resend' ? 'selected' : '' ?>>Resend API</option>
                </select>
            </div>

            <!-- SMTP Section -->
            <div id="smtp-fields" style="<?= $driver === 'smtp' ? '' : 'display: none;' ?>">
                <div class="form-row-grid">
                    <div class="field-group">
                        <label class="label">Host</label>
                        <input type="text" name="smtp_host" class="input" value="<?= htmlspecialchars($config['host'] ?? '') ?>" placeholder="smtp.mailtrap.io">
                    </div>
                    <div class="field-group">
                        <label class="label">Port</label>
                        <input type="number" name="smtp_port" class="input" value="<?= htmlspecialchars($config['port'] ?? '587') ?>">
                    </div>
                </div>
                
                <div class="form-row-grid">
                    <div class="field-group">
                        <label class="label">Username</label>
                        <input type="text" name="smtp_username" class="input" value="<?= htmlspecialchars($config['username'] ?? '') ?>">
                    </div>
                    <div class="field-group">
                        <label class="label">Password</label>
                        <input type="password" name="smtp_password" class="input" placeholder="<?= $hasSecret && $driver === 'smtp' ? '•••••••• (Set to overwrite)' : 'Enter SMTP password' ?>">
                    </div>
                </div>

                <div class="field-group">
                    <label class="label">Encryption</label>
                    <select name="smtp_encryption" class="input">
                        <option value="tls" <?= ($config['encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (STARTTLS)</option>
                        <option value="ssl" <?= ($config['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="" <?= empty($config['encryption']) ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
                
                <div class="form-row-grid">
                    <div class="field-group">
                        <label class="label">From Address</label>
                        <input type="email" name="smtp_from_address" class="input" value="<?= htmlspecialchars($config['from_address'] ?? '') ?>" placeholder="noreply@novatrust.com">
                    </div>
                    <div class="field-group">
                        <label class="label">From Name</label>
                        <input type="text" name="smtp_from_name" class="input" value="<?= htmlspecialchars($config['from_name'] ?? '') ?>" placeholder="NovaTrust">
                    </div>
                </div>
            </div>

            <!-- Resend Section -->
            <div id="resend-fields" style="<?= $driver === 'resend' ? '' : 'display: none;' ?>">
                <div class="field-group">
                    <label class="label">Resend API Key</label>
                    <input type="password" name="resend_api_key" class="input" placeholder="<?= $hasSecret && $driver === 'resend' ? '•••••••• (Set to overwrite)' : 're_...' ?>">
                </div>

                <div class="form-row-grid">
                    <div class="field-group">
                        <label class="label">From Address</label>
                        <input type="email" name="resend_from_address" class="input" value="<?= htmlspecialchars($config['from_address'] ?? '') ?>" placeholder="noreply@novatrust.com">
                    </div>
                    <div class="field-group">
                        <label class="label">From Name</label>
                        <input type="text" name="resend_from_name" class="input" value="<?= htmlspecialchars($config['from_name'] ?? '') ?>" placeholder="NovaTrust">
                    </div>
                </div>
            </div>

            <div style="margin-top: var(--space-6);">
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </div>
        </form>
    </div>

    <!-- Test Email Panel -->
    <div class="card" style="align-self: start;">
        <h3 style="font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink); margin-top: 0; margin-bottom: var(--space-4);">Send Test Email</h3>
        <p style="color: var(--slate-500); font-size: 0.9rem; margin-bottom: var(--space-4);">
            Verify your current active mail settings by sending a test email to your inbox.
        </p>

        <form id="test-email-form" onsubmit="sendTestEmail(event)">
            <?= \App\Middlewares\CsrfMiddleware::field() ?>
            <div class="field-group">
                <label class="label">Recipient Email</label>
                <input type="email" id="test-email-address" class="input" placeholder="admin@example.com" required>
            </div>
            
            <div id="test-email-result" style="margin-bottom: var(--space-4); display: none; font-size: 0.9rem; padding: var(--space-3); border-radius: var(--radius-md);"></div>

            <button type="submit" class="btn btn-secondary" id="test-btn" style="width: 100%;">
                <span class="material-symbols-outlined" style="margin-right: 8px;">send</span> Dispatch Test
            </button>
        </form>
    </div>
</div>

<script>
function toggleDriverFields() {
    const driver = document.getElementById('driver-select').value;
    document.getElementById('smtp-fields').style.display = driver === 'smtp' ? 'block' : 'none';
    document.getElementById('resend-fields').style.display = driver === 'resend' ? 'block' : 'none';
}

async function sendTestEmail(e) {
    e.preventDefault();
    
    const btn = document.getElementById('test-btn');
    const resultDiv = document.getElementById('test-email-result');
    const email = document.getElementById('test-email-address').value;
    
    // In our JSON API, we need to pass the CSRF token.
    const csrfInput = document.querySelector('input[name="csrf_token"]') || document.querySelector('input[name="_csrf"]');
    const csrfToken = csrfInput ? csrfInput.value : '';

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined" style="margin-right: 8px;">sync</span> Sending...';
    resultDiv.style.display = 'none';

    try {
        const response = await fetch('/api/admin/mail-settings/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ 
                test_email: email,
                _csrf: csrfToken
            }).toString()
        });

        const data = await response.json();

        resultDiv.style.display = 'block';
        if (response.ok) {
            resultDiv.style.background = 'var(--teal-50)';
            resultDiv.style.color = 'var(--color-primary)';
            resultDiv.style.border = '1px solid var(--teal-100)';
            resultDiv.textContent = data.message || 'Test email sent successfully!';
        } else {
            resultDiv.style.background = '#fef2f2';
            resultDiv.style.color = 'var(--color-danger)';
            resultDiv.style.border = '1px solid #fecaca';
            resultDiv.textContent = data.message || 'Failed to send test email.';
        }
    } catch (err) {
        resultDiv.style.display = 'block';
        resultDiv.style.background = '#fef2f2';
        resultDiv.style.color = 'var(--color-danger)';
        resultDiv.style.border = '1px solid #fecaca';
        resultDiv.textContent = 'Error: ' + err.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined" style="margin-right: 8px;">send</span> Dispatch Test';
    }
}
</script>

<?php
$content = ob_get_clean();
$currentPath = '/admin/mail-settings';
require __DIR__ . '/../../layouts/admin.php';
?>
