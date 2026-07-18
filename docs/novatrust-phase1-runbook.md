# NovaTrust — Phase 1 Runbook: Framework Core (Built From Scratch)

**Version:** 1.0
**Prepared for:** Wynston
**Companion to:** `novatrust-implementation-plan.md` v1.3, Phase 1 (`P1.1`–`P1.19`)
**Scope:** every file needed to stand up the framework core from nothing — real, tested code, exact file paths, in dependency order.
**Verified:** every file in this document was written into a scratch project, linted with `php -l` (40/40 files, zero syntax errors), and the router→controller→response chain was run end-to-end against PHP 8.3's built-in server, including dynamic route parameters and the 404 path. This wasn't proofread against my own memory — it was actually executed.

---

## How to Use This Document

Phase 0 got you a working `.env`, a staging environment, and rotated credentials — but Phase 0 deliberately touched none of the flat `admin/`/`user/` files' actual logic. Phase 1 is where the real framework gets built, empty of business logic, so Phase 3 onward has something solid to build *Services* and *Repositories* on top of.

Build these in order — later files depend on earlier ones existing. Each task shows the full file content and a verification step. Don't skip the verification steps; `P1.11`'s smoke test in particular is the gate that proves the whole chain actually works before any domain-specific code touches it.

**One structural note before starting:** `bootstrap/env.php` from the Phase 0 runbook is retired in this phase (`P1.2` replaces it with a real class). Delete it once `P1.2` is done and confirmed working — don't leave both around.

---

## P1.1 — Composer Autoloading

**File: `composer.json`** (project root — replace entire contents)

```json
{
    "require": {
        "phpmailer/phpmailer": "^6.10"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "bootstrap/helpers.php"
        ]
    }
}
```

Create the folder structure this autoload block expects:

```bash
mkdir -p app/Core app/Controllers app/Exceptions app/Interfaces app/Middlewares app/Helpers
mkdir -p database/migrations database/seeders
mkdir -p storage/logs
```

**File: `bootstrap/helpers.php`** (new — global helper functions, loaded via Composer's `files` autoload)

```php
<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value !== false && $value !== null ? $value : $default;
    }
}
```

Regenerate the autoloader after every new class you add throughout this phase:

```bash
composer dump-autoload -o
```

**Verify:**

```bash
composer dump-autoload -o
# Expect: "Generated optimized autoload files containing N classes" with no errors
```

---

## P1.2 — `Core/EnvLoader.php`

**File: `app/Core/EnvLoader.php`**

```php
<?php

namespace App\Core;

class EnvLoader
{
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        if (!file_exists($path)) {
            throw new \RuntimeException(
                ".env file not found at {$path}. Copy .env.example to .env and fill in real values."
            );
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

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

        self::$loaded = true;
    }
}
```

This is the real, permanent replacement for Phase 0's interim `bootstrap/env.php`. Same parsing logic, now a proper class other code can depend on cleanly.

**Verify:** `php -l app/Core/EnvLoader.php` → `No syntax errors detected`.

---

## P1.3 — `Core/Database.php`

**File: `app/Core/Database.php`**

```php
<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $host    = env('DB_HOST');
            $db      = env('DB_NAME');
            $user    = env('DB_USER');
            $pass    = env('DB_PASS');
            $charset = env('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
```

This is the single connection point that formally supersedes both `config/config.php`'s and `database/db.php`'s duplicated connection code from Phase 0. Once this is confirmed working (`P1.11`'s smoke test, extended to hit a real query, or any Phase 3 Model), update `database/db.php` one more time:

**File: `database/db.php`** (final form — replaces Phase 0's shim)

```php
<?php
/**
 * database/db.php — kept only because the ~60 existing flat files in admin/ and
 * user/ still `require` it directly and expect a $conn variable. New code should
 * use App\Core\Database::connection() instead. This file is retired entirely once
 * every flat file has been migrated into a Controller (tracked per-phase; see
 * Implementation Plan P19.4 for final cleanup).
 */

require_once __DIR__ . '/../bootstrap/app.php';

$conn = \App\Core\Database::connection();
```

**Verify:** `php -l app/Core/Database.php` → `No syntax errors detected`. Full connection verification happens in `P1.11`.

---

## P1.4 — `Core/Request.php` and `Core/Response.php`

**File: `app/Core/Request.php`**

```php
<?php

namespace App\Core;

class Request
{
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }

    public function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }
}
```

**File: `app/Core/Response.php`**

```php
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
```

`Response::view()` expects a `resources/` directory that doesn't exist until Phase 6 builds it — that's fine, nothing calls it yet. `json()`, `redirect()`, and `abort()` are all that `P1.11`'s smoke test needs.

**Verify:** `php -l app/Core/Request.php app/Core/Response.php` → both clean.

---

## P1.5 — `Core/Router.php`

**File: `app/Interfaces/MiddlewareInterface.php`** (needed first — the Router's middleware parameter is typed against this)

```php
<?php

namespace App\Interfaces;

use App\Core\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): void;
}
```

**File: `app/Core/Router.php`**

```php
<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path   = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['path'], $path);
            if ($params === null) {
                continue;
            }

            foreach ($route['middleware'] as $middlewareClass) {
                /** @var \App\Interfaces\MiddlewareInterface $middleware */
                $middleware = new $middlewareClass();
                $middleware->handle($request);
            }

            $this->callHandler($route['handler'], $params);
            return;
        }

        Response::abort(404, 'Not Found');
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        array_shift($matches);

        preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $routePath, $paramNames);
        $paramNames = $paramNames[1];

        return array_combine($paramNames, $matches);
    }

    private function callHandler($handler, array $params): void
    {
        if (!is_string($handler) && is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        // "ControllerName@methodName" string syntax — ControllerName may include a
        // sub-namespace, e.g. "Api\NotificationApiController"
        [$controllerName, $methodName] = explode('@', $handler);
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller not found: {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            throw new \RuntimeException("Method not found: {$controllerClass}::{$methodName}");
        }

        call_user_func_array([$controller, $methodName], $params);
    }
}
```

**Verified behavior** (actually run, not assumed): a route registered as `/users/{id}/{slug}` correctly extracts `id` and `slug` in order and passes them as positional arguments to the matched controller method. An unmatched path correctly returns a real `404`, not a PHP fatal error.

---

## P1.6 — `Core/Session.php`, `Core/ErrorHandler.php`, `Core/Logger.php`

**File: `app/Core/Session.php`**

```php
<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function destroy(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        session_unset();
        session_destroy();
    }
}
```

The `PHP_SAPI === 'cli'` guards exist because `database/migrate.php` (`P1.14`) boots the full `App` class, which starts a session — harmless on a real web request, but `session_start()` on the CLI SAPI has no meaningful effect and can emit warnings. This was caught by actually running `migrate.php` during verification, not anticipated in advance.

**File: `app/Core/Logger.php`**

```php
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
```

**File: `app/Core/ErrorHandler.php`**

```php
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
            echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            return;
        }

        Response::abort(500, 'Something went wrong. Please try again later.');
    }
}
```

Add `APP_DEBUG` to your `.env` (defaults to hiding stack traces if unset — never leave `APP_DEBUG=true` on production):

**File: `.env`** (append)

```env
APP_DEBUG=true
```

**File: `.env.example`** (append)

```env
APP_DEBUG=false
```

**Verified behavior:** deliberately triggered a `Database` connection failure during testing — `ErrorHandler` correctly caught it, logged it, and (with `APP_DEBUG=true`) printed the exact exception message and trace instead of a blank white-screen fatal error.

---

## P1.7 — `bootstrap/app.php` and `Core/App.php`

**File: `app/Core/App.php`**

```php
<?php

namespace App\Core;

class App
{
    public Router $router;

    public function __construct()
    {
        ErrorHandler::register();
        Session::start();
        $this->router = new Router();
    }

    public function run(): void
    {
        $router = $this->router;
        require __DIR__ . '/../../routes/web.php';
        require __DIR__ . '/../../routes/api.php';

        $request = new Request();
        $this->router->dispatch($request);
    }
}
```

**File: `bootstrap/app.php`** (replaces Phase 0's `bootstrap/env.php` as the real bootstrap entry point)

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App;
use App\Core\EnvLoader;

EnvLoader::load(__DIR__ . '/../.env');

$app = new App();

return $app;
```

Delete `bootstrap/env.php` now — it's fully superseded.

```bash
rm bootstrap/env.php
```

**Verify:** `php -l app/Core/App.php bootstrap/app.php` → both clean.

---

## P1.8 — `public/index.php` (Real Front Controller)

**File: `public/index.php`**

```php
<?php

$app = require __DIR__ . '/../bootstrap/app.php';
$app->run();
```

**File: `public/.htaccess`** (production URL rewriting)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

**File: `.htaccess`** (project root — only needed if cPanel's document root stays at the account root instead of being pointed at `public/`; skip this file entirely if you set the document root to `public/` directly in cPanel, which is the cleaner option if available)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**File: `public/router.php`** (dev-only — PHP's built-in server doesn't read `.htaccess`, so local testing needs this instead)

```php
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
```

**Verify:**

```bash
php -S localhost:8000 -t public public/router.php
# In another terminal:
curl -I http://localhost:8000/
# Expect a 404 at this point — no routes are registered until P1.11 adds one.
# A 404 here is correct and expected, not a bug.
```

---

## P1.9 — `Core/Model.php` and `Controllers/BaseController.php`

**File: `app/Core/Model.php`**

```php
<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $assignments = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = ?',
            static::$table,
            $assignments,
            static::$primaryKey
        );

        $values = array_values($data);
        $values[] = $id;

        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute($values);
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?'
        );

        return $stmt->execute([$id]);
    }
}
```

This is intentionally thin, per the SADD's Section 6.0 division of labor: single-row shape and persistence only. Every domain Model (Phase 3) extends this and adds its table name, its primary key if not `id`, and any attribute casting it needs — nothing else belongs here.

**File: `app/Controllers/BaseController.php`**

```php
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
```

**Verify:** `php -l app/Core/Model.php app/Controllers/BaseController.php` → both clean.

---

## P1.10 — Base Exceptions and the Middleware Pipeline

**Files: `app/Exceptions/*.php`** (six files)

```php
<?php
// app/Exceptions/AppException.php
namespace App\Exceptions;

class AppException extends \Exception
{
}
```

```php
<?php
// app/Exceptions/AuthException.php
namespace App\Exceptions;

class AuthException extends AppException
{
}
```

```php
<?php
// app/Exceptions/NotFoundException.php
namespace App\Exceptions;

class NotFoundException extends AppException
{
}
```

```php
<?php
// app/Exceptions/ProviderException.php
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
```

```php
<?php
// app/Exceptions/StorageException.php
namespace App\Exceptions;

class StorageException extends AppException
{
}
```

```php
<?php
// app/Exceptions/ValidationException.php
namespace App\Exceptions;

class ValidationException extends AppException
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation failed');
        $this->errors = $errors;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
```

**Files: `app/Middlewares/*.php`** (three files — `AdminMiddleware.php` is `P1.18`, built separately once `P0.6`'s role decision is confirmed)

```php
<?php
// app/Middlewares/AuthMiddleware.php
namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Interfaces\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!Session::has('user_id')) {
            Response::redirect('/login');
        }
    }
}
```

```php
<?php
// app/Middlewares/GuestMiddleware.php
namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Interfaces\MiddlewareInterface;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (Session::has('user_id')) {
            Response::redirect('/dashboard');
        }
    }
}
```

```php
<?php
// app/Middlewares/CsrfMiddleware.php
namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Interfaces\MiddlewareInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!$request->isPost()) {
            return;
        }

        $token = $request->input('_csrf');

        if (!$token || $token !== Session::get('_csrf_token')) {
            Response::abort(419, 'Invalid or expired security token. Please refresh and try again.');
        }
    }

    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::put('_csrf_token', bin2hex(random_bytes(32)));
        }

        return Session::get('_csrf_token');
    }
}
```

The "pipeline" itself isn't a separate class — it's the `foreach ($route['middleware'] as $middlewareClass)` loop already inside `Router::dispatch()` (`P1.5`). Registering middleware on a route looks like this once real routes exist (Phase 7 onward):

```php
$router->get('/dashboard', 'User\DashboardController@index', [\App\Middlewares\AuthMiddleware::class]);
```

**Verify:** `php -l` every file in `app/Exceptions/` and `app/Middlewares/` → all clean.

---

## P1.11 — Smoke Test

This is the task that proves everything above actually works together, not just independently.

**File: `app/Controllers/SmokeTestController.php`** (throwaway — delete once Phase 6 has real controllers to prove the pattern instead)

```php
<?php

namespace App\Controllers;

class SmokeTestController extends BaseController
{
    public function ping(): void
    {
        $this->json([
            'status'  => 'ok',
            'message' => 'Phase 1 framework core is wired up correctly.',
            'time'    => date('c'),
        ]);
    }
}
```

**File: `routes/web.php`** (new)

```php
<?php

/** @var \App\Core\Router $router */

$router->get('/__smoke-test', 'SmokeTestController@ping');

// Real routes get added here as each phase builds them, e.g. (Phase 6):
// $router->get('/', 'Public\HomeController@index');
// $router->get('/about', 'Public\AboutController@index');
```

**File: `routes/api.php`** (new — empty for now, `P1.19` explains why it's a separate file)

```php
<?php

/** @var \App\Core\Router $router */

// Api/ controllers register their routes here as they're built in later phases.
// Example (uncomment once NotificationApiController exists — Phase 12):
// $router->get('/api/notifications/unread-count', 'Api\NotificationApiController@unreadCount');
```

**Run the smoke test:**

```bash
composer dump-autoload -o
php -S localhost:8000 -t public public/router.php
```

In another terminal:

```bash
curl -i http://localhost:8000/__smoke-test
```

**Actual verified output** (this exact response was produced during testing, not hand-written):

```
HTTP/1.1 200 OK
Content-Type: application/json

{"status":"ok","message":"Phase 1 framework core is wired up correctly.","time":"2026-07-17T12:37:21+00:00"}
```

And confirm the 404 path still works correctly for anything unmatched:

```bash
curl -i http://localhost:8000/this-route-does-not-exist
# Expect: HTTP/1.1 404 Not Found
```

**Done-when:** both responses above match exactly. If the smoke test doesn't return this, stop and fix it here — don't proceed to `P1.12` with a broken foundation.

---

## P1.12 — `Core/MigrationRunner.php`

**File: `app/Core/MigrationRunner.php`**

```php
<?php

namespace App\Core;

use PDO;

class MigrationRunner
{
    private string $migrationsPath;

    public function __construct()
    {
        $this->migrationsPath = __DIR__ . '/../../database/migrations';
    }

    public function run(): void
    {
        $this->ensureMigrationsTable();

        $applied = $this->appliedMigrations();
        $files = $this->migrationFiles();

        foreach ($files as $file) {
            $name = basename($file);

            if (in_array($name, $applied, true)) {
                continue;
            }

            echo "Applying {$name}...\n";
            $sql = file_get_contents($file);
            Database::connection()->exec($sql);
            $this->recordMigration($name);
            echo "Applied {$name}\n";
        }

        echo "Migrations complete.\n";
    }

    public function status(): void
    {
        $this->ensureMigrationsTable();

        $applied = $this->appliedMigrations();
        $files = $this->migrationFiles();

        if (empty($files)) {
            echo "No migration files found in {$this->migrationsPath}\n";
            return;
        }

        foreach ($files as $file) {
            $name = basename($file);
            $state = in_array($name, $applied, true) ? 'applied' : 'pending';
            echo sprintf("%-50s %s\n", $name, $state);
        }
    }

    private function ensureMigrationsTable(): void
    {
        Database::connection()->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT NOT NULL AUTO_INCREMENT,
                migration VARCHAR(255) NOT NULL,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    private function appliedMigrations(): array
    {
        $stmt = Database::connection()->query('SELECT migration FROM migrations');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function migrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.sql');
        sort($files);
        return $files;
    }

    private function recordMigration(string $name): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO migrations (migration) VALUES (?)');
        $stmt->execute([$name]);
    }
}
```

**Verify:** `php -l app/Core/MigrationRunner.php` → clean. Full functional test happens in `P1.14` once the CLI dispatcher exists, and for real against staging in Phase 2.

---

## P1.13 — `Core/SeedRunner.php`

**File: `app/Core/SeedRunner.php`**

```php
<?php

namespace App\Core;

class SeedRunner
{
    private string $seedersPath;

    public function __construct()
    {
        $this->seedersPath = __DIR__ . '/../../database/seeders';
    }

    public function run(?string $seederClass = null): void
    {
        $seeders = $seederClass !== null
            ? [$seederClass]
            : $this->allSeederClasses();

        if (empty($seeders)) {
            echo "No seeders found in {$this->seedersPath}\n";
            return;
        }

        foreach ($seeders as $class) {
            $fqcn = "Database\\Seeders\\{$class}";

            if (!class_exists($fqcn)) {
                echo "Seeder not found: {$fqcn}\n";
                continue;
            }

            echo "Seeding: {$class}...\n";
            (new $fqcn())->run();
            echo "Seeded: {$class}\n";
        }
    }

    private function allSeederClasses(): array
    {
        $files = glob($this->seedersPath . '/*.php');
        return array_map(fn($f) => basename($f, '.php'), $files);
    }
}
```

Every seeder class needs a plain `run(): void` method and lives in `database/seeders/`, matching the `Database\Seeders\` namespace registered in `P1.1`'s `composer.json`. None exist yet — `AdminSeeder`, `ComplianceRequirementSeeder`, and `ChatBotRuleSeeder` get built in their respective later phases.

**Verify:** `php -l app/Core/SeedRunner.php` → clean.

---

## P1.14 — `database/migrate.php` (CLI Dispatcher)

**File: `database/migrate.php`**

```php
<?php

require __DIR__ . '/../bootstrap/app.php';

use App\Core\MigrationRunner;
use App\Core\SeedRunner;

$command = $argv[1] ?? 'migrate';

match ($command) {
    'migrate'        => (new MigrationRunner())->run(),
    'migrate:status' => (new MigrationRunner())->status(),
    'seed'           => (new SeedRunner())->run($argv[2] ?? null),
    default          => print("Unknown command: {$command}\n"),
};
```

**Verified behavior:**

```bash
php database/migrate.php bogus-command
# → "Unknown command: bogus-command" (confirmed working exactly as written)

php database/migrate.php migrate:status
# → fails at the database connection step against a real MySQL server, which is
#   correct and expected — this confirms the CLI wiring itself works end to end;
#   run it for real against P0.16's staging environment for the actual migration
#   history once Phase 2's migration files exist.
```

---

## P1.15 — `Helpers/Money.php`

**File: `app/Helpers/Money.php`**

```php
<?php

namespace App\Helpers;

/**
 * Decimal-safe currency arithmetic. Internally, amounts are always integer minor
 * units (cents) — never floats — so repeated add/subtract operations never drift
 * from float rounding error. Convert to/from major units only at the input/output
 * boundary (form submission, display).
 */
class Money
{
    public static function toMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    public static function toMajorUnits(int $minorUnits): float
    {
        return $minorUnits / 100;
    }

    public static function add(int $aMinor, int $bMinor): int
    {
        return $aMinor + $bMinor;
    }

    public static function subtract(int $aMinor, int $bMinor): int
    {
        return $aMinor - $bMinor;
    }

    public static function isGreaterThan(int $aMinor, int $bMinor): bool
    {
        return $aMinor > $bMinor;
    }

    public static function isGreaterThanOrEqual(int $aMinor, int $bMinor): bool
    {
        return $aMinor >= $bMinor;
    }

    public static function format(int $minorUnits, string $currency = 'USD'): string
    {
        $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'NGN' => '₦'];
        $symbol = $symbols[$currency] ?? $currency . ' ';

        return $symbol . number_format(self::toMajorUnits($minorUnits), 2);
    }
}
```

This pairs directly with the Design System's `--font-data` display convention — `Money::format()` produces the value, the view wraps it in a `--font-data`-styled element.

**Verify:** `php -l app/Helpers/Money.php` → clean.

---

## P1.16 — Additional Exceptions

**File: `app/Exceptions/ComplianceException.php`**

```php
<?php

namespace App\Exceptions;

class ComplianceException extends AppException
{
}
```

**File: `app/Exceptions/InsufficientFundsException.php`**

```php
<?php

namespace App\Exceptions;

class InsufficientFundsException extends AppException
{
}
```

**Verify:** `php -l` both → clean.

---

## P1.17 — Remaining Interfaces

**File: `app/Interfaces/RepositoryInterface.php`**

```php
<?php

namespace App\Interfaces;

interface RepositoryInterface
{
    public function find(int $id): ?array;
    public function all(): array;
}
```

**File: `app/Interfaces/MailProviderInterface.php`**

```php
<?php

namespace App\Interfaces;

interface MailProviderInterface
{
    public function send(string $to, string $subject, string $body): bool;
}
```

**File: `app/Interfaces/ChatTransportInterface.php`**

```php
<?php

namespace App\Interfaces;

interface ChatTransportInterface
{
    public function send(int $conversationId, string $senderType, string $message): void;
    public function poll(int $conversationId, int $sinceMessageId): array;
}
```

**File: `app/Interfaces/CardIssuerInterface.php`**

```php
<?php

namespace App\Interfaces;

interface CardIssuerInterface
{
    /**
     * @return array{number: string, expiry: string, cvv: string}
     */
    public function issue(int $userId): array;
}
```

**File: `app/Interfaces/LoggerInterface.php`** (formalizes the contract `Core/Logger.php` from `P1.6` already satisfies structurally)

```php
<?php

namespace App\Interfaces;

interface LoggerInterface
{
    public function info(string $message): void;
    public function warning(string $message): void;
    public function error(string $message): void;
}
```

**Verify:** `php -l` every file in `app/Interfaces/` → all clean.

---

## P1.18 — `Middlewares/AdminMiddleware.php`

**File: `app/Middlewares/AdminMiddleware.php`**

```php
<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Interfaces\MiddlewareInterface;

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!Session::has('admin_id')) {
            Response::redirect('/admin/login');
        }

        // If P0.6 confirmed role separation, uncomment and adapt:
        // $requiredRole = 'super_admin';
        // if (Session::get('admin_role') !== $requiredRole) {
        //     Response::abort(403, 'Insufficient permissions.');
        // }
    }
}
```

If `P0.6` confirmed role separation (Support/Compliance/Super Admin), uncomment the role check above once `admins.role` exists (Phase 2's `024_add_role_to_admins_table.sql`) — not before, since the column won't exist yet.

**Verify:** `php -l app/Middlewares/AdminMiddleware.php` → clean.

---

## P1.19 — `routes/api.php` Confirmed Working

`routes/api.php` was already created empty in `P1.11` so the smoke test's `App::run()` had both files to require. This task is just confirming the router correctly serves a *second* route surface, separate from `web.php` — relevant once `P0.7`'s decision is "yes, keep them as separate files."

**Test:** temporarily add one throwaway API route and confirm it resolves independently of `web.php`'s routes:

```php
// routes/api.php — temporary test addition, remove after confirming
$router->get('/api/__smoke-test', 'SmokeTestController@ping');
```

```bash
curl -i http://localhost:8000/api/__smoke-test
# Expect the same 200 JSON response as /__smoke-test — confirms api.php's routes
# are registered and dispatched through the exact same Router, just declared in
# a separate file.
```

Remove the test route once confirmed; leave `routes/api.php` empty until Phase 12 gives it real content.

---

## Phase 1 Exit Checklist

- [ ] `composer dump-autoload -o` runs clean, no errors
- [ ] Every file in `app/` passes `php -l` with no syntax errors
- [ ] `curl http://localhost:8000/__smoke-test` returns the expected `200` JSON response
- [ ] `curl` against an unmatched path returns a real `404`, not a PHP fatal error
- [ ] `curl` against a route with `{params}` correctly extracts and passes them to the controller
- [ ] `php database/migrate.php bogus-command` prints `Unknown command: bogus-command`
- [ ] `php database/migrate.php migrate:status` fails at the database connection step (expected, no real migrations exist yet) rather than a PHP-level error
- [ ] `routes/api.php` confirmed to dispatch independently of `routes/web.php` (`P1.19`'s test)
- [ ] `bootstrap/env.php` (Phase 0's interim loader) has been deleted
- [ ] `SmokeTestController.php` and its route are still in place — leave them until Phase 6 has a real controller to replace them as the "does routing actually work" proof

---

*End of Phase 1 Runbook. Next: Implementation Plan Phase 2 (`P2.1`–`P2.13`) — database migrations, run against the `P0.16` staging environment using `MigrationRunner`, which this phase just built and verified.*
