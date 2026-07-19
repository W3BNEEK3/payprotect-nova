<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index(): void
    {
        $this->view('public/home', [
            'pageTitle' => 'NovaTrust — Banking That Behaves',
            'metaDescription' => 'A digital bank account built for people who move money seriously — virtual cards, fast withdrawals, and compliance that never surprises you.',
        ]);
    }
}
