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
        $userRepo = new \App\Repositories\UserRepository();
        $user = $userRepo->find($userId);

        $approvedCard = $this->cardRepo->findApprovedForUser($userId);
        $pendingRequest = null;
        $decryptedCard = null;

        if ($approvedCard) {
            $decryptedCard = [
                'number'     => !empty($approvedCard['card_number_encrypted']) ? \App\Helpers\Crypto::decrypt($approvedCard['card_number_encrypted']) : '**** **** **** ****',
                'cvv'        => !empty($approvedCard['cvv_encrypted']) ? \App\Helpers\Crypto::decrypt($approvedCard['cvv_encrypted']) : '***',
                'expiry'     => $approvedCard['expiry_date'],
                'balance'    => $approvedCard['balance'] ?? '0.00',
                'card_style' => $approvedCard['card_style'] ?? 'visa_geo',
            ];
        } else {
            $pendingRequest = $this->requestRepo->findPendingForUser($userId);
        }

        return $this->view('user/virtual-card/index', [
            'pageTitle'      => 'Virtual Card',
            'user'           => $user,
            'approvedCard'   => $approvedCard,
            'decryptedCard'  => $decryptedCard,
            'pendingRequest' => $pendingRequest,
        ]);
    }

    public function create()
    {
        $userId = Session::get('user_id');

        $flagRepo = new \App\Repositories\ComplianceFlagRepository();
        if ($flagRepo->hasOpenFlag($userId)) {
            Session::flash('error', 'You must complete compliance verification before applying for a card.');
            return $this->redirect('/compliance');
        }

        $userRepo = new \App\Repositories\UserRepository();
        $user = $userRepo->find($userId);

        return $this->view('user/virtual-card/create', [
            'pageTitle' => 'Order New Card',
            'user'      => $user,
        ]);
    }

    public function request()
    {
        $userId = Session::get('user_id');

        $flagRepo = new \App\Repositories\ComplianceFlagRepository();
        if ($flagRepo->hasOpenFlag($userId)) {
            Session::flash('error', 'You must complete compliance verification before applying for a card.');
            return $this->redirect('/compliance');
        }

        // Guard: no approved card or pending request may already exist
        $approvedCard   = $this->cardRepo->findApprovedForUser($userId);
        $pendingRequest = $this->requestRepo->findPendingForUser($userId);

        if ($approvedCard || $pendingRequest) {
            Session::flash('error', 'You already have an active card or a pending request.');
            return $this->redirect('/virtual-card');
        }

        // Validate & sanitize the chosen style
        $allowedStyles = ['visa_geo', 'mc_dark', 'mc_light'];
        $rawStyle      = $_POST['card_style'] ?? 'visa_geo';
        $cardStyle     = in_array($rawStyle, $allowedStyles, true) ? $rawStyle : 'visa_geo';

        VirtualCardRequest::create([
            'user_id'    => $userId,
            'status'     => 'pending',
            'card_style' => $cardStyle,
        ]);

        Session::flash('success', 'Your virtual card request has been submitted and is pending review.');
        return $this->redirect('/virtual-card');
    }
}
