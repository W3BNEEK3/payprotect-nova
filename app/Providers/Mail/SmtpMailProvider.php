<?php

namespace App\Providers\Mail;

use App\Interfaces\MailProviderInterface;
use App\Exceptions\ProviderException;

class SmtpMailProvider implements MailProviderInterface
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password']; // decrypted by MailProviderFactory before this
            $mail->SMTPSecure = $this->config['encryption'] ?? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) ($this->config['port'] ?? 587);
            $mail->setFrom($this->config['from_address'], $this->config['from_name'] ?? 'NovaTrust');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (\Exception $e) {
            throw new ProviderException('SMTP send failed: ' . $e->getMessage(), 'smtp');
        }
    }
}
