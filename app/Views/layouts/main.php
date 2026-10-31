<?php
use App\Core\Session;

$authUser = Session::user();
$flashes = Session::getFlashes();
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle ?? 'Góp Gió Volunteer – Một hành động nhỏ, ngàn giá trị lớn') ?></title>
    <!-- Google Fonts: Be Vietnam Pro -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 (fallback compatibility) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Góp Gió Volunteer CSS -->
    <link href="/assets/css/style.css?v=2.1" rel="stylesheet">
</head>
<body>

<!-- Header Navigation -->
<header class="navbar navbar-expand-lg gg-navbar">
    <div class="container">
        <a class="gg-brand" href="/">
            <span class="gg-brand-mark" aria-hidden="true">
                <i data-lucide="sprout" style="width:18px;height:18px;"></i>
            </span>
            <div>
                <div class="gg-brand-name">Góp Gió <span>Volunteer</span></div>
                <span class="gg-brand-sub">Một hành động nhỏ – Ngàn giá trị lớn</span>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#ggNavbarContent" aria-label="Mở menu điều hướng">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="ggNavbarContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link gg-nav-link <?= $currentUri === '/' ? 'active' : '' ?>" href="/">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link gg-nav-link <?= str_starts_with($currentUri, '/events') ? 'active' : '' ?>" href="/events">Hoạt động</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link gg-nav-link <?= str_starts_with($currentUri, '/blood-donation') ? 'active' : '' ?>" href="/blood-donation">Hiến máu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link gg-nav-link <?= str_starts_with($currentUri, '/organizations') ? 'active' : '' ?>" href="/organizations">Tổ chức</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link gg-nav-link <?= str_starts_with($currentUri, '/leaderboard') ? 'active' : '' ?>" href="/leaderboard">Bảng xếp hạng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link gg-nav-link <?= str_starts_with($currentUri, '/about') ? 'active' : '' ?>" href="/about">Giới thiệu</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 pt-2 pt-lg-0">
                <?php if (!$authUser): ?>
                    <a href="/login" class="btn btn-gg-ghost btn-sm">Đăng nhập</a>
                    <a href="/register" class="btn btn-gg-primary btn-sm">Đăng ký tham gia</a>
                <?php else: ?>
                    <?php
                    $dashboardUrl = match ($authUser['role_slug']) {
                        'admin' => '/admin/dashboard',
                        'organizer' => '/organizer/dashboard',
                        default => '/volunteer/dashboard',
                    };
                    $roleDisplayName = match ($authUser['role_slug']) {
                        'admin' => 'Quản trị viên',
                        'organizer' => 'Đơn vị tổ chức',
                        default => 'Tình nguyện viên',
                    };
                    ?>
                    <!-- Notifications Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light position-relative border d-inline-flex align-items-center justify-content-center"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                style="width:36px;height:36px;border-radius:6px;padding:0;" aria-label="Thông báo">
                            <i data-lucide="bell" style="width:16px;height:16px;color:#1E293B;"></i>
                            <span id="ggUnreadBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size:0.65rem;">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm border p-0 mt-2" style="width:320px;border-radius:8px;overflow:hidden;">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom" style="background:#F7F9F7;">
                                <span class="fw-semibold small text-dark">Thông báo</span>
                                <form action="/notifications/read-all" method="POST" class="m-0">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size:0.75rem;color:#15803D;">Đánh dấu đã đọc</button>
                                </form>
                            </div>
                            <div id="ggHeaderNotifList" style="max-height:280px;overflow-y:auto;">
                                <div class="p-3 text-center text-muted small">Đang tải thông báo...</div>
                            </div>
                            <div class="p-2 text-center border-top" style="background:#F7F9F7;">
                                <a href="<?= e($dashboardUrl) ?>?tab=notifications" class="small fw-medium text-decoration-none" style="color:#15803D;">Xem toàn bộ lịch sử thông báo</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Account Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light border d-flex align-items-center gap-2 py-1 px-2"
                                type="button" data-bs-toggle="dropdown" style="border-radius:6px;">
                            <img src="<?= e($authUser['avatar'] ?: '/assets/images/avatars/user-vol1.jpg') ?>"
                                 alt="<?= e($authUser['full_name']) ?>" class="rounded-circle object-fit-cover" width="28" height="28">
                            <span class="small fw-medium text-dark d-none d-sm-inline"><?= e($authUser['full_name']) ?></span>
                            <i data-lucide="chevron-down" style="width:14px;height:14px;color:#64748B;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" style="border-radius:8px;min-width:225px;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-semibold small text-dark"><?= e($authUser['full_name']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= e($authUser['email']) ?></div>
                                <span class="gg-badge badge-success mt-1"><?= e($roleDisplayName) ?></span>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 small fw-medium d-flex align-items-center gap-2" href="<?= e($dashboardUrl) ?>">
                                    <i data-lucide="layout-dashboard" style="width:15px;height:15px;color:#15803D;"></i>
                                    <span>Bảng điều khiển</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 small d-flex align-items-center gap-2" href="/certificate/verify">
                                    <i data-lucide="award" style="width:15px;height:15px;color:#64748B;"></i>
                                    <span>Tra cứu chứng nhận</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form action="/logout" method="POST" class="m-0">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="dropdown-item py-2 small text-danger fw-medium d-flex align-items-center gap-2">
                                        <i data-lucide="log-out" style="width:15px;height:15px;"></i>
                                        <span>Đăng xuất</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Toast Notification Container -->
<div id="ggToastContainer" class="gg-toast-container"></div>

<!-- Inline Flash Banner -->
<?php if (!empty($flashes)): ?>
    <div class="container mt-3">
        <?php foreach ($flashes as $fType => $fMsg): ?>
            <?php
            $alertClass = match ($fType) {
                'error' => 'alert-danger',
                'warning' => 'alert-warning',
                default => 'alert-success',
            };
            $iconName = match ($fType) {
                'error' => 'alert-circle',
                'warning' => 'alert-triangle',
                default => 'check-circle-2',
            };
            ?>
            <div class="alert <?= $alertClass ?> alert-dismissible fade show border py-2 px-3 d-flex align-items-center gap-2" role="alert" style="border-radius:6px;font-size:0.9rem;">
                <i data-lucide="<?= $iconName ?>" style="width:17px;height:17px;flex-shrink:0;"></i>
                <div class="fw-medium"><?= e($fMsg) ?></div>
                <button type="button" class="btn-close py-2 ms-auto" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Main Page Content -->
<main>
    <?= $content ?? '' ?>
</main>

<!-- Footer -->
<footer class="gg-footer">
    <div class="container">
        <div class="row g-4 pb-4 border-bottom" style="border-color:#334155 !important;">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="gg-brand-mark" aria-hidden="true">
                        <i data-lucide="sprout" style="width:17px;height:17px;"></i>
                    </span>
                    <span class="fw-bold text-white" style="font-size:1.05rem;">Góp Gió Volunteer</span>
                </div>
                <p class="small mb-2" style="color:#CBD5E1;line-height:1.65;">
                    Một hành động nhỏ – Ngàn giá trị lớn.
                </p>
                <p class="small mb-0" style="color:#94A3B8;line-height:1.65;">
                    Nền tảng điều phối hoạt động tình nguyện, dự án môi trường, hỗ trợ giáo dục và lịch hiến máu nhân đạo dành cho sinh viên, câu lạc bộ và các tổ chức xã hội tại Việt Nam.
                </p>
            </div>
            <div class="col-6 col-lg-2 offset-lg-1">
                <h6 class="text-white fw-semibold mb-3" style="font-size:0.88rem;">Hoạt động</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="/events">Tất cả hoạt động</a></li>
                    <li><a href="/blood-donation">Chương trình hiến máu</a></li>
                    <li><a href="/organizations">Đơn vị tổ chức</a></li>
                    <li><a href="/leaderboard">Bảng ghi nhận đóng góp</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-semibold mb-3" style="font-size:0.88rem;">Tra cứu &amp; Quy định</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="/certificate/verify">Xác minh chứng nhận</a></li>
                    <li><a href="/about">Cơ chế điểm danh QR</a></li>
                    <li><a href="/about#privacy">Bảo mật thông tin</a></li>
                    <li><a href="/about#terms">Quy tắc cộng đồng</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6 class="text-white fw-semibold mb-3" style="font-size:0.88rem;">Ban điều phối Góp Gió</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0" style="color:#94A3B8;">
                    <li class="d-flex align-items-start gap-2">
                        <i data-lucide="map-pin" style="width:15px;height:15px;color:#22C55E;margin-top:2px;flex-shrink:0;"></i>
                        <span>Nhà Văn hóa Sinh viên, Khu đô thị ĐHQG TP.HCM, TP. Thủ Đức</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i data-lucide="mail" style="width:15px;height:15px;color:#22C55E;flex-shrink:0;"></i>
                        <span>banlienlac@gopgio.vn</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i data-lucide="phone" style="width:15px;height:15px;color:#22C55E;flex-shrink:0;"></i>
                        <span>Điện thoại hỗ trợ: (028) 3724 2160</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-3 small" style="color:#94A3B8;font-size:0.8rem;">
            <div>&copy; <?= date('Y') ?> Góp Gió Volunteer. Vận hành minh bạch vì cộng đồng tình nguyện Việt Nam.</div>
            <div class="mt-2 mt-md-0">Hệ thống quản lý tình nguyện &amp; hiến máu nhân đạo</div>
        </div>
    </div>
</footer>

<!-- Global Confirmation Modal for Important Actions -->
<div class="modal fade" id="ggConfirmModal" tabindex="-1" aria-labelledby="ggConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2" id="ggConfirmModalLabel">
                    <i data-lucide="alert-circle" style="width:18px;height:18px;color:#D97706;"></i>
                    <span>Xác nhận thao tác</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body py-3">
                <p id="ggConfirmModalMessage" class="mb-0 small text-dark" style="line-height:1.6;">
                    Bạn có chắc chắn muốn thực hiện thao tác này không?
                </p>
            </div>
            <div class="modal-footer border-top py-2 bg-light">
                <button type="button" class="btn btn-gg-outline btn-sm" data-bs-dismiss="modal">Quay lại</button>
                <button type="button" id="ggConfirmModalAccept" class="btn btn-gg-primary btn-sm">Đồng ý thực hiện</button>
            </div>
        </div>
    </div>
</div>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="/assets/js/app.js"></script>
<?php if (!empty($flashes)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($flashes as $fType => $fMsg): ?>
    GopGioApp.showToast(<?= json_encode($fMsg, JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($fType) ?>);
    <?php endforeach; ?>
});
</script>
<?php endif; ?>
</body>
</html>
