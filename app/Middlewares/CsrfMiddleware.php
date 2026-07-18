<?php
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
