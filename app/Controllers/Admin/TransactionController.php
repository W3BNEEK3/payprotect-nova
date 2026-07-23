<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Models\User;

class TransactionController extends BaseController
{
    public function index(): void
    {
        $type = $_GET['type'] ?? '';
        $userFilter = $_GET['user'] ?? '';
        $status = $_GET['status'] ?? '';
        $time = $_GET['time'] ?? '';

        $query = "SELECT t.*, u.fullname, u.email, u.account_number 
                  FROM transactions t 
                  LEFT JOIN users u ON t.user_id = u.id 
                  WHERE 1=1";
        $params = [];

        if (!empty($type)) {
            $query .= " AND t.type = ?";
            $params[] = $type;
        }

        if (!empty($userFilter)) {
            $query .= " AND (u.email = ? OR u.account_number = ? OR u.id = ?)";
            $params[] = $userFilter;
            $params[] = $userFilter;
            $params[] = (int)$userFilter;
        }

        if (!empty($status)) {
            $query .= " AND t.status = ?";
            $params[] = $status;
        }

        if (!empty($time)) {
            if ($time === 'today') {
                $query .= " AND DATE(t.created_at) = CURDATE()";
            } elseif ($time === 'week') {
                $query .= " AND t.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            } elseif ($time === 'month') {
                $query .= " AND t.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            }
        }

        $query .= " ORDER BY t.created_at DESC";

        $stmt = Database::connection()->prepare($query);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();

        $stmtUsers = Database::connection()->query("SELECT id, fullname, email, account_number FROM users ORDER BY fullname ASC");
        $usersList = $stmtUsers->fetchAll();

        $this->view('admin/transactions/index', [
            'pageTitle' => 'All Transactions',
            'transactions' => $transactions,
            'filters' => [
                'type' => $type,
                'user' => $userFilter,
                'status' => $status,
                'time' => $time
            ],
            'usersList' => $usersList
        ]);
    }

    public function store(): void
    {
        $request = new \App\Core\Request();
        $source = $request->input('source', 'index');
        
        $userId = (int)$request->input('user_id');
        $type = $request->input('type', 'Deposit');
        $amount = (float)$request->input('amount', 0);
        $method = trim((string)$request->input('method', ''));
        $message = trim((string)$request->input('message', ''));
        $status = $request->input('status', 'Completed');
        $createdAt = $request->input('created_at');
        $updateBalance = (bool)$request->input('update_balance', 0);
        $party = trim((string)$request->input('party', ''));

        if (!empty($party)) {
            $prefix = ($type === 'Deposit') ? 'From: ' : 'To: ';
            $message = $prefix . $party . ($message ? ' | ' . $message : '');
        }

        $redirectUrl = $source === 'user_profile' ? '/admin/users/' . $userId : '/admin/transactions';

        if ($userId <= 0 || $amount <= 0 || empty($method)) {
            \App\Core\Session::flash('error', 'Please provide a valid user, amount, and method.');
            $this->redirect($redirectUrl);
            return;
        }

        $userRepo = new \App\Repositories\UserRepository();
        $user = $userRepo->find($userId);

        if (!$user) {
            \App\Core\Session::flash('error', 'User not found.');
            $this->redirect($redirectUrl);
            return;
        }

        // Default date if none provided
        if (empty($createdAt)) {
            $createdAt = date('Y-m-d H:i:s');
        } else {
            // Ensure format is compatible with datetime if passed from datetime-local
            $createdAt = date('Y-m-d H:i:s', strtotime($createdAt));
        }

        $reference = 'TXN-' . strtoupper(uniqid());

        $stmt = Database::connection()->prepare(
            "INSERT INTO transactions (user_id, amount, currency, type, message, status, method, reference, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $userId, 
            $amount, 
            $user['currency'] ?? 'USD', 
            $type, 
            $message, 
            $status, 
            $method, 
            $reference, 
            $createdAt
        ]);

        $currency = $user['currency'] ?? 'USD';
        $amountFormatted = $currency . ' ' . number_format($amount, 2);

        // Update Balance
        if ($updateBalance) {
            if ($type === 'Deposit' || $type === 'Credit') {
                $userRepo->refundBalance($userId, $amount);
            } elseif ($type === 'Withdrawal' || $type === 'Transfer' || $type === 'Debit') {
                $userRepo->deductBalance($userId, $amount);
            }
        }

        // Send Notification
        $notifMessage = $type === 'Withdrawal' || $type === 'Debit' || $type === 'Transfer' 
            ? "Your account was debited $amountFormatted via $method." 
            : "Your account was credited $amountFormatted via $method.";

        \App\Models\Notification::create([
            'user_id' => $userId,
            'type'    => 'transaction_created',
            'message' => $notifMessage,
        ]);

        // Send Email
        try {
            $mailFactory = new \App\Providers\Mail\MailProviderFactory(new \App\Repositories\MailSettingsRepository());
            $mailer = $mailFactory->make();
            
            $fullname = $user['fullname'] ?? 'User';
            $date = date('F j, Y, g:i a', strtotime($createdAt));

            ob_start();
            if ($type === 'Withdrawal' || $type === 'Debit' || $type === 'Transfer') {
                $subject = "Account Debited - $amountFormatted";
                require __DIR__ . '/../../../resources/emails/account_debit.php';
            } else {
                $subject = "Account Credited - $amountFormatted";
                require __DIR__ . '/../../../resources/emails/account_credit.php';
            }
            $body = ob_get_clean();

            $mailer->send($user['email'], $subject, $body);
        } catch (\Exception $e) {
            error_log("Failed to send transaction email: " . $e->getMessage());
        }

        \App\Services\AuditLogger::log('create_transaction', 'transactions', $userId, [
            'type' => $type,
            'amount' => $amount,
            'update_balance' => $updateBalance
        ]);

        \App\Core\Session::flash('success', 'Transaction created successfully.');
        $this->redirect($redirectUrl);
    }
    public function edit(string $id)
    {
        $transactionRepo = new \App\Repositories\TransactionRepository();
        $transaction = $transactionRepo->find((int)$id);

        if (!$transaction) {
            \App\Core\Session::flash('error', 'Transaction not found.');
            $this->redirect('/admin/transactions');
            return;
        }

        $userRepo = new \App\Repositories\UserRepository();
        $user = $userRepo->find($transaction['user_id']);

        $this->view('admin/transactions/edit', [
            'title' => 'Edit Transaction',
            'transaction' => $transaction,
            'user' => $user
        ]);
    }

    public function update(string $id)
    {
        $transactionRepo = new \App\Repositories\TransactionRepository();
        $transaction = $transactionRepo->find((int)$id);

        if (!$transaction) {
            \App\Core\Session::flash('error', 'Transaction not found.');
            $this->redirect('/admin/transactions');
            return;
        }

        $amount = (float)$_POST['amount'];
        $type = $_POST['type'];
        $status = $_POST['status'];
        $method = $_POST['method'];
        $createdAt = $_POST['created_at'];
        $message = trim($_POST['message'] ?? '');
        $updateBalance = isset($_POST['update_balance']) && $_POST['update_balance'] === '1';

        // Update transaction
        $transactionRepo->update((int)$id, [
            'amount' => $amount,
            'type' => $type,
            'status' => $status,
            'method' => $method,
            'created_at' => $createdAt,
            'message' => $message
        ]);

        // If the admin checked "Update User Balance"
        if ($updateBalance) {
            $userRepo = new \App\Repositories\UserRepository();
            
            // Revert original transaction effect on balance
            $originalAmount = (float)$transaction['amount'];
            $originalIsCredit = in_array(strtolower($transaction['type']), ['credit', 'deposit', 'refund']);
            
            if ($originalIsCredit) {
                // Was credited, so deduct it
                $userRepo->deductBalance($transaction['user_id'], $originalAmount);
            } else {
                // Was debited, so refund it
                $userRepo->refundBalance($transaction['user_id'], $originalAmount);
            }

            // Apply new transaction effect on balance
            $newIsCredit = in_array(strtolower($type), ['credit', 'deposit', 'refund']);
            if ($newIsCredit) {
                $userRepo->refundBalance($transaction['user_id'], $amount); // credits the account
            } else {
                $userRepo->deductBalance($transaction['user_id'], $amount); // debits the account
            }
        }

        \App\Core\Session::flash('success', 'Transaction updated successfully.');
        $this->redirect('/admin/transactions');
    }
}
