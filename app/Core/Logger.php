<?php

namespace App\Core;

class Logger
{
    private static function path(): string
    {
        return __DIR__ . '/../../storage/logs/app.log';
    }

    private static function write(string $level, string $message): void
    {
        $dir = dirname(self::path());

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $line = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);
        file_put_contents(self::path(), $line, FILE_APPEND);
    }

    public static function info(string $message): void
    {
        self::write('info', $message);
    }

    public static function warning(string $message): void
    {
        self::write('warning', $message);
    }

    public static function error(string $message): void
    {
        self::write('error', $message);
    }
}
