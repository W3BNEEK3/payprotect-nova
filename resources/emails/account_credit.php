<?php
/**
 * Expected variables:
 * @var string $fullname
 * @var string $amountFormatted
 * @var string $method
 * @var string $date
 * @var string $reference
 */
ob_start();
?>
<div style="text-align: center; margin-bottom: 24px;">
    <h2 style="margin: 0; color: #0d121c; font-size: 24px;">Account Credited</h2>
</div>

<p style="color: #4b5563; font-size: 16px; line-height: 1.5; margin-bottom: 24px;">
    Hello <?= htmlspecialchars($fullname) ?>,
</p>

<p style="color: #4b5563; font-size: 16px; line-height: 1.5; margin-bottom: 24px;">
    We are writing to inform you that your NovaTrust account has been successfully credited with <strong><?= htmlspecialchars($amountFormatted) ?></strong>.
</p>

<div style="background-color: #f8fafc; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
    <h3 style="margin: 0 0 16px; color: #0d121c; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Transaction Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #64748b; font-size: 14px;">Amount</td>
            <td style="padding: 8px 0; color: #0d121c; font-size: 14px; text-align: right; font-weight: bold;"><?= htmlspecialchars($amountFormatted) ?></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #64748b; font-size: 14px;">Method</td>
            <td style="padding: 8px 0; color: #0d121c; font-size: 14px; text-align: right;"><?= htmlspecialchars($method) ?></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #64748b; font-size: 14px;">Date</td>
            <td style="padding: 8px 0; color: #0d121c; font-size: 14px; text-align: right;"><?= htmlspecialchars($date) ?></td>
        </tr>
        <?php if (!empty($reference)): ?>
        <tr>
            <td style="padding: 8px 0; color: #64748b; font-size: 14px;">Reference</td>
            <td style="padding: 8px 0; color: #0d121c; font-size: 14px; text-align: right;"><?= htmlspecialchars($reference) ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<p style="color: #4b5563; font-size: 16px; line-height: 1.5; margin-bottom: 24px;">
    You can log in to your dashboard to view your updated balance and transaction history.
</p>

<div style="text-align: center; margin-top: 32px;">
    <a href="<?= env('APP_URL') ?>/dashboard" style="display: inline-block; background-color: #0f766e; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 16px;">View Dashboard</a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
