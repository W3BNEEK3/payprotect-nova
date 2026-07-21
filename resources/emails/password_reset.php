<?php
/**
 * @var string $resetLink
 * @var string $subject
 */
?>
<h1>Password Reset</h1>
<p>Hello,</p>
<p>We received a request to reset the password for your NovaTrust account. If you didn't make this request, you can safely ignore this email.</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
    <tbody>
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td> <a href="<?= htmlspecialchars($resetLink) ?>" target="_blank">Reset Password</a> </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>

<p>This link will expire soon.</p>
<p>Regards,<br>The NovaTrust Team</p>

<hr class="hr">
<p class="subtext">
    If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
    <a href="<?= htmlspecialchars($resetLink) ?>"><?= htmlspecialchars($resetLink) ?></a>
</p>
