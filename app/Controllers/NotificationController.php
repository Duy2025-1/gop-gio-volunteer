<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Notification;

class NotificationController extends Controller
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    public function unreadCount(): void
    {
        if (!is_logged_in()) {
            $this->json(['success' => true, 'unread_count' => 0, 'items' => []]);
        }
        $userId = (int)Session::userId();
        $count = $this->notificationModel->countUnread($userId);
        $recent = $this->notificationModel->getByUser($userId, 8);

        $this->json([
            'success' => true,
            'unread_count' => $count,
            'items' => $recent,
        ]);
    }

    public function markRead(string $id): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->notificationModel->markAsRead((int)$id, (int)$user['id']);
        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    public function markAllRead(): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->notificationModel->markAllAsRead((int)$user['id']);
        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Đã đánh dấu tất cả thông báo là đã đọc.']);
        }
        Session::setFlash('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    public function delete(string $id): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->notificationModel->deleteById((int)$id, (int)$user['id']);
        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Đã xóa thông báo.']);
        }
        Session::setFlash('success', 'Đã xóa thông báo.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
