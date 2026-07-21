<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Middlewares\AuthMiddleware;
use App\Models\Support;
use App\Core\Session;

class SupportController extends BaseController
{

    public function index()
    {
        return $this->view('user/support/index', [
            'pageTitle' => 'Support',
        ]);
    }

    public function submitTicket()
    {
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';
        $userId = Session::get('user_id');

        if (empty(trim($subject)) || empty(trim($message))) {
            Session::flash('error', 'Subject and message are required.');
            return $this->redirect('/support');
        }

        Support::create([
            'user_id' => $userId,
            'subject' => trim($subject),
            'message' => trim($message),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Session::flash('success', 'Your ticket has been submitted. We will contact you via email.');
        return $this->redirect('/support');
    }

    public function widget()
    {
        return $this->view('components/chat/_widget', [], true);
    }
}
