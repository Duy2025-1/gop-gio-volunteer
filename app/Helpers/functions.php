<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

/**
 * Global HTML Escape Helper (XSS Prevention)
 */
function e(mixed $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_field(): string
{
    return Csrf::field();
}

function csrf_token(): string
{
    return Csrf::token();
}

function current_user(): ?array
{
    return Session::user();
}

function is_logged_in(): bool
{
    return Session::user() !== null;
}

function has_role(string|array $roles): bool
{
    $userRole = Session::userRole();
    if (is_array($roles)) {
        return in_array($userRole, $roles, true);
    }
    return $userRole === $roles;
}

function format_date(?string $dateStr, string $format = 'd/m/Y'): string
{
    if (empty($dateStr)) {
        return '—';
    }
    $ts = strtotime($dateStr);
    return $ts ? date($format, $ts) : $dateStr;
}

function format_datetime(?string $dateStr, string $format = 'H:i - d/m/Y'): string
{
    if (empty($dateStr)) {
        return '—';
    }
    $ts = strtotime($dateStr);
    return $ts ? date($format, $ts) : $dateStr;
}

function slugify(string $text): string
{
    $vietnamese = [
        '/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u' => 'a',
        '/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u' => 'e',
        '/(ì|í|ị|ỉ|ĩ)/u' => 'i',
        '/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u' => 'o',
        '/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u' => 'u',
        '/(ỳ|ý|ỵ|ỷ|ỹ)/u' => 'y',
        '/(đ)/u' => 'd',
        '/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/u' => 'A',
        '/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/u' => 'E',
        '/(Ì|Í|Ị|Ỉ|Ĩ)/u' => 'I',
        '/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/u' => 'O',
        '/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/u' => 'U',
        '/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/u' => 'Y',
        '/(Đ)/u' => 'D',
    ];
    $text = preg_replace(array_keys($vietnamese), array_values($vietnamese), $text);
    $text = strtolower((string)$text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim((string)$text));
    return trim((string)$text, '-') ?: 'su-kien-' . time();
}

function status_badge_Event(string $status): string
{
    $map = [
        'Draft' => ['Nháp', 'badge-neutral'],
        'Pending' => ['Chờ duyệt', 'badge-warning'],
        'Approved' => ['Đã duyệt', 'badge-success'],
        'Published' => ['Đang mở đăng ký', 'badge-success'],
        'Ongoing' => ['Đang diễn ra', 'badge-info'],
        'Completed' => ['Đã hoàn thành', 'badge-primary'],
        'Rejected' => ['Từ chối', 'badge-danger'],
        'Cancelled' => ['Đã hủy', 'badge-danger'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'badge-neutral'];
    return '<span class="gg-badge ' . $cls . '">' . e($label) . '</span>';
}

function status_badge_registration(string $status): string
{
    $map = [
        'Pending' => ['Chờ xác nhận', 'badge-warning'],
        'Confirmed' => ['Đã xác nhận', 'badge-success'],
        'Waitlisted' => ['Danh sách chờ', 'badge-info'],
        'Attended' => ['Đã tham gia', 'badge-primary'],
        'Cancelled' => ['Đã hủy', 'badge-danger'],
        'Absent' => ['Vắng mặt', 'badge-danger'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'badge-neutral'];
    return '<span class="gg-badge ' . $cls . '">' . e($label) . '</span>';
}

function volunteer_rank_badge(float $hours): array
{
    if ($hours >= 50) {
        return ['name' => 'Điều phối viên nòng cốt', 'icon' => 'award', 'color' => '#B45309', 'next' => 100];
    }
    if ($hours >= 20) {
        return ['name' => 'Tình nguyện viên tích cực', 'icon' => 'medal', 'color' => '#15803D', 'next' => 50];
    }
    if ($hours >= 5) {
        return ['name' => 'Thành viên gắn kết', 'icon' => 'check-circle-2', 'color' => '#16A34A', 'next' => 20];
    }
    return ['name' => 'Tình nguyện viên mới', 'icon' => 'sprout', 'color' => '#16A34A', 'next' => 5];
}

function lucide_name(string $iconName): string
{
    $legacyMap = [
        'fa-droplet' => 'droplet',
        'fa-leaf' => 'trees',
        'fa-book-open-reader' => 'book-open',
        'fa-hand-holding-heart' => 'utensils',
        'fa-people-group' => 'users',
        'fa-hands-holding-child' => 'heart-handshake',
        'fa-crown' => 'award',
        'fa-medal' => 'medal',
        'fa-heart-circle-check' => 'check-circle-2',
        'fa-seedling' => 'sprout',
    ];
    $clean = trim($iconName);
    return $legacyMap[$clean] ?? (str_starts_with($clean, 'fa-') ? substr($clean, 3) : $clean);
}

/**
 * Secure file upload helper with MIME & extension verification + random filename
 */
function handle_secure_upload(array $file, string $subFolder = 'events'): ?string
{
    if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $maxBytes = ((int)env('MAX_UPLOAD_SIZE_MB', 5)) * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxBytes) {
        throw new \RuntimeException('Dung lượng file vượt quá giới hạn cho phép (tối đa 5MB).');
    }

    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $originalName = (string)($file['name'] ?? '');
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        throw new \RuntimeException('Định dạng file không được phép. Chỉ chấp nhận JPG, PNG, WEBP hoặc PDF.');
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    if (!in_array($mime, $allowedMimes, true)) {
        throw new \RuntimeException('Loại MIME của file không hợp lệ (' . $mime . ').');
    }

    $targetDir = PUBLIC_PATH . '/uploads/' . trim($subFolder, '/');
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0775, true);
    }

    $randomFileName = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = $targetDir . '/' . $randomFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new \RuntimeException('Không thể lưu file tải lên.');
    }

    return '/uploads/' . trim($subFolder, '/') . '/' . $randomFileName;
}
