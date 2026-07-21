<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): void
    {
        echo "<h1>Dashboard Placeholder</h1>";
        echo "<p>Welcome to your account. This is a temporary placeholder until Phase 8 builds the real shell.</p>";
        echo '<form action="/logout" method="POST">';
        echo \App\Middlewares\CsrfMiddleware::field();
        echo '<button type="submit">Log Out</button>';
        echo '</form>';
    }
}
