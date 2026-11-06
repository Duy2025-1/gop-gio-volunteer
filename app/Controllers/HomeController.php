<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Review;
use App\Services\StatisticsService;

class HomeController extends Controller
{
    public function index(): void
    {
        $statsService = new StatisticsService();
        $eventModel = new Event();
        $reviewModel = new Review();

        $communityStats = $statsService->getCommunityStats();
        $featuredEvents = $eventModel->getFeaturedEvents(6);
        $categories = $eventModel->getCategories();
        $topOrganizations = $statsService->getTopOrganizations(3);
        $topVolunteers = $statsService->getTopVolunteers('all', 5);
        $reviews = $reviewModel->getRecentCommunityReviews(3);

        $this->render('home/index', [
            'pageTitle' => 'Góp Gió Volunteer – Một hành động nhỏ, Ngàn giá trị lớn',
            'communityStats' => $communityStats,
            'featuredEvents' => $featuredEvents,
            'categories' => $categories,
            'topOrganizations' => $topOrganizations,
            'topVolunteers' => $topVolunteers,
            'reviews' => $reviews,
        ]);
    }

    public function about(): void
    {
        $statsService = new StatisticsService();
        $auditModel = new AuditLog();
        $settings = $auditModel->getSystemSettings()['map'];

        $this->render('home/about', [
            'pageTitle' => 'Giới thiệu – Góp Gió Volunteer',
            'communityStats' => $statsService->getCommunityStats(),
            'settings' => $settings,
        ]);
    }

    public function leaderboard(): void
    {
        $statsService = new StatisticsService();
        $period = $_GET['period'] ?? 'all';
        if (!in_array($period, ['month', 'year', 'all'], true)) {
            $period = 'all';
        }

        $this->render('leaderboard/index', [
            'pageTitle' => 'Bảng xếp hạng Tình nguyện viên & Tổ chức – Góp Gió Volunteer',
            'activePeriod' => $period,
            'topMonth' => $statsService->getTopVolunteers('month', 10),
            'topYear' => $statsService->getTopVolunteers('year', 10),
            'topAllTime' => $statsService->getTopVolunteers('all', 10),
            'topOrganizations' => $statsService->getTopOrganizations(10),
        ]);
    }

    public function bloodDonation(): void
    {
        $eventModel = new Event();
        $auditModel = new AuditLog();

        $bloodEvents = $eventModel->searchEvents(['is_blood_donation' => 1], 1, 12)['items'];
        $settings = $auditModel->getSystemSettings()['map'];

        $this->render('blood/index', [
            'pageTitle' => 'Hiến Máu Nhân Đạo – Giọt Hồng Sẻ Chia | Góp Gió Volunteer',
            'bloodEvents' => $bloodEvents,
            'disclaimer' => $settings['blood_donation_disclaimer'] ?? '',
        ]);
    }

    public function health(): void
    {
        $this->json([
            'status' => 'ok',
            'app' => 'Góp Gió Volunteer',
            'timestamp' => date('c'),
        ]);
    }
}
