<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Review;
use App\Repositories\EventRepository;

class EventController extends Controller
{
    private Event $eventModel;
    private EventRepository $eventRepo;
    private Registration $regModel;
    private Review $reviewModel;

    public function __construct()
    {
        $this->eventModel = new Event();
        $this->eventRepo = new EventRepository($this->eventModel);
        $this->regModel = new Registration();
        $this->reviewModel = new Review();
    }

    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'keyword' => $_GET['keyword'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'category_slug' => $_GET['category'] ?? '',
            'province' => $_GET['province'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'sort' => $_GET['sort'] ?? 'upcoming',
        ];

        $catalog = $this->eventRepo->paginateCatalog($filters, $page, 6);
        $categories = $this->eventModel->getCategories();

        $this->render('events/index', [
            'pageTitle' => 'Khám phá Sự kiện Thiện nguyện & Hiến máu – Góp Gió Volunteer',
            'events' => $catalog['items'],
            'pagination' => $catalog,
            'filters' => $filters,
            'categories' => $categories,
        ]);
    }

    public function show(string $id): void
    {
        $eventId = (int)$id;
        $event = $this->eventRepo->findWithDetails($eventId);
        if (!$event) {
            http_response_code(404);
            $this->render('layouts/404', ['pageTitle' => 'Không tìm thấy sự kiện']);
            return;
        }

        // Only allow viewing Draft/Pending/Rejected events if owner Organizer or Admin
        if (in_array($event['status'], ['Draft', 'Pending', 'Rejected'], true)) {
            $user = Session::user();
            $canPreview = $user && (
                $user['role_slug'] === 'admin'
                || (int)$user['id'] === (int)$event['created_by']
                || (int)$user['id'] === (int)$event['organization_owner_id']
            );
            if (!$canPreview) {
                Session::setFlash('warning', 'Sự kiện này đang chờ phê duyệt và chưa được công khai.');
                $this->redirect('/events');
            }
        }

        $userRegistration = null;
        if (is_logged_in()) {
            $userRegistration = $this->regModel->findByEventAndUser($eventId, (int)Session::userId());
        }

        $reviews = $this->reviewModel->getVisibleByEvent($eventId);
        $avgRating = 5.0;
        if (!empty($reviews)) {
            $sum = array_sum(array_column($reviews, 'rating'));
            $avgRating = round($sum / count($reviews), 1);
        }

        $this->render('events/show', [
            'pageTitle' => $event['title'] . ' – Góp Gió Volunteer',
            'event' => $event,
            'userRegistration' => $userRegistration,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
        ]);
    }

    public function submitReview(string $id): void
    {
        $user = AuthMiddleware::requireRoles(['volunteer']);
        $eventId = (int)$id;

        try {
            $event = $this->eventModel->findById($eventId);
            if (!$event) {
                throw new \RuntimeException('Không tìm thấy sự kiện.');
            }

            $reg = $this->regModel->findByEventAndUser($eventId, (int)$user['id']);
            if (!$reg || $reg['status'] !== 'Attended') {
                throw new \RuntimeException('Chỉ những tình nguyện viên đã tham gia (Attended) sự kiện mới được gửi đánh giá.');
            }

            if ($this->reviewModel->findByRegistrationId((int)$reg['id']) !== null) {
                throw new \RuntimeException('Bạn đã đánh giá sự kiện này rồi. Mỗi lượt tham gia chỉ được đánh giá 1 lần.');
            }

            $rating = (int)($_POST['rating'] ?? 5);
            $comment = trim((string)($_POST['comment'] ?? ''));
            if ($comment === '') {
                throw new \InvalidArgumentException('Vui lòng nhập nội dung nhận xét.');
            }

            $this->reviewModel->createReview([
                'event_id' => $eventId,
                'organization_id' => (int)$event['organization_id'],
                'user_id' => (int)$user['id'],
                'registration_id' => (int)$reg['id'],
                'rating' => $rating,
                'comment' => $comment,
            ]);

            Session::setFlash('success', 'Cảm ơn bạn đã gửi đánh giá cho sự kiện!');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('/events/' . $eventId);
    }

    public function submitReport(string $id): void
    {
        $user = AuthMiddleware::requireAuth();
        $eventId = (int)$id;

        try {
            $reason = trim((string)($_POST['reason'] ?? ''));
            $details = trim((string)($_POST['details'] ?? ''));
            if ($reason === '') {
                throw new \InvalidArgumentException('Vui lòng chọn hoặc nhập lý do báo cáo.');
            }

            $this->reviewModel->createReport([
                'reporter_id' => (int)$user['id'],
                'target_type' => $_POST['target_type'] ?? 'Event',
                'target_id' => $eventId,
                'reason' => $reason,
                'details' => $details,
            ]);

            Session::setFlash('success', 'Đã gửi báo cáo đến Quản trị viên hệ thống để kiểm tra.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('/events/' . $eventId);
    }
}
