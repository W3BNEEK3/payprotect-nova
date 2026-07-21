<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Repositories\NotificationRepository;

class NotificationController extends BaseController
{
    private NotificationRepository $repo;

    public function __construct()
    {
        $this->repo = new NotificationRepository();
    }

    public function index(): void
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        // We can fetch the full list here, or do pagination. For now, fetch all or a decent chunk (e.g. 50).
        // The SADD mentions grouping by date, filterable by All/Unread, with mark all as read.
        
        $filter = $_GET['filter'] ?? 'all'; // 'all' or 'unread'
        
        // Let's implement a simple fetch for the view. We can add a specialized repo method if needed,
        // but since we are using PDO directly, we can just do a custom query here for the full page.
        $sql = 'SELECT * FROM notifications WHERE user_id = ?';
        if ($filter === 'unread') {
            $sql .= ' AND is_read = 0';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 100';

        $stmt = \App\Core\Database::connection()->prepare($sql);
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll();

        // Group by date
        $grouped = [];
        foreach ($notifications as $n) {
            $date = (new \DateTime($n['created_at']))->format('Y-m-d');
            $grouped[$date][] = $n;
        }

        $this->view('user/notifications/index', [
            'pageTitle' => 'Notifications',
            'grouped' => $grouped,
            'filter' => $filter
        ]);
    }
}
