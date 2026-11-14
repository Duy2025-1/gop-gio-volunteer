<?php
declare(strict_types=1);

namespace App\Models;

class Notification extends BaseModel
{
    public function create(
        int $userId,
        string $title,
        string $message,
        string $type = 'System',
        ?string $linkUrl = null
    ): int {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            "INSERT INTO notifications (user_id, title, message, type, link_url, is_read, created_at, updated_at)
             VALUES (:user_id, :title, :message, :type, :link_url, 0, :now, :now)",
            [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link_url' => $linkUrl,
                'now' => $now,
            ]
        );
        return $this->lastInsertId();
    }

    public function getByUser(int $userId, int $limit = 30): array
    {
        return $this->fetchAll(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY id DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }

    public function countUnread(int $userId): int
    {
        return (int)$this->fetchValue(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0",
            ['uid' => $userId]
        );
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        return $this->execute(
            "UPDATE notifications SET is_read = 1, read_at = :now, updated_at = :now WHERE id = :id AND user_id = :uid",
            ['id' => $notificationId, 'uid' => $userId, 'now' => date('Y-m-d H:i:s')]
        );
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->execute(
            "UPDATE notifications SET is_read = 1, read_at = :now, updated_at = :now WHERE user_id = :uid AND is_read = 0",
            ['uid' => $userId, 'now' => date('Y-m-d H:i:s')]
        );
    }

    public function deleteById(int $notificationId, int $userId): bool
    {
        return $this->execute(
            "DELETE FROM notifications WHERE id = :id AND user_id = :uid",
            ['id' => $notificationId, 'uid' => $userId]
        );
    }
}
