<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Registration;
use App\Services\EventService;
use App\Services\StatisticsService;

class OrganizerController extends Controller
{
    private Organization $orgModel;
    private Event $eventModel;
    private Registration $regModel;
    private EventService $eventService;
    private StatisticsService $statsService;
    private Notification $notificationModel;

    public function __construct()
    {
        $this->orgModel = new Organization();
        $this->eventModel = new Event();
        $this->regModel = new Registration();
        $this->eventService = new EventService();
        $this->statsService = new StatisticsService();
        $this->notificationModel = new Notification();
    }

    public function dashboard(): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        $org = $this->orgModel->findByOwnerUserId((int)$user['id']);

        if (!$org && $user['role_slug'] === 'admin') {
            $allOrgs = $this->orgModel->getAll();
            $org = $allOrgs[0] ?? null;
        }

        $events = [];
        $stats = [
            'total_events' => 0,
            'active_events' => 0,
            'total_registrations' => 0,
            'total_attended' => 0,
            'total_hours' => 0,
        ];
        $verifications = [];
        $selectedEvent = null;
        $selectedEventParticipants = [];

        if ($org) {
            $orgId = (int)$org['id'];
            $events = $this->eventModel->getByOrganization($orgId);
            $stats = $this->statsService->getOrganizerDashboardStats($orgId);
            $verifications = $this->orgModel->getVerificationsByOrg($orgId);

            $selectedEventId = !empty($_GET['event_id']) ? (int)$_GET['event_id'] : (isset($events[0]['id']) ? (int)$events[0]['id'] : 0);
            if ($selectedEventId > 0) {
                $ev = $this->eventModel->findById($selectedEventId);
                if ($ev && (int)$ev['organization_id'] === $orgId) {
                    $selectedEvent = $ev;
                    $selectedEventParticipants = $this->regModel->getByEvent($selectedEventId);
                }
            }
        }

        $categories = $this->eventModel->getCategories();
        $notifications = $this->notificationModel->getByUser((int)$user['id'], 15);

        $this->render('organizer/dashboard', [
            'pageTitle' => 'Dashboard Ban Tổ chức – Góp Gió Volunteer',
            'org' => $org,
            'stats' => $stats,
            'events' => $events,
            'verifications' => $verifications,
            'categories' => $categories,
            'selectedEvent' => $selectedEvent,
            'selectedEventParticipants' => $selectedEventParticipants,
            'notifications' => $notifications,
            'activeTab' => $_GET['tab'] ?? 'overview',
        ]);
    }

    public function createEvent(): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        try {
            $coverImage = $_POST['cover_image'] ?? null;
            if (!empty($_FILES['cover_file']['tmp_name'])) {
                $coverImage = handle_secure_upload($_FILES['cover_file'], 'events');
            }

            $reqs = [];
            if (!empty($_POST['requirements_text'])) {
                $reqs = preg_split('/\r\n|\r|\n/', (string)$_POST['requirements_text']) ?: [];
            }

            $input = $_POST;
            $input['cover_image'] = $coverImage;
            $input['requirements'] = $reqs;

            $eventId = $this->eventService->createEvent($user, $input);
            Session::setFlash('success', 'Đã tạo sự kiện thành công! Sự kiện đang ở trạng thái [' . ($input['status'] ?? 'Pending') . '].');
            $this->redirect('/organizer/dashboard?tab=events&event_id=' . $eventId);
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            $this->redirect('/organizer/dashboard?tab=create');
        }
    }

    public function updateEvent(string $id): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        $eventId = (int)$id;
        try {
            $coverImage = !empty($_POST['cover_image']) ? trim((string)$_POST['cover_image']) : null;
            if (!empty($_FILES['cover_file']['tmp_name'])) {
                $coverImage = handle_secure_upload($_FILES['cover_file'], 'events');
            }
            $input = $_POST;
            if ($coverImage !== null) {
                $input['cover_image'] = $coverImage;
            }
            $this->eventService->updateEvent($user, $eventId, $input);
            Session::setFlash('success', 'Đã cập nhật thông tin sự kiện.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }
        $this->redirect('/organizer/dashboard?tab=events&event_id=' . $eventId);
    }

    public function changeEventState(string $id): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        $eventId = (int)$id;
        try {
            $newState = (string)($_POST['status'] ?? 'Pending');
            $this->eventService->changeOrganizerEventState($user, $eventId, $newState);
            Session::setFlash('success', 'Đã chuyển trạng thái sự kiện sang [' . $newState . '].');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }
        $this->redirect('/organizer/dashboard?tab=events&event_id=' . $eventId);
    }

    public function exportCsv(string $eventId): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        $eid = (int)$eventId;
        $event = $this->eventModel->findById($eid);
        if (!$event) {
            http_response_code(404);
            exit;
        }

        if ($user['role_slug'] === 'organizer') {
            $org = $this->orgModel->findByOwnerUserId((int)$user['id']);
            if (!$org || (int)$org['id'] !== (int)$event['organization_id']) {
                http_response_code(403);
                exit;
            }
        }

        $participants = $this->regModel->getByEvent($eid);
        $filename = 'danh_sach_tnv_su_kien_' . $eid . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel Vietnamese compatibility
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Mã Đăng Ký',
            'Họ và Tên',
            'Email',
            'Số Điện Thoại',
            'Nhóm Máu',
            'Trạng Thái Đăng Ký',
            'Giờ Check-in',
            'Giờ Check-out',
            'Số Giờ Tính Toán',
            'Số Giờ Đã Duyệt',
            'Trạng Thái Giờ',
            'Mã Chứng Nhận',
        ]);

        foreach ($participants as $p) {
            fputcsv($out, [
                $p['registration_code'],
                $p['volunteer_name'],
                $p['volunteer_email'],
                $p['volunteer_phone'],
                $p['blood_type'] ?? '',
                $p['status'],
                $p['check_in_time'] ?? '',
                $p['check_out_time'] ?? '',
                $p['calculated_hours'] ?? '0',
                $p['verified_hours'] ?? '0',
                $p['hours_status'] ?? 'Chưa duyệt',
                $p['certificate_code'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }
}
