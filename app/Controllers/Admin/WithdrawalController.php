<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Core\Database;
use App\Repositories\WithdrawalRequestRepository;
use App\Repositories\UserRepository;

class WithdrawalController extends BaseController
{
    private WithdrawalRequestRepository $withdrawalRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->withdrawalRepo = new WithdrawalRequestRepository();
        $this->userRepo = new UserRepository();
    }

    public function index()
    {
        $page = (int)($_GET['page'] ?? 1);
        $status = $_GET['status'] ?? null;
        if ($status === '') $status = null;

        $requests = $this->withdrawalRepo->paginate($page, 20, $status);

        return $this->view('admin/withdrawals/index', [
            'pageTitle' => 'Withdrawal Queue',
            'requests' => $requests,
            'page' => $page,
            'statusFilter' => $status
        ]);
    }

    public function show(string $id)
    {
        $request = $this->withdrawalRepo->find((int)$id);
        if (!$request) {
            Session::flash('error', 'Request not found.');
            return $this->redirect('/admin/withdrawals');
        }

        $user = $this->userRepo->find((int)$request['user_id']);

        return $this->view('admin/withdrawals/show', [
            'pageTitle' => 'Withdrawal Request #' . $request['id'],
            'request' => $request,
            'user' => $user
        ]);
    }

    public function approve(string $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/withdrawals/' . $id);
        }

        $request = $this->withdrawalRepo->find((int)$id);
        if (!$request || $request['status'] !== 'pending_review') {
            Session::flash('error', 'Invalid request or already processed.');
            return $this->redirect('/admin/withdrawals/' . $id);
        }

        try {
            Database::connection()->beginTransaction();

            // 1. Update request status
            $stmt = Database::connection()->prepare(
                "UPDATE withdrawal_requests SET status = 'completed', completed_at = NOW(), reviewed_by = ?, reviewed_at = NOW() WHERE id = ?"
            );
            $stmt->execute([Session::get('admin_id'), $id]);

            // 2. Update transaction status
            $txnStmt = Database::connection()->prepare(
                "UPDATE transactions SET status = 'successful' WHERE withdrawal_request_id = ?"
            );
            $txnStmt->execute([$id]);

            // 3. Notify user
            \App\Models\Notification::create([
                'user_id' => $request['user_id'],
                'type' => 'withdrawal_approved',
                'message' => "Your withdrawal request for {$request['currency']} {$request['amount']} has been approved and completed."
            ]);
            
            \App\Services\AuditLogger::log('approve_withdrawal', 'withdrawal_requests', (int)$id, [
                'amount' => $request['amount'],
                'currency' => $request['currency']
            ]);

            Database::connection()->commit();
            Session::flash('success', 'Withdrawal request marked as completed.');
        } catch (\Exception $e) {
            Database::connection()->rollBack();
            Session::flash('error', 'Failed to approve request.');
        }

        return $this->redirect('/admin/withdrawals/' . $id);
    }

    public function reject(string $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/withdrawals/' . $id);
        }

        $request = $this->withdrawalRepo->find((int)$id);
        if (!$request || $request['status'] !== 'pending_review') {
            Session::flash('error', 'Invalid request or already processed.');
            return $this->redirect('/admin/withdrawals/' . $id);
        }

        $reason = trim($_POST['rejection_reason'] ?? '');
        if (empty($reason)) {
            Session::flash('error', 'You must provide a reason for rejection.');
            return $this->redirect('/admin/withdrawals/' . $id);
        }

        try {
            Database::connection()->beginTransaction();

            // 1. Update request
            $stmt = Database::connection()->prepare(
                "UPDATE withdrawal_requests SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$reason, Session::get('admin_id'), $id]);

            // 2. Update transaction status
            $txnStmt = Database::connection()->prepare(
                "UPDATE transactions SET status = 'failed', message = ? WHERE withdrawal_request_id = ?"
            );
            $txnStmt->execute([$reason, $id]);

            // 3. Refund the balance
            $this->userRepo->refundBalance((int)$request['user_id'], (float)$request['amount']);

            // 4. Notify user
            \App\Models\Notification::create([
                'user_id' => $request['user_id'],
                'type' => 'withdrawal_rejected',
                'message' => "Your withdrawal request for {$request['currency']} {$request['amount']} was rejected. Reason: {$reason}. The funds have been returned to your balance."
            ]);
            
            \App\Services\AuditLogger::log('reject_withdrawal', 'withdrawal_requests', (int)$id, [
                'amount' => $request['amount'],
                'currency' => $request['currency'],
                'reason' => $reason
            ]);

            Database::connection()->commit();
            Session::flash('success', 'Withdrawal request rejected and funds refunded.');
        } catch (\Exception $e) {
            Database::connection()->rollBack();
            Session::flash('error', 'Failed to reject request.');
        }

        return $this->redirect('/admin/withdrawals/' . $id);
    }
}
