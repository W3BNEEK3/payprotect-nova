<?php

namespace App\Controllers;

use App\Core\Response;

abstract class BaseController
{
    protected function json(array $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function view(string $path, array $data = []): void
    {
        Response::view($path, $data);
    }

    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }
}
