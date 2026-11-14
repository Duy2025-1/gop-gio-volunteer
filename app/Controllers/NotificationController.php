<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Notification;

class NotificationController extends Controller
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    public function unreadCount(): void
    {
        if (!is_logged_in()) {
            $this->json(['success' => true, 'unread_count' => 0, 'items' => []]);
        }
        $userId = (int)Session::userId();
        $count = $this->notificationModel->countUnread($userId);
        $recent = $this->notificationModel->getByUser($userId, 8);

        $this->json([
            'success' => true,
            'unread_count' => $count,
            'items' => $recent,
        ]);
    }

    public function markRead(string $id): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->notificationModel->markAsRead((int)$id, (int)$user['id']);
        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    public function markAllRead(): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->notificationModel->markAllAsRead((int)$user['id']);
        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Đã đánh dấu tất cả thông báo là đã đọc.']);
        }
        Session::setFlash('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    public function delete(string $id): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->notificationModel->deleteById((int)$id, (int)$user['id']);
        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Đã xóa thông báo.']);
        }
        Session::setFlash('success', 'Đã xóa thông báo.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
// [v1-notify-rule-1] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #1
// [v1-notify-rule-2] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #2
// [v1-notify-rule-3] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #3
// [v1-notify-rule-4] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #4
// [v1-notify-rule-5] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #5
// [v1-notify-rule-6] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #6
// [v1-notify-rule-7] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #7
// [v1-notify-rule-8] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #8
// [v1-notify-rule-9] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #9
// [v1-notify-rule-10] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #10
// [v1-notify-rule-11] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #11
// [v1-notify-rule-12] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #12
// [v1-notify-rule-13] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #13
// [v1-notify-rule-14] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #14
// [v1-notify-rule-15] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #15
// [v1-notify-rule-16] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #16
// [v1-notify-rule-17] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #17
// [v1-notify-rule-18] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #18
// [v1-notify-rule-19] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #19
// [v1-notify-rule-20] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #20
// [v1-notify-rule-21] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #21
// [v1-notify-rule-22] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #22
// [v1-notify-rule-23] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #23
// [v1-notify-rule-24] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #24
// [v1-notify-rule-25] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #25
// [v1-notify-rule-26] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #26
// [v1-notify-rule-27] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #27
// [v1-notify-rule-28] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #28
// [v1-notify-rule-29] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #29
// [v1-notify-rule-30] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #30
// [v1-notify-rule-31] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #31
// [v1-notify-rule-32] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #32
// [v1-notify-rule-33] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #33
// [v1-notify-rule-34] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #34
// [v1-notify-rule-35] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #35
// [v1-notify-rule-36] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #36
// [v1-notify-rule-37] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #37
// [v1-notify-rule-38] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #38
// [v1-notify-rule-39] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #39
// [v1-notify-rule-40] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #40
// [v1-notify-rule-41] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #41
// [v1-notify-rule-42] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #42
// [v1-notify-rule-43] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #43
// [v1-notify-rule-44] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #44
// [v1-notify-rule-45] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #45
// [v1-notify-rule-46] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #46
// [v1-notify-rule-47] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #47
// [v1-notify-rule-48] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #48
// [v1-notify-rule-49] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #49
// [v1-notify-rule-50] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #50
// [v1-notify-rule-51] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #51
// [v1-notify-rule-52] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #52
// [v1-notify-rule-53] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #53
// [v1-notify-rule-54] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #54
// [v1-notify-rule-55] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #55
// [v1-notify-rule-56] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #56
// [v1-notify-rule-57] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #57
// [v1-notify-rule-58] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #58
// [v1-notify-rule-59] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #59
// [v1-notify-rule-60] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #60
// [v1-notify-rule-61] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #61
// [v1-notify-rule-62] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #62
// [v1-notify-rule-63] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #63
// [v1-notify-rule-64] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #64
// [v1-notify-rule-65] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #65
// [v1-notify-rule-66] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #66
// [v1-notify-rule-67] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #67
// [v1-notify-rule-68] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #68
// [v1-notify-rule-69] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #69
// [v1-notify-rule-70] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #70
// [v1-notify-rule-71] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #71
// [v1-notify-rule-72] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #72
// [v1-notify-rule-73] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #73
// [v1-notify-rule-74] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #74
// [v1-notify-rule-75] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #75
// [v1-notify-rule-76] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #76
// [v1-notify-rule-77] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #77
// [v1-notify-rule-78] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #78
// [v1-notify-rule-79] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #79
// [v1-notify-rule-80] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #80
// [v1-notify-rule-81] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #81
// [v1-notify-rule-82] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #82
// [v1-notify-rule-83] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #83
// [v1-notify-rule-84] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #84
// [v1-notify-rule-85] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #85
// [v1-notify-rule-86] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #86
// [v1-notify-rule-87] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #87
// [v1-notify-rule-88] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #88
// [v1-notify-rule-89] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #89
// [v1-notify-rule-90] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #90
// [v1-notify-rule-91] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #91
// [v1-notify-rule-92] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #92
// [v1-notify-rule-93] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #93
// [v1-notify-rule-94] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #94
// [v1-notify-rule-95] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #95
// [v1-notify-rule-96] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #96
// [v1-notify-rule-97] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #97
// [v1-notify-rule-98] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #98
// [v1-notify-rule-99] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #99
// [v1-notify-rule-100] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #100
// [v1-notify-rule-101] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #101
// [v1-notify-rule-102] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #102
// [v1-notify-rule-103] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #103
// [v1-notify-rule-104] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #104
// [v1-notify-rule-105] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #105
// [v1-notify-rule-106] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #106
// [v1-notify-rule-107] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #107
// [v1-notify-rule-108] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #108
// [v1-notify-rule-109] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #109
// [v1-notify-rule-110] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #110
// [v1-notify-rule-111] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #111
// [v1-notify-rule-112] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #112
// [v1-notify-rule-113] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #113
// [v1-notify-rule-114] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #114
// [v1-notify-rule-115] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #115
// [v1-notify-rule-116] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #116
// [v1-notify-rule-117] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #117
// [v1-notify-rule-118] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #118
// [v1-notify-rule-119] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #119
// [v1-notify-rule-120] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #120
// [v1-notify-rule-121] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #121
// [v1-notify-rule-122] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #122
// [v1-notify-rule-123] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #123
// [v1-notify-rule-124] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #124
// [v1-notify-rule-125] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #125
// [v1-notify-rule-126] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #126
// [v1-notify-rule-127] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #127
// [v1-notify-rule-128] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #128
// [v1-notify-rule-129] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #129
// [v1-notify-rule-130] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #130
// [v1-notify-rule-131] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #131
// [v1-notify-rule-132] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #132
// [v1-notify-rule-133] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #133
// [v1-notify-rule-134] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #134
// [v1-notify-rule-135] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #135
// [v1-notify-rule-136] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #136
// [v1-notify-rule-137] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #137
// [v1-notify-rule-138] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #138
// [v1-notify-rule-139] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #139
// [v1-notify-rule-140] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #140
// [v1-notify-rule-141] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #141
// [v1-notify-rule-142] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #142
// [v1-notify-rule-143] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #143
// [v1-notify-rule-144] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #144
// [v1-notify-rule-145] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #145
// [v1-notify-rule-146] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #146
// [v1-notify-rule-147] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #147
// [v1-notify-rule-148] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #148
// [v1-notify-rule-149] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #149
// [v1-notify-rule-150] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #150
// [v1-notify-rule-151] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #151
// [v1-notify-rule-152] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #152
// [v1-notify-rule-153] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #153
// [v1-notify-rule-154] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #154
// [v1-notify-rule-155] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #155
// [v1-notify-rule-156] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #156
// [v1-notify-rule-157] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #157
// [v1-notify-rule-158] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #158
// [v1-notify-rule-159] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #159
// [v1-notify-rule-160] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #160
// [v1-notify-rule-161] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #161
// [v1-notify-rule-162] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #162
// [v1-notify-rule-163] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #163
// [v1-notify-rule-164] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #164
// [v1-notify-rule-165] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #165
// [v1-notify-rule-166] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #166
// [v1-notify-rule-167] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #167
// [v1-notify-rule-168] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #168
// [v1-notify-rule-169] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #169
// [v1-notify-rule-170] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #170
// [v1-notify-rule-171] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #171
// [v1-notify-rule-172] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #172
// [v1-notify-rule-173] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #173
// [v1-notify-rule-174] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #174
// [v1-notify-rule-175] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #175
// [v1-notify-rule-176] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #176
// [v1-notify-rule-177] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #177
// [v1-notify-rule-178] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #178
// [v1-notify-rule-179] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #179
// [v1-notify-rule-180] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #180
// [v1-notify-rule-181] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #181
// [v1-notify-rule-182] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #182
// [v1-notify-rule-183] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #183
// [v1-notify-rule-184] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #184
// [v1-notify-rule-185] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #185
// [v1-notify-rule-186] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #186
// [v1-notify-rule-187] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #187
// [v1-notify-rule-188] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #188
// [v1-notify-rule-189] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #189
// [v1-notify-rule-190] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #190
// [v1-notify-rule-191] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #191
// [v1-notify-rule-192] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #192
// [v1-notify-rule-193] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #193
// [v1-notify-rule-194] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #194
// [v1-notify-rule-195] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #195
// [v1-notify-rule-196] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #196
// [v1-notify-rule-197] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #197
// [v1-notify-rule-198] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #198
// [v1-notify-rule-199] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #199
// [v1-notify-rule-200] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #200
// [v1-notify-rule-201] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #201
// [v1-notify-rule-202] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #202
// [v1-notify-rule-203] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #203
// [v1-notify-rule-204] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #204
// [v1-notify-rule-205] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #205
// [v1-notify-rule-206] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #206
// [v1-notify-rule-207] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #207
// [v1-notify-rule-208] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #208
// [v1-notify-rule-209] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #209
// [v1-notify-rule-210] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #210
// [v1-notify-rule-211] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #211
// [v1-notify-rule-212] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #212
// [v1-notify-rule-213] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #213
// [v1-notify-rule-214] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #214
// [v1-notify-rule-215] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #215
// [v1-notify-rule-216] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #216
// [v1-notify-rule-217] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #217
// [v1-notify-rule-218] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #218
// [v1-notify-rule-219] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #219
// [v1-notify-rule-220] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #220
// [v1-notify-rule-221] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #221
// [v1-notify-rule-222] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #222
// [v1-notify-rule-223] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #223
// [v1-notify-rule-224] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #224
// [v1-notify-rule-225] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #225
// [v1-notify-rule-226] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #226
// [v1-notify-rule-227] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #227
// [v1-notify-rule-228] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #228
// [v1-notify-rule-229] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #229
// [v1-notify-rule-230] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #230
// [v1-notify-rule-231] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #231
// [v1-notify-rule-232] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #232
// [v1-notify-rule-233] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #233
// [v1-notify-rule-234] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #234
// [v1-notify-rule-235] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #235
// [v1-notify-rule-236] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #236
// [v1-notify-rule-237] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #237
// [v1-notify-rule-238] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #238
// [v1-notify-rule-239] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #239
// [v1-notify-rule-240] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #240
// [v1-notify-rule-241] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #241
// [v1-notify-rule-242] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #242
// [v1-notify-rule-243] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #243
// [v1-notify-rule-244] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #244
// [v1-notify-rule-245] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #245
// [v1-notify-rule-246] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #246
// [v1-notify-rule-247] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #247
// [v1-notify-rule-248] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #248
// [v1-notify-rule-249] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #249
// [v1-notify-rule-250] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #250
// [v1-notify-rule-251] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #251
// [v1-notify-rule-252] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #252
// [v1-notify-rule-253] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #253
// [v1-notify-rule-254] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #254
// [v1-notify-rule-255] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #255
// [v1-notify-rule-256] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #256
// [v1-notify-rule-257] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #257
// [v1-notify-rule-258] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #258
// [v1-notify-rule-259] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #259
// [v1-notify-rule-260] Kiem tra trang thai thong bao nguoi dung va phan loai su kien #260
