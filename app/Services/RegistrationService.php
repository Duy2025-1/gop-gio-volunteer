<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Registration;
use PDO;

class RegistrationService
{
    private PDO $db;
    private Event $eventModel;
    private Registration $registrationModel;
    private Notification $notificationModel;
    private AuditLog $auditModel;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->eventModel = new Event($this->db);
        $this->registrationModel = new Registration($this->db);
        $this->notificationModel = new Notification($this->db);
        $this->auditModel = new AuditLog($this->db);
    }

    /**
     * Register a volunteer for an event using a Database Transaction.
     * Automatically places the volunteer on the Waitlist if the event has reached max_volunteers.
     * Supports optional blood donation time slot booking for blood donation events.
     */
    public function registerForEvent(array $actorUser, int $eventId, array $input = []): array
    {
        AuthMiddleware::assertRole($actorUser, ['volunteer'], 'đăng ký tham gia sự kiện');
        $userId = (int)$actorUser['id'];

        $inTransaction = $this->db->inTransaction();
        if (!$inTransaction) {
            $this->db->beginTransaction();
        }

        try {
            // Lock event row in MySQL (FOR UPDATE) or fetch in SQLite transaction
            $sqlEvent = Database::getDriver() === 'mysql'
                ? "SELECT e.*, c.is_blood_donation FROM events e INNER JOIN event_categories c ON c.id = e.category_id WHERE e.id = :eid FOR UPDATE"
                : "SELECT e.*, c.is_blood_donation FROM events e INNER JOIN event_categories c ON c.id = e.category_id WHERE e.id = :eid";

            $stmtEvent = $this->db->prepare($sqlEvent);
            $stmtEvent->execute(['eid' => $eventId]);
            $event = $stmtEvent->fetch();

            if (!$event) {
                throw new \RuntimeException('Sự kiện không tồn tại.');
            }

            if (!in_array($event['status'], ['Published', 'Approved', 'Ongoing'], true)) {
                throw new \RuntimeException('Sự kiện này hiện không mở đăng ký (Trạng thái: ' . $event['status'] . ').');
            }

            if (strtotime((string)$event['end_time']) < time()) {
                throw new \RuntimeException('Không thể đăng ký sự kiện đã kết thúc.');
            }

            // Check existing registration
            $existing = $this->registrationModel->findByEventAndUser($eventId, $userId);
            if ($existing && $existing['status'] !== 'Cancelled') {
                throw new \RuntimeException('Bạn đã đăng ký sự kiện này rồi (Mã đăng ký: ' . $existing['registration_code'] . ').');
            }

            // Count active confirmed/attended spots inside transaction
            $stmtCount = $this->db->prepare(
                "SELECT COUNT(*) FROM registrations WHERE event_id = :eid AND status IN ('Confirmed', 'Attended')"
            );
            $stmtCount->execute(['eid' => $eventId]);
            $activeCount = (int)$stmtCount->fetchColumn();

            $maxVolunteers = (int)$event['max_volunteers'];
            $isFull = ($activeCount >= $maxVolunteers);
            $status = $isFull ? 'Waitlisted' : 'Confirmed';

            $now = date('Y-m-d H:i:s');
            $regCode = 'REG-' . date('Ym') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $qrToken = 'ggqr_ev' . $eventId . '_u' . $userId . '_' . bin2hex(random_bytes(16));
            $qrTokenHash = hash('sha256', $qrToken);

            if ($existing && $existing['status'] === 'Cancelled') {
                // Re-activate cancelled registration
                $regId = (int)$existing['id'];
                $stmtUpd = $this->db->prepare(
                    "UPDATE registrations
                     SET registration_code = :code,
                         qr_token = :token,
                         qr_token_hash = :thash,
                         status = :status,
                         motivation_note = :note,
                         emergency_contact = :contact,
                         cancel_reason = NULL,
                         cancelled_at = NULL,
                         registered_at = :now,
                         confirmed_at = :confirmed_at,
                         updated_at = :now
                     WHERE id = :id"
                );
                $stmtUpd->execute([
                    'code' => $regCode,
                    'token' => $qrToken,
                    'thash' => $qrTokenHash,
                    'status' => $status,
                    'note' => $input['motivation_note'] ?? null,
                    'contact' => $input['emergency_contact'] ?? null,
                    'now' => $now,
                    'confirmed_at' => $status === 'Confirmed' ? $now : null,
                    'id' => $regId,
                ]);
                // Remove old waitlist record if any
                $this->db->prepare("DELETE FROM waitlist WHERE registration_id = :rid")->execute(['rid' => $regId]);
            } else {
                $stmtIns = $this->db->prepare(
                    "INSERT INTO registrations (
                        event_id, user_id, registration_code, qr_token, qr_token_hash,
                        status, motivation_note, emergency_contact, registered_at, confirmed_at, created_at, updated_at
                     ) VALUES (
                        :eid, :uid, :code, :token, :thash,
                        :status, :note, :contact, :now, :confirmed_at, :now, :now
                     )"
                );
                $stmtIns->execute([
                    'eid' => $eventId,
                    'uid' => $userId,
                    'code' => $regCode,
                    'token' => $qrToken,
                    'thash' => $qrTokenHash,
                    'status' => $status,
                    'note' => $input['motivation_note'] ?? null,
                    'contact' => $input['emergency_contact'] ?? null,
                    'now' => $now,
                    'confirmed_at' => $status === 'Confirmed' ? $now : null,
                ]);
                $regId = (int)$this->db->lastInsertId();
            }

            $waitlistPosition = null;
            if ($status === 'Waitlisted') {
                $stmtMaxPos = $this->db->prepare(
                    "SELECT COALESCE(MAX(position_number), 0) + 1 FROM waitlist WHERE event_id = :eid AND status = 'Waiting'"
                );
                $stmtMaxPos->execute(['eid' => $eventId]);
                $waitlistPosition = (int)$stmtMaxPos->fetchColumn();

                $stmtWl = $this->db->prepare(
                    "INSERT INTO waitlist (event_id, user_id, registration_id, position_number, status, created_at, updated_at)
                     VALUES (:eid, :uid, :rid, :pos, 'Waiting', :now, :now)"
                );
                $stmtWl->execute([
                    'eid' => $eventId,
                    'uid' => $userId,
                    'rid' => $regId,
                    'pos' => $waitlistPosition,
                    'now' => $now,
                ]);
            }

            // If blood donation event or time_slot provided, create blood_donation_appointments record
            if ((int)$event['is_blood_donation'] === 1 || !empty($input['time_slot'])) {
                $apptCode = 'BLD-' . date('Ym') . '-' . strtoupper(bin2hex(random_bytes(3)));
                $timeSlot = !empty($input['time_slot']) ? trim((string)$input['time_slot']) : 'Ca 1: 07:30 - 09:00';
                $bloodGroup = !empty($input['blood_group_declared']) ? trim((string)$input['blood_group_declared']) : ($actorUser['blood_type'] ?: 'Chưa rõ');
                $volumeMl = !empty($input['donation_volume_ml']) ? (int)$input['donation_volume_ml'] : 350;

                $this->db->prepare("DELETE FROM blood_donation_appointments WHERE registration_id = :rid")->execute(['rid' => $regId]);
                $stmtBld = $this->db->prepare(
                    "INSERT INTO blood_donation_appointments (
                        event_id, user_id, registration_id, appointment_code, time_slot,
                        blood_group_declared, donation_volume_ml, medical_unit_name, status, created_at, updated_at
                     ) VALUES (
                        :eid, :uid, :rid, :acode, :slot, :bgroup, :vol, :med_unit, 'Scheduled', :now, :now
                     )"
                );
                $stmtBld->execute([
                    'eid' => $eventId,
                    'uid' => $userId,
                    'rid' => $regId,
                    'acode' => $apptCode,
                    'slot' => $timeSlot,
                    'bgroup' => $bloodGroup,
                    'vol' => $volumeMl,
                    'med_unit' => $event['medical_unit'] ?: 'Đơn vị Y tế Phối hợp',
                    'now' => $now,
                ]);
            }

            $this->eventModel->syncRegisteredCount($eventId);

            if (!$inTransaction) {
                $this->db->commit();
            }

            if ($status === 'Confirmed') {
                $this->notificationModel->create(
                    $userId,
                    'Đăng ký sự kiện thành công!',
                    'Bạn đã được xác nhận tham gia "' . $event['title'] . '". Hãy sử dụng mã QR cá nhân để điểm danh.',
                    'Registration',
                    '/volunteer/dashboard'
                );
            } else {
                $this->notificationModel->create(
                    $userId,
                    'Đã ghi nhận vào Danh sách chờ (Vị trí #' . $waitlistPosition . ')',
                    'Sự kiện "' . $event['title'] . '" hiện đã đủ người. Hệ thống sẽ tự động chuyển bạn sang Xác nhận ngay khi có chỗ trống.',
                    'Registration',
                    '/volunteer/dashboard'
                );
            }

            $this->auditModel->log($userId, 'REGISTER_EVENT', 'Registration', $regId, null, [
                'event_id' => $eventId,
                'status' => $status,
                'registration_code' => $regCode,
            ]);

            return [
                'registration_id' => $regId,
                'registration_code' => $regCode,
                'qr_token' => $qrToken,
                'status' => $status,
                'waitlist_position' => $waitlistPosition,
            ];
        } catch (\Throwable $e) {
            if (!$inTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Cancel registration and automatically promote the first volunteer from the waitlist!
     */
    public function cancelRegistration(array $actorUser, int $registrationId, string $reason = 'Bận việc đột xuất'): array
    {
        $reg = $this->registrationModel->findById($registrationId);
        if (!$reg) {
            throw new \RuntimeException('Không tìm thấy lượt đăng ký.');
        }

        if ((int)$reg['user_id'] !== (int)$actorUser['id'] && !in_array($actorUser['role_slug'], ['organizer', 'admin'], true)) {
            throw new \RuntimeException('Bạn không có quyền hủy lượt đăng ký của người khác.');
        }

        if (in_array($reg['status'], ['Attended', 'Cancelled'], true)) {
            throw new \RuntimeException('Không thể hủy lượt đăng ký ở trạng thái [' . $reg['status'] . '].');
        }

        $inTransaction = $this->db->inTransaction();
        if (!$inTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $now = date('Y-m-d H:i:s');
            $wasConfirmed = ($reg['status'] === 'Confirmed');
            $eventId = (int)$reg['event_id'];

            $this->db->prepare(
                "UPDATE registrations SET status = 'Cancelled', cancel_reason = :reason, cancelled_at = :now, updated_at = :now WHERE id = :id"
            )->execute(['reason' => $reason, 'now' => $now, 'id' => $registrationId]);

            $this->db->prepare(
                "UPDATE waitlist SET status = 'Cancelled', updated_at = :now WHERE registration_id = :rid"
            )->execute(['now' => $now, 'rid' => $registrationId]);

            $this->db->prepare(
                "UPDATE blood_donation_appointments SET status = 'Cancelled', updated_at = :now WHERE registration_id = :rid"
            )->execute(['now' => $now, 'rid' => $registrationId]);

            $promotedUserId = null;
            $promotedRegistrationId = null;

            // If a Confirmed slot opened up, automatically promote the first Waiting person on the waitlist!
            if ($wasConfirmed) {
                $stmtNext = $this->db->prepare(
                    "SELECT * FROM waitlist WHERE event_id = :eid AND status = 'Waiting' ORDER BY position_number ASC, id ASC LIMIT 1"
                );
                $stmtNext->execute(['eid' => $eventId]);
                $nextWait = $stmtNext->fetch();

                if ($nextWait) {
                    $promotedRegistrationId = (int)$nextWait['registration_id'];
                    $promotedUserId = (int)$nextWait['user_id'];

                    $this->db->prepare(
                        "UPDATE registrations SET status = 'Confirmed', confirmed_at = :now, updated_at = :now WHERE id = :rid"
                    )->execute(['now' => $now, 'rid' => $promotedRegistrationId]);

                    $this->db->prepare(
                        "UPDATE waitlist SET status = 'Promoted', promoted_at = :now, updated_at = :now WHERE id = :wid"
                    )->execute(['now' => $now, 'wid' => (int)$nextWait['id']]);
                }
            }

            $this->eventModel->syncRegisteredCount($eventId);

            if (!$inTransaction) {
                $this->db->commit();
            }

            if ($promotedUserId !== null) {
                $this->notificationModel->create(
                    $promotedUserId,
                    'Tin vui: Bạn đã được chuyển từ Danh sách chờ sang Xác nhận!',
                    'Sự kiện "' . $reg['event_title'] . '" vừa có chỗ trống và hệ thống đã tự động xác nhận lượt đăng ký của bạn.',
                    'Registration',
                    '/volunteer/dashboard'
                );
            }

            $this->auditModel->log((int)$actorUser['id'], 'CANCEL_REGISTRATION', 'Registration', $registrationId, [
                'status' => $reg['status']
            ], [
                'status' => 'Cancelled',
                'reason' => $reason,
                'promoted_registration_id' => $promotedRegistrationId,
            ]);

            return [
                'cancelled_registration_id' => $registrationId,
                'promoted_registration_id' => $promotedRegistrationId,
                'promoted_user_id' => $promotedUserId,
            ];
        } catch (\Throwable $e) {
            if (!$inTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
