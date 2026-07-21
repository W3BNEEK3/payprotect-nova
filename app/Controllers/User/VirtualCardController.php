<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Models\VirtualCardRequest;
use App\Repositories\VirtualCardRepository;
use App\Repositories\VirtualCardRequestRepository;

class VirtualCardController extends BaseController
{
    private VirtualCardRepository $cardRepo;
    private VirtualCardRequestRepository $requestRepo;

    public function __construct()
    {
        $this->cardRepo = new VirtualCardRepository();
        $this->requestRepo = new VirtualCardRequestRepository();
    }

    public function index()
    {
        $userId = Session::get('user_id');

        $approvedCard = $this->cardRepo->findApprovedForUser($userId);
        $pendingRequest = null;
        $decryptedCard = null;

        if ($approvedCard) {
            $decryptedCard = [
                'number'  => \App\Helpers\Crypto::decrypt($approvedCard['card_number_encrypted']),
                'cvv'     => \App\Helpers\Crypto::decrypt($approvedCard['cvv_encrypted']),
                'expiry'  => $approvedCard['expiry_date'],
                'balance' => $approvedCard['balance'] ?? '0.00',
            ];
        } else {
            $pendingRequest = $this->requestRepo->findPendingForUser($userId);
        }

        return $this->view('user/virtual-card/index', [
            'pageTitle'     => 'Virtual Card',
            'approvedCard'  => $approvedCard,
            'decryptedCard' => $decryptedCard,
            'pendingRequest' => $pendingRequest,
        ]);
    }

    public function request()
    {
        $userId = Session::get('user_id');

        // Guard: no approved card or pending request may already exist
        $approvedCard   = $this->cardRepo->findApprovedForUser($userId);
        $pendingRequest = $this->requestRepo->findPendingForUser($userId);

        if ($approvedCard || $pendingRequest) {
            Session::flash('error', 'You already have an active card or a pending request.');
            return $this->redirect('/virtual-card');
        }

        VirtualCardRequest::create([
            'user_id' => $userId,
            'status'  => 'pending',
        ]);

        Session::flash('success', 'Your virtual card request has been submitted and is pending review.');
        return $this->redirect('/virtual-card');
    }
}
