<?php

// Dev-only router for PHP's built-in server (mimics .htaccess rewriting,
// which the built-in server doesn't respect).
// Usage: php -S localhost:8000 -t public public/router.php

$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve real files (css, js, images) directly if they exist:
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    return false;
}

require __DIR__ . '/index.php';
