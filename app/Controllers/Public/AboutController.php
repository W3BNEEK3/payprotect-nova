<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class AboutController extends BaseController
{
    public function index(): void
    {
        $this->view('public/about', [
            'pageTitle' => 'About — NovaTrust',
            'metaDescription' => 'Why NovaTrust exists, and the principles behind how we build it.',
        ]);
    }
}
