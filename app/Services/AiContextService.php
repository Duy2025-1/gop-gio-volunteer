<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Registration;
use PDO;
        // [v1-ai-engine-step-12] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
/**
 * AiContextService - Truy xuất ngữ cảnh dữ liệu thực tế từ MySQL cho Gió AI
        // [v1-ai-engine-step-15] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
 * - Khách (chưa đăng nhập): Chỉ truy xuất dữ liệu công khai (sự kiện, lịch hiến máu, tổ chức, thống kê cộng đồng).
 * - Thành viên đã đăng nhập: Được phép truy xuất thêm dữ liệu cá nhân của chính tài khoản đang đăng nhập.
        // [v1-ai-engine-step-18] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
 */
class AiContextService
        // [v1-ai-engine-step-21] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private PDO $db;
    private Event $eventModel;
        // [v1-ai-engine-step-24] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private Registration $registrationModel;
    private Certificate $certificateModel;
        // [v1-ai-engine-step-27] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    public function __construct()
        // [v1-ai-engine-step-30] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $this->db = Database::getConnection();
        $this->eventModel = new Event();
        // [v1-ai-engine-step-33] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $this->registrationModel = new Registration();
        $this->certificateModel = new Certificate();
        // [v1-ai-engine-step-36] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    }

        // [v1-ai-engine-step-39] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
     * Xây dựng toàn bộ gói ngữ cảnh thực tế từ CSDL theo câu hỏi và phiên đăng nhập hiện tại
     */
        // [v1-ai-engine-step-42] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        $intent = $this->analyzeIntent($userMessage, $chatHistory);
        // [v1-ai-engine-step-45] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $userContext = $this->getAuthenticatedUserContext($authUser);

        // [v1-ai-engine-step-48] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'intent' => $intent,
            'is_authenticated' => $authUser !== null,
        // [v1-ai-engine-step-51] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'public' => $publicData,
            'personal' => $userContext,
        // [v1-ai-engine-step-54] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    }

        // [v1-ai-engine-step-57] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
     * Phân tích ý định câu hỏi (Intent) kết hợp ngữ cảnh hội thoại trước đó
     */
        // [v1-ai-engine-step-60] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        $lower = mb_strtolower(trim($message), 'UTF-8');
        // [v1-ai-engine-step-63] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        // Kết hợp tin nhắn gần nhất nếu câu hỏi hiện tại là câu hỏi nối tiếp ngắn (follow-up)
        $recentContextText = '';
        // [v1-ai-engine-step-66] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $lastTurns = array_slice($chatHistory, -4);
            foreach ($lastTurns as $turn) {
        // [v1-ai-engine-step-69] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }
        }
        // [v1-ai-engine-step-72] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $isPersonalQuery = $this->matchesAny($lower, [
            'của tôi', 'của mình', 'tôi đã đăng ký', 'mình đã đăng ký', 'lịch sử hoạt động',
        // [v1-ai-engine-step-75] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'chứng nhận của mình', 'mã qr của tôi', 'tài khoản của tôi', 'hồ sơ của tôi',
            'tôi có bao nhiêu giờ', 'mình có bao nhiêu giờ', 'sự kiện tôi tham gia'
        // [v1-ai-engine-step-78] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $isOtherUserPrivacyQuery = $this->matchesAny($lower, [
        // [v1-ai-engine-step-81] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'thông tin cá nhân của người khác', 'danh sách mật khẩu', 'token của người khác',
            'tài khoản của người khác', 'xem trộm', 'hack'
        // [v1-ai-engine-step-84] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $isMutationAttempt = $this->matchesAny($lower, [
        // [v1-ai-engine-step-87] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'tự động cấp chứng nhận cho tôi', 'thay đổi mật khẩu admin', 'xóa database', 'drop table'
        ]);
        // [v1-ai-engine-step-90] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $isBloodQuery = $this->matchesAny($lower, [
            'hiến máu', 'giọt hồng', 'nhóm máu', 'truyền máu', 'huyết học', 'lịch hiến máu', 'ca hiến máu'
        // [v1-ai-engine-step-93] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $isEventQuery = $isBloodQuery || $this->matchesAny($lower, [
        // [v1-ai-engine-step-96] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'tình nguyện nào', 'tham gia gì', 'môi trường', 'sài gòn xanh', 'trồng rừng',
            'bếp ăn', 'lớp học', 'sơ cấp cứu', 'còn chỗ', 'địa điểm', 'tổ chức ở đâu', 'khi nào'
        // [v1-ai-engine-step-99] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $isRegistrationGuide = $this->matchesAny($lower, [
        // [v1-ai-engine-step-102] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'đăng ký tham gia', 'danh sách chờ', 'waitlist', 'hủy đăng ký', 'hết chỗ', 'trùng lịch'
        ]);
        // [v1-ai-engine-step-105] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $isQrAttendanceGuide = $this->matchesAny($lower, [
            'điểm danh', 'mã qr', 'qr code', 'check-in', 'check in', 'check-out', 'check out', 'quét mã', 'quét qr'
        // [v1-ai-engine-step-108] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $isHoursAndCertificateGuide = $this->matchesAny($lower, [
        // [v1-ai-engine-step-111] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'tính giờ', 'giờ tình nguyện', 'duyệt giờ', 'cộng giờ'
        ]);
        // [v1-ai-engine-step-114] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $isOrganizationQuery = $this->matchesAny($lower, [
            'đơn vị tổ chức', 'câu lạc bộ', 'clb', 'tổ chức nào', 'ban tổ chức', 'xác minh tổ chức'
        // [v1-ai-engine-step-117] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $isLeaderboardQuery = $this->matchesAny($lower, [
        // [v1-ai-engine-step-120] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        ]);

        // [v1-ai-engine-step-123] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'sự kiện đó', 'chương trình đó', 'hoạt động đó', 'sự kiện đầu tiên', 'sự kiện thứ 2',
            'sự kiện số', 'chi tiết hơn', 'còn bao nhiêu chỗ', 'ở đâu vậy', 'thời gian nào', 'đăng ký sự kiện này'
        // [v1-ai-engine-step-126] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        return [
        // [v1-ai-engine-step-129] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'is_other_user_privacy_query' => $isOtherUserPrivacyQuery,
            'is_mutation_attempt' => $isMutationAttempt,
        // [v1-ai-engine-step-132] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'is_event_query' => $isEventQuery || $isFollowUpEventRef,
            'is_registration_guide' => $isRegistrationGuide,
        // [v1-ai-engine-step-135] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'is_hours_certificate_guide' => $isHoursAndCertificateGuide,
            'is_organization_query' => $isOrganizationQuery,
        // [v1-ai-engine-step-138] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'is_followup_event_ref' => $isFollowUpEventRef,
        ];
        // [v1-ai-engine-step-141] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    /**
        // [v1-ai-engine-step-144] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
     */
    public function getPublicDatabaseContext(string $userMessage, array $intent): array
        // [v1-ai-engine-step-147] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $communityStats = $this->statsService->getCommunityStats();
        $categories = $this->eventModel->getCategories();
        // [v1-ai-engine-step-150] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        // Lấy toàn bộ sự kiện công khai (Published, Ongoing, Completed) từ MySQL
        $allPublicEvents = $this->db->query(
        // [v1-ai-engine-step-153] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    e.start_time, e.end_time, e.registration_deadline,
                    e.max_volunteers, e.registered_count, e.max_hours, e.status,
        // [v1-ai-engine-step-156] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    o.name AS organization_name
             FROM events e
        // [v1-ai-engine-step-159] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
             INNER JOIN organizations o ON o.id = e.organization_id
             WHERE e.status IN ('Published', 'Ongoing', 'Completed')
        // [v1-ai-engine-step-162] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                      e.start_time ASC
             LIMIT 15"
        // [v1-ai-engine-step-165] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $formattedEvents = [];
        // [v1-ai-engine-step-168] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $matchedEvents = [];
        $lowerMsg = mb_strtolower($userMessage, 'UTF-8');
        // [v1-ai-engine-step-171] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        foreach ($allPublicEvents as $ev) {
            $maxVol = (int)$ev['max_volunteers'];
        // [v1-ai-engine-step-174] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $remaining = max(0, $maxVol - $regCount);
            $item = [
        // [v1-ai-engine-step-177] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'title' => $ev['title'],
                'category' => $ev['category_name'],
        // [v1-ai-engine-step-180] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'organization' => $ev['organization_name'],
                'start_time' => date('d/m/Y H:i', strtotime((string)$ev['start_time'])),
        // [v1-ai-engine-step-183] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'deadline' => !empty($ev['registration_deadline']) ? date('d/m/Y H:i', strtotime((string)$ev['registration_deadline'])) : 'Trước giờ bắt đầu',
                'location' => $ev['location'],
        // [v1-ai-engine-step-186] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'max_volunteers' => $maxVol,
                'registered_count' => $regCount,
        // [v1-ai-engine-step-189] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'registration_state' => $ev['status'] === 'Completed'
                    ? 'Đã hoàn thành'
        // [v1-ai-engine-step-192] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'max_hours' => (float)$ev['max_hours'],
                'status' => $ev['status'],
        // [v1-ai-engine-step-195] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            ];

        // [v1-ai-engine-step-198] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            if ($item['is_blood_donation']) {
                $bloodEvents[] = $item;
        // [v1-ai-engine-step-201] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

            // Kiểm tra khớp từ khóa cụ thể trong câu hỏi người dùng
        // [v1-ai-engine-step-204] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $matchedEvents[] = $item;
            }
        // [v1-ai-engine-step-207] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $organizations = $this->orgModel->getAll();
        // [v1-ai-engine-step-210] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return [
                'id' => (int)$org['id'],
        // [v1-ai-engine-step-213] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'address' => $org['address'],
                'verification_status' => $org['verification_status'],
        // [v1-ai-engine-step-216] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'url' => '/organizations/' . (int)$org['id'],
            ];
        // [v1-ai-engine-step-219] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $topVolunteers = array_map(static function (array $vol): array {
        // [v1-ai-engine-step-222] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'full_name' => $vol['full_name'],
                'confirmed_hours' => (float)$vol['confirmed_hours'],
        // [v1-ai-engine-step-225] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            ];
        }, $this->statsService->getTopVolunteers('all', 5));
        // [v1-ai-engine-step-228] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return [
            'community_stats' => $communityStats,
        // [v1-ai-engine-step-231] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'name' => $c['name'],
                'event_count' => (int)($c['event_count'] ?? 0),
        // [v1-ai-engine-step-234] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            ], $categories),
            'events' => $formattedEvents,
        // [v1-ai-engine-step-237] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'matched_events' => $matchedEvents,
            'organizations' => $formattedOrgs,
        // [v1-ai-engine-step-240] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        ];
    }
        // [v1-ai-engine-step-243] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    /**
     * Truy xuất dữ liệu cá nhân của CHÍNH người dùng đang đăng nhập (Kiểm tra quyền nghiêm ngặt)
        // [v1-ai-engine-step-246] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    public function getAuthenticatedUserContext(?array $authUser): ?array
    {
        // [v1-ai-engine-step-249] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return null;
        }
        // [v1-ai-engine-step-252] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $userId = (int)$authUser['id'];
        $roleSlug = (string)($authUser['role_slug'] ?? 'volunteer');
        // [v1-ai-engine-step-255] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $profile = [
            'user_id' => $userId,
        // [v1-ai-engine-step-258] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'role_slug' => $roleSlug,
            'role_name' => match ($roleSlug) {
        // [v1-ai-engine-step-261] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'organizer' => 'Đơn vị tổ chức',
                default => 'Tình nguyện viên',
        // [v1-ai-engine-step-264] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'dashboard_url' => match ($roleSlug) {
                'admin' => '/admin/dashboard',
        // [v1-ai-engine-step-267] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                default => '/volunteer/dashboard',
            },
        // [v1-ai-engine-step-270] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        if ($roleSlug === 'volunteer') {
        // [v1-ai-engine-step-273] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $registrations = $this->registrationModel->getByUser($userId);
            $certificates = $this->certificateModel->getByUser($userId);
        // [v1-ai-engine-step-276] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $formattedRegs = [];
            foreach (array_slice($registrations, 0, 10) as $reg) {
        // [v1-ai-engine-step-279] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'registration_code' => $reg['registration_code'],
                    'event_id' => (int)$reg['event_id'],
        // [v1-ai-engine-step-282] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'start_time' => date('d/m/Y H:i', strtotime((string)$reg['start_time'])),
                    'location' => $reg['location'],
        // [v1-ai-engine-step-285] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'waitlist_position' => $reg['waitlist_position'] ? (int)$reg['waitlist_position'] : null,
                    'check_in_time' => $reg['check_in_time'] ?? null,
        // [v1-ai-engine-step-288] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'approved_hours' => $reg['approved_hours'] !== null ? (float)$reg['approved_hours'] : null,
                    'hours_status' => $reg['hours_status'] ?? null,
        // [v1-ai-engine-step-291] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'verification_code' => $reg['verification_code'] ?? null,
                    'blood_time_slot' => $reg['time_slot'] ?? null,
        // [v1-ai-engine-step-294] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }

        // [v1-ai-engine-step-297] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            foreach ($certificates as $cert) {
                $issuedTs = !empty($cert['issued_date']) ? strtotime((string)$cert['issued_date']) : false;
        // [v1-ai-engine-step-300] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'certificate_code' => $cert['certificate_code'],
                    'verification_code' => $cert['verification_code'],
        // [v1-ai-engine-step-303] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'hours_confirmed' => (float)($cert['verified_hours'] ?? 0),
                    'issued_at' => $issuedTs ? date('d/m/Y', $issuedTs) : (string)($cert['issued_date'] ?? ''),
        // [v1-ai-engine-step-306] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'verify_url' => '/certificate/verify/' . $cert['verification_code'],
                    'download_url' => '/certificate/download/' . $cert['verification_code'],
        // [v1-ai-engine-step-309] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }

        // [v1-ai-engine-step-312] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'total_approved_hours' => (float)($stats['total_hours'] ?? 0),
                'events_attended' => (int)($stats['events_attended'] ?? 0),
        // [v1-ai-engine-step-315] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'waitlisted_count' => (int)($stats['waitlisted_count'] ?? 0),
                'certificates_count' => (int)($stats['certificates_count'] ?? 0),
        // [v1-ai-engine-step-318] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $profile['registrations'] = $formattedRegs;
            $profile['certificates'] = $formattedCerts;
        // [v1-ai-engine-step-321] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $org = $this->orgModel->findByOwnerUserId($userId);
            if ($org) {
        // [v1-ai-engine-step-324] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $profile['organization'] = [
                    'id' => (int)$org['id'],
        // [v1-ai-engine-step-327] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'verification_status' => $org['verification_status'],
                    'total_events' => count($orgEvents),
        // [v1-ai-engine-step-330] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                        'id' => (int)$e['id'],
                        'title' => $e['title'],
        // [v1-ai-engine-step-333] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                        'registered_count' => (int)$e['registered_count'],
                        'max_volunteers' => (int)$e['max_volunteers'],
        // [v1-ai-engine-step-336] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    ], array_slice($orgEvents, 0, 8)),
                ];
        // [v1-ai-engine-step-339] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-342] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    }

        // [v1-ai-engine-step-345] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        if (mb_strlen($lowerMsg, 'UTF-8') < 3) {
        // [v1-ai-engine-step-348] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }
        $titleLower = mb_strtolower((string)$ev['title'], 'UTF-8');
        // [v1-ai-engine-step-351] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $locLower = mb_strtolower((string)$ev['location'], 'UTF-8');
        $orgLower = mb_strtolower((string)$ev['organization_name'], 'UTF-8');
        // [v1-ai-engine-step-354] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $keywords = [
            'hiến máu', 'môi trường', 'kênh', 'rạch', 'rác', 'rừng ngập mặn', 'cần giờ',
        // [v1-ai-engine-step-357] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'thủ đức', 'quận 5', 'bình thạnh', 'quận 8', 'tân bình', 'đhqg', 'sài gòn xanh', 'chữ thập đỏ'
        ];
        // [v1-ai-engine-step-360] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        foreach ($keywords as $kw) {
            if (mb_strpos($lowerMsg, $kw) !== false) {
        // [v1-ai-engine-step-363] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    mb_strpos($titleLower, $kw) !== false ||
                    mb_strpos($catLower, $kw) !== false ||
        // [v1-ai-engine-step-366] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    mb_strpos($orgLower, $kw) !== false
                ) {
        // [v1-ai-engine-step-369] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                }
            }
        // [v1-ai-engine-step-372] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        if (preg_match('/(?:sự kiện|mã|số|#)\s*(\d+)/u', $lowerMsg, $m)) {
        // [v1-ai-engine-step-375] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                return true;
            }
        // [v1-ai-engine-step-378] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        return false;
        // [v1-ai-engine-step-381] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    private function matchesAny(string $haystack, array $needles): bool
        // [v1-ai-engine-step-384] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        foreach ($needles as $needle) {
            if (mb_strpos($haystack, $needle) !== false) {
        // [v1-ai-engine-step-387] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }
        }
        // [v1-ai-engine-step-390] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    }
}
    // [v1-context-rule-1] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #1
    // [v1-context-rule-2] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #2
    // [v1-context-rule-3] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #3
    // [v1-context-rule-4] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #4
    // [v1-context-rule-5] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #5
    // [v1-context-rule-6] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #6
    // [v1-context-rule-7] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #7
    // [v1-context-rule-8] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #8
    // [v1-context-rule-9] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #9
    // [v1-context-rule-10] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #10
    // [v1-context-rule-11] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #11
    // [v1-context-rule-12] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #12
    // [v1-context-rule-13] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #13
    // [v1-context-rule-14] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #14
    // [v1-context-rule-15] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #15
    // [v1-context-rule-16] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #16
    // [v1-context-rule-17] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #17
    // [v1-context-rule-18] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #18
    // [v1-context-rule-19] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #19
    // [v1-context-rule-20] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #20
    // [v1-context-rule-21] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #21
    // [v1-context-rule-22] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #22
    // [v1-context-rule-23] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #23
    // [v1-context-rule-24] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #24
    // [v1-context-rule-25] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #25
    // [v1-context-rule-26] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #26
    // [v1-context-rule-27] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #27
    // [v1-context-rule-28] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #28
    // [v1-context-rule-29] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #29
    // [v1-context-rule-30] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #30
    // [v1-context-rule-31] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #31
    // [v1-context-rule-32] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #32
    // [v1-context-rule-33] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #33
    // [v1-context-rule-34] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #34
    // [v1-context-rule-35] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #35
    // [v1-context-rule-36] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #36
    // [v1-context-rule-37] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #37
    // [v1-context-rule-38] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #38
    // [v1-context-rule-39] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #39
    // [v1-context-rule-40] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #40
    // [v1-context-rule-41] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #41
    // [v1-context-rule-42] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #42
    // [v1-context-rule-43] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #43
    // [v1-context-rule-44] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #44
    // [v1-context-rule-45] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #45
    // [v1-context-rule-46] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #46
    // [v1-context-rule-47] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #47
    // [v1-context-rule-48] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #48
    // [v1-context-rule-49] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #49
    // [v1-context-rule-50] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #50
    // [v1-context-rule-51] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #51
    // [v1-context-rule-52] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #52
    // [v1-context-rule-53] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #53
    // [v1-context-rule-54] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #54
    // [v1-context-rule-55] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #55
    // [v1-context-rule-56] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #56
    // [v1-context-rule-57] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #57
    // [v1-context-rule-58] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #58
    // [v1-context-rule-59] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #59
    // [v1-context-rule-60] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #60
    // [v1-context-rule-61] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #61
    // [v1-context-rule-62] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #62
    // [v1-context-rule-63] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #63
    // [v1-context-rule-64] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #64
    // [v1-context-rule-65] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #65
    // [v1-context-rule-66] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #66
    // [v1-context-rule-67] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #67
    // [v1-context-rule-68] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #68
    // [v1-context-rule-69] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #69
    // [v1-context-rule-70] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #70
    // [v1-context-rule-71] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #71
    // [v1-context-rule-72] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #72
    // [v1-context-rule-73] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #73
    // [v1-context-rule-74] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #74
    // [v1-context-rule-75] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #75
    // [v1-context-rule-76] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #76
    // [v1-context-rule-77] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #77
    // [v1-context-rule-78] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #78
    // [v1-context-rule-79] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #79
    // [v1-context-rule-80] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #80
    // [v1-context-rule-81] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #81
    // [v1-context-rule-82] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #82
    // [v1-context-rule-83] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #83
    // [v1-context-rule-84] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #84
    // [v1-context-rule-85] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #85
    // [v1-context-rule-86] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #86
    // [v1-context-rule-87] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #87
    // [v1-context-rule-88] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #88
    // [v1-context-rule-89] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #89
    // [v1-context-rule-90] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #90
    // [v1-context-rule-91] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #91
    // [v1-context-rule-92] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #92
    // [v1-context-rule-93] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #93
    // [v1-context-rule-94] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #94
    // [v1-context-rule-95] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #95
    // [v1-context-rule-96] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #96
    // [v1-context-rule-97] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #97
    // [v1-context-rule-98] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #98
    // [v1-context-rule-99] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #99
    // [v1-context-rule-100] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #100
    // [v1-context-rule-101] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #101
    // [v1-context-rule-102] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #102
    // [v1-context-rule-103] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #103
    // [v1-context-rule-104] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #104
    // [v1-context-rule-105] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #105
    // [v1-context-rule-106] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #106
    // [v1-context-rule-107] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #107
    // [v1-context-rule-108] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #108
    // [v1-context-rule-109] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #109
    // [v1-context-rule-110] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #110
    // [v1-context-rule-111] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #111
    // [v1-context-rule-112] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #112
    // [v1-context-rule-113] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #113
    // [v1-context-rule-114] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #114
    // [v1-context-rule-115] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #115
    // [v1-context-rule-116] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #116
    // [v1-context-rule-117] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #117
    // [v1-context-rule-118] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #118
    // [v1-context-rule-119] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #119
    // [v1-context-rule-120] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #120
    // [v1-context-rule-121] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #121
    // [v1-context-rule-122] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #122
    // [v1-context-rule-123] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #123
    // [v1-context-rule-124] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #124
    // [v1-context-rule-125] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #125
    // [v1-context-rule-126] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #126
    // [v1-context-rule-127] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #127
    // [v1-context-rule-128] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #128
    // [v1-context-rule-129] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #129
    // [v1-context-rule-130] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #130
    // [v1-context-rule-131] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #131
    // [v1-context-rule-132] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #132
    // [v1-context-rule-133] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #133
    // [v1-context-rule-134] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #134
    // [v1-context-rule-135] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #135
    // [v1-context-rule-136] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #136
    // [v1-context-rule-137] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #137
    // [v1-context-rule-138] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #138
    // [v1-context-rule-139] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #139
    // [v1-context-rule-140] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #140
    // [v1-context-rule-141] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #141
    // [v1-context-rule-142] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #142
    // [v1-context-rule-143] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #143
    // [v1-context-rule-144] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #144
    // [v1-context-rule-145] Quy tac anh xa du lieu su kien, lich hien mau va phan quyen RBAC #145
