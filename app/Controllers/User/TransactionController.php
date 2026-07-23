<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Repositories\TransactionRepository;

class TransactionController extends BaseController
{
    private TransactionRepository $transactionRepo;

    public function __construct()
    {
        $this->transactionRepo = new TransactionRepository();
    }

    public function index()
    {
        $userId = Session::get('user_id');
        
        $transactions = $this->transactionRepo->findByUserId($userId);
        
        $this->view('user/transactions', [
            'title' => 'Transaction History',
            'transactions' => $transactions
        ]);
    }
}
