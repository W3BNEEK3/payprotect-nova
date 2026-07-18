<?php

namespace App\Core;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    public static function view(string $path, array $data = []): void
    {
        extract($data);
        $file = __DIR__ . '/../../resources/' . $path . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: {$path}");
        }

        require $file;
    }

    public static function abort(int $status, string $message = ''): void
    {
        http_response_code($status);
        echo $message !== '' ? $message : "Error {$status}";
        exit;
    }
}
