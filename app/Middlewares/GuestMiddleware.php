<?php
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
