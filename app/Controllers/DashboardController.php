<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Repositories\UserRepository;
use App\Repositories\VirtualCardRepository;
use App\Repositories\TransactionRepository;
use App\Services\ComplianceEngine;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $userId = Session::get('user_id');
        
        $userRepo = new UserRepository();
        $user = $userRepo->find($userId);

        $cardRepo = new VirtualCardRepository();
        $card = $cardRepo->findApprovedForUser($userId);

        $txnRepo = new TransactionRepository();
        // Fetch top 5 recent transactions
        $transactions = array_slice($txnRepo->findByUserId($userId), 0, 5);

        $complianceEngine = new ComplianceEngine();
        $hasOpenFlag = $complianceEngine->hasOpenFlag($userId);
        $pendingCode = $complianceEngine->getOldestPendingCode($userId);

        $monthlyIncome = $txnRepo->getMonthlyIncome($userId);
        $incomeTrend = $txnRepo->getMonthlyIncomeTrend($userId);
        $spendBreakdown = $txnRepo->getSpendBreakdown($userId);

        $this->view('public/dashboard', [
            'pageTitle' => 'Dashboard',
            'user' => $user,
            'card' => $card,
            'transactions' => $transactions,
            'hasOpenFlag' => $hasOpenFlag,
            'pendingCode' => $pendingCode,
            'monthlyIncome' => $monthlyIncome,
            'incomeTrend' => $incomeTrend,
            'spendData' => $spendBreakdown
        ]);
    }
}
