<?php
namespace App\Exceptions;

class ProviderException extends AppException
{
    private string $provider;

    public function __construct(string $message, string $provider = '')
    {
        parent::__construct($message);
        $this->provider = $provider;
    }

    public function provider(): string
    {
        return $this->provider;
    }
}
