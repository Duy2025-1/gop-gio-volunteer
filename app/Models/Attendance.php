<?php
declare(strict_types=1);

namespace App\Models;

class Attendance extends BaseModel
{
    public function findByRegistrationId(int $registrationId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM attendance WHERE registration_id = :rid",
            ['rid' => $registrationId]
        );
    }

    public function findVolunteerHoursByRegistrationId(int $registrationId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM volunteer_hours WHERE registration_id = :rid",
            ['rid' => $registrationId]
        );
    }

    public function findVolunteerHoursById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT vh.*, e.organization_id, e.max_hours, e.title AS event_title,
                    u.full_name AS volunteer_name, o.name AS organization_name
             FROM volunteer_hours vh
             INNER JOIN events e ON e.id = vh.event_id
             INNER JOIN organizations o ON o.id = e.organization_id
             INNER JOIN users u ON u.id = vh.user_id
             WHERE vh.id = :id",
            ['id' => $id]
        );
    }

    public function getAttendanceHistoryByUser(int $userId): array
    {
        return $this->fetchAll(
            "SELECT a.*,
                    e.title AS event_title, e.location,
                    o.name AS organization_name,
                    vh.hours AS approved_hours, vh.status AS hours_status, vh.adjustment_reason
             FROM attendance a
             INNER JOIN events e ON e.id = a.event_id
             INNER JOIN organizations o ON o.id = e.organization_id
             LEFT JOIN volunteer_hours vh ON vh.registration_id = a.registration_id
             WHERE a.user_id = :uid
             ORDER BY a.id DESC",
            ['uid' => $userId]
        );
    }

    public function getAllVolunteerHoursForAdmin(): array
    {
        return $this->fetchAll(
            "SELECT vh.*,
                    u.full_name AS volunteer_name, u.email AS volunteer_email,
                    e.title AS event_title, e.max_hours,
                    o.name AS organization_name,
                    a.check_in_time, a.check_out_time, a.calculated_hours
             FROM volunteer_hours vh
             INNER JOIN users u ON u.id = vh.user_id
             INNER JOIN events e ON e.id = vh.event_id
             INNER JOIN organizations o ON o.id = e.organization_id
             INNER JOIN attendance a ON a.id = vh.attendance_id
             ORDER BY vh.id DESC"
        );
    }
}
