<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Repositories\UserRepository;

class UsersController extends BaseController
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function index(): void
    {
        // Simple listing for all users for now
        $users = $this->userRepo->all();

        $this->view('admin/users', [
            'pageTitle' => 'Users Management',
            'users' => $users
        ]);
    }

    public function search(): void
    {
        if (isset($_GET['all'])) {
            $users = $this->userRepo->all();
            header('Content-Type: application/json');
            echo json_encode(array_slice($users, 0, 50));
            return;
        }

        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }

        $users = $this->userRepo->searchByEmail($query);
        header('Content-Type: application/json');
        echo json_encode($users);
    }

    public function show(int $id): void
    {
        $user = $this->userRepo->find($id);

        if (!$user) {
            \App\Core\Session::flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $this->view('admin/users/show', [
            'pageTitle' => 'Edit User',
            'user' => $user
        ]);
    }

    public function update(int $id): void
    {
        $user = $this->userRepo->find($id);
        if (!$user) {
            \App\Core\Session::flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $accountNumber = trim((string)($_POST['account_number'] ?? $user['account_number']));
        
        if ($accountNumber !== $user['account_number']) {
            $existing = $this->userRepo->findByAccountNumber($accountNumber);
            if ($existing && (int)$existing['id'] !== $id) {
                \App\Core\Session::flash('error', 'Account number is already in use by another user.');
                $this->redirect('/admin/users/' . $id);
                return;
            }
        }

        $data = [
            'fullname' => $_POST['fullname'] ?? $user['fullname'],
            'email' => $_POST['email'] ?? $user['email'],
            'account_number' => $accountNumber,
            'currency' => $_POST['currency'] ?? $user['currency'],
            'is_kyc_verified' => isset($_POST['is_kyc_verified']) ? 1 : 0,
            'is_upgraded' => isset($_POST['is_upgraded']) ? 1 : 0
        ];

        $this->userRepo->updateUser($id, $data);

        \App\Core\Session::flash('success', 'User updated successfully.');
        $this->redirect('/admin/users/' . $id);
    }

    public function updateBalance(int $id): void
    {
        $user = $this->userRepo->find($id);
        if (!$user) {
            \App\Core\Session::flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $action = $_POST['action'] ?? 'credit';

        if ($amount <= 0) {
            \App\Core\Session::flash('error', 'Invalid amount.');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        if ($action === 'credit') {
            $this->userRepo->refundBalance($id, $amount);
            \App\Core\Session::flash('success', 'Balance credited successfully.');
        } elseif ($action === 'debit') {
            $this->userRepo->deductBalance($id, $amount);
            \App\Core\Session::flash('success', 'Balance debited successfully.');
        }

        $this->redirect('/admin/users/' . $id);
    }
}
