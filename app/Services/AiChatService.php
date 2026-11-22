<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use App\Core\Session;

/**
 * AiChatService - Xử lý hội thoại Gió AI, kết nối Gemini / OpenAI API,
 * quản lý lịch sử phiên trò chuyện, giới hạn tốc độ (Rate Limit) và truy xuất dữ liệu MySQL thực tế.
 */
class AiChatService
{
    private AiContextService $contextService;
    private string $provider;
    private string $geminiApiKey;
    private string $geminiModel;
    private string $openaiApiKey;
    private string $openaiModel;
    private string $openaiBaseUrl;
    private int $timeoutSeconds;
    private int $maxMessageLength;
    private int $maxHistoryMessages;
    private int $rateLimitPerMinute;

    public function __construct()
    {
        $this->contextService = new AiContextService();
        $this->provider = strtolower((string)env('AI_PROVIDER', 'gemini'));
        $this->geminiApiKey = trim((string)env('GEMINI_API_KEY', ''));
        $this->geminiModel = trim((string)env('GEMINI_MODEL', 'gemini-2.5-flash'));
        $this->openaiApiKey = trim((string)env('OPENAI_API_KEY', ''));
        $this->openaiModel = trim((string)env('OPENAI_MODEL', 'gpt-4o-mini'));
        $this->openaiBaseUrl = rtrim(trim((string)env('OPENAI_BASE_URL', 'https://api.openai.com/v1')), '/');
        $this->timeoutSeconds = max(5, (int)env('AI_TIMEOUT_SECONDS', 15));
        $this->maxMessageLength = max(100, (int)env('AI_MAX_MESSAGE_LENGTH', 500));
        $this->maxHistoryMessages = 20;
        $this->rateLimitPerMinute = max(5, (int)env('AI_RATE_LIMIT_PER_MINUTE', 15));
    }

    public function getWelcomePayload(?array $authUser): array
    {
        return [
            'bot_name' => 'Gió AI – Trợ lý tình nguyện viên',
            'status_text' => 'Đang hỗ trợ',
            'welcome_message' => "Xin chào! Mình là Gió AI – trợ lý của Góp Gió Volunteer.\n\nBạn đang cần hỗ trợ điều gì hôm nay? Mình có thể giúp bạn tìm hiểu các chương trình tình nguyện, hướng dẫn đăng ký tham gia hoặc giải đáp những thắc mắc của bạn.",
            'suggested_questions' => [
                'Làm sao để đăng ký tham gia tình nguyện?',
                'Làm sao để điểm danh bằng mã QR?',
                'Tôi có thể xem lịch sử hoạt động tình nguyện ở đâu?',
                'Làm thế nào để nhận giấy chứng nhận?',
                'Hiện tại có những chương trình tình nguyện nào?',
            ],
            'max_message_length' => $this->maxMessageLength,
            'is_authenticated' => $authUser !== null,
            'user_name' => $authUser['full_name'] ?? null,
            'history' => $this->getHistory($authUser),
        ];
    }

    /**
     * Xử lý tin nhắn từ người dùng, kiểm tra bảo mật, gọi AI và lưu lịch sử phiên chat
     */
    public function processMessage(string $rawMessage, ?array $authUser, array $options = []): array
    {
        $message = trim($rawMessage);

        // 1. Kiểm tra tin nhắn rỗng
        if ($message === '') {
            throw new \InvalidArgumentException('Vui lòng nhập nội dung câu hỏi trước khi gửi.');
        }

        // 2. Kiểm tra giới hạn độ dài tin nhắn
        $msgLength = mb_strlen($message, 'UTF-8');
        if ($msgLength > $this->maxMessageLength) {
            throw new \InvalidArgumentException(
                "Tin nhắn quá dài ({$msgLength}/{$this->maxMessageLength} ký tự). Vui lòng rút gọn câu hỏi để Gió AI hỗ trợ tốt nhất nhé."
            );
        }

        // 3. Kiểm tra chống gửi liên tục (Rate Limiting)
        if (empty($options['skip_rate_limit'])) {
            $this->enforceRateLimit();
        }

        // 4. Kiểm tra cờ mô phỏng lỗi kết nối phục vụ kiểm thử tự động
        if (!empty($options['simulate_api_error'])) {
            throw new \RuntimeException('Không thể kết nối tới dịch vụ Gió AI hoặc hết thời gian phản hồi. Vui lòng thử gửi lại.');
        }

        $history = $this->getHistory($authUser);
        $context = $this->contextService->buildContext($message, $authUser, $history);
        $systemPrompt = AiPromptBuilder::buildSystemPrompt($context);

        $providerUsed = 'contextual-engine';
        $replyText = '';

        // 5. Nếu người dùng hỏi thông tin nhạy cảm của người khác hoặc yêu cầu thao tác quản trị trái phép -> Chặn ngay ở tầng Backend
        if (!empty($context['intent']['is_other_user_privacy_query'])) {
            $replyText = "Vì lý do **bảo mật và quyền riêng tư**, Gió AI không được phép truy cập hoặc cung cấp mật khẩu, mã token hay thông tin cá nhân (email, số điện thoại, lịch sử hoạt động) của tài khoản khác.\n\nBạn chỉ có thể tra cứu các thông tin sự kiện công khai hoặc thông tin cá nhân của chính tài khoản bạn đang đăng nhập thôi nhé!";
            $providerUsed = 'security-guard';
        } elseif (!empty($context['intent']['is_mutation_attempt'])) {
            $replyText = "Mình là **Gió AI – Trợ lý tư vấn & tra cứu thông tin**, mình không có quyền tự ý thay đổi dữ liệu hệ thống, xóa sự kiện, khóa tài khoản hay tự động cộng giờ tình nguyện.\n\nNếu bạn là **Ban tổ chức** hoặc **Quản trị viên**, vui lòng thực hiện các thao tác này trực tiếp tại **Bảng điều khiển** (`/organizer/dashboard` hoặc `/admin/dashboard`) nhé!";
            $providerUsed = 'security-guard';
        } else {
            // 6. Ưu tiên gọi AI API thực tế (Gemini API hoặc OpenAI API) nếu đã cấu hình API Key hợp lệ
            if ($this->hasValidGeminiKey() && ($this->provider === 'gemini' || !$this->hasValidOpenAiKey())) {
                try {
                    $replyText = $this->callGeminiApi($systemPrompt, $history, $message);
                    $providerUsed = 'gemini';
                } catch (\Throwable $e) {
                    Logger::error('Gió AI - Gemini API Error: ' . $e->getMessage());
                    if (!empty($options['strict_api_only'])) {
                        throw new \RuntimeException('Dịch vụ AI tạm thời không phản hồi. Vui lòng thử gửi lại sau giây lát.');
                    }
                }
            } elseif ($this->hasValidOpenAiKey() && $this->provider === 'openai') {
                try {
                    $replyText = $this->callOpenAiApi($systemPrompt, $history, $message);
                    $providerUsed = 'openai';
                } catch (\Throwable $e) {
                    Logger::error('Gió AI - OpenAI API Error: ' . $e->getMessage());
                    if (!empty($options['strict_api_only'])) {
                        throw new \RuntimeException('Dịch vụ AI tạm thời không phản hồi. Vui lòng thử gửi lại sau giây lát.');
                    }
                }
            }

            // 7. Cơ chế dự phòng thông minh kết nối trực tiếp CSDL MySQL khi API chưa gắn Key hoặc mất kết nối
            if (trim($replyText) === '') {
                $replyText = $this->generateContextualDatabaseReply($message, $context, $history);
                $providerUsed = 'mysql-contextual-ai';
            }
        }

        $replyText = $this->sanitizeOutput($replyText);
        $nowTime = date('H:i');

        // 8. Lưu lịch sử trò chuyện vào phiên làm việc
        $this->appendMessageToHistory($authUser, 'user', $message, $nowTime);
        $this->appendMessageToHistory($authUser, 'assistant', $replyText, $nowTime);

        return [
            'reply' => $replyText,
            'timestamp' => $nowTime,
            'provider' => $providerUsed,
            'history_count' => count($this->getHistory($authUser)),
        ];
    }

    public function getHistory(?array $authUser): array
    {
        Session::start();
        $key = $this->getHistorySessionKey($authUser);
        $history = $_SESSION[$key] ?? [];
        return is_array($history) ? $history : [];
    }

    public function clearHistory(?array $authUser): void
    {
        Session::start();
        $key = $this->getHistorySessionKey($authUser);
        unset($_SESSION[$key], $_SESSION['gio_ai_last_events']);
    }

    private function appendMessageToHistory(?array $authUser, string $role, string $content, string $time): void
    {
        Session::start();
        $key = $this->getHistorySessionKey($authUser);
        $history = $_SESSION[$key] ?? [];
        if (!is_array($history)) {
            $history = [];
        }

        $history[] = [
            'role' => $role,
            'content' => $content,
            'time' => $time,
        ];

        if (count($history) > $this->maxHistoryMessages) {
            $history = array_slice($history, -$this->maxHistoryMessages);
        }

        $_SESSION[$key] = $history;
    }

    private function getHistorySessionKey(?array $authUser): string
    {
        if ($authUser && !empty($authUser['id'])) {
            return 'gio_ai_history_user_' . (int)$authUser['id'];
        }
        return 'gio_ai_history_guest';
    }

    private function enforceRateLimit(): void
    {
        Session::start();
        $now = time();
        $bucket = $_SESSION['gio_ai_rate_limit'] ?? ['timestamps' => []];
        $timestamps = array_filter(
            $bucket['timestamps'] ?? [],
            static fn(int $ts): bool => ($now - $ts) < 60
        );

        if (count($timestamps) >= $this->rateLimitPerMinute) {
            throw new \RuntimeException(
                'Bạn đang gửi tin nhắn quá nhanh. Vui lòng đợi vài giây trước khi gửi câu hỏi tiếp theo nhé!'
            );
        }

        $timestamps[] = $now;
        $_SESSION['gio_ai_rate_limit'] = ['timestamps' => array_values($timestamps)];
    }

    private function hasValidGeminiKey(): bool
    {
        if ($this->geminiApiKey === '') {
            return false;
        }
        $lower = strtolower($this->geminiApiKey);
        return !str_contains($lower, 'your_') && !str_contains($lower, 'change_this') && strlen($this->geminiApiKey) > 18;
    }

    private function hasValidOpenAiKey(): bool
    {
        if ($this->openaiApiKey === '') {
            return false;
        }
        $lower = strtolower($this->openaiApiKey);
        return !str_contains($lower, 'your_') && !str_contains($lower, 'change_this') && strlen($this->openaiApiKey) > 18;
    }

    /**
     * Gọi Google Gemini REST API (generateContent) kèm System Instruction & Lịch sử hội thoại
     */
    private function callGeminiApi(string $systemPrompt, array $history, string $userMessage): string
    {
        $contents = [];
        foreach (array_slice($history, -10) as $turn) {
            $role = ($turn['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $text = trim((string)($turn['content'] ?? ''));
            if ($text !== '') {
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 900,
            ],
        ];

        $model = $this->geminiModel !== '' ? $this->geminiModel : 'gemini-2.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($this->geminiApiKey);

        $response = $this->httpPostJson($url, $payload, [
            'Content-Type: application/json',
        ]);

        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (!is_string($text) || trim($text) === '') {
            throw new \RuntimeException('Phản hồi từ Gemini API rỗng.');
        }

        return trim($text);
    }

    /**
     * Gọi OpenAI-compatible Chat Completions API
     */
    private function callOpenAiApi(string $systemPrompt, array $history, string $userMessage): string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach (array_slice($history, -10) as $turn) {
            $role = ($turn['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user';
            $text = trim((string)($turn['content'] ?? ''));
            if ($text !== '') {
                $messages[] = ['role' => $role, 'content' => $text];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $payload = [
            'model' => $this->openaiModel !== '' ? $this->openaiModel : 'gpt-4o-mini',
            'messages' => $messages,
            'temperature' => 0.4,
            'max_tokens' => 900,
        ];

        $url = $this->openaiBaseUrl . '/chat/completions';
        $response = $this->httpPostJson($url, $payload, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiApiKey,
        ]);

        $text = $response['choices'][0]['message']['content'] ?? '';
        if (!is_string($text) || trim($text) === '') {
            throw new \RuntimeException('Phản hồi từ OpenAI API rỗng.');
        }

        return trim($text);
    }

    private function httpPostJson(string $url, array $payload, array $headers): array
    {
        $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonBody,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeoutSeconds,
                CURLOPT_CONNECTTIMEOUT => 6,
            ]);
            $raw = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($raw === false) {
                throw new \RuntimeException('Lỗi kết nối mạng tới AI API: ' . $err);
            }
            if ($httpCode < 200 || $httpCode >= 300) {
                throw new \RuntimeException("AI API trả về mã lỗi HTTP {$httpCode}");
            }

            $decoded = json_decode((string)$raw, true);
            if (!is_array($decoded)) {
                throw new \RuntimeException('Dữ liệu JSON từ AI API không hợp lệ.');
            }
            return $decoded;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $jsonBody,
                'timeout' => $this->timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            throw new \RuntimeException('Không thể kết nối tới AI API endpoint.');
        }
        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Phản hồi từ AI API không đúng định dạng JSON.');
        }
        return $decoded;
    }

    /**
     * Động cơ suy luận ngữ cảnh tiếng Việt kết nối trực tiếp dữ liệu MySQL thực tế
     * Đảm bảo Gió AI luôn trả lời chính xác dữ liệu thật ngay cả khi chưa cấu hình khóa ngoại vi
     */
    private function generateContextualDatabaseReply(string $message, array $context, array $history): string
    {
        $lower = mb_strtolower($message, 'UTF-8');
        $intent = $context['intent'] ?? [];
        $public = $context['public'] ?? [];
        $personal = $context['personal'] ?? null;
        $isAuthenticated = !empty($context['is_authenticated']);

        $allEvents = $public['events'] ?? [];
        $bloodEvents = $public['blood_events'] ?? [];
        $matchedEvents = $public['matched_events'] ?? [];

        // 1. Xử lý câu hỏi về thông tin cá nhân / lịch sử hoạt động
        if (!empty($intent['is_personal_query'])) {
            if (!$isAuthenticated || $personal === null) {
                return "Để xem **lịch sử hoạt động tình nguyện**, số giờ đã tích lũy, mã QR điểm danh hoặc tải giấy chứng nhận cá nhân, bạn cần đăng nhập vào tài khoản trước nhé!\n\n"
                    . "1. Truy cập trang **Đăng nhập** tại `/login`.\n"
                    . "2. Sau khi đăng nhập, nhấn vào tên của bạn ở góc trên bên phải và chọn **Bảng điều khiển** (`/volunteer/dashboard`).\n\n"
                    . "Tại đó bạn sẽ thấy toàn bộ các sự kiện đã đăng ký, trạng thái duyệt giờ và file PDF chứng nhận của mình!";
            }

            if ($personal['role_slug'] === 'volunteer') {
                $vs = $personal['volunteer_stats'] ?? [];
                $regs = $personal['registrations'] ?? [];
                $certs = $personal['certificates'] ?? [];

                $lines = [
                    "Chào **{$personal['full_name']}**! Mình vừa tra cứu dữ liệu hồ sơ Tình nguyện viên của bạn trên hệ thống:",
                    "",
                    "- **Tổng giờ tình nguyện đã duyệt:** **{$vs['total_approved_hours']} giờ**",
                    "- **Sự kiện đã hoàn thành điểm danh:** **{$vs['events_attended']} sự kiện**",
                    "- **Đăng ký đang hoạt động:** **{$vs['active_registrations']}** (Đang chờ Waitlist: **{$vs['waitlisted_count']}**)",
                    "- **Giấy chứng nhận đã cấp:** **{$vs['certificates_count']} chứng nhận**",
                ];

                if (!empty($regs)) {
                    $lines[] = "\n**Các hoạt động gần nhất bạn đã đăng ký:**";
                    foreach (array_slice($regs, 0, 4) as $idx => $r) {
                        $num = $idx + 1;
                        $statusVi = match ($r['status']) {
                            'Confirmed' => 'Đã xác nhận chỗ (Confirmed)',
                            'Attended' => 'Đã hoàn thành điểm danh (Attended)',
                            'Waitlisted' => 'Đang trong Danh sách chờ (Vị trí #' . ($r['waitlist_position'] ?? 1) . ')',
                            'Cancelled' => 'Đã hủy',
                            default => $r['status'],
                        };
                        $hrText = $r['approved_hours'] !== null ? " – Giờ: **{$r['approved_hours']}h** ({$r['hours_status']})" : "";
                        $lines[] = "{$num}. **{$r['event_title']}** ({$r['start_time']}) – Trạng thái: *{$statusVi}*{$hrText}";
                    }
                }

                if (!empty($certs)) {
                    $lines[] = "\n**Giấy chứng nhận của bạn:**";
                    foreach ($certs as $c) {
                        $lines[] = "- Mã **{$c['certificate_code']}** ({$c['event_title']} – {$c['hours_confirmed']} giờ) | Tải PDF tại: `{$c['download_url']}`";
                    }
                }

                $lines[] = "\nBạn có thể xem chi tiết và lấy mã QR điểm danh trực tiếp tại **Bảng điều khiển Tình nguyện viên** (`/volunteer/dashboard`) nhé!";
                return implode("\n", $lines);
            }

            return "Chào **{$personal['full_name']}**! Bạn đang đăng nhập với vai trò **{$personal['role_name']}**. Bạn có thể quản lý toàn bộ hoạt động và xem báo cáo chi tiết tại **Bảng điều khiển** (`{$personal['dashboard_url']}`) nhé!";
        }

        // 2. Xử lý câu hỏi nối tiếp (Follow-up) tham chiếu tới sự kiện ở câu trả lời trước
        if (!empty($intent['is_followup_event_ref']) || $this->isFollowUpQuestion($lower)) {
            $lastEvents = $_SESSION['gio_ai_last_events'] ?? [];
            if (!empty($lastEvents)) {
                $targetEvent = $lastEvents[0];
                if (mb_strpos($lower, 'thứ 2') !== false || mb_strpos($lower, 'số 2') !== false) {
                    $targetEvent = $lastEvents[1] ?? $lastEvents[0];
                } elseif (mb_strpos($lower, 'thứ 3') !== false || mb_strpos($lower, 'số 3') !== false) {
                    $targetEvent = $lastEvents[2] ?? $lastEvents[0];
                }

                return "Thông tin chi tiết về chương trình **{$targetEvent['title']}** từ cơ sở dữ liệu hệ thống:\n\n"
                    . "- **Đơn vị tổ chức:** {$targetEvent['organization']}\n"
                    . "- **Danh mục:** {$targetEvent['category']}\n"
                    . "- **Thời gian:** {$targetEvent['start_time']} – {$targetEvent['end_time']}\n"
                    . "- **Hạn đăng ký:** {$targetEvent['deadline']}\n"
                    . "- **Địa điểm:** {$targetEvent['location']}\n"
                    . "- **Tình trạng chỗ:** **{$targetEvent['registration_state']}** (Đã đăng ký: {$targetEvent['registered_count']}/{$targetEvent['max_volunteers']})\n"
                    . "- **Số giờ tình nguyện tối đa:** {$targetEvent['max_hours']} giờ\n"
                    . "- **Xem chi tiết & Đăng ký tại:** `{$targetEvent['url']}`";
            }
        }

        // 3. Xử lý câu hỏi về Sự kiện Hiến máu nhân đạo thực tế từ MySQL
        if (!empty($intent['is_blood_query'])) {
            if (empty($bloodEvents)) {
                return "Hiện tại trên cơ sở dữ liệu hệ thống chưa có lịch hiến máu nhân đạo nào đang mở. Bạn có thể theo dõi chuyên trang **Hiến máu** tại `/blood-donation` để cập nhật đợt hiến máu mới nhất nhé!";
            }

            $_SESSION['gio_ai_last_events'] = array_values($bloodEvents);
            $lines = [
                "Hiện tại trên hệ thống **Góp Gió Volunteer** đang có **" . count($bloodEvents) . " chương trình Hiến máu nhân đạo** trong cơ sở dữ liệu:",
                ""
            ];

            foreach ($bloodEvents as $idx => $ev) {
                $num = $idx + 1;
                $lines[] = "{$num}. **{$ev['title']}**";
                $lines[] = "   - **Đơn vị tổ chức:** {$ev['organization']}";
                $lines[] = "   - **Thời gian:** {$ev['start_time']} – {$ev['end_time']}";
                $lines[] = "   - **Địa điểm:** {$ev['location']}";
                $lines[] = "   - **Trạng thái đăng ký:** {$ev['registration_state']} (Tối đa {$ev['max_hours']} giờ tình nguyện)";
                $lines[] = "   - **Chi tiết & Đặt lịch ca hiến máu:** `{$ev['url']}`";
            }

            $lines[] = "\nKhi đăng ký sự kiện hiến máu, bạn có thể chọn khung giờ (**Ca 1: 07:30 - 09:00**, **Ca 2: 09:00 - 10:30**, **Ca 3: 10:30 - 11:30**) và xem thêm thông tin tại chuyên trang `/blood-donation` nhé!";
            return implode("\n", $lines);
        }

        // 4. Xử lý câu hỏi tìm kiếm Sự kiện cụ thể hoặc liệt kê các chương trình tình nguyện từ MySQL
        if (!empty($intent['is_event_query']) && (empty($intent['is_registration_guide']) || mb_strpos($lower, 'những chương trình') !== false || mb_strpos($lower, 'sự kiện nào') !== false)) {
            $eventsToShow = !empty($matchedEvents) ? $matchedEvents : $allEvents;

            // Nếu người dùng hỏi một chủ đề cụ thể mà không khớp sự kiện nào
            if (empty($matchedEvents) && $this->hasSpecificUnmatchedKeyword($lower)) {
                return "Hiện tại hệ thống **chưa có sự kiện nào khớp với từ khóa hoặc địa điểm bạn vừa tìm** trong cơ sở dữ liệu.\n\n"
                    . "Tuy nhiên, bạn có thể tham khảo **" . count($allEvents) . " chương trình tình nguyện & hiến máu** đang có trên hệ thống tại trang `/events`, hoặc hỏi mình về các hoạt động tại **TP. Hồ Chí Minh** (như Hiến máu nhân đạo, Làm sạch kênh rạch Sài Gòn Xanh, Trồng rừng ngập mặn Cần Giờ, Bếp ăn thiện nguyện, Lớp học vùng ven) nhé!";
            }

            if (empty($eventsToShow)) {
                return "Hiện tại hệ thống chưa có sự kiện tình nguyện nào được công bố trong cơ sở dữ liệu. Vui lòng quay lại kiểm tra sau tại `/events` nhé!";
            }

            $_SESSION['gio_ai_last_events'] = array_values($eventsToShow);
            $displayList = array_slice($eventsToShow, 0, 6);

            $header = !empty($matchedEvents)
                ? "Mình tìm thấy **" . count($matchedEvents) . " sự kiện phù hợp** trong cơ sở dữ liệu Góp Gió Volunteer:"
                : "Hiện tại hệ thống **Góp Gió Volunteer** đang có **" . count($allEvents) . " chương trình tình nguyện & hiến máu** thực tế (dưới đây là các chương trình tiêu biểu):";

            $lines = [$header, ""];
            foreach ($displayList as $idx => $ev) {
                $num = $idx + 1;
                $lines[] = "{$num}. **{$ev['title']}** *({$ev['category']})*";
                $lines[] = "   - **Ban tổ chức:** {$ev['organization']}";
                $lines[] = "   - **Thời gian:** {$ev['start_time']} – {$ev['end_time']}";
                $lines[] = "   - **Địa điểm:** {$ev['location']}";
                $lines[] = "   - **Tình trạng:** **{$ev['registration_state']}** | Ghi nhận tối đa **{$ev['max_hours']} giờ**";
                $lines[] = "   - **Link đăng ký:** `{$ev['url']}`";
            }

            $lines[] = "\nBạn muốn xem chi tiết hơn về sự kiện nào (ví dụ: *\"Sự kiện đầu tiên còn bao nhiêu chỗ?\"*) hay muốn mình hướng dẫn cách đăng ký không?";
            return implode("\n", $lines);
        }

        // 5. Hướng dẫn quy trình đăng ký tham gia tình nguyện & Waitlist
        if (!empty($intent['is_registration_guide'])) {
            return "Chào bạn! Rất vui vì bạn muốn tham gia cùng **Góp Gió Volunteer**. ❤️\n\n"
                . "Bạn có thể đăng ký tham gia hoạt động tình nguyện theo **4 bước đơn giản** sau:\n\n"
                . "1. **Đăng nhập tài khoản:** Truy cập `/login` (hoặc tạo tài khoản Tình nguyện viên mới tại `/register`).\n"
                . "2. **Chọn chương trình:** Vào mục **Hoạt động** (`/events`) hoặc **Hiến máu** (`/blood-donation`) để lọc sự kiện theo lĩnh vực, thời gian và địa điểm.\n"
                . "3. **Nhấn Đăng ký tham gia:** Mở trang chi tiết sự kiện và bấm nút **Đăng ký tham gia**.\n"
                . "4. **Nhận mã QR điểm danh:**\n"
                . "   - Nếu sự kiện **còn chỗ**: Bạn được xác nhận ngay (`Confirmed`) và hệ thống tự động cấp **Mã QR cá nhân** trong `/volunteer/dashboard`.\n"
                . "   - Nếu sự kiện **đã đủ số lượng**: Hệ thống tự động xếp bạn vào **Danh sách chờ (`Waitlist`)** theo thứ tự ưu tiên. Ngay khi có người hủy đăng ký, hệ thống sẽ **tự động đôn người chờ #1 lên chính thức** và gửi thông báo cho bạn!";
        }

        // 6. Hướng dẫn quy trình Điểm danh bằng mã QR (QR Attendance)
        if (!empty($intent['is_qr_attendance_guide'])) {
            return "Quy trình **Điểm danh bằng mã QR (QR Attendance)** trên Góp Gió Volunteer hoạt động như sau:\n\n"
                . "1. **Lấy mã QR cá nhân:** Sau khi đăng ký sự kiện thành công (`Confirmed`), bạn vào **Bảng điều khiển Tình nguyện viên** (`/volunteer/dashboard`), bấm nút **Mã QR Điểm danh** tại sự kiện đó để mở mã QR của riêng bạn.\n"
                . "2. **Quét QR Check-in khi đến sự kiện:** Tại địa điểm tổ chức, Ban tổ chức dùng trình quét Camera (`/organizer/events/{id}/scanner`) để quét mã QR của bạn ghi nhận thời gian **Check-in** (hệ thống có cơ chế chống quét trùng lặp 2 lần).\n"
                . "3. **Quét QR Check-out khi hoàn thành:** Khi kết thúc ca tình nguyện, Ban tổ chức quét mã lần nữa để **Check-out**.\n"
                . "4. **Tự động tính giờ:** Hệ thống tự động tính số giờ tham gia theo công thức `(Check-out - Check-in) / 3600` (không vượt quá số giờ tối đa của sự kiện) và chuyển sang chờ Ban tổ chức xác nhận duyệt giờ!";
        }

        // 7. Hướng dẫn về Giờ tình nguyện & Giấy chứng nhận PDF
        if (!empty($intent['is_hours_certificate_guide'])) {
            return "Về **Giờ tình nguyện và Giấy chứng nhận điện tử (PDF)** trên Góp Gió Volunteer:\n\n"
                . "1. **Điều kiện nhận chứng nhận:**\n"
                . "   - Bạn đã tham gia sự kiện và hoàn tất điểm danh **Check-in & Check-out** (trạng thái `Attended`).\n"
                . "   - Số giờ tình nguyện của bạn tại sự kiện đó đã được Ban tổ chức hoặc Quản trị viên xác nhận **Đã duyệt (`Approved`)**.\n"
                . "2. **Cách tải Giấy chứng nhận PDF:**\n"
                . "   - Sau khi Ban tổ chức nhấn **Cấp chứng nhận**, hệ thống tự động tạo file **Chứng nhận PDF tiếng Việt** kèm mã QR và mã xác minh duy nhất (`verification_code`).\n"
                . "   - Bạn truy cập **Bảng điều khiển Tình nguyện viên** (`/volunteer/dashboard`), kéo tới mục **Chứng nhận điện tử** và nhấn **Tải PDF**.\n"
                . "3. **Xác minh chứng nhận công khai:**\n"
                . "   - Nhà trường hoặc đơn vị tuyển dụng có thể kiểm tra tính hợp lệ của chứng nhận bất cứ lúc nào tại trang **Tra cứu chứng nhận** (`/certificate/verify`).";
        }

        // 8. Hỏi về Đơn vị tổ chức
        if (!empty($intent['is_organization_query'])) {
            $orgs = $public['organizations'] ?? [];
            $lines = [
                "Hiện tại trên nền tảng **Góp Gió Volunteer** có **" . count($orgs) . " Đơn vị tổ chức / Câu lạc bộ** đang hoạt động:",
                ""
            ];
            foreach ($orgs as $idx => $org) {
                $num = $idx + 1;
                $lines[] = "{$num}. **{$org['name']}** – Trạng thái: *{$org['verification_status']}* ({$org['total_events']} sự kiện) | Địa chỉ: {$org['address']} | Xem hồ sơ: `{$org['url']}`";
            }
            $lines[] = "\nBạn có thể xem toàn bộ danh sách các đơn vị tổ chức tại `/organizations` nhé!";
            return implode("\n", $lines);
        }

        // 9. Hỏi về Bảng xếp hạng Tình nguyện viên
        if (!empty($intent['is_leaderboard_query'])) {
            $topVols = $public['top_volunteers'] ?? [];
            $lines = [
                "Dưới đây là **Top Tình nguyện viên tiêu biểu** trên **Bảng xếp hạng Góp Gió Volunteer** (tính theo số giờ tình nguyện đã được duyệt thực tế trong CSDL):",
                ""
            ];
            foreach ($topVols as $idx => $v) {
                $rank = $idx + 1;
                $lines[] = "{$rank}. **{$v['full_name']}** – **{$v['confirmed_hours']} giờ** ({$v['events_attended']} hoạt động hoàn thành)";
            }
            $lines[] = "\nBạn có thể xem bảng vinh danh theo Tháng, Năm và Toàn thời gian tại `/leaderboard` nhé!";
            return implode("\n", $lines);
        }

        // 10. Chào hỏi / giới thiệu chung
        $stats = $public['community_stats'] ?? [];
        return "Chào bạn! Mình là **Gió AI – Trợ lý tình nguyện viên** của nền tảng **Góp Gió Volunteer** (*\"Một hành động nhỏ – Ngàn giá trị lớn\"*). 🌱\n\n"
            . "Hiện tại cộng đồng Góp Gió đang có **{$stats['total_volunteers']} tình nguyện viên**, **{$stats['total_organizations']} đơn vị tổ chức** và **{$stats['total_events']} sự kiện** với tổng cộng **{$stats['total_hours']} giờ tình nguyện** đã được ghi nhận.\n\n"
            . "Mình có thể hỗ trợ bạn:\n"
            . "- Tra cứu **danh sách sự kiện tình nguyện** (`/events`) và **lịch hiến máu nhân đạo** (`/blood-donation`) thực tế từ hệ thống.\n"
            . "- Hướng dẫn **đăng ký tham gia**, cơ chế **Danh sách chờ tự động (Waitlist)** và **Điểm danh bằng mã QR**.\n"
            . "- Giải đáp về cách tính **giờ tình nguyện** và tải **Giấy chứng nhận PDF** (`/certificate/verify`).\n\n"
            . "Bạn đang quan tâm đến hoạt động nào, cứ nhắn cho mình biết nhé!";
    }

    private function isFollowUpQuestion(string $lower): bool
    {
        $followUpPhrases = [
            'sự kiện đó', 'chương trình đó', 'sự kiện đầu tiên', 'sự kiện số 1',
            'sự kiện thứ 2', 'còn bao nhiêu chỗ', 'tổ chức ở đâu', 'chi tiết sự kiện này'
        ];
        foreach ($followUpPhrases as $phrase) {
            if (mb_strpos($lower, $phrase) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasSpecificUnmatchedKeyword(string $lower): bool
    {
        $unmatchedPlacesOrTopics = [
            'hà nội', 'đà nẵng', 'cần thơ', 'hải phòng', 'nha trang', 'huế', 'đà lạt', 'năm 2028', 'năm 2030'
        ];
        foreach ($unmatchedPlacesOrTopics as $kw) {
            if (mb_strpos($lower, $kw) !== false) {
                return true;
            }
        }
        return false;
    }

    private function sanitizeOutput(string $text): string
    {
        // Đảm bảo không bao giờ rò rỉ API Key hoặc chuỗi hash nhạy cảm trong phản hồi
        $secrets = array_filter([
            $this->geminiApiKey,
            $this->openaiApiKey,
            (string)env('CSRF_TOKEN_SECRET', ''),
            (string)env('QR_HMAC_SECRET', ''),
            (string)env('DB_PASSWORD', ''),
        ], static fn(string $s): bool => strlen($s) > 5);

        foreach ($secrets as $secret) {
            $text = str_replace($secret, '[REDACTED]', $text);
        }

        return trim($text);
    }
}
