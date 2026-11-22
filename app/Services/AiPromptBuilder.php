<?php
declare(strict_types=1);

namespace App\Services;

/**
 * AiPromptBuilder - Xây dựng System Prompt và Kiến thức hệ thống chuẩn xác cho Gió AI
 */
class AiPromptBuilder
{
    public static function buildSystemPrompt(array $context): string
        // [v1-ai-engine-step-12] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $public = $context['public'] ?? [];
        $personal = $context['personal'] ?? null;
        // [v1-ai-engine-step-15] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        $eventsText = self::formatEventsForPrompt($public['events'] ?? []);
        // [v1-ai-engine-step-18] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $topVolsText = self::formatTopVolunteersForPrompt($public['top_volunteers'] ?? []);
        $personalText = self::formatPersonalContextForPrompt($personal);
        // [v1-ai-engine-step-21] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return <<<PROMPT
Bạn là **Gió AI – Trợ lý tình nguyện viên** chính thức của nền tảng **Góp Gió Volunteer** (Slogan: *"Một hành động nhỏ – Ngàn giá trị lớn."*).
        // [v1-ai-engine-step-24] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
## 1. PHONG CÁCH & TÍNH CÁCH CỦA GIÓ AI
- Thân thiện, nhiệt tình, lịch sự, dễ hiểu và gần gũi với sinh viên, tình nguyện viên tại Việt Nam.
        // [v1-ai-engine-step-27] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
- Không trả lời lan man hay quá dài dòng đối với các câu hỏi đơn giản.

        // [v1-ai-engine-step-30] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
1. **Mục đích nền tảng**: Kết nối sinh viên, tình nguyện viên với các câu lạc bộ thiện nguyện, tổ chức xã hội và các điểm tiếp nhận hiến máu nhân đạo.
2. **Các loại tài khoản & Quyền hạn (RBAC)**:
        // [v1-ai-engine-step-33] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
   - **Tình nguyện viên (`volunteer`)**: Đăng ký tham gia sự kiện, đặt lịch hẹn ca hiến máu, nhận mã QR điểm danh cá nhân, xếp hàng chờ tự động (`Waitlist`), xem thống kê giờ tình nguyện & lịch sử tham gia tại **Bảng điều khiển Tình nguyện viên** (`/volunteer/dashboard`), tải chứng nhận PDF và gửi đánh giá 1–5 sao sau khi đã tham gia (`Attended`).
   - **Đơn vị tổ chức (`organizer`)**: Quản lý hồ sơ tổ chức, gửi yêu cầu xác minh tổ chức, tạo và quản lý sự kiện tại **Bảng điều khiển Ban tổ chức** (`/organizer/dashboard`), quét mã QR điểm danh bằng Camera (`/organizer/events/{id}/scanner`), xác nhận duyệt giờ tình nguyện, cấp chứng nhận PDF và xuất danh sách CSV.
        // [v1-ai-engine-step-36] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
3. **Quy trình Đăng ký tài khoản & Đăng nhập**:
   - Đăng ký tại `/register` (chọn loại tài khoản Tình nguyện viên hoặc Đơn vị tổ chức, nhập Họ tên, Email, Số điện thoại, Mật khẩu từ 6 ký tự).
        // [v1-ai-engine-step-39] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
4. **Quy trình Đăng ký tham gia sự kiện & Hàng chờ tự động (Waitlist)**:
   - Bước 1: Đăng nhập bằng tài khoản Tình nguyện viên (`/login`).
        // [v1-ai-engine-step-42] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
   - Bước 3: Nhấn nút **Đăng ký tham gia** (với sự kiện hiến máu có thể chọn khung giờ Ca 1, Ca 2, Ca 3 và khai báo nhóm máu).
   - Hệ thống tự động kiểm tra chống đăng ký trùng lặp.
        // [v1-ai-engine-step-45] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
   - Nếu sự kiện đã đủ số lượng tối đa (`registered_count >= max_volunteers`): Hệ thống tự động đưa vào **Danh sách chờ (`Waitlisted`)** theo thứ tự ưu tiên (#1, #2...). Khi có người hủy đăng ký chính thức, hệ thống tự động đôn người chờ #1 lên `Confirmed` và gửi thông báo!
5. **Quy trình Điểm danh bằng mã QR (`QR Attendance`)**:
        // [v1-ai-engine-step-48] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
   - Tại địa điểm sự kiện, Ban tổ chức mở công cụ **Quét QR Điểm danh** (`/organizer/events/{id}/scanner`) dùng Camera (`html5-qrcode`) hoặc nhập mã QR Token để **Check-in** khi bắt đầu và **Check-out** khi kết thúc.
   - Hệ thống chặn quét trùng mã QR 2 lần.
        // [v1-ai-engine-step-51] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
   - Khi Check-out, hệ thống tự động tính giờ thực tế theo công thức: `(Check-out - Check-in) / 3600` (không vượt quá `max_hours` của sự kiện).
   - Sau khi Ban tổ chức nhấn **Duyệt giờ (`Approved`)**, số giờ đó mới chính thức được cộng vào tổng giờ tình nguyện cá nhân và tính vào **Bảng xếp hạng** (`/leaderboard`).
        // [v1-ai-engine-step-54] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
   - Tình nguyện viên xem và tải file PDF chứng nhận tại `/volunteer/dashboard` (mục Chứng nhận), hoặc bất kỳ ai cũng có thể tra cứu tính hợp lệ tại `/certificate/verify`.

        // [v1-ai-engine-step-57] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
- **Thống kê cộng đồng hiện tại**: {$stats['total_volunteers']} Tình nguyện viên hoạt động, {$stats['total_events']} Sự kiện công khai, {$stats['total_hours']} Giờ tình nguyện đã duyệt, {$stats['total_organizations']} Tổ chức đã xác minh, {$stats['total_blood_appointments']} Lượt đăng ký hiến máu.

        // [v1-ai-engine-step-60] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
{$eventsText}

        // [v1-ai-engine-step-63] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
{$orgsText}

        // [v1-ai-engine-step-66] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
{$topVolsText}

        // [v1-ai-engine-step-69] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
{$personalText}

        // [v1-ai-engine-step-72] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
1. **Đảm bảo tính chính xác dữ liệu**: Chỉ trả lời dựa trên danh sách sự kiện, thời gian, địa điểm, số lượng chỗ và chức năng có thật ở trên. Nếu người dùng hỏi một sự kiện không có trong danh sách, hãy trả lời rõ là hiện tại hệ thống chưa có sự kiện đó và gợi ý các sự kiện đang mở.
2. **Bảo vệ dữ liệu cá nhân**:
        // [v1-ai-engine-step-75] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
   - Nếu người dùng **ĐÃ ĐĂNG NHẬP**: Chỉ cung cấp thông tin cá nhân của chính họ có trong mục 4.
   - Tuyệt đối không tiết lộ mật khẩu, token bảo mật, hoặc thông tin cá nhân (email, số điện thoại, lịch sử) của người dùng khác.
        // [v1-ai-engine-step-78] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
PROMPT;
    }
        // [v1-ai-engine-step-81] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private static function formatEventsForPrompt(array $events): string
    {
        // [v1-ai-engine-step-84] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return "- Hiện tại hệ thống chưa có sự kiện công khai nào trong cơ sở dữ liệu.";
        }
        // [v1-ai-engine-step-87] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        foreach ($events as $ev) {
            $typeLabel = $ev['is_blood_donation'] ? '[HIẾN MÁU NHÂN ĐẠO]' : '[' . $ev['category'] . ']';
        // [v1-ai-engine-step-90] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                "- [ID #%d] %s **%s** | Tổ chức: %s | Thời gian: %s đến %s | Địa điểm: %s | Trạng thái sự kiện: %s | Đăng ký: %s (Đã đăng ký: %d/%d) | Số giờ tối đa: %s giờ | Link: %s",
                $ev['id'],
        // [v1-ai-engine-step-93] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $ev['title'],
                $ev['organization'],
        // [v1-ai-engine-step-96] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $ev['end_time'],
                $ev['location'],
        // [v1-ai-engine-step-99] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $ev['registration_state'],
                $ev['registered_count'],
        // [v1-ai-engine-step-102] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                number_format((float)$ev['max_hours'], 1),
                $ev['url']
        // [v1-ai-engine-step-105] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }
        return implode("\n", $lines);
        // [v1-ai-engine-step-108] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

    private static function formatOrganizationsForPrompt(array $orgs): string
        // [v1-ai-engine-step-111] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (empty($orgs)) {
            return "- Chưa có dữ liệu đơn vị tổ chức.";
        // [v1-ai-engine-step-114] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $lines = [];
        foreach ($orgs as $org) {
        // [v1-ai-engine-step-117] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                "- **%s** (Trạng thái: %s | Số sự kiện: %d | Địa chỉ: %s | Link: %s)",
                $org['name'],
        // [v1-ai-engine-step-120] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $org['total_events'],
                $org['address'],
        // [v1-ai-engine-step-123] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            );
        }
        // [v1-ai-engine-step-126] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    }

        // [v1-ai-engine-step-129] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    {
        if (empty($vols)) {
        // [v1-ai-engine-step-132] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }
        $lines = [];
        // [v1-ai-engine-step-135] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $rank = $idx + 1;
            $lines[] = sprintf(
        // [v1-ai-engine-step-138] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $rank,
                $v['full_name'],
        // [v1-ai-engine-step-141] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $v['events_attended']
            );
        // [v1-ai-engine-step-144] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        return implode("\n", $lines);
    }
        // [v1-ai-engine-step-147] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
    private static function formatPersonalContextForPrompt(?array $personal): string
    {
        // [v1-ai-engine-step-150] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            return "- Trạng thái: **CHƯA ĐĂNG NHẬP (Khách vãng lai)**. Chỉ được phép trả lời thông tin công khai. Nếu người dùng hỏi dữ liệu cá nhân của họ, hãy nhắc họ đăng nhập tại `/login`.";
        }
        // [v1-ai-engine-step-153] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        $lines = [
            sprintf(
        // [v1-ai-engine-step-156] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $personal['full_name'],
                $personal['role_name'],
        // [v1-ai-engine-step-159] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $personal['dashboard_url']
            )
        // [v1-ai-engine-step-162] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        if (!empty($personal['volunteer_stats'])) {
        // [v1-ai-engine-step-165] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $lines[] = sprintf(
                "- Thống kê cá nhân của %s: Tổng giờ đã duyệt: **%.1f giờ** | Sự kiện đã tham gia: **%d** | Đăng ký đang hoạt động: **%d** | Đang chờ Waitlist: **%d** | Chứng nhận đã cấp: **%d**",
        // [v1-ai-engine-step-168] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $vs['total_approved_hours'],
                $vs['events_attended'],
        // [v1-ai-engine-step-171] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $vs['waitlisted_count'],
                $vs['certificates_count']
        // [v1-ai-engine-step-174] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        }

        // [v1-ai-engine-step-177] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            $lines[] = "- Lịch sử đăng ký sự kiện của người dùng này:";
            foreach ($personal['registrations'] as $r) {
        // [v1-ai-engine-step-180] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $hrInfo = $r['approved_hours'] !== null ? " | Giờ ghi nhận: {$r['approved_hours']}h ({$r['hours_status']})" : "";
                $certInfo = $r['certificate_code'] ? " | Mã chứng nhận: {$r['certificate_code']} (Mã xác minh: {$r['verification_code']})" : "";
        // [v1-ai-engine-step-183] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    "  + [%s] Sự kiện: **%s** (%s tại %s) | Trạng thái: **%s**%s%s%s",
                    $r['registration_code'],
        // [v1-ai-engine-step-186] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    $r['start_time'],
                    $r['location'],
        // [v1-ai-engine-step-189] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    $wlInfo,
                    $hrInfo,
        // [v1-ai-engine-step-192] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                );
            }
        // [v1-ai-engine-step-195] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        if (!empty($personal['certificates'])) {
        // [v1-ai-engine-step-198] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            foreach ($personal['certificates'] as $c) {
                $lines[] = sprintf(
        // [v1-ai-engine-step-201] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    $c['certificate_code'],
                    $c['event_title'],
        // [v1-ai-engine-step-204] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                    $c['issued_at'],
                    $c['download_url']
        // [v1-ai-engine-step-207] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
            }
        }
        // [v1-ai-engine-step-210] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
        if (!empty($personal['organization'])) {
            $org = $personal['organization'];
        // [v1-ai-engine-step-213] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                "- Đơn vị tổ chức đang quản lý: **%s** (Xác minh: %s | Tổng sự kiện: %d)",
                $org['name'],
        // [v1-ai-engine-step-216] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
                $org['total_events']
            );
        // [v1-ai-engine-step-219] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien

        return implode("\n", $lines);
        // [v1-ai-engine-step-222] Xu ly phan tich ngu canh hoi thoai va truy van du lieu su kien
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
