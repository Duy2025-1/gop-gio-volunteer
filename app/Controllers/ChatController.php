<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Session;
use App\Services\AiChatService;

/**
 * ChatController - API Backend xử lý tin nhắn và lịch sử trò chuyện cho Gió AI
 */
class ChatController extends Controller
{
    private AiChatService $chatService;

    public function __construct()
    {
        $this->chatService = new AiChatService();
    }

    /**
     * GET /api/chat/history
     * Lấy màn hình chào mừng, câu hỏi gợi ý và lịch sử trò chuyện trong phiên hiện tại
     */
    public function history(): void
    {
        $authUser = Session::user();
        $payload = $this->chatService->getWelcomePayload($authUser);

        $this->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    /**
     * POST /api/chat/send
     * Tiếp nhận tin nhắn từ người dùng, kiểm tra hợp lệ và trả về phản hồi của Gió AI
     */
    public function send(): void
    {
        $authUser = Session::user();
        $input = $this->parseRequestInput();
        $message = (string)($input['message'] ?? '');
        $simulateError = !empty($input['simulate_api_error']);

        try {
            $result = $this->chatService->processMessage($message, $authUser, [
                'simulate_api_error' => $simulateError,
            ]);

            $this->json([
                'success' => true,
                'reply' => $result['reply'],
                'timestamp' => $result['timestamp'],
                'provider' => $result['provider'],
                'history_count' => $result['history_count'],
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->json([
                'success' => false,
                'error_type' => 'validation',
                'retryable' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\RuntimeException $e) {
            $isRateLimit = str_contains($e->getMessage(), 'quá nhanh');
            $this->json([
                'success' => false,
                'error_type' => $isRateLimit ? 'rate_limit' : 'ai_service',
                'retryable' => true,
                'message' => $e->getMessage(),
            ], $isRateLimit ? 429 : 503);
        } catch (\Throwable $e) {
            Logger::error('Gió AI ChatController Exception: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->json([
                'success' => false,
                'error_type' => 'server_error',
                'retryable' => true,
                'message' => 'Gió AI đang bận hoặc kết nối tạm thời gián đoạn. Vui lòng nhấn "Thử gửi lại" sau giây lát nhé!',
            ], 500);
        }
    }

    /**
     * POST /api/chat/clear
     * Xóa lịch sử trò chuyện trong phiên và bắt đầu cuộc trò chuyện mới
     */
    public function clear(): void
    {
        $authUser = Session::user();
        $this->chatService->clearHistory($authUser);

        $this->json([
            'success' => true,
            'message' => 'Đã xóa lịch sử và bắt đầu cuộc trò chuyện mới.',
            'data' => $this->chatService->getWelcomePayload($authUser),
        ]);
    }

    private function parseRequestInput(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }
        $raw = file_get_contents('php://input');
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }
}
