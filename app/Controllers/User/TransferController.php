<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Core\Database;
use App\Repositories\UserRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\VirtualCardRepository;
use App\Repositories\ComplianceFlagRepository;

class TransferController extends BaseController
{
    private UserRepository $userRepo;
    private TransactionRepository $transactionRepo;
    private VirtualCardRepository $cardRepo;
    private ComplianceFlagRepository $flagRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->transactionRepo = new TransactionRepository();
        $this->cardRepo = new VirtualCardRepository();
        $this->flagRepo = new ComplianceFlagRepository();
    }

    private function checkGates(int $userId, bool $checkCompliance = true): bool
    {
        // 1. KYC / Upgrade
        $settingsRepo = new \App\Repositories\ComplianceSettingsRepository();
        $requireKyc = $settingsRepo->get('require_kyc_for_withdrawal', '0') === '1';
        
        if ($requireKyc && !$this->userRepo->isFullyVerified($userId)) {
            Session::flash('error', 'Your account must be fully verified (KYC & Upgrades) to transfer funds.');
            $this->redirect('/dashboard');
            return false;
        }

        // 2. Virtual Card
        if (!$this->cardRepo->findApprovedForUser($userId)) {
            Session::flash('error', 'You must apply for and receive a virtual card before transferring funds.');
            $this->redirect('/virtual-card');
            return false;
        }

        // 3. Compliance Flags
        if ($checkCompliance && $this->flagRepo->hasOpenFlag($userId)) {
            $this->redirect('/compliance');
            return false;
        }

        return true;
    }

    public function index()
    {
        $userId = Session::get('user_id');
        if (!$this->checkGates($userId, false)) return;

        $this->view('user/transfers/index', [
            'title' => 'Send Money / Transfer'
        ]);
    }

    public function internal()
    {
        $userId = Session::get('user_id');
        if (!$this->checkGates($userId, false)) return;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->checkGates($userId, true)) return;
            $this->handleInternalTransfer();
            return;
        }

        $this->view('user/transfers/internal', [
            'title' => 'Internal Transfer'
        ]);
    }

    public function bank()
    {
        $userId = Session::get('user_id');
        if (!$this->checkGates($userId, false)) return;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->checkGates($userId, true)) return;
            $this->handleBankTransfer();
            return;
        }

        $this->view('user/transfers/bank', [
            'title' => 'Local Bank Transfer'
        ]);
    }

    public function international()
    {
        $userId = Session::get('user_id');
        if (!$this->checkGates($userId, false)) return;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->checkGates($userId, true)) return;
            $this->handleInternationalTransfer();
            return;
        }

        $this->view('user/transfers/international', [
            'title' => 'International Transfer'
        ]);
    }

    private function handleInternalTransfer()
    {
        $user = Session::get('user');
        $freshUser = $this->userRepo->find($user->id);
        
        $accountNumber = trim($_POST['account_number'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $message = trim($_POST['message'] ?? 'Internal Transfer');

        if (empty($accountNumber) || $amount <= 0) {
            Session::flash('error', 'Invalid account number or amount.');
            $this->redirect('/transfer/internal');
            return;
        }

        if ($freshUser['balance'] < $amount) {
            Session::flash('error', 'Insufficient balance.');
            $this->redirect('/transfer/internal');
            return;
        }

        if ($accountNumber === $freshUser['account_number']) {
            Session::flash('error', 'You cannot transfer money to yourself.');
            $this->redirect('/transfer/internal');
            return;
        }

        $recipient = $this->userRepo->findByAccountNumber($accountNumber);

        if (!$recipient) {
            Session::flash('error', 'Recipient account not found.');
            $this->redirect('/transfer/internal');
            return;
        }

        // Process transfer
        $this->userRepo->deductBalance($user->id, $amount);
        $this->userRepo->refundBalance($recipient['id'], $amount); // Credits the recipient

        // Sender Transaction
        $this->transactionRepo->create([
            'user_id' => $user->id,
            'type' => 'Transfer',
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'Completed',
            'method' => 'Internal Transfer',
            'message' => "Transfer to {$recipient['fullname']} ({$accountNumber})"
        ]);

        // Recipient Transaction
        $this->transactionRepo->create([
            'user_id' => $recipient['id'],
            'type' => 'Credit',
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'Completed',
            'method' => 'Internal Transfer',
            'message' => "Transfer from {$freshUser['fullname']} ({$freshUser['account_number']})"
        ]);

        Session::flash('success', "Successfully transferred $" . number_format($amount, 2) . " to {$recipient['fullname']}.");
        $this->redirect('/transactions');
    }

    private function handleBankTransfer()
    {
        $user = Session::get('user');
        $freshUser = $this->userRepo->find($user->id);
        
        $bankName = trim($_POST['bank_name'] ?? '');
        $routingNumber = trim($_POST['routing_number'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $message = trim($_POST['message'] ?? 'Local Bank Transfer');

        if (empty($bankName) || empty($routingNumber) || empty($accountNumber) || $amount <= 0) {
            Session::flash('error', 'Please fill in all bank details correctly.');
            $this->redirect('/transfer/bank');
            return;
        }

        if ($freshUser['balance'] < $amount) {
            Session::flash('error', 'Insufficient balance.');
            $this->redirect('/transfer/bank');
            return;
        }

        try {
            Database::connection()->beginTransaction();

            $this->userRepo->deductBalance($user->id, $amount);

            $stmt = Database::connection()->prepare(
                'INSERT INTO withdrawal_requests (user_id, method, amount, currency, destination_details, status) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $user->id,
                'bank', // Use 'bank' so it matches Withdrawal flow methods
                $amount,
                'USD',
                json_encode(['bank_name' => $bankName, 'routing_number' => $routingNumber, 'account_number' => $accountNumber, 'message' => $message]),
                'pending_review'
            ]);
            $requestId = (int)Database::connection()->lastInsertId();

            $logStmt = Database::connection()->prepare(
                'INSERT INTO transactions (user_id, amount, currency, type, status, method, withdrawal_request_id, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $logStmt->execute([
                $user->id,
                $amount,
                'USD',
                'Withdrawal',
                'Pending',
                'Local Bank Transfer',
                $requestId,
                "Withdrawal to {$bankName} (AC: {$accountNumber})"
            ]);

            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_pending',
                'message' => "Your local bank transfer of USD {$amount} has been submitted and is pending review."
            ]);

            Database::connection()->commit();
            
            Session::flash('success', "Your local bank transfer of $" . number_format($amount, 2) . " has been initiated and is pending approval.");
            $this->redirect('/transactions');
        } catch (\Exception $e) {
            Database::connection()->rollBack();
            Session::flash('error', 'An error occurred. Please try again.');
            $this->redirect('/transfer/bank');
        }
    }

    private function handleInternationalTransfer()
    {
        $user = Session::get('user');
        $freshUser = $this->userRepo->find($user->id);
        
        $bankName = trim($_POST['bank_name'] ?? '');
        $swiftIban = trim($_POST['swift_iban'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $message = trim($_POST['message'] ?? 'International Transfer');

        if (empty($bankName) || empty($swiftIban) || empty($country) || $amount <= 0) {
            Session::flash('error', 'Please fill in all international transfer details correctly.');
            $this->redirect('/transfer/international');
            return;
        }

        if ($freshUser['balance'] < $amount) {
            Session::flash('error', 'Insufficient balance.');
            $this->redirect('/transfer/international');
            return;
        }

        try {
            Database::connection()->beginTransaction();

            $this->userRepo->deductBalance($user->id, $amount);

            $stmt = Database::connection()->prepare(
                'INSERT INTO withdrawal_requests (user_id, method, amount, currency, destination_details, status) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $user->id,
                'bank', // Defaulting to bank since international is just a foreign bank transfer
                $amount,
                'USD',
                json_encode(['bank_name' => $bankName, 'swift_iban' => $swiftIban, 'country' => $country, 'message' => $message]),
                'pending_review'
            ]);
            $requestId = (int)Database::connection()->lastInsertId();

            $logStmt = Database::connection()->prepare(
                'INSERT INTO transactions (user_id, amount, currency, type, status, method, withdrawal_request_id, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $logStmt->execute([
                $user->id,
                $amount,
                'USD',
                'Withdrawal',
                'Pending',
                'International Transfer (SWIFT)',
                $requestId,
                "Transfer to {$bankName} in {$country}"
            ]);

            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_pending',
                'message' => "Your international transfer of USD {$amount} has been submitted and is pending review."
            ]);

            Database::connection()->commit();
            
            Session::flash('success', "Your international transfer of $" . number_format($amount, 2) . " has been initiated and is pending approval.");
            $this->redirect('/transactions');
        } catch (\Exception $e) {
            Database::connection()->rollBack();
            Session::flash('error', 'An error occurred. Please try again.');
            $this->redirect('/transfer/international');
        }
    }
}
