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
