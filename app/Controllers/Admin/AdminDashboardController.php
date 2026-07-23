<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Repositories\UserRepository;
use App\Repositories\WithdrawalRequestRepository;
use App\Repositories\VirtualCardRequestRepository;
use App\Repositories\ComplianceFlagRepository;

class AdminDashboardController extends BaseController
{
    public function index(): void
    {
        $userRepo = new UserRepository();
        $withdrawalRepo = new WithdrawalRequestRepository();
        $cardReqRepo = new VirtualCardRequestRepository();
        $complianceRepo = new ComplianceFlagRepository();

        $this->view('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard — NovaTrust',
            'stats' => [
                'totalUsers' => count($userRepo->all()),
                'pendingWithdrawals' => count($withdrawalRepo->findPendingReview()),
                'pendingCards' => count($cardReqRepo->findAllPending()),
                'openFlags' => count($complianceRepo->findAllOpen())
            ]
        ]);
    }
}
