<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Registration;
use App\Services\AttendanceService;
use App\Services\CertificateService;

class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;
    private CertificateService $certService;
    private Event $eventModel;
    private Organization $orgModel;
    private Registration $regModel;
    private AuditLog $auditModel;

    public function __construct()
    {
        $this->attendanceService = new AttendanceService();
        $this->certService = new CertificateService();
        $this->eventModel = new Event();
        $this->orgModel = new Organization();
        $this->regModel = new Registration();
        $this->auditModel = new AuditLog();
    }

    public function scanner(string $eventId): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        $eid = (int)$eventId;
        $event = $this->eventModel->findById($eid);

        if (!$event) {
            Session::setFlash('error', 'Không tìm thấy sự kiện.');
            $this->redirect('/organizer/dashboard');
        }

        if ($user['role_slug'] === 'organizer') {
            $org = $this->orgModel->findByOwnerUserId((int)$user['id']);
            if (!$org || (int)$org['id'] !== (int)$event['organization_id']) {
                Session::setFlash('error', 'Bạn không có quyền truy cập trang điểm danh của tổ chức khác.');
                $this->redirect('/organizer/dashboard');
            }
        }

        $participants = $this->regModel->getByEvent($eid);

        $this->render('attendance/scanner', [
            'pageTitle' => 'Quét QR Điểm danh & Quản lý Giờ – ' . $event['title'],
            'event' => $event,
            'participants' => $participants,
        ]);
    }

    public function apiScan(): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);

        try {
            $action = strtolower(trim((string)($_POST['scan_mode'] ?? 'checkin')));
            $qrToken = isset($_POST['qr_token']) ? trim((string)$_POST['qr_token']) : null;
            $eventId = !empty($_POST['event_id']) ? (int)$_POST['event_id'] : null;
            $manualRegId = !empty($_POST['registration_id']) ? (int)$_POST['registration_id'] : null;
            $notes = trim((string)($_POST['notes'] ?? ''));

            if ($action === 'checkout') {
                $res = $this->attendanceService->checkOut($user, $qrToken, $eventId, $manualRegId);
                $msg = 'Check-out thành công cho [' . $res['volunteer_name'] . ']! Thời lượng tính toán: ' . $res['calculated_hours'] . ' giờ.';
            } else {
                $res = $this->attendanceService->checkIn($user, $qrToken, $eventId, $manualRegId, null, $notes);
                $msg = 'Check-in thành công cho [' . $res['volunteer_name'] . '] lúc ' . format_datetime($res['check_in_time']) . '!';
            }

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => $msg,
                    'data' => $res,
                ]);
            }

            Session::setFlash('success', $msg);
        } catch (\Throwable $e) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::setFlash('error', $e->getMessage());
        }

        $redirectUrl = !empty($_POST['event_id'])
            ? '/organizer/events/' . (int)$_POST['event_id'] . '/scanner'
            : '/organizer/dashboard';
        $this->redirect($redirectUrl);
    }

    public function approveHours(): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);

        try {
            $vhId = (int)($_POST['volunteer_hour_id'] ?? 0);
            $adjustedHours = isset($_POST['hours']) && $_POST['hours'] !== '' ? (float)$_POST['hours'] : null;
            $reason = !empty($_POST['adjustment_reason']) ? trim((string)$_POST['adjustment_reason']) : null;

            $result = $this->attendanceService->confirmOrAdjustHours($user, $vhId, $adjustedHours, $reason);

            // Auto-issue certificate if system setting auto_issue_certificate is enabled or requested
            if (!empty($_POST['issue_certificate'])) {
                $this->certService->issueCertificate($user, (int)$result['registration_id']);
            }

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Đã xác nhận ' . $result['approved_hours'] . ' giờ tình nguyện thành công.',
                    'data' => $result,
                ]);
            }

            Session::setFlash('success', 'Đã xác nhận ' . $result['approved_hours'] . ' giờ tình nguyện thành công.');
        } catch (\Throwable $e) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/organizer/dashboard');
    }

    public function updateBloodAppointment(): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        try {
            $apptId = (int)($_POST['appointment_id'] ?? 0);
            $status = (string)($_POST['status'] ?? 'Completed');
            $staffName = trim((string)($_POST['medical_staff_confirmed_by'] ?? $user['full_name']));
            $note = trim((string)($_POST['medical_confirmation_note'] ?? ''));
            $volumeMl = !empty($_POST['donation_volume_ml']) ? (int)$_POST['donation_volume_ml'] : 350;

            $this->regModel->updateBloodAppointmentStatus($apptId, $status, $staffName, $note, $volumeMl);
            $this->auditModel->log((int)$user['id'], 'UPDATE_BLOOD_APPOINTMENT', 'BloodDonationAppointment', $apptId, null, [
                'status' => $status,
                'staff' => $staffName,
                'volume_ml' => $volumeMl,
            ]);

            Session::setFlash('success', 'Đã cập nhật kết quả lịch hẹn hiến máu từ đơn vị y tế.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/organizer/dashboard');
    }
}
