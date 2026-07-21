<?php
namespace App\Interfaces;

interface CardIssuerInterface
{
    public function issue(int $userId, bool $isApproved = false): array;
}
