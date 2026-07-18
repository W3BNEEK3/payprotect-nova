<?php

namespace App\Controllers;

class SmokeTestController extends BaseController
{
    public function ping(): void
    {
        $this->json([
            'status'  => 'ok',
            'message' => 'Phase 1 framework core is wired up correctly.',
            'time'    => date('c'),
        ]);
    }
}
