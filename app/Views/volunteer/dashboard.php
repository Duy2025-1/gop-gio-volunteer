<?php
$badge = $stats['badge'];
?>
<div class="gg-page-header py-3">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-8 d-flex align-items-center gap-3">
                <img src="<?= e($profile['avatar'] ?: '/assets/images/avatars/user-vol1.jpg') ?>"
                     alt="<?= e($profile['full_name']) ?>"
                     class="rounded-circle object-fit-cover border"
                     width="68" height="68">
                <div>
                    <div class="gg-section-kicker mb-1">Dashboard Tình nguyện viên</div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h1 class="h3 mb-0"><?= e($profile['full_name']) ?></h1>
                        <span class="gg-badge badge-success"><?= e($badge['name']) ?></span>
                        <?php if (!empty($profile['blood_type'])): ?>
                            <span class="gg-badge badge-danger">Nhóm máu <?= e($profile['blood_type']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted">
                        <?= e($profile['email']) ?> &bull; <?= e($profile['phone'] ?: 'Chưa cập nhật SĐT') ?> &bull; <?= e($profile['address'] ?: 'TP. Hồ Chí Minh') ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="/events" class="btn btn-gg-primary btn-sm">Đăng ký hoạt động mới</a>
                <a href="/blood-donation" class="btn btn-gg-outline btn-sm ms-1">Lịch hiến máu</a>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Dải số liệu cá nhân -->
    <div class="row gg-stats-strip g-0 mb-4 bg-white border px-3" style="border-radius:8px;">
        <div class="col-6 col-lg-3">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= number_format((float)$stats['total_hours'], 1) ?> giờ</div>
                <div class="gg-stats-label">Giờ tình nguyện đã xác nhận</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= (int)$stats['attended_events'] ?></div>
                <div class="gg-stats-label">Hoạt động đã hoàn thành</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= (int)$stats['total_certificates'] ?></div>
                <div class="gg-stats-label">Chứng nhận PDF đã cấp</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= (int)$stats['upcoming_events'] ?></div>
                <div class="gg-stats-label">Lịch hoạt động sắp tới</div>
            </div>
        </div>
    </div>

    <!-- Thanh điều hướng phân hệ cá nhân -->
    <ul class="nav nav-pills gg-tab-nav mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'overview' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#v-overview" type="button">
                Lịch sắp tới &amp; Mã QR
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'registrations' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#v-registrations" type="button">
                Hồ sơ đăng ký (<?= count($registrations) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'attendance' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#v-attendance" type="button">
                Nhật ký điểm danh
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'certificates' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#v-certificates" type="button">
                Chứng nhận (<?= count($certificates) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'notifications' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#v-notifications" type="button">
                Thông báo
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'profile' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#v-profile" type="button">
                Cập nhật hồ sơ
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB 1: LỊCH SẮP TỚI, MÃ QR & THỐNG KÊ THÁNG -->
        <div class="tab-pane fade <?= $activeTab === 'overview' ? 'show active' : '' ?>" id="v-overview">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="gg-card p-4">
                        <h2 class="h4 mb-3">Hoạt động sắp tham gia &amp; Mã QR điểm danh</h2>
                        <?php if (empty($upcomingRegistrations)): ?>
                            <div class="gg-empty-state py-4">
                                <p class="small text-muted mb-2">Bạn hiện chưa có lịch hoạt động nào sắp diễn ra.</p>
                                <a href="/events" class="btn btn-gg-outline btn-sm">Tìm hoạt động phù hợp</a>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($upcomingRegistrations as $up): ?>
                                    <div class="p-3 border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="background-color:#F7F9F7;border-radius:6px;">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <?= status_badge_registration($up['status']) ?>
                                                <span class="small text-muted">Mã hồ sơ: <code><?= e($up['registration_code']) ?></code></span>
                                            </div>
                                            <h3 class="h5 mb-1">
                                                <a href="/events/<?= (int)$up['event_id'] ?>" class="text-dark text-decoration-none"><?= e($up['event_title']) ?></a>
                                            </h3>
                                            <div class="small text-muted">
                                                Thời gian: <?= format_datetime($up['start_time']) ?> &bull; Địa điểm: <?= e($up['location']) ?>
                                            </div>
                                            <?php if (!empty($up['time_slot'])): ?>
                                                <div class="small text-danger fw-medium mt-1">
                                                    Khung giờ hiến máu: <?= e($up['time_slot']) ?> (Mã hẹn: <?= e($up['appointment_code']) ?>)
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($up['status'] === 'Confirmed'): ?>
                                            <div class="text-center bg-white p-2 rounded border flex-shrink-0">
                                                <img src="/registrations/<?= (int)$up['id'] ?>/qr.svg" alt="Mã QR Điểm danh" width="104" height="104">
                                                <div class="text-muted mt-1" style="font-size:0.7rem;">Xuất trình tại bàn BTC</div>
                                            </div>
                                        <?php else: ?>
                                            <span class="gg-badge badge-info">Thứ tự chờ #<?= (int)$up['waitlist_position'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Biểu đồ giờ tình nguyện theo tháng -->
                <div class="col-lg-5">
                    <div class="gg-card p-4 h-100">
                        <h2 class="h4 mb-3">Thống kê giờ tình nguyện theo tháng</h2>
                        <canvas id="volunteerHoursChart" height="210"></canvas>
                        <div class="mt-3 p-3 border small" style="background-color:#F7F9F7;border-radius:6px;">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold text-dark">Mức ghi nhận: <?= e($badge['name']) ?></span>
                                <span class="fw-bold" style="color:#15803D;"><?= number_format((float)$stats['total_hours'], 1) ?> / <?= (int)$badge['next'] ?> giờ</span>
                            </div>
                            <div class="gg-progress">
                                <div class="gg-progress-bar" style="width: <?= min(100, round(((float)$stats['total_hours'] / max(1, (int)$badge['next'])) * 100)) ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: HỒ SƠ ĐĂNG KÝ -->
        <div class="tab-pane fade <?= $activeTab === 'registrations' ? 'show active' : '' ?>" id="v-registrations">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Lịch sử đăng ký tham gia hoạt động</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Mã đăng ký</th>
                                <th>Chương trình &amp; Đơn vị tổ chức</th>
                                <th>Thời gian bắt đầu</th>
                                <th>Trạng thái</th>
                                <th>Giờ được duyệt</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $r): ?>
                                <tr>
                                    <td><code><?= e($r['registration_code']) ?></code></td>
                                    <td>
                                        <a href="/events/<?= (int)$r['event_id'] ?>" class="fw-semibold text-dark text-decoration-none"><?= e($r['event_title']) ?></a>
                                        <div class="text-muted"><?= e($r['organization_name']) ?></div>
                                    </td>
                                    <td><?= format_datetime($r['start_time']) ?></td>
                                    <td><?= status_badge_registration($r['status']) ?></td>
                                    <td>
                                        <?php if ($r['hours_status'] === 'Approved'): ?>
                                            <span class="fw-semibold" style="color:#15803D;"><?= number_format((float)$r['approved_hours'], 1) ?> giờ</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if (in_array($r['status'], ['Confirmed', 'Waitlisted', 'Pending'], true)): ?>
                                            <form action="/registrations/<?= (int)$r['id'] ?>/cancel" method="POST" class="d-inline"
                                                  data-confirm="Bạn có chắc chắn muốn hủy đăng ký hoạt động này?">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2">Hủy đăng ký</button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="/events/<?= (int)$r['event_id'] ?>" class="btn btn-gg-ghost btn-sm py-1 px-2">Xem</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: NHẬT KÝ ĐIỂM DANH -->
        <div class="tab-pane fade <?= $activeTab === 'attendance' ? 'show active' : '' ?>" id="v-attendance">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Nhật ký quét mã Check-in &amp; Check-out</h2>
                </div>
                <?php if (empty($attendanceHistory)): ?>
                    <div class="gg-empty-state border-0">Chưa có bản ghi điểm danh nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table gg-table align-middle small mb-0">
                            <thead>
                                <tr>
                                    <th>Hoạt động</th>
                                    <th>Giờ Check-in</th>
                                    <th>Giờ Check-out</th>
                                    <th>Hình thức</th>
                                    <th>Thời lượng ghi nhận</th>
                                    <th>Trạng thái duyệt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceHistory as $att): ?>
                                    <tr>
                                        <td class="fw-semibold text-dark"><?= e($att['event_title']) ?></td>
                                        <td><?= format_datetime($att['check_in_time']) ?></td>
                                        <td><?= format_datetime($att['check_out_time']) ?></td>
                                        <td><span class="gg-badge badge-neutral"><?= e($att['check_in_method']) ?></span></td>
                                        <td><?= number_format((float)$att['calculated_hours'], 2) ?> giờ</td>
                                        <td>
                                            <span class="gg-badge <?= ($att['hours_status'] ?? '') === 'Approved' ? 'badge-success' : 'badge-warning' ?>">
                                                <?= ($att['hours_status'] ?? '') === 'Approved' ? 'Đã phê duyệt' : 'Đang đối soát' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB 4: CHỨNG NHẬN PDF -->
        <div class="tab-pane fade <?= $activeTab === 'certificates' ? 'show active' : '' ?>" id="v-certificates">
            <div class="gg-card p-4">
                <h2 class="h4 mb-3">Chứng nhận giờ tình nguyện đã được cấp</h2>
                <?php if (empty($certificates)): ?>
                    <div class="gg-empty-state">Bạn chưa có chứng nhận nào. Chứng nhận điện tử sẽ được tạo tự động ngay sau khi Ban tổ chức phê duyệt giờ tham gia.</div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($certificates as $cert): ?>
                            <div class="col-md-6">
                                <div class="p-3 border d-flex flex-column h-100" style="background-color:#F7F9F7;border-radius:6px;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <code class="fw-bold" style="color:#15803D;"><?= e($cert['certificate_code']) ?></code>
                                        <span class="gg-badge <?= $cert['status'] === 'Valid' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $cert['status'] === 'Valid' ? 'Hợp lệ' : 'Đã thu hồi' ?>
                                        </span>
                                    </div>
                                    <h3 class="h5 mb-1"><?= e($cert['event_title']) ?></h3>
                                    <div class="small text-muted mb-2">Đơn vị xác nhận: <?= e($cert['organization_name']) ?></div>
                                    <div class="small mb-3">
                                        Thời lượng công nhận: <strong><?= number_format((float)$cert['verified_hours'], 1) ?> giờ</strong>
                                        &bull; Ngày cấp: <?= format_date($cert['issued_date']) ?>
                                    </div>
                                    <div class="mt-auto d-flex gap-2">
                                        <a href="/certificate/download/<?= urlencode($cert['verification_code']) ?>" class="btn btn-gg-primary btn-sm">
                                            Tải bản PDF
                                        </a>
                                        <a href="/certificate/verify/<?= urlencode($cert['verification_code']) ?>" class="btn btn-gg-outline btn-sm">
                                            Xem trang xác thực
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB 5: THÔNG BÁO -->
        <div class="tab-pane fade <?= $activeTab === 'notifications' ? 'show active' : '' ?>" id="v-notifications">
            <div class="gg-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Thông báo của bạn</h2>
                    <form action="/notifications/read-all" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-gg-outline btn-sm">Đánh dấu tất cả đã đọc</button>
                    </form>
                </div>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($notifications as $n): ?>
                        <div class="p-3 border d-flex justify-content-between align-items-center"
                             style="border-radius:6px;background-color:<?= (int)$n['is_read'] === 0 ? '#F7F9F7' : '#FFFFFF' ?>;">
                            <div>
                                <div class="fw-semibold small text-dark"><?= e($n['title']) ?></div>
                                <div class="small text-muted"><?= e($n['message']) ?></div>
                                <div class="text-muted mt-1" style="font-size:0.74rem;"><?= format_datetime($n['created_at']) ?></div>
                            </div>
                            <form action="/notifications/<?= (int)$n['id'] ?>/delete" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-gg-ghost btn-sm text-danger">Xóa</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- TAB 6: CẬP NHẬT HỒ SƠ & MẬT KHẨU -->
        <div class="tab-pane fade <?= $activeTab === 'profile' ? 'show active' : '' ?>" id="v-profile">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="gg-card p-4">
                        <h2 class="h4 mb-3">Thông tin cá nhân</h2>
                        <form action="/profile/update" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Họ và tên *</label>
                                    <input type="text" name="full_name" class="form-control form-control-sm" value="<?= e($profile['full_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control form-control-sm" value="<?= e($profile['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date" name="birth_date" class="form-control form-control-sm" value="<?= e($profile['birth_date'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Giới tính</label>
                                    <select name="gender" class="form-select form-select-sm">
                                        <option value="Male" <?= ($profile['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Nam</option>
                                        <option value="Female" <?= ($profile['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Nữ</option>
                                        <option value="Other" <?= ($profile['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Khác</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nhóm máu</label>
                                    <input type="text" name="blood_type" class="form-control form-control-sm" value="<?= e($profile['blood_type'] ?? '') ?>" placeholder="O+, A+, B+...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Địa chỉ liên hệ</label>
                                    <input type="text" name="address" class="form-control form-control-sm" value="<?= e($profile['address'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Ảnh đại diện mới (JPG/PNG/WEBP)</label>
                                    <input type="file" name="avatar_file" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Kỹ năng &amp; giới thiệu bản thân</label>
                                    <textarea name="bio" class="form-control form-control-sm" rows="2"><?= e($profile['bio'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-gg-primary btn-sm px-4">Lưu thông tin hồ sơ</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="gg-card p-4">
                        <h2 class="h4 mb-3">Thay đổi mật khẩu</h2>
                        <form action="/profile/password" method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password" name="new_password" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" class="btn btn-gg-outline btn-sm w-100">Cập nhật mật khẩu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js for Monthly Hours -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('volunteerHoursChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($stats['chart_labels'], JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Số giờ tình nguyện đã xác nhận',
                    data: <?= json_encode($stats['chart_values']) ?>,
                    backgroundColor: '#15803D',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>
// [v1-stats-draft-1] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #1
// [v1-stats-draft-2] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #2
// [v1-stats-draft-3] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #3
// [v1-stats-draft-4] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #4
// [v1-stats-draft-5] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #5
// [v1-stats-draft-6] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #6
// [v1-stats-draft-7] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #7
// [v1-stats-draft-8] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #8
// [v1-stats-draft-9] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #9
// [v1-stats-draft-10] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #10
// [v1-stats-draft-11] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #11
// [v1-stats-draft-12] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #12
// [v1-stats-draft-13] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #13
// [v1-stats-draft-14] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #14
// [v1-stats-draft-15] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #15
// [v1-stats-draft-16] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #16
// [v1-stats-draft-17] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #17
// [v1-stats-draft-18] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #18
// [v1-stats-draft-19] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #19
// [v1-stats-draft-20] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #20
// [v1-stats-draft-21] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #21
// [v1-stats-draft-22] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #22
// [v1-stats-draft-23] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #23
// [v1-stats-draft-24] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #24
