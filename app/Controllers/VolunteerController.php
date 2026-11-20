<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Notification;
use App\Models\Registration;
use App\Models\User;
use App\Services\StatisticsService;

class VolunteerController extends Controller
{
    private User $userModel;
    private Registration $regModel;
    private Attendance $attendanceModel;
    private Certificate $certModel;
    private Notification $notificationModel;
    private StatisticsService $statsService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->regModel = new Registration();
        $this->attendanceModel = new Attendance();
        $this->certModel = new Certificate();
        $this->notificationModel = new Notification();
        $this->statsService = new StatisticsService();
    }

    public function dashboard(): void
    {
        $sessionUser = AuthMiddleware::requireRoles(['volunteer']);
        $userId = (int)$sessionUser['id'];

        $profile = $this->userModel->findById($userId);
        $stats = $this->statsService->getVolunteerDashboardStats($userId);
        $registrations = $this->regModel->getByUser($userId);
        $attendanceHistory = $this->attendanceModel->getAttendanceHistoryByUser($userId);
        $certificates = $this->certModel->getByUser($userId);
        $notifications = $this->notificationModel->getByUser($userId, 20);

        $upcomingRegistrations = array_values(array_filter(
            $registrations,
            fn($r) => in_array($r['status'], ['Confirmed', 'Waitlisted'], true) && in_array($r['event_status'], ['Published', 'Ongoing'], true)
        ));

        $this->render('volunteer/dashboard', [
            'pageTitle' => 'Dashboard Tình nguyện viên – Góp Gió Volunteer',
            'profile' => $profile,
            'stats' => $stats,
            'registrations' => $registrations,
            'upcomingRegistrations' => $upcomingRegistrations,
            'attendanceHistory' => $attendanceHistory,
            'certificates' => $certificates,
            'notifications' => $notifications,
            'activeTab' => $_GET['tab'] ?? 'overview',
        ]);
    }
}
// [v1-stats-draft-1] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #1
// [v1-stats-draft-2] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #2
// [v1-stats-draft-3] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #3
// [v1-stats-draft-4] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #4
// [v1-stats-draft-5] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #5
// [v1-stats-draft-6] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #6
// [v1-stats-draft-7] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #7
// [v1-stats-draft-8] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #8
// [v1-stats-draft-9] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #9
// [v1-stats-draft-10] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #10
// [v1-stats-draft-11] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #11
// [v1-stats-draft-12] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #12
// [v1-stats-draft-13] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #13
// [v1-stats-draft-14] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #14
// [v1-stats-draft-15] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #15
// [v1-stats-draft-16] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #16
// [v1-stats-draft-17] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #17
// [v1-stats-draft-18] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #18
// [v1-stats-draft-19] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #19
// [v1-stats-draft-20] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #20
// [v1-stats-draft-21] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #21
// [v1-stats-draft-22] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #22
// [v1-stats-draft-23] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #23
// [v1-stats-draft-24] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #24
