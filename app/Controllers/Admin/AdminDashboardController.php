<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AdminDashboardController extends BaseController
{
    public function index(): void
    {
        $this->view('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard — NovaTrust',
        ]);
    }
}
