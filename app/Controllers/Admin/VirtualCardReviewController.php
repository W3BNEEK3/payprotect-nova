<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Models\Notification;
use App\Models\VirtualCardRequest;
use App\Providers\Cards\SimulatedCardProvider;
use App\Repositories\VirtualCardRequestRepository;

class VirtualCardReviewController extends BaseController
{
    private VirtualCardRequestRepository $requestRepo;
    private SimulatedCardProvider $cardProvider;

    public function __construct()
    {
        $this->requestRepo = new VirtualCardRequestRepository();
        $this->cardProvider = new SimulatedCardProvider();
    }

    public function index()
    {
        $pendingRequests = $this->requestRepo->findAllPending();

        return $this->view('admin/virtual-cards/index', [
            'pageTitle' => 'Review Virtual Cards',
            'pendingRequests' => $pendingRequests
        ], 'admin');
    }

    public function approve(int $id)
    {
        $request = VirtualCardRequest::find($id);
        if (!$request || $request['status'] !== 'pending') {
            Session::flash('error', 'Request not found or already processed.');
            return $this->redirect('/admin/virtual-cards');
        }

        // Issue card, passing true to auto-approve it, and copy over the requested style
        $this->cardProvider->issue($request['user_id'], true, $request['card_style'] ?? 'visa_geo');

        VirtualCardRequest::update($id, [
            'status' => 'approved',
            'reviewed_by' => Session::get('admin_id'),
            'reviewed_at' => date('Y-m-d H:i:s')
        ]);

        Notification::create([
            'user_id' => $request['user_id'],
            'title' => 'Virtual Card Approved',
            'message' => 'Your virtual card has been approved and is ready to use.',
            'type' => 'card_approved'
        ]);
        
        \App\Services\AuditLogger::log('approve_virtual_card', 'virtual_card_requests', $id, [
            'user_id' => $request['user_id']
        ]);

        Session::flash('success', 'Virtual card request approved.');
        return $this->redirect('/admin/virtual-cards');
    }

    public function reject(int $id)
    {
        $request = VirtualCardRequest::find($id);
        if (!$request || $request['status'] !== 'pending') {
            Session::flash('error', 'Request not found or already processed.');
            return $this->redirect('/admin/virtual-cards');
        }

        $reason = trim($_POST['reason'] ?? '');
        if (empty($reason)) {
            Session::flash('error', 'Rejection reason is required.');
            return $this->redirect('/admin/virtual-cards');
        }

        VirtualCardRequest::update($id, [
            'status' => 'rejected',
            'reason' => $reason,
            'reviewed_by' => Session::get('admin_id'),
            'reviewed_at' => date('Y-m-d H:i:s')
        ]);

        Notification::create([
            'user_id' => $request['user_id'],
            'title' => 'Virtual Card Request Rejected',
            'message' => 'Your request for a virtual card was rejected: ' . $reason,
            'type' => 'card_rejected'
        ]);
        
        \App\Services\AuditLogger::log('reject_virtual_card', 'virtual_card_requests', $id, [
            'user_id' => $request['user_id'],
            'reason' => $reason
        ]);

        Session::flash('success', 'Virtual card request rejected.');
        return $this->redirect('/admin/virtual-cards');
    }
}
