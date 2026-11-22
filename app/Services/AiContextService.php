<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Registration;
use PDO;

/**
 * AiContextService - Truy xuất ngữ cảnh dữ liệu thực tế từ MySQL cho Gió AI
 * Đảm bảo kiểm soát quyền riêng tư (RBAC):
 * - Khách (chưa đăng nhập): Chỉ truy xuất dữ liệu công khai (sự kiện, lịch hiến máu, tổ chức, thống kê cộng đồng).
 * - Thành viên đã đăng nhập: Được phép truy xuất thêm dữ liệu cá nhân của chính tài khoản đang đăng nhập.
 * - Tuyệt đối không truy xuất mật khẩu, secret token hoặc thông tin cá nhân của người dùng khác.
 */
class AiContextService
{
    private PDO $db;
    private Event $eventModel;
    private Organization $orgModel;
    private Registration $registrationModel;
    private Certificate $certificateModel;
    private StatisticsService $statsService;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->eventModel = new Event();
        $this->orgModel = new Organization();
        $this->registrationModel = new Registration();
        $this->certificateModel = new Certificate();
        $this->statsService = new StatisticsService();
    }

    /**
     * Xây dựng toàn bộ gói ngữ cảnh thực tế từ CSDL theo câu hỏi và phiên đăng nhập hiện tại
     */
    public function buildContext(string $userMessage, ?array $authUser, array $chatHistory = []): array
    {
        $intent = $this->analyzeIntent($userMessage, $chatHistory);
        $publicData = $this->getPublicDatabaseContext($userMessage, $intent);
        $userContext = $this->getAuthenticatedUserContext($authUser);

        return [
            'intent' => $intent,
            'is_authenticated' => $authUser !== null,
            'current_time' => date('Y-m-d H:i:s'),
            'public' => $publicData,
            'personal' => $userContext,
        ];
    }

    /**
     * Phân tích ý định câu hỏi (Intent) kết hợp ngữ cảnh hội thoại trước đó
     */
    public function analyzeIntent(string $message, array $chatHistory = []): array
    {
        $lower = mb_strtolower(trim($message), 'UTF-8');

        // Kết hợp tin nhắn gần nhất nếu câu hỏi hiện tại là câu hỏi nối tiếp ngắn (follow-up)
        $recentContextText = '';
        if (!empty($chatHistory)) {
            $lastTurns = array_slice($chatHistory, -4);
            foreach ($lastTurns as $turn) {
                $recentContextText .= ' ' . mb_strtolower((string)($turn['content'] ?? ''), 'UTF-8');
            }
        }

        $isPersonalQuery = $this->matchesAny($lower, [
            'của tôi', 'của mình', 'tôi đã đăng ký', 'mình đã đăng ký', 'lịch sử hoạt động',
            'lịch sử tham gia', 'giờ của tôi', 'giờ của mình', 'chứng nhận của tôi',
            'chứng nhận của mình', 'mã qr của tôi', 'tài khoản của tôi', 'hồ sơ của tôi',
            'tôi có bao nhiêu giờ', 'mình có bao nhiêu giờ', 'sự kiện tôi tham gia'
        ]);

        $isOtherUserPrivacyQuery = $this->matchesAny($lower, [
            'mật khẩu của', 'password của', 'số điện thoại của người khác', 'email của bạn',
            'thông tin cá nhân của người khác', 'danh sách mật khẩu', 'token của người khác',
            'tài khoản của người khác', 'xem trộm', 'hack'
        ]);

        $isMutationAttempt = $this->matchesAny($lower, [
            'xóa sự kiện giúp tôi', 'duyệt sự kiện giúp', 'khóa tài khoản này', 'cộng giờ cho tôi',
            'tự động cấp chứng nhận cho tôi', 'thay đổi mật khẩu admin', 'xóa database', 'drop table'
        ]);

        $isBloodQuery = $this->matchesAny($lower, [
            'hiến máu', 'giọt hồng', 'nhóm máu', 'truyền máu', 'huyết học', 'lịch hiến máu', 'ca hiến máu'
        ]);

        $isEventQuery = $isBloodQuery || $this->matchesAny($lower, [
            'sự kiện', 'chương trình', 'hoạt động', 'chiến dịch', 'sắp tới', 'đang mở',
            'tình nguyện nào', 'tham gia gì', 'môi trường', 'sài gòn xanh', 'trồng rừng',
            'bếp ăn', 'lớp học', 'sơ cấp cứu', 'còn chỗ', 'địa điểm', 'tổ chức ở đâu', 'khi nào'
        ]);

        $isRegistrationGuide = $this->matchesAny($lower, [
            'làm sao để đăng ký', 'cách đăng ký', 'hướng dẫn đăng ký', 'muốn tham gia',
            'đăng ký tham gia', 'danh sách chờ', 'waitlist', 'hủy đăng ký', 'hết chỗ', 'trùng lịch'
        ]);

        $isQrAttendanceGuide = $this->matchesAny($lower, [
            'điểm danh', 'mã qr', 'qr code', 'check-in', 'check in', 'check-out', 'check out', 'quét mã', 'quét qr'
        ]);

        $isHoursAndCertificateGuide = $this->matchesAny($lower, [
            'giấy chứng nhận', 'chứng nhận', 'certificate', 'pdf', 'xác minh chứng nhận',
            'tính giờ', 'giờ tình nguyện', 'duyệt giờ', 'cộng giờ'
        ]);

        $isOrganizationQuery = $this->matchesAny($lower, [
            'đơn vị tổ chức', 'câu lạc bộ', 'clb', 'tổ chức nào', 'ban tổ chức', 'xác minh tổ chức'
        ]);

        $isLeaderboardQuery = $this->matchesAny($lower, [
            'bảng xếp hạng', 'top tình nguyện', 'vinh danh', 'nhiều giờ nhất', 'xếp hạng'
        ]);

        $isFollowUpEventRef = $this->matchesAny($lower, [
            'sự kiện đó', 'chương trình đó', 'hoạt động đó', 'sự kiện đầu tiên', 'sự kiện thứ 2',
            'sự kiện số', 'chi tiết hơn', 'còn bao nhiêu chỗ', 'ở đâu vậy', 'thời gian nào', 'đăng ký sự kiện này'
        ]) && ($this->matchesAny($recentContextText, ['sự kiện', 'hiến máu', 'chương trình', '/events/']));

        return [
            'is_personal_query' => $isPersonalQuery,
            'is_other_user_privacy_query' => $isOtherUserPrivacyQuery,
            'is_mutation_attempt' => $isMutationAttempt,
            'is_blood_query' => $isBloodQuery,
            'is_event_query' => $isEventQuery || $isFollowUpEventRef,
            'is_registration_guide' => $isRegistrationGuide,
            'is_qr_attendance_guide' => $isQrAttendanceGuide,
            'is_hours_certificate_guide' => $isHoursAndCertificateGuide,
            'is_organization_query' => $isOrganizationQuery,
            'is_leaderboard_query' => $isLeaderboardQuery,
            'is_followup_event_ref' => $isFollowUpEventRef,
        ];
    }

    /**
     * Truy xuất dữ liệu công khai từ MySQL (Thống kê, Sự kiện, Lịch hiến máu, Tổ chức, Bảng xếp hạng)
     */
    public function getPublicDatabaseContext(string $userMessage, array $intent): array
    {
        $communityStats = $this->statsService->getCommunityStats();
        $categories = $this->eventModel->getCategories();

        // Lấy toàn bộ sự kiện công khai (Published, Ongoing, Completed) từ MySQL
        $allPublicEvents = $this->db->query(
            "SELECT e.id, e.title, e.short_description, e.location, e.province,
                    e.start_time, e.end_time, e.registration_deadline,
                    e.max_volunteers, e.registered_count, e.max_hours, e.status,
                    c.name AS category_name, c.is_blood_donation,
                    o.name AS organization_name
             FROM events e
             INNER JOIN event_categories c ON c.id = e.category_id
             INNER JOIN organizations o ON o.id = e.organization_id
             WHERE e.status IN ('Published', 'Ongoing', 'Completed')
             ORDER BY CASE WHEN e.status = 'Published' THEN 0 WHEN e.status = 'Ongoing' THEN 1 ELSE 2 END ASC,
                      e.start_time ASC
             LIMIT 15"
        )->fetchAll();

        $formattedEvents = [];
        $bloodEvents = [];
        $matchedEvents = [];
        $lowerMsg = mb_strtolower($userMessage, 'UTF-8');

        foreach ($allPublicEvents as $ev) {
            $maxVol = (int)$ev['max_volunteers'];
            $regCount = (int)$ev['registered_count'];
            $remaining = max(0, $maxVol - $regCount);
            $item = [
                'id' => (int)$ev['id'],
                'title' => $ev['title'],
                'category' => $ev['category_name'],
                'is_blood_donation' => (bool)$ev['is_blood_donation'],
                'organization' => $ev['organization_name'],
                'start_time' => date('d/m/Y H:i', strtotime((string)$ev['start_time'])),
                'end_time' => date('d/m/Y H:i', strtotime((string)$ev['end_time'])),
                'deadline' => !empty($ev['registration_deadline']) ? date('d/m/Y H:i', strtotime((string)$ev['registration_deadline'])) : 'Trước giờ bắt đầu',
                'location' => $ev['location'],
                'province' => $ev['province'] ?? 'TP. Hồ Chí Minh',
                'max_volunteers' => $maxVol,
                'registered_count' => $regCount,
                'remaining_slots' => $remaining,
                'registration_state' => $ev['status'] === 'Completed'
                    ? 'Đã hoàn thành'
                    : ($remaining > 0 ? "Còn {$remaining}/{$maxVol} chỗ" : "Đã đủ {$maxVol} chỗ (Đăng ký mới sẽ vào Danh sách chờ Waitlist)"),
                'max_hours' => (float)$ev['max_hours'],
                'status' => $ev['status'],
                'url' => '/events/' . (int)$ev['id'],
            ];

            $formattedEvents[] = $item;
            if ($item['is_blood_donation']) {
                $bloodEvents[] = $item;
            }

            // Kiểm tra khớp từ khóa cụ thể trong câu hỏi người dùng
            if ($this->eventMatchesQuery($ev, $lowerMsg)) {
                $matchedEvents[] = $item;
            }
        }

        $organizations = $this->orgModel->getAll();
        $formattedOrgs = array_map(static function (array $org): array {
            return [
                'id' => (int)$org['id'],
                'name' => $org['name'],
                'address' => $org['address'],
                'verification_status' => $org['verification_status'],
                'total_events' => (int)($org['total_events'] ?? 0),
                'url' => '/organizations/' . (int)$org['id'],
            ];
        }, array_slice($organizations, 0, 8));

        $topVolunteers = array_map(static function (array $vol): array {
            return [
                'full_name' => $vol['full_name'],
                'confirmed_hours' => (float)$vol['confirmed_hours'],
                'events_attended' => (int)$vol['events_attended'],
            ];
        }, $this->statsService->getTopVolunteers('all', 5));

        return [
            'community_stats' => $communityStats,
            'categories' => array_map(static fn(array $c) => [
                'name' => $c['name'],
                'event_count' => (int)($c['event_count'] ?? 0),
                'is_blood_donation' => (bool)$c['is_blood_donation'],
            ], $categories),
            'events' => $formattedEvents,
            'blood_events' => $bloodEvents,
            'matched_events' => $matchedEvents,
            'organizations' => $formattedOrgs,
            'top_volunteers' => $topVolunteers,
        ];
    }

    /**
     * Truy xuất dữ liệu cá nhân của CHÍNH người dùng đang đăng nhập (Kiểm tra quyền nghiêm ngặt)
     */
    public function getAuthenticatedUserContext(?array $authUser): ?array
    {
        if (!$authUser || empty($authUser['id'])) {
            return null;
        }

        $userId = (int)$authUser['id'];
        $roleSlug = (string)($authUser['role_slug'] ?? 'volunteer');

        $profile = [
            'user_id' => $userId,
            'full_name' => (string)($authUser['full_name'] ?? ''),
            'role_slug' => $roleSlug,
            'role_name' => match ($roleSlug) {
                'admin' => 'Quản trị viên',
                'organizer' => 'Đơn vị tổ chức',
                default => 'Tình nguyện viên',
            },
            'dashboard_url' => match ($roleSlug) {
                'admin' => '/admin/dashboard',
                'organizer' => '/organizer/dashboard',
                default => '/volunteer/dashboard',
            },
        ];

        if ($roleSlug === 'volunteer') {
            $stats = $this->statsService->getVolunteerDashboardStats($userId);
            $registrations = $this->registrationModel->getByUser($userId);
            $certificates = $this->certificateModel->getByUser($userId);

            $formattedRegs = [];
            foreach (array_slice($registrations, 0, 10) as $reg) {
                $formattedRegs[] = [
                    'registration_code' => $reg['registration_code'],
                    'event_id' => (int)$reg['event_id'],
                    'event_title' => $reg['event_title'],
                    'start_time' => date('d/m/Y H:i', strtotime((string)$reg['start_time'])),
                    'location' => $reg['location'],
                    'status' => $reg['status'],
                    'waitlist_position' => $reg['waitlist_position'] ? (int)$reg['waitlist_position'] : null,
                    'check_in_time' => $reg['check_in_time'] ?? null,
                    'check_out_time' => $reg['check_out_time'] ?? null,
                    'approved_hours' => $reg['approved_hours'] !== null ? (float)$reg['approved_hours'] : null,
                    'hours_status' => $reg['hours_status'] ?? null,
                    'certificate_code' => $reg['certificate_code'] ?? null,
                    'verification_code' => $reg['verification_code'] ?? null,
                    'blood_time_slot' => $reg['time_slot'] ?? null,
                ];
            }

            $formattedCerts = [];
            foreach ($certificates as $cert) {
                $issuedTs = !empty($cert['issued_date']) ? strtotime((string)$cert['issued_date']) : false;
                $formattedCerts[] = [
                    'certificate_code' => $cert['certificate_code'],
                    'verification_code' => $cert['verification_code'],
                    'event_title' => $cert['event_title'],
                    'hours_confirmed' => (float)($cert['verified_hours'] ?? 0),
                    'issued_at' => $issuedTs ? date('d/m/Y', $issuedTs) : (string)($cert['issued_date'] ?? ''),
                    'status' => $cert['status'],
                    'verify_url' => '/certificate/verify/' . $cert['verification_code'],
                    'download_url' => '/certificate/download/' . $cert['verification_code'],
                ];
            }

            $profile['volunteer_stats'] = [
                'total_approved_hours' => (float)($stats['total_hours'] ?? 0),
                'events_attended' => (int)($stats['events_attended'] ?? 0),
                'active_registrations' => (int)($stats['active_registrations'] ?? 0),
                'waitlisted_count' => (int)($stats['waitlisted_count'] ?? 0),
                'certificates_count' => (int)($stats['certificates_count'] ?? 0),
            ];
            $profile['registrations'] = $formattedRegs;
            $profile['certificates'] = $formattedCerts;
        } elseif ($roleSlug === 'organizer') {
            $org = $this->orgModel->findByOwnerUserId($userId);
            if ($org) {
                $orgEvents = $this->eventModel->getByOrganization((int)$org['id']);
                $profile['organization'] = [
                    'id' => (int)$org['id'],
                    'name' => $org['name'],
                    'verification_status' => $org['verification_status'],
                    'total_events' => count($orgEvents),
                    'events' => array_map(static fn(array $e) => [
                        'id' => (int)$e['id'],
                        'title' => $e['title'],
                        'status' => $e['status'],
                        'registered_count' => (int)$e['registered_count'],
                        'max_volunteers' => (int)$e['max_volunteers'],
                        'scanner_url' => '/organizer/events/' . (int)$e['id'] . '/scanner',
                    ], array_slice($orgEvents, 0, 8)),
                ];
            }
        }

        return $profile;
    }

    private function eventMatchesQuery(array $ev, string $lowerMsg): bool
    {
        if (mb_strlen($lowerMsg, 'UTF-8') < 3) {
            return false;
        }
        $titleLower = mb_strtolower((string)$ev['title'], 'UTF-8');
        $catLower = mb_strtolower((string)$ev['category_name'], 'UTF-8');
        $locLower = mb_strtolower((string)$ev['location'], 'UTF-8');
        $orgLower = mb_strtolower((string)$ev['organization_name'], 'UTF-8');

        $keywords = [
            'hiến máu', 'môi trường', 'kênh', 'rạch', 'rác', 'rừng ngập mặn', 'cần giờ',
            'lớp học', 'sách', 'giáo dục', 'bếp ăn', 'bệnh viện', 'người cao tuổi', 'sơ cấp cứu',
            'thủ đức', 'quận 5', 'bình thạnh', 'quận 8', 'tân bình', 'đhqg', 'sài gòn xanh', 'chữ thập đỏ'
        ];

        foreach ($keywords as $kw) {
            if (mb_strpos($lowerMsg, $kw) !== false) {
                if (
                    mb_strpos($titleLower, $kw) !== false ||
                    mb_strpos($catLower, $kw) !== false ||
                    mb_strpos($locLower, $kw) !== false ||
                    mb_strpos($orgLower, $kw) !== false
                ) {
                    return true;
                }
            }
        }

        if (preg_match('/(?:sự kiện|mã|số|#)\s*(\d+)/u', $lowerMsg, $m)) {
            if ((int)$m[1] === (int)$ev['id']) {
                return true;
            }
        }

        return false;
    }

    private function matchesAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}
