<?php
namespace App\Interfaces;

interface MailProviderInterface
{
    public function send(string $to, string $subject, string $body): bool;
}
