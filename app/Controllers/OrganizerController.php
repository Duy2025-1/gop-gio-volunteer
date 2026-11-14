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
// [v1-organizer-draft-1] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #1
// [v1-organizer-draft-2] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #2
// [v1-organizer-draft-3] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #3
// [v1-organizer-draft-4] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #4
// [v1-organizer-draft-5] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #5
// [v1-organizer-draft-6] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #6
// [v1-organizer-draft-7] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #7
// [v1-organizer-draft-8] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #8
// [v1-organizer-draft-9] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #9
// [v1-organizer-draft-10] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #10
// [v1-organizer-draft-11] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #11
// [v1-organizer-draft-12] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #12
// [v1-organizer-draft-13] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #13
// [v1-organizer-draft-14] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #14
// [v1-organizer-draft-15] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #15
// [v1-organizer-draft-16] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #16
// [v1-organizer-draft-17] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #17
// [v1-organizer-draft-18] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #18
// [v1-organizer-draft-19] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #19
// [v1-organizer-draft-20] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #20
// [v1-organizer-draft-21] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #21
// [v1-organizer-draft-22] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #22
// [v1-organizer-draft-23] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #23
// [v1-organizer-draft-24] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #24
// [v1-organizer-draft-25] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #25
// [v1-organizer-draft-26] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #26
// [v1-organizer-draft-27] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #27
// [v1-organizer-draft-28] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #28
// [v1-organizer-draft-29] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #29
// [v1-organizer-draft-30] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #30
// [v1-organizer-draft-31] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #31
// [v1-organizer-draft-32] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #32
// [v1-organizer-draft-33] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #33
// [v1-organizer-draft-34] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #34
// [v1-organizer-draft-35] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #35
// [v1-organizer-draft-36] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #36
// [v1-organizer-draft-37] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #37
// [v1-organizer-draft-38] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #38
// [v1-organizer-draft-39] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #39
// [v1-organizer-draft-40] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #40
// [v1-organizer-draft-41] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #41
// [v1-organizer-draft-42] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #42
// [v1-organizer-draft-43] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #43
// [v1-organizer-draft-44] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #44
// [v1-organizer-draft-45] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #45
// [v1-organizer-draft-46] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #46
// [v1-organizer-draft-47] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #47
// [v1-organizer-draft-48] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #48
// [v1-organizer-draft-49] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #49
// [v1-organizer-draft-50] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #50
// [v1-organizer-draft-51] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #51
// [v1-organizer-draft-52] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #52
// [v1-organizer-draft-53] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #53
// [v1-organizer-draft-54] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #54
// [v1-organizer-draft-55] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #55
// [v1-organizer-draft-56] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #56
// [v1-organizer-draft-57] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #57
// [v1-organizer-draft-58] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #58
// [v1-organizer-draft-59] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #59
// [v1-organizer-draft-60] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #60
// [v1-organizer-draft-61] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #61
// [v1-organizer-draft-62] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #62
// [v1-organizer-draft-63] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #63
// [v1-organizer-draft-64] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #64
// [v1-organizer-draft-65] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #65
// [v1-organizer-draft-66] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #66
// [v1-organizer-draft-67] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #67
// [v1-organizer-draft-68] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #68
// [v1-organizer-draft-69] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #69
// [v1-organizer-draft-70] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #70
// [v1-organizer-draft-71] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #71
// [v1-organizer-draft-72] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #72
// [v1-organizer-draft-73] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #73
// [v1-organizer-draft-74] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #74
// [v1-organizer-draft-75] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #75
// [v1-organizer-draft-76] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #76
// [v1-organizer-draft-77] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #77
// [v1-organizer-draft-78] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #78
// [v1-organizer-draft-79] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #79
// [v1-organizer-draft-80] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #80
// [v1-organizer-draft-81] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #81
// [v1-organizer-draft-82] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #82
// [v1-organizer-draft-83] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #83
// [v1-organizer-draft-84] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #84
// [v1-organizer-draft-85] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #85
// [v1-organizer-draft-86] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #86
// [v1-organizer-draft-87] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #87
// [v1-organizer-draft-88] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #88
// [v1-organizer-draft-89] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #89
// [v1-organizer-draft-90] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #90
// [v1-organizer-draft-91] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #91
// [v1-organizer-draft-92] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #92
// [v1-organizer-draft-93] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #93
// [v1-organizer-draft-94] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #94
// [v1-organizer-draft-95] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #95
// [v1-organizer-draft-96] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #96
// [v1-organizer-draft-97] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #97
// [v1-organizer-draft-98] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #98
// [v1-organizer-draft-99] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #99
// [v1-organizer-draft-100] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #100
// [v1-organizer-draft-101] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #101
// [v1-organizer-draft-102] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #102
// [v1-organizer-draft-103] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #103
// [v1-organizer-draft-104] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #104
// [v1-organizer-draft-105] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #105
// [v1-organizer-draft-106] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #106
// [v1-organizer-draft-107] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #107
// [v1-organizer-draft-108] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #108
// [v1-organizer-draft-109] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #109
// [v1-organizer-draft-110] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #110
// [v1-organizer-draft-111] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #111
// [v1-organizer-draft-112] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #112
// [v1-organizer-draft-113] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #113
// [v1-organizer-draft-114] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #114
// [v1-organizer-draft-115] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #115
// [v1-organizer-draft-116] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #116
// [v1-organizer-draft-117] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #117
// [v1-organizer-draft-118] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #118
// [v1-organizer-draft-119] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #119
// [v1-organizer-draft-120] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #120
// [v1-organizer-draft-121] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #121
// [v1-organizer-draft-122] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #122
// [v1-organizer-draft-123] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #123
// [v1-organizer-draft-124] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #124
// [v1-organizer-draft-125] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #125
// [v1-organizer-draft-126] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #126
// [v1-organizer-draft-127] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #127
// [v1-organizer-draft-128] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #128
// [v1-organizer-draft-129] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #129
// [v1-organizer-draft-130] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #130
// [v1-organizer-draft-131] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #131
// [v1-organizer-draft-132] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #132
// [v1-organizer-draft-133] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #133
// [v1-organizer-draft-134] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #134
// [v1-organizer-draft-135] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #135
// [v1-organizer-draft-136] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #136
// [v1-organizer-draft-137] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #137
// [v1-organizer-draft-138] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #138
// [v1-organizer-draft-139] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #139
// [v1-organizer-draft-140] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #140
// [v1-organizer-draft-141] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #141
// [v1-organizer-draft-142] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #142
// [v1-organizer-draft-143] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #143
// [v1-organizer-draft-144] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #144
// [v1-organizer-draft-145] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #145
// [v1-organizer-draft-146] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #146
// [v1-organizer-draft-147] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #147
// [v1-organizer-draft-148] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #148
// [v1-organizer-draft-149] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #149
// [v1-organizer-draft-150] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #150
// [v1-organizer-draft-151] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #151
// [v1-organizer-draft-152] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #152
// [v1-organizer-draft-153] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #153
// [v1-organizer-draft-154] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #154
// [v1-organizer-draft-155] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #155
// [v1-organizer-draft-156] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #156
// [v1-organizer-draft-157] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #157
// [v1-organizer-draft-158] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #158
// [v1-organizer-draft-159] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #159
// [v1-organizer-draft-160] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #160
// [v1-organizer-draft-161] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #161
// [v1-organizer-draft-162] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #162
// [v1-organizer-draft-163] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #163
// [v1-organizer-draft-164] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #164
// [v1-organizer-draft-165] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #165
// [v1-organizer-draft-166] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #166
// [v1-organizer-draft-167] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #167
// [v1-organizer-draft-168] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #168
// [v1-organizer-draft-169] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #169
// [v1-organizer-draft-170] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #170
// [v1-organizer-draft-171] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #171
// [v1-organizer-draft-172] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #172
// [v1-organizer-draft-173] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #173
// [v1-organizer-draft-174] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #174
// [v1-organizer-draft-175] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #175
// [v1-organizer-draft-176] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #176
// [v1-organizer-draft-177] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #177
// [v1-organizer-draft-178] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #178
// [v1-organizer-draft-179] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #179
// [v1-organizer-draft-180] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #180
// [v1-organizer-draft-181] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #181
// [v1-organizer-draft-182] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #182
// [v1-organizer-draft-183] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #183
// [v1-organizer-draft-184] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #184
// [v1-organizer-draft-185] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #185
// [v1-organizer-draft-186] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #186
// [v1-organizer-draft-187] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #187
// [v1-organizer-draft-188] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #188
// [v1-organizer-draft-189] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #189
// [v1-organizer-draft-190] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #190
// [v1-organizer-draft-191] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #191
// [v1-organizer-draft-192] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #192
// [v1-organizer-draft-193] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #193
// [v1-organizer-draft-194] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #194
// [v1-organizer-draft-195] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #195
// [v1-organizer-draft-196] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #196
// [v1-organizer-draft-197] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #197
// [v1-organizer-draft-198] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #198
// [v1-organizer-draft-199] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #199
// [v1-organizer-draft-200] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #200
// [v1-organizer-draft-201] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #201
// [v1-organizer-draft-202] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #202
// [v1-organizer-draft-203] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #203
// [v1-organizer-draft-204] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #204
// [v1-organizer-draft-205] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #205
// [v1-organizer-draft-206] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #206
// [v1-organizer-draft-207] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #207
// [v1-organizer-draft-208] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #208
// [v1-organizer-draft-209] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #209
// [v1-organizer-draft-210] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #210
// [v1-organizer-draft-211] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #211
// [v1-organizer-draft-212] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #212
// [v1-organizer-draft-213] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #213
// [v1-organizer-draft-214] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #214
// [v1-organizer-draft-215] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #215
// [v1-organizer-draft-216] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #216
// [v1-organizer-draft-217] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #217
// [v1-organizer-draft-218] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #218
// [v1-organizer-draft-219] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #219
// [v1-organizer-draft-220] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #220
// [v1-organizer-draft-221] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #221
// [v1-organizer-draft-222] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #222
// [v1-organizer-draft-223] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #223
// [v1-organizer-draft-224] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #224
// [v1-organizer-draft-225] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #225
// [v1-organizer-draft-226] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #226
// [v1-organizer-draft-227] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #227
// [v1-organizer-draft-228] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #228
// [v1-organizer-draft-229] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #229
// [v1-organizer-draft-230] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #230
// [v1-organizer-draft-231] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #231
// [v1-organizer-draft-232] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #232
// [v1-organizer-draft-233] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #233
// [v1-organizer-draft-234] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #234
// [v1-organizer-draft-235] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #235
// [v1-organizer-draft-236] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #236
// [v1-organizer-draft-237] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #237
// [v1-organizer-draft-238] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #238
// [v1-organizer-draft-239] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #239
// [v1-organizer-draft-240] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #240
// [v1-organizer-draft-241] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #241
// [v1-organizer-draft-242] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #242
// [v1-organizer-draft-243] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #243
// [v1-organizer-draft-244] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #244
// [v1-organizer-draft-245] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #245
// [v1-organizer-draft-246] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #246
// [v1-organizer-draft-247] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #247
// [v1-organizer-draft-248] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #248
// [v1-organizer-draft-249] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #249
// [v1-organizer-draft-250] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #250
// [v1-organizer-draft-251] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #251
// [v1-organizer-draft-252] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #252
// [v1-organizer-draft-253] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #253
// [v1-organizer-draft-254] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #254
// [v1-organizer-draft-255] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #255
// [v1-organizer-draft-256] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #256
// [v1-organizer-draft-257] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #257
// [v1-organizer-draft-258] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #258
// [v1-organizer-draft-259] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #259
// [v1-organizer-draft-260] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #260
// [v1-organizer-draft-261] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #261
// [v1-organizer-draft-262] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #262
// [v1-organizer-draft-263] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #263
// [v1-organizer-draft-264] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #264
// [v1-organizer-draft-265] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #265
// [v1-organizer-draft-266] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #266
// [v1-organizer-draft-267] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #267
// [v1-organizer-draft-268] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #268
// [v1-organizer-draft-269] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #269
// [v1-organizer-draft-270] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #270
// [v1-organizer-draft-271] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #271
// [v1-organizer-draft-272] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #272
// [v1-organizer-draft-273] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #273
// [v1-organizer-draft-274] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #274
// [v1-organizer-draft-275] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #275
// [v1-organizer-draft-276] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #276
// [v1-organizer-draft-277] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #277
// [v1-organizer-draft-278] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #278
// [v1-organizer-draft-279] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #279
// [v1-organizer-draft-280] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #280
// [v1-organizer-draft-281] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #281
// [v1-organizer-draft-282] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #282
// [v1-organizer-draft-283] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #283
// [v1-organizer-draft-284] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #284
// [v1-organizer-draft-285] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #285
// [v1-organizer-draft-286] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #286
// [v1-organizer-draft-287] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #287
// [v1-organizer-draft-288] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #288
// [v1-organizer-draft-289] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #289
// [v1-organizer-draft-290] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #290
