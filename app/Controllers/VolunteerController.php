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
