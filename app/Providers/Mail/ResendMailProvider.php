<?php

namespace App\Providers\Mail;

use App\Interfaces\MailProviderInterface;
use App\Exceptions\ProviderException;

class ResendMailProvider implements MailProviderInterface
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->config['api_key'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($this->buildPayload($to, $subject, $body)),
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        }

        throw new ProviderException("Resend send failed (HTTP {$statusCode}): {$response}", 'resend');
    }

    /**
     * Payload construction split out from send() specifically so it can be unit
     * tested without making a real network call to api.resend.com.
     */
    public function buildPayload(string $to, string $subject, string $body): array
    {
        return [
            'from' => ($this->config['from_name'] ?? 'NovaTrust') . ' <' . $this->config['from_address'] . '>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $body,
        ];
    }
}
