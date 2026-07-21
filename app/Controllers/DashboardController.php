<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->view('public/dashboard', [
            'pageTitle' => 'Dashboard — NovaTrust',
        ]);
    }
}
