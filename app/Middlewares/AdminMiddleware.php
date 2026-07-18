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
