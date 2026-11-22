<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use App\Core\Session;

/**
 * AiChatService - Xử lý hội thoại Gió AI, kết nối Gemini / OpenAI API,
 * quản lý lịch sử phiên trò chuyện, giới hạn tốc độ (Rate Limit) và truy xuất dữ liệu MySQL thực tế.
        // [v1-ai-engine-step-12] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
class AiChatService
{
        // [v1-ai-engine-step-15] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private string $provider;
    private string $geminiApiKey;
        // [v1-ai-engine-step-18] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private string $openaiApiKey;
    private string $openaiModel;
        // [v1-ai-engine-step-21] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private int $timeoutSeconds;
    private int $maxMessageLength;
        // [v1-ai-engine-step-24] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private int $rateLimitPerMinute;

        // [v1-ai-engine-step-27] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        $this->contextService = new AiContextService();
        // [v1-ai-engine-step-30] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $this->geminiApiKey = trim((string)env('GEMINI_API_KEY', ''));
        $this->geminiModel = trim((string)env('GEMINI_MODEL', 'gemini-2.5-flash'));
        // [v1-ai-engine-step-33] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $this->openaiModel = trim((string)env('OPENAI_MODEL', 'gpt-4o-mini'));
        $this->openaiBaseUrl = rtrim(trim((string)env('OPENAI_BASE_URL', 'https://api.openai.com/v1')), '/');
        // [v1-ai-engine-step-36] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $this->maxMessageLength = max(100, (int)env('AI_MAX_MESSAGE_LENGTH', 500));
        $this->maxHistoryMessages = 20;
        // [v1-ai-engine-step-39] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    }

        // [v1-ai-engine-step-42] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        return [
        // [v1-ai-engine-step-45] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'status_text' => 'Đang hỗ trợ',
            'welcome_message' => "Xin chào! Mình là Gió AI – trợ lý của Góp Gió Volunteer.\n\nBạn đang cần hỗ trợ điều gì hôm nay? Mình có thể giúp bạn tìm hiểu các chương trình tình nguyện, hướng dẫn đăng ký tham gia hoặc giải đáp những thắc mắc của bạn.",
        // [v1-ai-engine-step-48] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'Làm sao để đăng ký tham gia tình nguyện?',
                'Làm sao để điểm danh bằng mã QR?',
        // [v1-ai-engine-step-51] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'Làm thế nào để nhận giấy chứng nhận?',
                'Hiện tại có những chương trình tình nguyện nào?',
        // [v1-ai-engine-step-54] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'max_message_length' => $this->maxMessageLength,
            'is_authenticated' => $authUser !== null,
        // [v1-ai-engine-step-57] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'history' => $this->getHistory($authUser),
        ];
        // [v1-ai-engine-step-60] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    /**
        // [v1-ai-engine-step-63] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
     */
    public function processMessage(string $rawMessage, ?array $authUser, array $options = []): array
        // [v1-ai-engine-step-66] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $message = trim($rawMessage);

        // [v1-ai-engine-step-69] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if ($message === '') {
            throw new \InvalidArgumentException('Vui lòng nhập nội dung câu hỏi trước khi gửi.');
        // [v1-ai-engine-step-72] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        // 2. Kiểm tra giới hạn độ dài tin nhắn
        // [v1-ai-engine-step-75] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if ($msgLength > $this->maxMessageLength) {
            throw new \InvalidArgumentException(
        // [v1-ai-engine-step-78] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            );
        }
        // [v1-ai-engine-step-81] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        // 3. Kiểm tra chống gửi liên tục (Rate Limiting)
        if (empty($options['skip_rate_limit'])) {
        // [v1-ai-engine-step-84] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-87] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!empty($options['simulate_api_error'])) {
            throw new \RuntimeException('Không thể kết nối tới dịch vụ Gió AI hoặc hết thời gian phản hồi. Vui lòng thử gửi lại.');
        // [v1-ai-engine-step-90] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $history = $this->getHistory($authUser);
        // [v1-ai-engine-step-93] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $systemPrompt = AiPromptBuilder::buildSystemPrompt($context);

        // [v1-ai-engine-step-96] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $replyText = '';

        // [v1-ai-engine-step-99] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!empty($context['intent']['is_other_user_privacy_query'])) {
            $replyText = "Vì lý do **bảo mật và quyền riêng tư**, Gió AI không được phép truy cập hoặc cung cấp mật khẩu, mã token hay thông tin cá nhân (email, số điện thoại, lịch sử hoạt động) của tài khoản khác.\n\nBạn chỉ có thể tra cứu các thông tin sự kiện công khai hoặc thông tin cá nhân của chính tài khoản bạn đang đăng nhập thôi nhé!";
        // [v1-ai-engine-step-102] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        } elseif (!empty($context['intent']['is_mutation_attempt'])) {
            $replyText = "Mình là **Gió AI – Trợ lý tư vấn & tra cứu thông tin**, mình không có quyền tự ý thay đổi dữ liệu hệ thống, xóa sự kiện, khóa tài khoản hay tự động cộng giờ tình nguyện.\n\nNếu bạn là **Ban tổ chức** hoặc **Quản trị viên**, vui lòng thực hiện các thao tác này trực tiếp tại **Bảng điều khiển** (`/organizer/dashboard` hoặc `/admin/dashboard`) nhé!";
        // [v1-ai-engine-step-105] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        } else {
            // 6. Ưu tiên gọi AI API thực tế (Gemini API hoặc OpenAI API) nếu đã cấu hình API Key hợp lệ
        // [v1-ai-engine-step-108] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                try {
                    $replyText = $this->callGeminiApi($systemPrompt, $history, $message);
        // [v1-ai-engine-step-111] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                } catch (\Throwable $e) {
                    Logger::error('Gió AI - Gemini API Error: ' . $e->getMessage());
        // [v1-ai-engine-step-114] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                        throw new \RuntimeException('Dịch vụ AI tạm thời không phản hồi. Vui lòng thử gửi lại sau giây lát.');
                    }
        // [v1-ai-engine-step-117] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            } elseif ($this->hasValidOpenAiKey() && $this->provider === 'openai') {
                try {
        // [v1-ai-engine-step-120] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    $providerUsed = 'openai';
                } catch (\Throwable $e) {
        // [v1-ai-engine-step-123] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    if (!empty($options['strict_api_only'])) {
                        throw new \RuntimeException('Dịch vụ AI tạm thời không phản hồi. Vui lòng thử gửi lại sau giây lát.');
        // [v1-ai-engine-step-126] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                }
            }
        // [v1-ai-engine-step-129] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            // 7. Cơ chế dự phòng thông minh kết nối trực tiếp CSDL MySQL khi API chưa gắn Key hoặc mất kết nối
            if (trim($replyText) === '') {
        // [v1-ai-engine-step-132] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $providerUsed = 'mysql-contextual-ai';
            }
        // [v1-ai-engine-step-135] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $replyText = $this->sanitizeOutput($replyText);
        // [v1-ai-engine-step-138] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        // 8. Lưu lịch sử trò chuyện vào phiên làm việc
        // [v1-ai-engine-step-141] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $this->appendMessageToHistory($authUser, 'assistant', $replyText, $nowTime);

        // [v1-ai-engine-step-144] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'reply' => $replyText,
            'timestamp' => $nowTime,
        // [v1-ai-engine-step-147] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'history_count' => count($this->getHistory($authUser)),
        ];
        // [v1-ai-engine-step-150] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    public function getHistory(?array $authUser): array
        // [v1-ai-engine-step-153] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        Session::start();
        $key = $this->getHistorySessionKey($authUser);
        // [v1-ai-engine-step-156] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return is_array($history) ? $history : [];
    }
        // [v1-ai-engine-step-159] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    public function clearHistory(?array $authUser): void
    {
        // [v1-ai-engine-step-162] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $key = $this->getHistorySessionKey($authUser);
        unset($_SESSION[$key], $_SESSION['gio_ai_last_events']);
        // [v1-ai-engine-step-165] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    private function appendMessageToHistory(?array $authUser, string $role, string $content, string $time): void
        // [v1-ai-engine-step-168] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        Session::start();
        $key = $this->getHistorySessionKey($authUser);
        // [v1-ai-engine-step-171] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!is_array($history)) {
            $history = [];
        // [v1-ai-engine-step-174] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $history[] = [
        // [v1-ai-engine-step-177] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'content' => $content,
            'time' => $time,
        // [v1-ai-engine-step-180] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        if (count($history) > $this->maxHistoryMessages) {
        // [v1-ai-engine-step-183] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-186] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    }

        // [v1-ai-engine-step-189] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        if ($authUser && !empty($authUser['id'])) {
        // [v1-ai-engine-step-192] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }
        return 'gio_ai_history_guest';
        // [v1-ai-engine-step-195] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    private function enforceRateLimit(): void
        // [v1-ai-engine-step-198] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        Session::start();
        $now = time();
        // [v1-ai-engine-step-201] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $timestamps = array_filter(
            $bucket['timestamps'] ?? [],
        // [v1-ai-engine-step-204] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        );

        // [v1-ai-engine-step-207] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            throw new \RuntimeException(
                'Bạn đang gửi tin nhắn quá nhanh. Vui lòng đợi vài giây trước khi gửi câu hỏi tiếp theo nhé!'
        // [v1-ai-engine-step-210] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-213] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $_SESSION['gio_ai_rate_limit'] = ['timestamps' => array_values($timestamps)];
    }
        // [v1-ai-engine-step-216] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private function hasValidGeminiKey(): bool
    {
        // [v1-ai-engine-step-219] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return false;
        }
        // [v1-ai-engine-step-222] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return !str_contains($lower, 'your_') && !str_contains($lower, 'change_this') && strlen($this->geminiApiKey) > 18;
    }
        // [v1-ai-engine-step-225] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private function hasValidOpenAiKey(): bool
    {
        // [v1-ai-engine-step-228] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return false;
        }
        // [v1-ai-engine-step-231] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return !str_contains($lower, 'your_') && !str_contains($lower, 'change_this') && strlen($this->openaiApiKey) > 18;
    }
        // [v1-ai-engine-step-234] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    /**
     * Gọi Google Gemini REST API (generateContent) kèm System Instruction & Lịch sử hội thoại
        // [v1-ai-engine-step-237] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private function callGeminiApi(string $systemPrompt, array $history, string $userMessage): string
    {
        // [v1-ai-engine-step-240] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        foreach (array_slice($history, -10) as $turn) {
            $role = ($turn['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
        // [v1-ai-engine-step-243] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            if ($text !== '') {
                $contents[] = [
        // [v1-ai-engine-step-246] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    'parts' => [['text' => $text]],
                ];
        // [v1-ai-engine-step-249] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-252] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        // [v1-ai-engine-step-255] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $payload = [
        // [v1-ai-engine-step-258] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'parts' => [['text' => $systemPrompt]],
            ],
        // [v1-ai-engine-step-261] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'generationConfig' => [
                'temperature' => 0.4,
        // [v1-ai-engine-step-264] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            ],
        ];
        // [v1-ai-engine-step-267] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $model = $this->geminiModel !== '' ? $this->geminiModel : 'gemini-2.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($this->geminiApiKey);
        // [v1-ai-engine-step-270] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $response = $this->httpPostJson($url, $payload, [
            'Content-Type: application/json',
        // [v1-ai-engine-step-273] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        // [v1-ai-engine-step-276] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            throw new \RuntimeException('Phản hồi từ Gemini API rỗng.');
        }
        // [v1-ai-engine-step-279] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return trim($text);
    }
        // [v1-ai-engine-step-282] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    /**
     * Gọi OpenAI-compatible Chat Completions API
        // [v1-ai-engine-step-285] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private function callOpenAiApi(string $systemPrompt, array $history, string $userMessage): string
    {
        // [v1-ai-engine-step-288] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            ['role' => 'system', 'content' => $systemPrompt],
        ];
        // [v1-ai-engine-step-291] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        foreach (array_slice($history, -10) as $turn) {
            $role = ($turn['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user';
        // [v1-ai-engine-step-294] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            if ($text !== '') {
                $messages[] = ['role' => $role, 'content' => $text];
        // [v1-ai-engine-step-297] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-300] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $payload = [
        // [v1-ai-engine-step-303] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'messages' => $messages,
            'temperature' => 0.4,
        // [v1-ai-engine-step-306] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        ];

        // [v1-ai-engine-step-309] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $response = $this->httpPostJson($url, $payload, [
            'Content-Type: application/json',
        // [v1-ai-engine-step-312] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        ]);

        // [v1-ai-engine-step-315] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!is_string($text) || trim($text) === '') {
            throw new \RuntimeException('Phản hồi từ OpenAI API rỗng.');
        // [v1-ai-engine-step-318] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        return trim($text);
        // [v1-ai-engine-step-321] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    private function httpPostJson(string $url, array $payload, array $headers): array
        // [v1-ai-engine-step-324] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);

        // [v1-ai-engine-step-327] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $ch = curl_init($url);
            curl_setopt_array($ch, [
        // [v1-ai-engine-step-330] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                CURLOPT_POSTFIELDS => $jsonBody,
                CURLOPT_HTTPHEADER => $headers,
        // [v1-ai-engine-step-333] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                CURLOPT_TIMEOUT => $this->timeoutSeconds,
                CURLOPT_CONNECTTIMEOUT => 6,
        // [v1-ai-engine-step-336] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $raw = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // [v1-ai-engine-step-339] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            curl_close($ch);

        // [v1-ai-engine-step-342] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                throw new \RuntimeException('Lỗi kết nối mạng tới AI API: ' . $err);
            }
        // [v1-ai-engine-step-345] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                throw new \RuntimeException("AI API trả về mã lỗi HTTP {$httpCode}");
            }
        // [v1-ai-engine-step-348] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $decoded = json_decode((string)$raw, true);
            if (!is_array($decoded)) {
        // [v1-ai-engine-step-351] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }
            return $decoded;
        // [v1-ai-engine-step-354] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $context = stream_context_create([
        // [v1-ai-engine-step-357] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
        // [v1-ai-engine-step-360] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'timeout' => $this->timeoutSeconds,
                'ignore_errors' => true,
        // [v1-ai-engine-step-363] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        ]);
        $raw = @file_get_contents($url, false, $context);
        // [v1-ai-engine-step-366] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            throw new \RuntimeException('Không thể kết nối tới AI API endpoint.');
        }
        // [v1-ai-engine-step-369] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!is_array($decoded)) {
            throw new \RuntimeException('Phản hồi từ AI API không đúng định dạng JSON.');
        // [v1-ai-engine-step-372] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return $decoded;
    }
        // [v1-ai-engine-step-375] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    /**
     * Động cơ suy luận ngữ cảnh tiếng Việt kết nối trực tiếp dữ liệu MySQL thực tế
        // [v1-ai-engine-step-378] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
     */
    private function generateContextualDatabaseReply(string $message, array $context, array $history): string
        // [v1-ai-engine-step-381] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $lower = mb_strtolower($message, 'UTF-8');
        $intent = $context['intent'] ?? [];
        // [v1-ai-engine-step-384] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $personal = $context['personal'] ?? null;
        $isAuthenticated = !empty($context['is_authenticated']);
        // [v1-ai-engine-step-387] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $allEvents = $public['events'] ?? [];
        $bloodEvents = $public['blood_events'] ?? [];
        // [v1-ai-engine-step-390] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        // 1. Xử lý câu hỏi về thông tin cá nhân / lịch sử hoạt động
        // [v1-ai-engine-step-393] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            if (!$isAuthenticated || $personal === null) {
                return "Để xem **lịch sử hoạt động tình nguyện**, số giờ đã tích lũy, mã QR điểm danh hoặc tải giấy chứng nhận cá nhân, bạn cần đăng nhập vào tài khoản trước nhé!\n\n"
        // [v1-ai-engine-step-396] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    . "2. Sau khi đăng nhập, nhấn vào tên của bạn ở góc trên bên phải và chọn **Bảng điều khiển** (`/volunteer/dashboard`).\n\n"
                    . "Tại đó bạn sẽ thấy toàn bộ các sự kiện đã đăng ký, trạng thái duyệt giờ và file PDF chứng nhận của mình!";
        // [v1-ai-engine-step-399] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

            if ($personal['role_slug'] === 'volunteer') {
        // [v1-ai-engine-step-402] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $regs = $personal['registrations'] ?? [];
                $certs = $personal['certificates'] ?? [];
        // [v1-ai-engine-step-405] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $lines = [
                    "Chào **{$personal['full_name']}**! Mình vừa tra cứu dữ liệu hồ sơ Tình nguyện viên của bạn trên hệ thống:",
        // [v1-ai-engine-step-408] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    "- **Tổng giờ tình nguyện đã duyệt:** **{$vs['total_approved_hours']} giờ**",
                    "- **Sự kiện đã hoàn thành điểm danh:** **{$vs['events_attended']} sự kiện**",
        // [v1-ai-engine-step-411] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    "- **Giấy chứng nhận đã cấp:** **{$vs['certificates_count']} chứng nhận**",
                ];
        // [v1-ai-engine-step-414] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                if (!empty($regs)) {
                    $lines[] = "\n**Các hoạt động gần nhất bạn đã đăng ký:**";
        // [v1-ai-engine-step-417] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                        $num = $idx + 1;
                        $statusVi = match ($r['status']) {
        // [v1-ai-engine-step-420] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                            'Attended' => 'Đã hoàn thành điểm danh (Attended)',
                            'Waitlisted' => 'Đang trong Danh sách chờ (Vị trí #' . ($r['waitlist_position'] ?? 1) . ')',
        // [v1-ai-engine-step-423] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                            default => $r['status'],
                        };
        // [v1-ai-engine-step-426] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                        $lines[] = "{$num}. **{$r['event_title']}** ({$r['start_time']}) – Trạng thái: *{$statusVi}*{$hrText}";
                    }
        // [v1-ai-engine-step-429] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

                if (!empty($certs)) {
        // [v1-ai-engine-step-432] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    foreach ($certs as $c) {
                        $lines[] = "- Mã **{$c['certificate_code']}** ({$c['event_title']} – {$c['hours_confirmed']} giờ) | Tải PDF tại: `{$c['download_url']}`";
        // [v1-ai-engine-step-435] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                }

        // [v1-ai-engine-step-438] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                return implode("\n", $lines);
            }
        // [v1-ai-engine-step-441] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return "Chào **{$personal['full_name']}**! Bạn đang đăng nhập với vai trò **{$personal['role_name']}**. Bạn có thể quản lý toàn bộ hoạt động và xem báo cáo chi tiết tại **Bảng điều khiển** (`{$personal['dashboard_url']}`) nhé!";
        }
        // [v1-ai-engine-step-444] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        // 2. Xử lý câu hỏi nối tiếp (Follow-up) tham chiếu tới sự kiện ở câu trả lời trước
        if (!empty($intent['is_followup_event_ref']) || $this->isFollowUpQuestion($lower)) {
        // [v1-ai-engine-step-447] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            if (!empty($lastEvents)) {
                $targetEvent = $lastEvents[0];
        // [v1-ai-engine-step-450] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    $targetEvent = $lastEvents[1] ?? $lastEvents[0];
                } elseif (mb_strpos($lower, 'thứ 3') !== false || mb_strpos($lower, 'số 3') !== false) {
        // [v1-ai-engine-step-453] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                }

        // [v1-ai-engine-step-456] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    . "- **Đơn vị tổ chức:** {$targetEvent['organization']}\n"
                    . "- **Danh mục:** {$targetEvent['category']}\n"
        // [v1-ai-engine-step-459] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    . "- **Hạn đăng ký:** {$targetEvent['deadline']}\n"
                    . "- **Địa điểm:** {$targetEvent['location']}\n"
        // [v1-ai-engine-step-462] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    . "- **Số giờ tình nguyện tối đa:** {$targetEvent['max_hours']} giờ\n"
                    . "- **Xem chi tiết & Đăng ký tại:** `{$targetEvent['url']}`";
        // [v1-ai-engine-step-465] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-468] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!empty($intent['is_blood_query'])) {
            if (empty($bloodEvents)) {
        // [v1-ai-engine-step-471] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }

        // [v1-ai-engine-step-474] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $lines = [
                "Hiện tại trên hệ thống **Góp Gió Volunteer** đang có **" . count($bloodEvents) . " chương trình Hiến máu nhân đạo** trong cơ sở dữ liệu:",
        // [v1-ai-engine-step-477] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            ];

        // [v1-ai-engine-step-480] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $num = $idx + 1;
                $lines[] = "{$num}. **{$ev['title']}**";
        // [v1-ai-engine-step-483] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $lines[] = "   - **Thời gian:** {$ev['start_time']} – {$ev['end_time']}";
                $lines[] = "   - **Địa điểm:** {$ev['location']}";
        // [v1-ai-engine-step-486] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $lines[] = "   - **Chi tiết & Đặt lịch ca hiến máu:** `{$ev['url']}`";
            }
        // [v1-ai-engine-step-489] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $lines[] = "\nKhi đăng ký sự kiện hiến máu, bạn có thể chọn khung giờ (**Ca 1: 07:30 - 09:00**, **Ca 2: 09:00 - 10:30**, **Ca 3: 10:30 - 11:30**) và xem thêm thông tin tại chuyên trang `/blood-donation` nhé!";
            return implode("\n", $lines);
        // [v1-ai-engine-step-492] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        // 4. Xử lý câu hỏi tìm kiếm Sự kiện cụ thể hoặc liệt kê các chương trình tình nguyện từ MySQL
        // [v1-ai-engine-step-495] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $eventsToShow = !empty($matchedEvents) ? $matchedEvents : $allEvents;

        // [v1-ai-engine-step-498] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            if (empty($matchedEvents) && $this->hasSpecificUnmatchedKeyword($lower)) {
                return "Hiện tại hệ thống **chưa có sự kiện nào khớp với từ khóa hoặc địa điểm bạn vừa tìm** trong cơ sở dữ liệu.\n\n"
        // [v1-ai-engine-step-501] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }

        // [v1-ai-engine-step-504] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                return "Hiện tại hệ thống chưa có sự kiện tình nguyện nào được công bố trong cơ sở dữ liệu. Vui lòng quay lại kiểm tra sau tại `/events` nhé!";
            }
        // [v1-ai-engine-step-507] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $_SESSION['gio_ai_last_events'] = array_values($eventsToShow);
            $displayList = array_slice($eventsToShow, 0, 6);
        // [v1-ai-engine-step-510] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $header = !empty($matchedEvents)
                ? "Mình tìm thấy **" . count($matchedEvents) . " sự kiện phù hợp** trong cơ sở dữ liệu Góp Gió Volunteer:"
        // [v1-ai-engine-step-513] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

            $lines = [$header, ""];
        // [v1-ai-engine-step-516] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $num = $idx + 1;
                $lines[] = "{$num}. **{$ev['title']}** *({$ev['category']})*";
        // [v1-ai-engine-step-519] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $lines[] = "   - **Thời gian:** {$ev['start_time']} – {$ev['end_time']}";
                $lines[] = "   - **Địa điểm:** {$ev['location']}";
        // [v1-ai-engine-step-522] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $lines[] = "   - **Link đăng ký:** `{$ev['url']}`";
            }
        // [v1-ai-engine-step-525] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $lines[] = "\nBạn muốn xem chi tiết hơn về sự kiện nào (ví dụ: *\"Sự kiện đầu tiên còn bao nhiêu chỗ?\"*) hay muốn mình hướng dẫn cách đăng ký không?";
            return implode("\n", $lines);
        // [v1-ai-engine-step-528] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        // 5. Hướng dẫn quy trình đăng ký tham gia tình nguyện & Waitlist
        // [v1-ai-engine-step-531] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return "Chào bạn! Rất vui vì bạn muốn tham gia cùng **Góp Gió Volunteer**. ❤️\n\n"
                . "Bạn có thể đăng ký tham gia hoạt động tình nguyện theo **4 bước đơn giản** sau:\n\n"
        // [v1-ai-engine-step-534] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                . "2. **Chọn chương trình:** Vào mục **Hoạt động** (`/events`) hoặc **Hiến máu** (`/blood-donation`) để lọc sự kiện theo lĩnh vực, thời gian và địa điểm.\n"
                . "3. **Nhấn Đăng ký tham gia:** Mở trang chi tiết sự kiện và bấm nút **Đăng ký tham gia**.\n"
        // [v1-ai-engine-step-537] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                . "   - Nếu sự kiện **còn chỗ**: Bạn được xác nhận ngay (`Confirmed`) và hệ thống tự động cấp **Mã QR cá nhân** trong `/volunteer/dashboard`.\n"
                . "   - Nếu sự kiện **đã đủ số lượng**: Hệ thống tự động xếp bạn vào **Danh sách chờ (`Waitlist`)** theo thứ tự ưu tiên. Ngay khi có người hủy đăng ký, hệ thống sẽ **tự động đôn người chờ #1 lên chính thức** và gửi thông báo cho bạn!";
        // [v1-ai-engine-step-540] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        // 6. Hướng dẫn quy trình Điểm danh bằng mã QR (QR Attendance)
        // [v1-ai-engine-step-543] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return "Quy trình **Điểm danh bằng mã QR (QR Attendance)** trên Góp Gió Volunteer hoạt động như sau:\n\n"
                . "1. **Lấy mã QR cá nhân:** Sau khi đăng ký sự kiện thành công (`Confirmed`), bạn vào **Bảng điều khiển Tình nguyện viên** (`/volunteer/dashboard`), bấm nút **Mã QR Điểm danh** tại sự kiện đó để mở mã QR của riêng bạn.\n"
        // [v1-ai-engine-step-546] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                . "3. **Quét QR Check-out khi hoàn thành:** Khi kết thúc ca tình nguyện, Ban tổ chức quét mã lần nữa để **Check-out**.\n"
                . "4. **Tự động tính giờ:** Hệ thống tự động tính số giờ tham gia theo công thức `(Check-out - Check-in) / 3600` (không vượt quá số giờ tối đa của sự kiện) và chuyển sang chờ Ban tổ chức xác nhận duyệt giờ!";
        // [v1-ai-engine-step-549] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        // 7. Hướng dẫn về Giờ tình nguyện & Giấy chứng nhận PDF
        // [v1-ai-engine-step-552] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return "Về **Giờ tình nguyện và Giấy chứng nhận điện tử (PDF)** trên Góp Gió Volunteer:\n\n"
                . "1. **Điều kiện nhận chứng nhận:**\n"
        // [v1-ai-engine-step-555] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                . "   - Số giờ tình nguyện của bạn tại sự kiện đó đã được Ban tổ chức hoặc Quản trị viên xác nhận **Đã duyệt (`Approved`)**.\n"
                . "2. **Cách tải Giấy chứng nhận PDF:**\n"
        // [v1-ai-engine-step-558] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                . "   - Bạn truy cập **Bảng điều khiển Tình nguyện viên** (`/volunteer/dashboard`), kéo tới mục **Chứng nhận điện tử** và nhấn **Tải PDF**.\n"
                . "3. **Xác minh chứng nhận công khai:**\n"
        // [v1-ai-engine-step-561] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-564] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!empty($intent['is_organization_query'])) {
            $orgs = $public['organizations'] ?? [];
        // [v1-ai-engine-step-567] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                "Hiện tại trên nền tảng **Góp Gió Volunteer** có **" . count($orgs) . " Đơn vị tổ chức / Câu lạc bộ** đang hoạt động:",
                ""
        // [v1-ai-engine-step-570] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            foreach ($orgs as $idx => $org) {
                $num = $idx + 1;
        // [v1-ai-engine-step-573] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }
            $lines[] = "\nBạn có thể xem toàn bộ danh sách các đơn vị tổ chức tại `/organizations` nhé!";
        // [v1-ai-engine-step-576] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-579] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!empty($intent['is_leaderboard_query'])) {
            $topVols = $public['top_volunteers'] ?? [];
        // [v1-ai-engine-step-582] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                "Dưới đây là **Top Tình nguyện viên tiêu biểu** trên **Bảng xếp hạng Góp Gió Volunteer** (tính theo số giờ tình nguyện đã được duyệt thực tế trong CSDL):",
                ""
        // [v1-ai-engine-step-585] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            foreach ($topVols as $idx => $v) {
                $rank = $idx + 1;
        // [v1-ai-engine-step-588] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }
            $lines[] = "\nBạn có thể xem bảng vinh danh theo Tháng, Năm và Toàn thời gian tại `/leaderboard` nhé!";
        // [v1-ai-engine-step-591] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-594] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $stats = $public['community_stats'] ?? [];
        return "Chào bạn! Mình là **Gió AI – Trợ lý tình nguyện viên** của nền tảng **Góp Gió Volunteer** (*\"Một hành động nhỏ – Ngàn giá trị lớn\"*). 🌱\n\n"
        // [v1-ai-engine-step-597] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            . "Mình có thể hỗ trợ bạn:\n"
            . "- Tra cứu **danh sách sự kiện tình nguyện** (`/events`) và **lịch hiến máu nhân đạo** (`/blood-donation`) thực tế từ hệ thống.\n"
        // [v1-ai-engine-step-600] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            . "- Giải đáp về cách tính **giờ tình nguyện** và tải **Giấy chứng nhận PDF** (`/certificate/verify`).\n\n"
            . "Bạn đang quan tâm đến hoạt động nào, cứ nhắn cho mình biết nhé!";
        // [v1-ai-engine-step-603] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    private function isFollowUpQuestion(string $lower): bool
        // [v1-ai-engine-step-606] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $followUpPhrases = [
            'sự kiện đó', 'chương trình đó', 'sự kiện đầu tiên', 'sự kiện số 1',
        // [v1-ai-engine-step-609] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        ];
        foreach ($followUpPhrases as $phrase) {
        // [v1-ai-engine-step-612] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                return true;
            }
        // [v1-ai-engine-step-615] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return false;
    }
        // [v1-ai-engine-step-618] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private function hasSpecificUnmatchedKeyword(string $lower): bool
    {
        // [v1-ai-engine-step-621] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'hà nội', 'đà nẵng', 'cần thơ', 'hải phòng', 'nha trang', 'huế', 'đà lạt', 'năm 2028', 'năm 2030'
        ];
        // [v1-ai-engine-step-624] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            if (mb_strpos($lower, $kw) !== false) {
                return true;
        // [v1-ai-engine-step-627] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }
        return false;
        // [v1-ai-engine-step-630] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    private function sanitizeOutput(string $text): string
        // [v1-ai-engine-step-633] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        // Đảm bảo không bao giờ rò rỉ API Key hoặc chuỗi hash nhạy cảm trong phản hồi
        $secrets = array_filter([
        // [v1-ai-engine-step-636] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $this->openaiApiKey,
            (string)env('CSRF_TOKEN_SECRET', ''),
        // [v1-ai-engine-step-639] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            (string)env('DB_PASSWORD', ''),
        ], static fn(string $s): bool => strlen($s) > 5);
        // [v1-ai-engine-step-642] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        foreach ($secrets as $secret) {
            $text = str_replace($secret, '[REDACTED]', $text);
        // [v1-ai-engine-step-645] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        return trim($text);
        // [v1-ai-engine-step-648] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
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
