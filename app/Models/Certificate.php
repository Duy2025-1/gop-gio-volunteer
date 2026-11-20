<?php
declare(strict_types=1);

namespace App\Models;

class Certificate extends BaseModel
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT c.*, e.start_time, e.end_time, e.location, u.email AS recipient_email
             FROM certificates c
             INNER JOIN events e ON e.id = c.event_id
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.id = :id",
            ['id' => $id]
        );
    }

    public function findByVerificationCode(string $verificationCode): ?array
    {
        return $this->fetchOne(
            "SELECT c.*, e.start_time, e.end_time, e.location, u.email AS recipient_email,
                    o.logo AS organization_logo, o.verification_status AS organization_verification
             FROM certificates c
             INNER JOIN events e ON e.id = c.event_id
             INNER JOIN organizations o ON o.id = c.organization_id
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.verification_code = :code OR c.certificate_code = :code2",
            ['code' => trim($verificationCode), 'code2' => trim($verificationCode)]
        );
    }

    public function findByRegistrationId(int $registrationId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM certificates WHERE registration_id = :rid",
            ['rid' => $registrationId]
        );
    }

    public function getByUser(int $userId): array
    {
        return $this->fetchAll(
            "SELECT c.*, e.start_time, e.end_time, e.location
             FROM certificates c
             INNER JOIN events e ON e.id = c.event_id
             WHERE c.user_id = :uid
             ORDER BY c.id DESC",
            ['uid' => $userId]
        );
    }

    public function getAllForAdmin(): array
    {
        return $this->fetchAll(
            "SELECT c.*, u.email AS recipient_email
             FROM certificates c
             INNER JOIN users u ON u.id = c.user_id
             ORDER BY c.id DESC"
        );
    }

    public function updateStatus(int $id, string $status, ?string $revokedReason = null): bool
    {
        $now = date('Y-m-d H:i:s');
        return $this->execute(
            "UPDATE certificates
             SET status = :status,
                 revoked_reason = :reason,
                 revoked_at = CASE WHEN :status_check = 'Revoked' THEN :now ELSE NULL END,
                 updated_at = :now
             WHERE id = :id",
            [
                'status' => $status,
                'status_check' => $status,
                'reason' => $revokedReason,
                'now' => $now,
                'id' => $id,
            ]
        );
    }
}
