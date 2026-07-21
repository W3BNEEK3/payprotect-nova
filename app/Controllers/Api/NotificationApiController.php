<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Repositories\NotificationRepository;

class NotificationApiController extends BaseController
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
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $latest = $this->repo->latestForUser($userId, 5);
        $unreadCount = $this->repo->unreadCountForUser($userId);

        $this->json([
            'unread_count' => $unreadCount,
            'latest' => $latest
        ]);
    }

    public function markRead(string $id): void
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method Not Allowed'], 405);
            return;
        }

        // Verify the notification belongs to this user
        $notification = $this->repo->find((int)$id);
        if ($notification && (int)$notification['user_id'] === $userId) {
            $this->repo->markRead((int)$id);
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Not Found'], 404);
        }
    }

    public function markAllRead(): void
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method Not Allowed'], 405);
            return;
        }

        $this->repo->markAllReadForUser($userId);
        
        $this->json(['success' => true]);
    }
}
