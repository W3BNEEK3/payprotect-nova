<?php

namespace App\Interfaces;

use App\Core\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): void;
}
