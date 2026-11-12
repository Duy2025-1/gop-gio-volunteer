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
// [v1-draft-rule-1] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #1
// [v1-draft-rule-2] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #2
// [v1-draft-rule-3] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #3
// [v1-draft-rule-4] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #4
// [v1-draft-rule-5] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #5
// [v1-draft-rule-6] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #6
// [v1-draft-rule-7] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #7
// [v1-draft-rule-8] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #8
// [v1-draft-rule-9] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #9
// [v1-draft-rule-10] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #10
// [v1-draft-rule-11] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #11
// [v1-draft-rule-12] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #12
// [v1-draft-rule-13] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #13
// [v1-draft-rule-14] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #14
// [v1-draft-rule-15] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #15
// [v1-draft-rule-16] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #16
// [v1-draft-rule-17] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #17
// [v1-draft-rule-18] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #18
// [v1-draft-rule-19] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #19
// [v1-draft-rule-20] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #20
// [v1-draft-rule-21] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #21
// [v1-draft-rule-22] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #22
// [v1-draft-rule-23] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #23
// [v1-draft-rule-24] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #24
// [v1-draft-rule-25] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #25
// [v1-draft-rule-26] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #26
// [v1-draft-rule-27] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #27
// [v1-draft-rule-28] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #28
// [v1-draft-rule-29] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #29
// [v1-draft-rule-30] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #30
// [v1-draft-rule-31] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #31
// [v1-draft-rule-32] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #32
// [v1-draft-rule-33] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #33
// [v1-draft-rule-34] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #34
// [v1-draft-rule-35] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #35
// [v1-draft-rule-36] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #36
// [v1-draft-rule-37] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #37
// [v1-draft-rule-38] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #38
// [v1-draft-rule-39] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #39
// [v1-draft-rule-40] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #40
// [v1-draft-rule-41] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #41
// [v1-draft-rule-42] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #42
// [v1-draft-rule-43] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #43
// [v1-draft-rule-44] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #44
// [v1-draft-rule-45] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #45
// [v1-draft-rule-46] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #46
// [v1-draft-rule-47] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #47
// [v1-draft-rule-48] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #48
// [v1-draft-rule-49] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #49
// [v1-draft-rule-50] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #50
// [v1-draft-rule-51] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #51
// [v1-draft-rule-52] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #52
// [v1-draft-rule-53] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #53
// [v1-draft-rule-54] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #54
// [v1-draft-rule-55] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #55
// [v1-draft-rule-56] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #56
// [v1-draft-rule-57] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #57
// [v1-draft-rule-58] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #58
// [v1-draft-rule-59] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #59
// [v1-draft-rule-60] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #60
// [v1-draft-rule-61] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #61
// [v1-draft-rule-62] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #62
// [v1-draft-rule-63] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #63
// [v1-draft-rule-64] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #64
// [v1-draft-rule-65] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #65
// [v1-draft-rule-66] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #66
// [v1-draft-rule-67] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #67
// [v1-draft-rule-68] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #68
// [v1-draft-rule-69] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #69
// [v1-draft-rule-70] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #70
// [v1-draft-rule-71] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #71
// [v1-draft-rule-72] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #72
// [v1-draft-rule-73] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #73
// [v1-draft-rule-74] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #74
// [v1-draft-rule-75] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #75
// [v1-draft-rule-76] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #76
// [v1-draft-rule-77] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #77
// [v1-draft-rule-78] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #78
// [v1-draft-rule-79] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #79
// [v1-draft-rule-80] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #80
// [v1-draft-rule-81] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #81
// [v1-draft-rule-82] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #82
// [v1-draft-rule-83] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #83
// [v1-draft-rule-84] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #84
// [v1-draft-rule-85] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #85
// [v1-draft-rule-86] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #86
// [v1-draft-rule-87] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #87
// [v1-draft-rule-88] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #88
// [v1-draft-rule-89] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #89
// [v1-draft-rule-90] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #90
// [v1-draft-rule-91] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #91
// [v1-draft-rule-92] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #92
// [v1-draft-rule-93] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #93
// [v1-draft-rule-94] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #94
// [v1-draft-rule-95] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #95
// [v1-draft-rule-96] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #96
// [v1-draft-rule-97] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #97
// [v1-draft-rule-98] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #98
// [v1-draft-rule-99] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #99
// [v1-draft-rule-100] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #100
// [v1-draft-rule-101] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #101
// [v1-draft-rule-102] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #102
// [v1-draft-rule-103] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #103
// [v1-draft-rule-104] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #104
// [v1-draft-rule-105] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #105
// [v1-draft-rule-106] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #106
// [v1-draft-rule-107] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #107
// [v1-draft-rule-108] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #108
// [v1-draft-rule-109] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #109
// [v1-draft-rule-110] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #110
// [v1-draft-rule-111] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #111
// [v1-draft-rule-112] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #112
// [v1-draft-rule-113] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #113
// [v1-draft-rule-114] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #114
// [v1-draft-rule-115] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #115
// [v1-draft-rule-116] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #116
// [v1-draft-rule-117] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #117
// [v1-draft-rule-118] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #118
// [v1-draft-rule-119] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #119
// [v1-draft-rule-120] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #120
// [v1-draft-rule-121] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #121
// [v1-draft-rule-122] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #122
// [v1-draft-rule-123] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #123
// [v1-draft-rule-124] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #124
// [v1-draft-rule-125] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #125
// [v1-draft-rule-126] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #126
// [v1-draft-rule-127] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #127
// [v1-draft-rule-128] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #128
// [v1-draft-rule-129] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #129
// [v1-draft-rule-130] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #130
// [v1-draft-rule-131] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #131
// [v1-draft-rule-132] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #132
// [v1-draft-rule-133] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #133
// [v1-draft-rule-134] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #134
// [v1-draft-rule-135] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #135
// [v1-draft-rule-136] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #136
// [v1-draft-rule-137] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #137
// [v1-draft-rule-138] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #138
// [v1-draft-rule-139] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #139
// [v1-draft-rule-140] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #140
// [v1-draft-rule-141] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #141
// [v1-draft-rule-142] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #142
// [v1-draft-rule-143] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #143
// [v1-draft-rule-144] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #144
// [v1-draft-rule-145] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #145
// [v1-draft-rule-146] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #146
// [v1-draft-rule-147] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #147
// [v1-draft-rule-148] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #148
// [v1-draft-rule-149] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #149
// [v1-draft-rule-150] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #150
// [v1-draft-rule-151] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #151
// [v1-draft-rule-152] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #152
// [v1-draft-rule-153] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #153
// [v1-draft-rule-154] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #154
// [v1-draft-rule-155] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #155
// [v1-draft-rule-156] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #156
// [v1-draft-rule-157] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #157
// [v1-draft-rule-158] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #158
// [v1-draft-rule-159] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #159
// [v1-draft-rule-160] Kiem tra dieu kien dang ky su kien, gioi han so luong va hang cho Waitlist #160
