<?php
/**
 * config/mail_config.php
 *
 * SUPERSEDED BY: app/Providers/Mail/* + MailProviderFactory (Implementation Plan
 * P4.4–P4.5, wired into the admin UI in P14). Once that lands, these values move
 * again — out of .env entirely and into the mail_settings table.
 */

require_once __DIR__ . '/../bootstrap/env.php';
loadEnv(__DIR__ . '/../.env');
require_once __DIR__ . '/../vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = env('SMTP_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('SMTP_USER');
    $mail->Password   = env('SMTP_PASS');
    $mail->SMTPSecure = env('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
    $mail->Port       = (int) env('SMTP_PORT', 587);

    $mail->setFrom(env('SMTP_FROM_ADDRESS'), env('SMTP_FROM_NAME', 'NovaTrust'));

    return $mail;
}

$mail = getMailer();
?>