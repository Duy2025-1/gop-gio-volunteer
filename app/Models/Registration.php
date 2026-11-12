<?php
declare(strict_types=1);

namespace App\Models;

class Registration extends BaseModel
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT r.*,
                    u.full_name AS volunteer_name, u.email AS volunteer_email, u.phone AS volunteer_phone, u.avatar AS volunteer_avatar,
                    e.title AS event_title, e.start_time, e.end_time, e.location, e.max_hours, e.organization_id, e.status AS event_status,
                    o.name AS organization_name
             FROM registrations r
             INNER JOIN users u ON u.id = r.user_id
             INNER JOIN events e ON e.id = r.event_id
             INNER JOIN organizations o ON o.id = e.organization_id
             WHERE r.id = :id",
            ['id' => $id]
        );
    }

    public function findByEventAndUser(int $eventId, int $userId): ?array
    {
        return $this->fetchOne(
            "SELECT r.*, w.position_number AS waitlist_position
             FROM registrations r
             LEFT JOIN waitlist w ON w.registration_id = r.id AND w.status = 'Waiting'
             WHERE r.event_id = :eid AND r.user_id = :uid",
            ['eid' => $eventId, 'uid' => $userId]
        );
    }

    public function findByQrToken(string $qrToken): ?array
    {
        $tokenHash = hash('sha256', trim($qrToken));
        return $this->fetchOne(
            "SELECT r.*,
                    u.full_name AS volunteer_name, u.email AS volunteer_email, u.phone AS volunteer_phone,
                    e.title AS event_title, e.start_time, e.end_time, e.max_hours, e.organization_id, e.status AS event_status,
                    o.name AS organization_name
             FROM registrations r
             INNER JOIN users u ON u.id = r.user_id
             INNER JOIN events e ON e.id = r.event_id
             INNER JOIN organizations o ON o.id = e.organization_id
             WHERE r.qr_token = :token OR r.qr_token_hash = :thash",
            ['token' => trim($qrToken), 'thash' => $tokenHash]
        );
    }

    public function getByUser(int $userId): array
    {
        return $this->fetchAll(
            "SELECT r.*,
                    e.title AS event_title, e.slug AS event_slug, e.cover_image, e.start_time, e.end_time,
                    e.location, e.max_hours, e.status AS event_status,
                    c.name AS category_name, c.is_blood_donation,
                    o.name AS organization_name,
                    a.check_in_time, a.check_out_time, a.calculated_hours, a.status AS attendance_status,
                    vh.hours AS approved_hours, vh.status AS hours_status,
                    cert.certificate_code, cert.verification_code,
                    w.position_number AS waitlist_position,
                    bda.appointment_code, bda.time_slot, bda.status AS blood_appointment_status,
                    (SELECT COUNT(*) FROM reviews rv WHERE rv.registration_id = r.id) AS has_review
             FROM registrations r
             INNER JOIN events e ON e.id = r.event_id
             INNER JOIN event_categories c ON c.id = e.category_id
             INNER JOIN organizations o ON o.id = e.organization_id
             LEFT JOIN attendance a ON a.registration_id = r.id
             LEFT JOIN volunteer_hours vh ON vh.registration_id = r.id
             LEFT JOIN certificates cert ON cert.registration_id = r.id AND cert.status = 'Valid'
             LEFT JOIN waitlist w ON w.registration_id = r.id AND w.status = 'Waiting'
             LEFT JOIN blood_donation_appointments bda ON bda.registration_id = r.id
             WHERE r.user_id = :uid
             ORDER BY r.id DESC",
            ['uid' => $userId]
        );
    }

    public function getByEvent(int $eventId): array
    {
        return $this->fetchAll(
            "SELECT r.*,
                    u.full_name AS volunteer_name, u.email AS volunteer_email, u.phone AS volunteer_phone,
                    u.avatar AS volunteer_avatar, u.blood_type,
                    a.id AS attendance_id, a.check_in_time, a.check_out_time, a.check_in_method, a.check_out_method,
                    a.calculated_hours, a.status AS attendance_status,
                    vh.id AS volunteer_hour_id, vh.hours AS verified_hours, vh.status AS hours_status,
                    cert.id AS certificate_id, cert.certificate_code, cert.verification_code,
                    w.position_number AS waitlist_position,
                    bda.id AS blood_appointment_id, bda.appointment_code, bda.time_slot, bda.blood_group_declared,
                    bda.donation_volume_ml, bda.status AS blood_status, bda.medical_staff_confirmed_by
             FROM registrations r
             INNER JOIN users u ON u.id = r.user_id
             LEFT JOIN attendance a ON a.registration_id = r.id
             LEFT JOIN volunteer_hours vh ON vh.registration_id = r.id
             LEFT JOIN certificates cert ON cert.registration_id = r.id
             LEFT JOIN waitlist w ON w.registration_id = r.id AND w.status = 'Waiting'
             LEFT JOIN blood_donation_appointments bda ON bda.registration_id = r.id
             WHERE r.event_id = :eid
             ORDER BY CASE r.status
                WHEN 'Attended' THEN 1
                WHEN 'Confirmed' THEN 2
                WHEN 'Waitlisted' THEN 3
                WHEN 'Pending' THEN 4
                ELSE 5
             END ASC, r.id ASC",
            ['eid' => $eventId]
        );
    }

    public function getBloodAppointmentsByEvent(int $eventId): array
    {
        return $this->fetchAll(
            "SELECT bda.*, u.full_name, u.email, u.phone, r.registration_code, r.qr_token
             FROM blood_donation_appointments bda
             INNER JOIN users u ON u.id = bda.user_id
             INNER JOIN registrations r ON r.id = bda.registration_id
             WHERE bda.event_id = :eid
             ORDER BY bda.time_slot ASC, bda.id ASC",
            ['eid' => $eventId]
        );
    }

    public function updateBloodAppointmentStatus(
        int $appointmentId,
        string $status,
        string $staffName,
        ?string $note = null,
        ?int $volumeMl = null
    ): bool {
        return $this->execute(
            "UPDATE blood_donation_appointments
             SET status = :st,
                 medical_staff_confirmed_by = :staff,
                 medical_confirmation_note = :note,
                 donation_volume_ml = COALESCE(:vol, donation_volume_ml),
                 confirmed_at = :now,
                 updated_at = :now
             WHERE id = :id",
            [
                'st' => $status,
                'staff' => $staffName,
                'note' => $note,
                'vol' => $volumeMl,
                'now' => date('Y-m-d H:i:s'),
                'id' => $appointmentId,
            ]
        );
    }
}
