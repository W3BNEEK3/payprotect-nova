<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, mixed $handler, array $middleware): void
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

    private function callHandler(mixed $handler, array $params): void
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
