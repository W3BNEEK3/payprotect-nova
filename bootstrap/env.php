<?php
/**
 * bootstrap/env.php — interim .env loader, Phase 0 only.
 *
 * This is deliberately minimal. It exists solely to get plaintext credentials out of
 * source control immediately, without waiting on Phase 1's full framework build.
 *
 * SUPERSEDED BY: app/Core/EnvLoader.php (Implementation Plan P1.2).
 * Do not add features to this file — when Phase 1 lands, delete it and update the two
 * call sites below (config/config.php, config/mail_config.php) to use the real class.
 */

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                ".env file not found at {$path}. Copy .env.example to .env and fill in real values."
            );
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and blank lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip surrounding quotes if present
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}
