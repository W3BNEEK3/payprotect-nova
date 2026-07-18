<?php
namespace App\Interfaces;

interface RepositoryInterface
{
    public function find(int $id): ?array;
    public function all(): array;
}
