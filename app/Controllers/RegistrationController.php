<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Registration;
use App\Services\AttendanceService;
use App\Services\RegistrationService;

class RegistrationController extends Controller
{
    private RegistrationService $regService;
    private Registration $regModel;
    private AttendanceService $attendanceService;

    public function __construct()
    {
        $this->regService = new RegistrationService();
        $this->regModel = new Registration();
        $this->attendanceService = new AttendanceService();
    }

    public function register(string $eventId): void
    {
        $user = AuthMiddleware::requireRoles(['volunteer']);
        $eid = (int)$eventId;

        try {
            $result = $this->regService->registerForEvent($user, $eid, $_POST);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => $result['status'] === 'Waitlisted'
                        ? 'Sự kiện đã đủ số lượng chính thức. Bạn đã được đưa vào Danh sách chờ (Vị trí #' . $result['waitlist_position'] . ').'
                        : 'Đăng ký tham gia sự kiện thành công! Mã QR điểm danh của bạn đã sẵn sàng.',
                    'data' => $result,
                ]);
            }

            if ($result['status'] === 'Waitlisted') {
                Session::setFlash('warning', 'Sự kiện hiện đã đủ chỗ. Bạn đã được ghi nhận vào Danh sách chờ ở vị trí #' . $result['waitlist_position'] . '.');
            } else {
                Session::setFlash('success', 'Đăng ký tham gia sự kiện thành công! Mã đăng ký: ' . $result['registration_code']);
            }
        } catch (\Throwable $e) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('/events/' . $eid);
    }

    public function cancel(string $registrationId): void
    {
        $user = AuthMiddleware::requireAuth();
        $rid = (int)$registrationId;

        try {
            $reason = trim((string)($_POST['cancel_reason'] ?? 'Thay đổi kế hoạch cá nhân'));
            $result = $this->regService->cancelRegistration($user, $rid, $reason);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Đã hủy đăng ký thành công.',
                    'data' => $result,
                ]);
            }

            Session::setFlash('success', 'Đã hủy đăng ký thành công.' . ($result['promoted_registration_id'] ? ' Hệ thống đã tự động chuyển 1 bạn từ Danh sách chờ lên thay thế.' : ''));
        } catch (\Throwable $e) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/volunteer/dashboard');
    }

    public function qrCodeSvg(string $registrationId): void
    {
        $user = AuthMiddleware::requireAuth();
        $rid = (int)$registrationId;
        $reg = $this->regModel->findById($rid);

        if (!$reg) {
            http_response_code(404);
            exit;
        }

        // Only owner volunteer, event organizer, or admin can view QR
        if ((int)$reg['user_id'] !== (int)$user['id'] && !in_array($user['role_slug'], ['organizer', 'admin'], true)) {
            http_response_code(403);
            exit;
        }

        $svg = $this->attendanceService->generateQrSvg((string)$reg['qr_token'], 240);
        header('Content-Type: image/svg+xml; charset=utf-8');
        echo $svg;
        exit;
    }
}
