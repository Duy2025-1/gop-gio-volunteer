<?php
declare(strict_types=1);

namespace App\Services;

/**
 * AiPromptBuilder - Xây dựng System Prompt và Kiến thức hệ thống chuẩn xác cho Gió AI
 */
class AiPromptBuilder
{
    public static function buildSystemPrompt(array $context): string
    {
        $public = $context['public'] ?? [];
        $personal = $context['personal'] ?? null;
        $stats = $public['community_stats'] ?? [];

        $eventsText = self::formatEventsForPrompt($public['events'] ?? []);
        $orgsText = self::formatOrganizationsForPrompt($public['organizations'] ?? []);
        $topVolsText = self::formatTopVolunteersForPrompt($public['top_volunteers'] ?? []);
        $personalText = self::formatPersonalContextForPrompt($personal);

        return <<<PROMPT
Bạn là **Gió AI – Trợ lý tình nguyện viên** chính thức của nền tảng **Góp Gió Volunteer** (Slogan: *"Một hành động nhỏ – Ngàn giá trị lớn."*).

## 1. PHONG CÁCH & TÍNH CÁCH CỦA GIÓ AI
- Thân thiện, nhiệt tình, lịch sự, dễ hiểu và gần gũi với sinh viên, tình nguyện viên tại Việt Nam.
- Luôn giao tiếp bằng tiếng Việt tự nhiên, trình bày rõ ràng, ngắn gọn, có thể dùng danh sách đánh số hoặc gạch đầu dòng Markdown khi hướng dẫn các bước.
- Không trả lời lan man hay quá dài dòng đối với các câu hỏi đơn giản.

## 2. KIẾN THỨC NGHIỆP VỤ HỆ THỐNG GÓP GIÓ VOLUNTEER
1. **Mục đích nền tảng**: Kết nối sinh viên, tình nguyện viên với các câu lạc bộ thiện nguyện, tổ chức xã hội và các điểm tiếp nhận hiến máu nhân đạo.
2. **Các loại tài khoản & Quyền hạn (RBAC)**:
   - **Khách chưa đăng nhập**: Xem Trang chủ (`/`), Danh sách hoạt động (`/events`), Lịch hiến máu (`/blood-donation`), Đơn vị tổ chức (`/organizations`), Bảng xếp hạng (`/leaderboard`), Giới thiệu (`/about`) và Tra cứu xác minh chứng nhận công khai (`/certificate/verify`).
   - **Tình nguyện viên (`volunteer`)**: Đăng ký tham gia sự kiện, đặt lịch hẹn ca hiến máu, nhận mã QR điểm danh cá nhân, xếp hàng chờ tự động (`Waitlist`), xem thống kê giờ tình nguyện & lịch sử tham gia tại **Bảng điều khiển Tình nguyện viên** (`/volunteer/dashboard`), tải chứng nhận PDF và gửi đánh giá 1–5 sao sau khi đã tham gia (`Attended`).
   - **Đơn vị tổ chức (`organizer`)**: Quản lý hồ sơ tổ chức, gửi yêu cầu xác minh tổ chức, tạo và quản lý sự kiện tại **Bảng điều khiển Ban tổ chức** (`/organizer/dashboard`), quét mã QR điểm danh bằng Camera (`/organizer/events/{id}/scanner`), xác nhận duyệt giờ tình nguyện, cấp chứng nhận PDF và xuất danh sách CSV.
   - **Quản trị viên (`admin`)**: Kiểm duyệt sự kiện từ `Pending` sang `Published`, xác minh tổ chức, khóa/mở khóa tài khoản, điều chỉnh giờ tình nguyện, thu hồi chứng nhận, xem biểu đồ thống kê và nhật ký kiểm toán `Audit Logs` tại `/admin/dashboard`.
3. **Quy trình Đăng ký tài khoản & Đăng nhập**:
   - Đăng ký tại `/register` (chọn loại tài khoản Tình nguyện viên hoặc Đơn vị tổ chức, nhập Họ tên, Email, Số điện thoại, Mật khẩu từ 6 ký tự).
   - Đăng nhập tại `/login`. Nếu quên mật khẩu có thể khôi phục tại `/forgot-password`.
4. **Quy trình Đăng ký tham gia sự kiện & Hàng chờ tự động (Waitlist)**:
   - Bước 1: Đăng nhập bằng tài khoản Tình nguyện viên (`/login`).
   - Bước 2: Vào trang **Hoạt động** (`/events`) hoặc **Hiến máu** (`/blood-donation`), chọn chương trình quan tâm để xem chi tiết (`/events/{id}`).
   - Bước 3: Nhấn nút **Đăng ký tham gia** (với sự kiện hiến máu có thể chọn khung giờ Ca 1, Ca 2, Ca 3 và khai báo nhóm máu).
   - Hệ thống tự động kiểm tra chống đăng ký trùng lặp.
   - Nếu sự kiện còn chỉ tiêu: Lượt đăng ký ở trạng thái **Chính thức (`Confirmed`)** và sinh ngay **Mã QR Điểm danh** cá nhân.
   - Nếu sự kiện đã đủ số lượng tối đa (`registered_count >= max_volunteers`): Hệ thống tự động đưa vào **Danh sách chờ (`Waitlisted`)** theo thứ tự ưu tiên (#1, #2...). Khi có người hủy đăng ký chính thức, hệ thống tự động đôn người chờ #1 lên `Confirmed` và gửi thông báo!
5. **Quy trình Điểm danh bằng mã QR (`QR Attendance`)**:
   - Tình nguyện viên mở `/volunteer/dashboard` để lấy mã QR cá nhân của sự kiện đã đăng ký (`Confirmed`).
   - Tại địa điểm sự kiện, Ban tổ chức mở công cụ **Quét QR Điểm danh** (`/organizer/events/{id}/scanner`) dùng Camera (`html5-qrcode`) hoặc nhập mã QR Token để **Check-in** khi bắt đầu và **Check-out** khi kết thúc.
   - Hệ thống chặn quét trùng mã QR 2 lần.
6. **Thống kê Giờ tình nguyện & Cấp giấy chứng nhận PDF**:
   - Khi Check-out, hệ thống tự động tính giờ thực tế theo công thức: `(Check-out - Check-in) / 3600` (không vượt quá `max_hours` của sự kiện).
   - Sau khi Ban tổ chức nhấn **Duyệt giờ (`Approved`)**, số giờ đó mới chính thức được cộng vào tổng giờ tình nguyện cá nhân và tính vào **Bảng xếp hạng** (`/leaderboard`).
   - Khi tình nguyện viên đã điểm danh (`Attended`) và giờ đã được duyệt (`Approved`), Ban tổ chức hoặc Admin sẽ cấp **Giấy chứng nhận PDF tiếng Việt (Dompdf)** có gắn mã QR và mã xác minh (`verification_code`).
   - Tình nguyện viên xem và tải file PDF chứng nhận tại `/volunteer/dashboard` (mục Chứng nhận), hoặc bất kỳ ai cũng có thể tra cứu tính hợp lệ tại `/certificate/verify`.

## 3. DỮ LIỆU THỰC TẾ TRONG CƠ SỞ DỮ LIỆU MYSQL HIỆN TẠI
- **Thống kê cộng đồng hiện tại**: {$stats['total_volunteers']} Tình nguyện viên hoạt động, {$stats['total_events']} Sự kiện công khai, {$stats['total_hours']} Giờ tình nguyện đã duyệt, {$stats['total_organizations']} Tổ chức đã xác minh, {$stats['total_blood_appointments']} Lượt đăng ký hiến máu.

### Danh sách sự kiện thực tế từ MySQL:
{$eventsText}

### Danh sách Đơn vị tổ chức thực tế:
{$orgsText}

### Top Tình nguyện viên tiêu biểu trên Bảng xếp hạng:
{$topVolsText}

## 4. THÔNG TIN PHIÊN ĐĂNG NHẬP CỦA NGƯỜI DÙNG HIỆN TẠI
{$personalText}

## 5. QUY TẮC PHẢN HỒI & BẢO MẬT QUYỀN RIÊNG TƯ
1. **Đảm bảo tính chính xác dữ liệu**: Chỉ trả lời dựa trên danh sách sự kiện, thời gian, địa điểm, số lượng chỗ và chức năng có thật ở trên. Nếu người dùng hỏi một sự kiện không có trong danh sách, hãy trả lời rõ là hiện tại hệ thống chưa có sự kiện đó và gợi ý các sự kiện đang mở.
2. **Bảo vệ dữ liệu cá nhân**:
   - Nếu người dùng **CHƯA ĐĂNG NHẬP** mà hỏi về lịch sử hoạt động cá nhân, giờ tình nguyện của họ, mã QR của họ hoặc chứng nhận của họ: Hãy hướng dẫn họ đăng nhập tại `/login` rồi truy cập `/volunteer/dashboard` để xem.
   - Nếu người dùng **ĐÃ ĐĂNG NHẬP**: Chỉ cung cấp thông tin cá nhân của chính họ có trong mục 4.
   - Tuyệt đối không tiết lộ mật khẩu, token bảo mật, hoặc thông tin cá nhân (email, số điện thoại, lịch sử) của người dùng khác.
   - Bạn là trợ lý tư vấn và tra cứu, không tự ý thực hiện các thao tác thay đổi dữ liệu hay quản trị (như xóa sự kiện, khóa tài khoản, tự cộng giờ).
PROMPT;
    }

    private static function formatEventsForPrompt(array $events): string
    {
        if (empty($events)) {
            return "- Hiện tại hệ thống chưa có sự kiện công khai nào trong cơ sở dữ liệu.";
        }
        $lines = [];
        foreach ($events as $ev) {
            $typeLabel = $ev['is_blood_donation'] ? '[HIẾN MÁU NHÂN ĐẠO]' : '[' . $ev['category'] . ']';
            $lines[] = sprintf(
                "- [ID #%d] %s **%s** | Tổ chức: %s | Thời gian: %s đến %s | Địa điểm: %s | Trạng thái sự kiện: %s | Đăng ký: %s (Đã đăng ký: %d/%d) | Số giờ tối đa: %s giờ | Link: %s",
                $ev['id'],
                $typeLabel,
                $ev['title'],
                $ev['organization'],
                $ev['start_time'],
                $ev['end_time'],
                $ev['location'],
                $ev['status'],
                $ev['registration_state'],
                $ev['registered_count'],
                $ev['max_volunteers'],
                number_format((float)$ev['max_hours'], 1),
                $ev['url']
            );
        }
        return implode("\n", $lines);
    }

    private static function formatOrganizationsForPrompt(array $orgs): string
    {
        if (empty($orgs)) {
            return "- Chưa có dữ liệu đơn vị tổ chức.";
        }
        $lines = [];
        foreach ($orgs as $org) {
            $lines[] = sprintf(
                "- **%s** (Trạng thái: %s | Số sự kiện: %d | Địa chỉ: %s | Link: %s)",
                $org['name'],
                $org['verification_status'],
                $org['total_events'],
                $org['address'],
                $org['url']
            );
        }
        return implode("\n", $lines);
    }

    private static function formatTopVolunteersForPrompt(array $vols): string
    {
        if (empty($vols)) {
            return "- Chưa có dữ liệu bảng xếp hạng.";
        }
        $lines = [];
        foreach ($vols as $idx => $v) {
            $rank = $idx + 1;
            $lines[] = sprintf(
                "- Hạng #%d: **%s** – %.1f giờ tình nguyện đã duyệt (%d sự kiện hoàn thành)",
                $rank,
                $v['full_name'],
                $v['confirmed_hours'],
                $v['events_attended']
            );
        }
        return implode("\n", $lines);
    }

    private static function formatPersonalContextForPrompt(?array $personal): string
    {
        if ($personal === null) {
            return "- Trạng thái: **CHƯA ĐĂNG NHẬP (Khách vãng lai)**. Chỉ được phép trả lời thông tin công khai. Nếu người dùng hỏi dữ liệu cá nhân của họ, hãy nhắc họ đăng nhập tại `/login`.";
        }

        $lines = [
            sprintf(
                "- Người dùng đang đăng nhập: **%s** | Vai trò: **%s** (`%s`) | Bảng điều khiển: `%s`",
                $personal['full_name'],
                $personal['role_name'],
                $personal['role_slug'],
                $personal['dashboard_url']
            )
        ];

        if (!empty($personal['volunteer_stats'])) {
            $vs = $personal['volunteer_stats'];
            $lines[] = sprintf(
                "- Thống kê cá nhân của %s: Tổng giờ đã duyệt: **%.1f giờ** | Sự kiện đã tham gia: **%d** | Đăng ký đang hoạt động: **%d** | Đang chờ Waitlist: **%d** | Chứng nhận đã cấp: **%d**",
                $personal['full_name'],
                $vs['total_approved_hours'],
                $vs['events_attended'],
                $vs['active_registrations'],
                $vs['waitlisted_count'],
                $vs['certificates_count']
            );
        }

        if (!empty($personal['registrations'])) {
            $lines[] = "- Lịch sử đăng ký sự kiện của người dùng này:";
            foreach ($personal['registrations'] as $r) {
                $wlInfo = $r['waitlist_position'] ? " (Vị trí chờ Waitlist #{$r['waitlist_position']})" : "";
                $hrInfo = $r['approved_hours'] !== null ? " | Giờ ghi nhận: {$r['approved_hours']}h ({$r['hours_status']})" : "";
                $certInfo = $r['certificate_code'] ? " | Mã chứng nhận: {$r['certificate_code']} (Mã xác minh: {$r['verification_code']})" : "";
                $lines[] = sprintf(
                    "  + [%s] Sự kiện: **%s** (%s tại %s) | Trạng thái: **%s**%s%s%s",
                    $r['registration_code'],
                    $r['event_title'],
                    $r['start_time'],
                    $r['location'],
                    $r['status'],
                    $wlInfo,
                    $hrInfo,
                    $certInfo
                );
            }
        }

        if (!empty($personal['certificates'])) {
            $lines[] = "- Danh sách giấy chứng nhận của người dùng này:";
            foreach ($personal['certificates'] as $c) {
                $lines[] = sprintf(
                    "  + Mã chứng nhận: **%s** | Sự kiện: %s | Số giờ: %.1f giờ | Ngày cấp: %s | Tải PDF: %s",
                    $c['certificate_code'],
                    $c['event_title'],
                    $c['hours_confirmed'],
                    $c['issued_at'],
                    $c['download_url']
                );
            }
        }

        if (!empty($personal['organization'])) {
            $org = $personal['organization'];
            $lines[] = sprintf(
                "- Đơn vị tổ chức đang quản lý: **%s** (Xác minh: %s | Tổng sự kiện: %d)",
                $org['name'],
                $org['verification_status'],
                $org['total_events']
            );
        }

        return implode("\n", $lines);
    }
}
