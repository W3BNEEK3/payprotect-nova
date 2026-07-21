<?php

namespace App\Core;

class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        Logger::error("{$message} in {$file}:{$line}");

        if (env('APP_DEBUG', 'false') === 'true') {
            echo "<pre>Error: {$message} in {$file} on line {$line}</pre>";
        }

        return true;
    }

    public static function handleException(\Throwable $e): void
    {
        Logger::error($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

        if (env('APP_DEBUG', 'false') === 'true') {
            require __DIR__ . '/../../resources/errors/debug.php';
            return;
        }

        Response::abort(500, 'Something went wrong. Please try again later.');
    }
}
