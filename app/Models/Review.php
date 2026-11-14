<?php
declare(strict_types=1);

namespace App\Models;

class Review extends BaseModel
{
    public function getVisibleByEvent(int $eventId): array
    {
        return $this->fetchAll(
            "SELECT rv.*, u.full_name AS user_name, u.avatar AS user_avatar
             FROM reviews rv
             INNER JOIN users u ON u.id = rv.user_id
             WHERE rv.event_id = :eid AND rv.status = 'Visible'
             ORDER BY rv.id DESC",
            ['eid' => $eventId]
        );
    }

    public function getRecentCommunityReviews(int $limit = 6): array
    {
        return $this->fetchAll(
            "SELECT rv.*, u.full_name AS user_name, u.avatar AS user_avatar,
                    e.title AS event_title, o.name AS organization_name
             FROM reviews rv
             INNER JOIN users u ON u.id = rv.user_id
             INNER JOIN events e ON e.id = rv.event_id
             INNER JOIN organizations o ON o.id = rv.organization_id
             WHERE rv.status = 'Visible'
             ORDER BY rv.id DESC
             LIMIT " . (int)$limit
        );
    }

    public function findByRegistrationId(int $registrationId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM reviews WHERE registration_id = :rid",
            ['rid' => $registrationId]
        );
    }

    public function createReview(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            "INSERT INTO reviews (event_id, organization_id, user_id, registration_id, rating, comment, status, created_at, updated_at)
             VALUES (:eid, :oid, :uid, :rid, :rating, :comment, 'Visible', :now, :now)",
            [
                'eid' => (int)$data['event_id'],
                'oid' => (int)$data['organization_id'],
                'uid' => (int)$data['user_id'],
                'rid' => (int)$data['registration_id'],
                'rating' => max(1, min(5, (int)$data['rating'])),
                'comment' => trim((string)$data['comment']),
                'now' => $now,
            ]
        );
        return $this->lastInsertId();
    }

    public function createReport(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            "INSERT INTO reports (reporter_id, target_type, target_id, reason, details, status, created_at, updated_at)
             VALUES (:reporter_id, :target_type, :target_id, :reason, :details, 'Pending', :now, :now)",
            [
                'reporter_id' => (int)$data['reporter_id'],
                'target_type' => $data['target_type'],
                'target_id' => (int)$data['target_id'],
                'reason' => trim((string)$data['reason']),
                'details' => $data['details'] ?? null,
                'now' => $now,
            ]
        );
        return $this->lastInsertId();
    }

    public function getAllReviewsForAdmin(): array
    {
        return $this->fetchAll(
            "SELECT rv.*, u.full_name AS user_name, e.title AS event_title, o.name AS organization_name
             FROM reviews rv
             INNER JOIN users u ON u.id = rv.user_id
             INNER JOIN events e ON e.id = rv.event_id
             INNER JOIN organizations o ON o.id = rv.organization_id
             ORDER BY rv.id DESC"
        );
    }

    public function moderateReview(int $reviewId, string $status, int $adminId, ?string $note = null): bool
    {
        return $this->execute(
            "UPDATE reviews SET status = :st, moderated_by = :aid, moderation_note = :note, updated_at = :now WHERE id = :id",
            ['st' => $status, 'aid' => $adminId, 'note' => $note, 'now' => date('Y-m-d H:i:s'), 'id' => $reviewId]
        );
    }

    public function getAllReportsForAdmin(): array
    {
        return $this->fetchAll(
            "SELECT rp.*, u.full_name AS reporter_name, u.email AS reporter_email
             FROM reports rp
             INNER JOIN users u ON u.id = rp.reporter_id
             ORDER BY CASE WHEN rp.status = 'Pending' THEN 0 ELSE 1 END ASC, rp.id DESC"
        );
    }

    public function resolveReport(int $reportId, string $status, int $adminId, string $resolutionNote): bool
    {
        $now = date('Y-m-d H:i:s');
        return $this->execute(
            "UPDATE reports SET status = :st, resolved_by = :aid, resolution_note = :note, resolved_at = :now, updated_at = :now WHERE id = :id",
            ['st' => $status, 'aid' => $adminId, 'note' => $resolutionNote, 'now' => $now, 'id' => $reportId]
        );
    }
}
