<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Review;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\EventService;
use App\Services\StatisticsService;

class AdminController extends Controller
{
    private User $userModel;
    private Organization $orgModel;
    private Event $eventModel;
    private Attendance $attendanceModel;
    private Certificate $certModel;
    private Review $reviewModel;
    private AuditLog $auditModel;
    private Notification $notificationModel;
    private EventService $eventService;
    private AttendanceService $attendanceService;
    private StatisticsService $statsService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->orgModel = new Organization();
        $this->eventModel = new Event();
        $this->attendanceModel = new Attendance();
        $this->certModel = new Certificate();
        $this->reviewModel = new Review();
        $this->auditModel = new AuditLog();
        $this->notificationModel = new Notification();
        $this->eventService = new EventService();
        $this->attendanceService = new AttendanceService();
        $this->statsService = new StatisticsService();
    }

    public function dashboard(): void
    {
        AuthMiddleware::requireRoles(['admin']);

        $adminData = $this->statsService->getAdminDashboardStats();
        $users = $this->userModel->getAllUsers($_GET['q_user'] ?? '', $_GET['role'] ?? '');
        $organizations = $this->orgModel->getAll($_GET['q_org'] ?? '');
        $events = $this->eventModel->getAllForAdmin($_GET['event_status'] ?? '', $_GET['q_event'] ?? '');
        $categories = $this->eventModel->getCategories();
        $volunteerHours = $this->attendanceModel->getAllVolunteerHoursForAdmin();
        $certificates = $this->certModel->getAllForAdmin();
        $reviews = $this->reviewModel->getAllReviewsForAdmin();
        $reports = $this->reviewModel->getAllReportsForAdmin();
        $auditLogs = $this->auditModel->getRecentLogs(60);
        $settingsData = $this->auditModel->getSystemSettings();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Quản trị Hệ thống – Góp Gió Volunteer',
            'adminData' => $adminData,
            'users' => $users,
            'organizations' => $organizations,
            'events' => $events,
            'categories' => $categories,
            'volunteerHours' => $volunteerHours,
            'certificates' => $certificates,
            'reviews' => $reviews,
            'reports' => $reports,
            'auditLogs' => $auditLogs,
            'settings' => $settingsData['rows'],
            'activeTab' => $_GET['tab'] ?? 'overview',
        ]);
    }

    public function toggleUserStatus(string $id): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        $targetId = (int)$id;

        if ($targetId === (int)$admin['id']) {
            Session::setFlash('error', 'Không thể tự khóa tài khoản Quản trị viên đang đăng nhập.');
            $this->redirect('/admin/dashboard?tab=users');
        }

        $targetUser = $this->userModel->findById($targetId);
        if ($targetUser) {
            $newStatus = ($targetUser['status'] === 'Active') ? 'Locked' : 'Active';
            $this->userModel->updateStatus($targetId, $newStatus);
            $this->auditModel->log((int)$admin['id'], 'ADMIN_USER_STATUS', 'User', $targetId, ['status' => $targetUser['status']], ['status' => $newStatus]);
            Session::setFlash('success', 'Đã cập nhật trạng thái tài khoản [' . $targetUser['full_name'] . '] thành ' . $newStatus . '.');
        }
        $this->redirect('/admin/dashboard?tab=users');
    }

    public function verifyOrganization(string $id): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        $orgId = (int)$id;
        $status = (string)($_POST['verification_status'] ?? 'Verified');
        $feedback = trim((string)($_POST['admin_feedback'] ?? ''));

        $org = $this->orgModel->findById($orgId);
        if ($org) {
            $this->orgModel->updateVerificationStatus($orgId, $status, (int)$admin['id'], $feedback);
            $this->notificationModel->create(
                (int)$org['owner_user_id'],
                'Cập nhật trạng thái xác minh tổ chức: ' . $status,
                'Tổ chức "' . $org['name'] . '" đã được Admin cập nhật trạng thái xác minh thành [' . $status . ']. ' . ($feedback ? 'Ghi chú: ' . $feedback : ''),
                'System',
                '/organizer/dashboard?tab=organization'
            );
            $this->auditModel->log((int)$admin['id'], 'ADMIN_VERIFY_ORG', 'Organization', $orgId, ['verification_status' => $org['verification_status']], ['verification_status' => $status, 'feedback' => $feedback]);
            Session::setFlash('success', 'Đã cập nhật trạng thái xác minh cho tổ chức "' . $org['name'] . '".');
        }
        $this->redirect('/admin/dashboard?tab=organizations');
    }

    public function reviewEvent(string $id): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        $eventId = (int)$id;
        try {
            $decision = (string)($_POST['decision'] ?? 'approve');
            $reason = trim((string)($_POST['rejection_reason'] ?? ''));
            $this->eventService->reviewEvent($admin, $eventId, $decision, $reason ?: null);
            Session::setFlash('success', 'Đã xử lý phê duyệt sự kiện #' . $eventId . ' thành công.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }
        $this->redirect('/admin/dashboard?tab=events');
    }

    public function saveCategory(): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        $db = Database::getConnection();
        $name = trim((string)($_POST['name'] ?? ''));
        $icon = trim((string)($_POST['icon'] ?? 'fa-hand-holding-heart'));
        $color = trim((string)($_POST['color_badge'] ?? '#16A34A'));
        $desc = trim((string)($_POST['description'] ?? ''));
        $isBlood = !empty($_POST['is_blood_donation']) ? 1 : 0;

        if ($name !== '') {
            $slug = slugify($name);
            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare(
                "INSERT INTO event_categories (name, slug, icon, color_badge, description, is_blood_donation, display_order, status, created_at, updated_at)
                 VALUES (:name, :slug, :icon, :color, :desc, :blood, 10, 'Active', :now, :now)"
            );
            $stmt->execute([
                'name' => $name,
                'slug' => $slug . '-' . random_int(10, 99),
                'icon' => $icon,
                'color' => $color,
                'desc' => $desc,
                'blood' => $isBlood,
                'now' => $now,
            ]);
            $this->auditModel->log((int)$admin['id'], 'CREATE_CATEGORY', 'EventCategory', (int)$db->lastInsertId(), null, ['name' => $name]);
            Session::setFlash('success', 'Đã thêm danh mục hoạt động "' . $name . '".');
        }
        $this->redirect('/admin/dashboard?tab=categories');
    }

    public function adjustHours(): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        try {
            $vhId = (int)($_POST['volunteer_hour_id'] ?? 0);
            $hours = (float)($_POST['hours'] ?? 0);
            $reason = trim((string)($_POST['adjustment_reason'] ?? 'Admin điều chỉnh giờ tình nguyện'));
            if ($reason === '') {
                throw new \InvalidArgumentException('Admin bắt buộc phải nhập lý do khi điều chỉnh giờ tình nguyện.');
            }
            $this->attendanceService->confirmOrAdjustHours($admin, $vhId, $hours, $reason);
            Session::setFlash('success', 'Đã cập nhật & phê duyệt giờ tình nguyện #' . $vhId . '.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }
        $this->redirect('/admin/dashboard?tab=hours');
    }

    public function toggleCertificateStatus(string $id): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        $certId = (int)$id;
        $cert = $this->certModel->findById($certId);
        if ($cert) {
            $newStatus = ($cert['status'] === 'Valid') ? 'Revoked' : 'Valid';
            $reason = trim((string)($_POST['revoked_reason'] ?? 'Thu hồi theo quyết định kiểm tra của Quản trị viên'));
            $this->certModel->updateStatus($certId, $newStatus, $newStatus === 'Revoked' ? $reason : null);
            $this->auditModel->log((int)$admin['id'], 'ADMIN_CERT_STATUS', 'Certificate', $certId, ['status' => $cert['status']], ['status' => $newStatus, 'reason' => $reason]);
            Session::setFlash('success', 'Đã cập nhật trạng thái chứng nhận [' . $cert['certificate_code'] . '] sang ' . $newStatus . '.');
        }
        $this->redirect('/admin/dashboard?tab=certificates');
    }

    public function moderateReview(string $id): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        $reviewId = (int)$id;
        $status = (string)($_POST['status'] ?? 'Hidden');
        $note = trim((string)($_POST['moderation_note'] ?? 'Vi phạm quy chuẩn cộng đồng'));
        $this->reviewModel->moderateReview($reviewId, $status, (int)$admin['id'], $note);
        $this->auditModel->log((int)$admin['id'], 'MODERATE_REVIEW', 'Review', $reviewId, null, ['status' => $status, 'note' => $note]);
        Session::setFlash('success', 'Đã cập nhật trạng thái đánh giá #' . $reviewId . '.');
        $this->redirect('/admin/dashboard?tab=reviews');
    }

    public function resolveReport(string $id): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        $reportId = (int)$id;
        $status = (string)($_POST['status'] ?? 'Resolved');
        $note = trim((string)($_POST['resolution_note'] ?? 'Đã kiểm tra và xử lý'));
        $this->reviewModel->resolveReport($reportId, $status, (int)$admin['id'], $note);
        $this->auditModel->log((int)$admin['id'], 'RESOLVE_REPORT', 'Report', $reportId, null, ['status' => $status, 'note' => $note]);
        Session::setFlash('success', 'Đã xử lý báo cáo vi phạm #' . $reportId . '.');
        $this->redirect('/admin/dashboard?tab=reports');
    }

    public function broadcastNotification(): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        $title = trim((string)($_POST['title'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));
        $targetRole = (string)($_POST['target_role'] ?? '');

        if ($title !== '' && $message !== '') {
            $users = $this->userModel->getAllUsers('', $targetRole, 'Active');
            foreach ($users as $u) {
                $this->notificationModel->create((int)$u['id'], $title, $message, 'System', '/');
            }
            $this->auditModel->log((int)$admin['id'], 'BROADCAST_NOTIFICATION', 'Notification', null, null, [
                'title' => $title,
                'target_role' => $targetRole ?: 'all',
                'recipients' => count($users),
            ]);
            Session::setFlash('success', 'Đã gửi thông báo hệ thống tới ' . count($users) . ' tài khoản.');
        }
        $this->redirect('/admin/dashboard?tab=settings');
    }

    public function updateSettings(): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        if (!empty($_POST['settings']) && is_array($_POST['settings'])) {
            foreach ($_POST['settings'] as $key => $val) {
                $this->auditModel->updateSystemSetting((string)$key, (string)$val, (int)$admin['id']);
            }
            $this->auditModel->log((int)$admin['id'], 'UPDATE_SYSTEM_SETTINGS', 'SystemSettings');
            Session::setFlash('success', 'Đã lưu thiết lập cấu hình hệ thống.');
        }
        $this->redirect('/admin/dashboard?tab=settings');
    }
}
