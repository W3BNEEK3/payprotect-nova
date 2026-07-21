<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Repositories\UserRepository;
use App\Repositories\VirtualCardRepository;
use App\Repositories\ComplianceFlagRepository;
use App\Repositories\WithdrawalRequestRepository;
use App\Models\Transaction;
use App\Core\Database;

class WithdrawalController extends BaseController
{
    private UserRepository $userRepo;
    private VirtualCardRepository $cardRepo;
    private ComplianceFlagRepository $flagRepo;
    private WithdrawalRequestRepository $withdrawalRepo;

    // Supported methods
    private const METHODS = [
        'bank' => 'Bank Transfer',
        'crypto' => 'Cryptocurrency',
        'paypal' => 'PayPal',
        'wise' => 'Wise',
        'skrill' => 'Skrill',
        'western_union' => 'Western Union',
        'google_pay' => 'Google Pay',
        'payoneer' => 'Payoneer'
    ];

    public function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->cardRepo = new VirtualCardRepository();
        $this->flagRepo = new ComplianceFlagRepository();
        $this->withdrawalRepo = new WithdrawalRequestRepository();
    }

    /**
     * BR-9: Gate checks before showing withdrawal method selection.
     */
    private function checkGates(int $userId): bool
    {
        // 1. Virtual Card
        if (!$this->cardRepo->findApprovedForUser($userId)) {
            Session::flash('error', 'You must have an approved virtual card before withdrawing.');
            $this->redirect('/virtual-card');
            return false;
        }

        // 2. KYC / Upgrade
        $settingsRepo = new \App\Repositories\ComplianceSettingsRepository();
        $requireKyc = $settingsRepo->get('require_kyc_for_withdrawal', '0') === '1';
        
        if ($requireKyc && !$this->userRepo->isFullyVerified($userId)) {
            Session::flash('error', 'Your account must be fully verified (KYC & Upgrades) to withdraw.');
            $this->redirect('/dashboard');
            return false;
        }

        // 3. Compliance Flags
        if ($this->flagRepo->hasOpenFlag($userId)) {
            $this->redirect('/compliance');
            return false;
        }

        return true;
    }

    /**
     * Step 1: Method Selection
     */
    public function index()
    {
        $userId = Session::get('user_id');
        if (!$this->checkGates($userId)) {
            return;
        }

        return $this->view('user/withdraw/index', [
            'pageTitle' => 'Withdraw Funds',
            'methods' => self::METHODS
        ]);
    }

    /**
     * Step 2: Form input for specific method
     */
    public function methodForm(string $method)
    {
        $userId = Session::get('user_id');
        if (!$this->checkGates($userId)) {
            return;
        }

        if (!array_key_exists($method, self::METHODS)) {
            Session::flash('error', 'Invalid withdrawal method selected.');
            return $this->redirect('/withdraw');
        }

        $user = $this->userRepo->find($userId);

        return $this->view('user/withdraw/form', [
            'pageTitle' => 'Withdraw via ' . self::METHODS[$method],
            'method' => $method,
            'methodName' => self::METHODS[$method],
            'balance' => $user['balance'] ?? 0,
            'currency' => $user['currency'] ?? 'USD'
        ]);
    }

    /**
     * Step 3: Review & Confirm
     */
    public function review(string $method)
    {
        $userId = Session::get('user_id');
        if (!$this->checkGates($userId)) {
            return;
        }

        if (!array_key_exists($method, self::METHODS)) {
            Session::flash('error', 'Invalid withdrawal method selected.');
            return $this->redirect('/withdraw');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect("/withdraw/{$method}");
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $user = $this->userRepo->find($userId);

        if ($amount < 100) {
            Session::flash('error', 'Minimum withdrawal amount is 100.');
            return $this->redirect("/withdraw/{$method}");
        }

        if ($amount > (float)$user['balance']) {
            Session::flash('error', 'Insufficient balance for this withdrawal.');
            return $this->redirect("/withdraw/{$method}");
        }

        // Capture details (excluding amount & csrf)
        $details = $_POST;
        unset($details['amount'], $details['csrf_token']);

        // Save to session for submit
        Session::put('withdraw_data', [
            'method' => $method,
            'amount' => $amount,
            'details' => $details
        ]);

        return $this->view('user/withdraw/review', [
            'pageTitle' => 'Review Withdrawal',
            'method' => $method,
            'methodName' => self::METHODS[$method],
            'amount' => $amount,
            'currency' => $user['currency'] ?? 'USD',
            'details' => $details
        ]);
    }

    /**
     * Step 4: Final Submission
     */
    public function submit()
    {
        $userId = Session::get('user_id');
        if (!$this->checkGates($userId)) {
            return;
        }

        $data = Session::get('withdraw_data');
        if (!$data || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/withdraw');
        }

        $user = $this->userRepo->find($userId);
        $amount = (float)$data['amount'];

        if ($amount > (float)$user['balance']) {
            Session::flash('error', 'Insufficient balance. Your balance may have changed.');
            Session::remove('withdraw_data');
            return $this->redirect('/withdraw');
        }

        try {
            Database::connection()->beginTransaction();

            // 1. Deduct balance
            $this->userRepo->deductBalance($userId, $amount);

            // 2. Create Request
            $userCurrency = $user['currency'] ?? 'USD';
            $stmt = Database::connection()->prepare(
                'INSERT INTO withdrawal_requests (user_id, method, amount, currency, destination_details, status) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $data['method'],
                $amount,
                $userCurrency,
                json_encode($data['details']),
                'pending_review'
            ]);
            $requestId = (int)Database::connection()->lastInsertId();

            // 3. Log transaction
            $logStmt = Database::connection()->prepare(
                'INSERT INTO transactions (user_id, amount, currency, type, status, method, withdrawal_request_id) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $logStmt->execute([
                $userId,
                $amount,
                $userCurrency,
                'withdrawal',
                'pending',
                $data['method'],
                $requestId
            ]);

            // Notify User
            \App\Models\Notification::create([
                'user_id' => $userId,
                'type' => 'withdrawal_pending',
                'message' => "Your withdrawal request for {$userCurrency} {$amount} via " . self::METHODS[$data['method']] . " has been submitted and is pending review."
            ]);

            Database::connection()->commit();
            
            Session::remove('withdraw_data');
            Session::flash('success', 'Withdrawal request submitted successfully.');
            
            return $this->redirect("/withdraw/success/{$requestId}");

        } catch (\Exception $e) {
            Database::connection()->rollBack();
            Session::flash('error', 'An error occurred while processing your request. Please try again.');
            return $this->redirect('/withdraw');
        }
    }

    /**
     * Step 5: Success Page
     */
    public function success(string $id)
    {
        $userId = Session::get('user_id');
        $request = $this->withdrawalRepo->find((int)$id);

        if (!$request || (int)$request['user_id'] !== $userId) {
            return $this->redirect('/dashboard');
        }

        return $this->view('user/withdraw/success', [
            'pageTitle' => 'Withdrawal Submitted',
            'request' => $request,
            'methodName' => self::METHODS[$request['method']]
        ]);
    }
}
