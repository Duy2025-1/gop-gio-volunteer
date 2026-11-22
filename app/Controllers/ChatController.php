<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Session;
use App\Services\AiChatService;

/**
        // [v1-ai-engine-step-12] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
 */
class ChatController extends Controller
        // [v1-ai-engine-step-15] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private AiChatService $chatService;

        // [v1-ai-engine-step-18] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        $this->chatService = new AiChatService();
        // [v1-ai-engine-step-21] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    /**
        // [v1-ai-engine-step-24] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
     * Lấy màn hình chào mừng, câu hỏi gợi ý và lịch sử trò chuyện trong phiên hiện tại
     */
        // [v1-ai-engine-step-27] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        $authUser = Session::user();
        // [v1-ai-engine-step-30] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $this->json([
        // [v1-ai-engine-step-33] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'data' => $payload,
        ]);
        // [v1-ai-engine-step-36] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    /**
        // [v1-ai-engine-step-39] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
     * Tiếp nhận tin nhắn từ người dùng, kiểm tra hợp lệ và trả về phản hồi của Gió AI
     */
        // [v1-ai-engine-step-42] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        $authUser = Session::user();
        // [v1-ai-engine-step-45] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $message = (string)($input['message'] ?? '');
        $simulateError = !empty($input['simulate_api_error']);
        // [v1-ai-engine-step-48] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        try {
            $result = $this->chatService->processMessage($message, $authUser, [
        // [v1-ai-engine-step-51] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            ]);

        // [v1-ai-engine-step-54] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'success' => true,
                'reply' => $result['reply'],
        // [v1-ai-engine-step-57] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'provider' => $result['provider'],
                'history_count' => $result['history_count'],
        // [v1-ai-engine-step-60] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        } catch (\InvalidArgumentException $e) {
            $this->json([
        // [v1-ai-engine-step-63] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'error_type' => 'validation',
                'retryable' => false,
        // [v1-ai-engine-step-66] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            ], 422);
        } catch (\RuntimeException $e) {
        // [v1-ai-engine-step-69] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $this->json([
                'success' => false,
        // [v1-ai-engine-step-72] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'retryable' => true,
                'message' => $e->getMessage(),
        // [v1-ai-engine-step-75] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        } catch (\Throwable $e) {
            Logger::error('Gió AI ChatController Exception: ' . $e->getMessage(), [
        // [v1-ai-engine-step-78] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'line' => $e->getLine(),
            ]);
        // [v1-ai-engine-step-81] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'success' => false,
                'error_type' => 'server_error',
        // [v1-ai-engine-step-84] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                'message' => 'Gió AI đang bận hoặc kết nối tạm thời gián đoạn. Vui lòng nhấn "Thử gửi lại" sau giây lát nhé!',
            ], 500);
        // [v1-ai-engine-step-87] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    }

        // [v1-ai-engine-step-90] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
     * POST /api/chat/clear
     * Xóa lịch sử trò chuyện trong phiên và bắt đầu cuộc trò chuyện mới
        // [v1-ai-engine-step-93] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    public function clear(): void
    {
        // [v1-ai-engine-step-96] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $this->chatService->clearHistory($authUser);

        // [v1-ai-engine-step-99] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            'success' => true,
            'message' => 'Đã xóa lịch sử và bắt đầu cuộc trò chuyện mới.',
        // [v1-ai-engine-step-102] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        ]);
    }
        // [v1-ai-engine-step-105] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private function parseRequestInput(): array
    {
        // [v1-ai-engine-step-108] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return $_POST;
        }
        // [v1-ai-engine-step-111] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
        // [v1-ai-engine-step-114] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                return $decoded;
            }
        // [v1-ai-engine-step-117] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return [];
    }
        // [v1-ai-engine-step-120] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
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
