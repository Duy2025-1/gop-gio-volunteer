<?php
declare(strict_types=1);

namespace App\Models;

class AuditLog extends BaseModel
{
    public function log(
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        mixed $oldValues = null,
        mixed $newValues = null
    ): int {
        $oldJson = $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
        $newJson = $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Test'), 0, 250);

        $this->execute(
            "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at, updated_at)
             VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip, :ua, :now, :now)",
            [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $oldJson,
                'new_values' => $newJson,
                'ip' => $ip,
                'ua' => $ua,
                'now' => date('Y-m-d H:i:s'),
            ]
        );
        return $this->lastInsertId();
    }

    public function getRecentLogs(int $limit = 50): array
    {
        return $this->fetchAll(
            "SELECT al.*, u.full_name AS user_name, u.email AS user_email
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.id DESC
             LIMIT " . (int)$limit
        );
    }

    public function getSystemSettings(): array
    {
        $rows = $this->fetchAll("SELECT * FROM system_settings ORDER BY id ASC");
        $map = [];
        foreach ($rows as $r) {
            $map[$r['setting_key']] = $r['setting_value'];
        }
        return ['rows' => $rows, 'map' => $map];
    }

    public function updateSystemSetting(string $key, string $value, int $updatedBy): void
    {
        $this->execute(
            "UPDATE system_settings SET setting_value = :val, updated_by = :uid, updated_at = :now WHERE setting_key = :key",
            [
                'val' => $value,
                'uid' => $updatedBy,
                'now' => date('Y-m-d H:i:s'),
                'key' => $key,
            ]
        );
    }
}
