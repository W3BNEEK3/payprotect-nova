<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'mail.novartrust.com';
$mail->SMTPAuth = true;
$mail->Username = 'accounts@novartrust.com';
$mail->Password = 'OGA! no foget to updat your password';
$mail->SMTPAutoTLS = false;
$mail->Port = 25;

// Set sender info
$mail->setFrom('noreply@novartrust.com', 'NovaTrust');