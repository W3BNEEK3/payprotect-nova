<?php

namespace App\Providers\Mail;

use App\Repositories\MailSettingsRepository;
use App\Helpers\Crypto;
use App\Interfaces\MailProviderInterface;

class MailProviderFactory
{
    public function __construct(private MailSettingsRepository $repo)
    {
    }

    /**
     * config JSON holds plaintext operational fields (host, port, from address)
     * plus one base64-encoded encrypted field (secret_encrypted) for the SMTP
     * password or Resend API key — never a plaintext secret at rest.
     */
    public function make(): MailProviderInterface
    {
        $settings = $this->repo->getActive();

        if ($settings === null) {
            throw new \RuntimeException('No active mail driver configured in mail_settings.');
        }

        $config = json_decode($settings['config'], true) ?: [];

        if (isset($config['secret_encrypted'])) {
            $secretBinary = base64_decode($config['secret_encrypted']);
            $decrypted = Crypto::decrypt($secretBinary);
            $config['password'] = $decrypted;
            $config['api_key'] = $decrypted;
        }

        return match ($settings['driver']) {
            'smtp' => new SmtpMailProvider($config),
            'resend' => new ResendMailProvider($config),
            default => throw new \RuntimeException("Unknown mail driver: {$settings['driver']}"),
        };
    }
}
