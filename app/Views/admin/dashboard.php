<?php
$kpis = $adminData['kpis'];
$charts = $adminData['charts'];
?>
<div class="gg-page-header py-3">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="gg-section-kicker mb-1">Phân hệ Quản trị hệ thống</div>
                <h1 class="h3 mb-0">Trung Tâm Điều Hành Góp Gió Volunteer</h1>
            </div>
            <div class="d-flex gap-2">
                <span class="gg-badge badge-warning">Chương trình chờ duyệt: <?= (int)$kpis['pending_events'] ?></span>
                <span class="gg-badge badge-info">Đơn vị chờ xác minh: <?= (int)$kpis['pending_organizations'] ?></span>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Bảng chỉ số vận hành toàn hệ thống (Gọn gàng, ít màu mè) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="small text-muted">Tổng tài khoản</div>
                <div class="gg-stat-value mt-1"><?= (int)$kpis['total_users'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="small text-muted">Tình nguyện viên</div>
                <div class="gg-stat-value mt-1" style="color:#15803D;"><?= (int)$kpis['total_volunteers'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="small text-muted">Đơn vị tổ chức</div>
                <div class="gg-stat-value mt-1"><?= (int)$kpis['total_organizations'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="small text-muted">Tổng chương trình</div>
                <div class="gg-stat-value mt-1"><?= (int)$kpis['total_events'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="small text-muted">Lượt đăng ký tham gia</div>
                <div class="gg-stat-value mt-1"><?= (int)$kpis['total_registrations'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="small text-muted">Giờ tình nguyện đã duyệt</div>
                <div class="gg-stat-value mt-1" style="color:#15803D;"><?= number_format((float)$kpis['total_hours'], 1) ?> giờ</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="small text-muted">Chương trình chờ duyệt</div>
                <div class="gg-stat-value mt-1 text-warning"><?= (int)$kpis['pending_events'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="small text-muted">Đơn vị chờ xác minh</div>
                <div class="gg-stat-value mt-1 text-primary"><?= (int)$kpis['pending_organizations'] ?></div>
            </div>
        </div>
    </div>

    <!-- Thanh điều hướng phân hệ Quản trị -->
    <ul class="nav nav-pills gg-tab-nav mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'overview' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-overview" type="button">
                Thống kê tổng hợp
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'events' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-events" type="button">
                Duyệt chương trình (<?= count($events) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'organizations' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-orgs" type="button">
                Đơn vị tổ chức (<?= count($organizations) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'users' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-users" type="button">
                Tài khoản (<?= count($users) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'categories' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-categories" type="button">
                Danh mục
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'hours' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-hours" type="button">
                Đối soát giờ TNV
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'certificates' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-certs" type="button">
                Chứng nhận
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= in_array($activeTab, ['reviews', 'reports'], true) ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-reviews" type="button">
                Phản ánh &amp; Đánh giá
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'audit' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-audit" type="button">
                Nhật ký hệ thống
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'settings' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#adm-settings" type="button">
                Cấu hình &amp; Thông báo
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB 1: BIỂU ĐỒ & THỐNG KÊ -->
        <div class="tab-pane fade <?= $activeTab === 'overview' ? 'show active' : '' ?>" id="adm-overview">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="gg-card p-3 h-100">
                        <h3 class="h5 mb-3">Chương trình theo tháng</h3>
                        <canvas id="admEventsChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="gg-card p-3 h-100">
                        <h3 class="h5 mb-3">Lượt đăng ký theo tháng</h3>
                        <canvas id="admParticipationsChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="gg-card p-3 h-100">
                        <h3 class="h5 mb-3">Giờ tình nguyện theo tháng</h3>
                        <canvas id="admHoursChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="gg-card p-3 h-100">
                        <h3 class="h5 mb-3">Cơ cấu lĩnh vực hoạt động</h3>
                        <canvas id="admCategoryChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="gg-card p-3 h-100">
                        <h3 class="h5 mb-3">Tình nguyện viên có số giờ cao nhất</h3>
                        <?php foreach ($adminData['top_volunteers'] as $idx => $v): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                                <span><strong>#<?= $idx + 1 ?></strong> <?= e($v['full_name']) ?></span>
                                <span class="gg-badge badge-neutral"><?= number_format((float)$v['confirmed_hours'], 1) ?> giờ</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="gg-card p-3 h-100">
                        <h3 class="h5 mb-3">Đơn vị tổ chức tích cực nhất</h3>
                        <?php foreach ($adminData['top_organizations'] as $idx => $o): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                                <span><strong>#<?= $idx + 1 ?></strong> <?= e($o['name']) ?></span>
                                <span class="gg-badge badge-neutral"><?= (int)$o['total_events'] ?> CT / <?= number_format((float)$o['total_hours'], 1) ?> giờ</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: DUYỆT CHƯƠNG TRÌNH -->
        <div class="tab-pane fade <?= $activeTab === 'events' ? 'show active' : '' ?>" id="adm-events">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Kiểm duyệt &amp; quản lý chương trình</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Chương trình &amp; Đơn vị tổ chức</th>
                                <th>Lĩnh vực</th>
                                <th>Thời gian bắt đầu</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thẩm định / Phê duyệt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $ev): ?>
                                <tr>
                                    <td>#<?= (int)$ev['id'] ?></td>
                                    <td>
                                        <a href="/events/<?= (int)$ev['id'] ?>" class="fw-semibold text-dark text-decoration-none"><?= e($ev['title']) ?></a>
                                        <div class="text-muted">Đơn vị: <?= e($ev['organization_name']) ?></div>
                                    </td>
                                    <td><?= e($ev['category_name']) ?></td>
                                    <td><?= format_datetime($ev['start_time']) ?></td>
                                    <td><?= status_badge_Event($ev['status']) ?></td>
                                    <td class="text-end">
                                        <form action="/admin/events/<?= (int)$ev['id'] ?>/review" method="POST" class="d-inline-flex gap-1 align-items-center">
                                            <?= csrf_field() ?>
                                            <input type="text" name="rejection_reason" class="form-control form-control-sm" style="width:155px;" placeholder="Ghi chú thẩm định...">
                                            <button type="submit" name="decision" value="approve" class="btn btn-gg-primary btn-sm py-1 px-2">Duyệt</button>
                                            <button type="submit" name="decision" value="reject" class="btn btn-gg-danger btn-sm py-1 px-2">Từ chối</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: ĐƠN VỊ TỔ CHỨC -->
        <div class="tab-pane fade <?= $activeTab === 'organizations' ? 'show active' : '' ?>" id="adm-orgs">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Thẩm định &amp; quản lý đơn vị tổ chức</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Đơn vị tổ chức</th>
                                <th>Người đại diện</th>
                                <th>Thông tin liên lạc</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Cập nhật xác minh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($organizations as $org): ?>
                                <tr>
                                    <td>
                                        <a href="/organizations/<?= (int)$org['id'] ?>" class="fw-semibold text-dark text-decoration-none"><?= e($org['name']) ?></a>
                                        <div class="text-muted"><?= e($org['address']) ?></div>
                                    </td>
                                    <td><?= e($org['owner_name']) ?></td>
                                    <td><?= e($org['contact_email']) ?><br><?= e($org['contact_phone']) ?></td>
                                    <td>
                                        <span class="gg-badge <?= $org['verification_status'] === 'Verified' ? 'badge-success' : 'badge-warning' ?>">
                                            <?= e($org['verification_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form action="/admin/organizations/<?= (int)$org['id'] ?>/verify" method="POST" class="d-inline-flex gap-1">
                                            <?= csrf_field() ?>
                                            <select name="verification_status" class="form-select form-select-sm" style="width:155px;">
                                                <option value="Verified">Đã xác minh (Verified)</option>
                                                <option value="Supplement_Required">Yêu cầu bổ sung</option>
                                                <option value="Rejected">Từ chối</option>
                                                <option value="Locked">Tạm khóa</option>
                                            </select>
                                            <input type="text" name="admin_feedback" class="form-control form-control-sm" style="width:140px;" placeholder="Phản hồi...">
                                            <button type="submit" class="btn btn-gg-primary btn-sm">Lưu</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 4: TÀI KHOẢN NGƯỜI DÙNG -->
        <div class="tab-pane fade <?= $activeTab === 'users' ? 'show active' : '' ?>" id="adm-users">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Danh sách tài khoản người dùng</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ và tên</th>
                                <th>Email &amp; Số điện thoại</th>
                                <th>Phân quyền</th>
                                <th>Giờ tích lũy</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Quản lý trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>#<?= (int)$u['id'] ?></td>
                                    <td class="fw-semibold text-dark"><?= e($u['full_name']) ?></td>
                                    <td><?= e($u['email']) ?> &bull; <?= e($u['phone']) ?></td>
                                    <td><span class="gg-badge badge-neutral"><?= e($u['role_name']) ?></span></td>
                                    <td class="fw-semibold" style="color:#15803D;"><?= number_format((float)$u['total_hours'], 1) ?> giờ</td>
                                    <td>
                                        <span class="gg-badge <?= $u['status'] === 'Active' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $u['status'] === 'Active' ? 'Hoạt động' : 'Đã khóa' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($u['role_slug'] !== 'admin'): ?>
                                            <form action="/admin/users/<?= (int)$u['id'] ?>/toggle-status" method="POST" class="d-inline"
                                                  data-confirm="Bạn có chắc chắn muốn thay đổi trạng thái hoạt động của tài khoản này?">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn <?= $u['status'] === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' ?> btn-sm py-1 px-2">
                                                    <?= $u['status'] === 'Active' ? 'Khóa tài khoản' : 'Mở khóa' ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 5: DANH MỤC HOẠT ĐỘNG -->
        <div class="tab-pane fade <?= $activeTab === 'categories' ? 'show active' : '' ?>" id="adm-categories">
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="gg-card p-4">
                        <h2 class="h4 mb-3">Thêm lĩnh vực hoạt động mới</h2>
                        <form action="/admin/categories/create" method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label">Tên lĩnh vực *</label>
                                <input type="text" name="name" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tên biểu tượng (Lucide / FontAwesome)</label>
                                <input type="text" name="icon" class="form-control form-control-sm" value="heart-handshake">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Màu nhận diện</label>
                                <input type="color" name="color_badge" class="form-control form-control-color" value="#15803D">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Mô tả ngắn</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_blood_donation" id="isBloodCat" value="1">
                                <label class="form-check-label small" for="isBloodCat">Thuộc nhóm chương trình Hiến máu nhân đạo</label>
                            </div>
                            <button type="submit" class="btn btn-gg-primary btn-sm w-100">Lưu danh mục</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="gg-card overflow-hidden">
                        <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                            <h2 class="h4 mb-0">Các lĩnh vực đang sử dụng</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table gg-table align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th>Tên lĩnh vực</th>
                                        <th>Định danh (Slug)</th>
                                        <th>Số chương trình</th>
                                        <th>Nhóm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $c): ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?= e($c['name']) ?></td>
                                            <td><code><?= e($c['slug']) ?></code></td>
                                            <td><?= (int)$c['event_count'] ?></td>
                                            <td><?= (int)$c['is_blood_donation'] === 1 ? '<span class="gg-badge badge-danger">Hiến máu</span>' : '<span class="gg-badge badge-neutral">Tình nguyện</span>' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 6: ĐỐI SOÁT & ĐIỀU CHỈNH GIỜ TÌNH NGUYỆN -->
        <div class="tab-pane fade <?= $activeTab === 'hours' ? 'show active' : '' ?>" id="adm-hours">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Đối soát &amp; điều chỉnh giờ tình nguyện (Ghi nhận vào Nhật ký hệ thống)</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Tình nguyện viên</th>
                                <th>Chương trình</th>
                                <th>Thời gian vào / ra</th>
                                <th>Giờ hiện tại</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Điều chỉnh giờ (kèm lý do)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($volunteerHours as $vh): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= e($vh['volunteer_name']) ?></td>
                                    <td><?= e($vh['event_title']) ?></td>
                                    <td><?= format_datetime($vh['check_in_time']) ?> &rarr; <?= format_datetime($vh['check_out_time']) ?></td>
                                    <td class="fw-bold" style="color:#15803D;"><?= number_format((float)$vh['hours'], 2) ?> giờ</td>
                                    <td><span class="gg-badge badge-success"><?= e($vh['status']) ?></span></td>
                                    <td class="text-end">
                                        <form action="/admin/hours/adjust" method="POST" class="d-inline-flex gap-1">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="volunteer_hour_id" value="<?= (int)$vh['id'] ?>">
                                            <input type="number" step="0.5" min="0" name="hours" value="<?= (float)$vh['hours'] ?>" class="form-control form-control-sm" style="width:72px;">
                                            <input type="text" name="adjustment_reason" class="form-control form-control-sm" style="width:165px;" placeholder="Lý do điều chỉnh..." required>
                                            <button type="submit" class="btn btn-gg-primary btn-sm">Cập nhật</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 7: QUẢN LÝ CHỨNG NHẬN -->
        <div class="tab-pane fade <?= $activeTab === 'certificates' ? 'show active' : '' ?>" id="adm-certs">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Danh sách chứng nhận đã cấp</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Số hiệu chứng nhận</th>
                                <th>Người được cấp</th>
                                <th>Chương trình</th>
                                <th>Thời lượng</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificates as $c): ?>
                                <tr>
                                    <td><code><?= e($c['certificate_code']) ?></code></td>
                                    <td class="fw-semibold text-dark"><?= e($c['recipient_name']) ?></td>
                                    <td><?= e($c['event_title']) ?></td>
                                    <td><?= number_format((float)$c['verified_hours'], 1) ?> giờ</td>
                                    <td>
                                        <span class="gg-badge <?= $c['status'] === 'Valid' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $c['status'] === 'Valid' ? 'Hợp lệ' : 'Đã thu hồi' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="/certificate/verify/<?= urlencode($c['verification_code']) ?>" class="btn btn-gg-ghost btn-sm py-1 px-2">Tra cứu</a>
                                        <form action="/admin/certificates/<?= (int)$c['id'] ?>/toggle-status" method="POST" class="d-inline"
                                              data-confirm="Xác nhận thay đổi trạng thái hiệu lực của chứng nhận này?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="revoked_reason" value="Thu hồi để đối soát lại bảng điểm danh">
                                            <button type="submit" class="btn <?= $c['status'] === 'Valid' ? 'btn-outline-danger' : 'btn-outline-success' ?> btn-sm py-1 px-2">
                                                <?= $c['status'] === 'Valid' ? 'Thu hồi' : 'Khôi phục' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 8: PHẢN ÁNH & ĐÁNH GIÁ -->
        <div class="tab-pane fade <?= in_array($activeTab, ['reviews', 'reports'], true) ? 'show active' : '' ?>" id="adm-reviews">
            <div class="gg-card overflow-hidden mb-4">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Xử lý phản ánh thông tin (<?= count($reports) ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Người gửi</th>
                                <th>Đối tượng</th>
                                <th>Nội dung phản ánh</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Ghi nhận kết quả xử lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $rp): ?>
                                <tr>
                                    <td><?= e($rp['reporter_name']) ?></td>
                                    <td><?= e($rp['target_type']) ?> #<?= (int)$rp['target_id'] ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($rp['reason']) ?></div>
                                        <div class="text-muted"><?= e($rp['details']) ?></div>
                                    </td>
                                    <td><span class="gg-badge <?= $rp['status'] === 'Resolved' ? 'badge-success' : 'badge-warning' ?>"><?= e($rp['status']) ?></span></td>
                                    <td class="text-end">
                                        <form action="/admin/reports/<?= (int)$rp['id'] ?>/resolve" method="POST" class="d-inline-flex gap-1">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="Resolved">
                                            <input type="text" name="resolution_note" class="form-control form-control-sm" style="width:165px;" placeholder="Kết quả xử lý..." value="<?= e($rp['resolution_note'] ?? '') ?>">
                                            <button type="submit" class="btn btn-gg-primary btn-sm">Hoàn tất</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Quản lý đánh giá sau chương trình</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Người đánh giá</th>
                                <th>Chương trình</th>
                                <th>Điểm</th>
                                <th>Nhận xét</th>
                                <th>Hiển thị</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $rv): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= e($rv['user_name']) ?></td>
                                    <td><?= e($rv['event_title']) ?></td>
                                    <td class="fw-semibold"><?= (int)$rv['rating'] ?>/5</td>
                                    <td><?= e($rv['comment']) ?></td>
                                    <td><span class="gg-badge <?= $rv['status'] === 'Visible' ? 'badge-success' : 'badge-danger' ?>"><?= e($rv['status']) ?></span></td>
                                    <td class="text-end">
                                        <form action="/admin/reviews/<?= (int)$rv['id'] ?>/moderate" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="<?= $rv['status'] === 'Visible' ? 'Hidden' : 'Visible' ?>">
                                            <button type="submit" class="btn btn-gg-ghost btn-sm py-1 px-2">
                                                <?= $rv['status'] === 'Visible' ? 'Ẩn nhận xét' : 'Hiển thị lại' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 9: NHẬT KÝ HỆ THỐNG (AUDIT LOGS) -->
        <div class="tab-pane fade <?= $activeTab === 'audit' ? 'show active' : '' ?>" id="adm-audit">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Nhật ký thao tác vận hành (Audit Logs)</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Thời điểm</th>
                                <th>Người thực hiện</th>
                                <th>Mã nghiệp vụ</th>
                                <th>Đối tượng</th>
                                <th>Dữ liệu ghi nhận</th>
                                <th>Địa chỉ IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditLogs as $log): ?>
                                <tr>
                                    <td class="text-nowrap"><?= format_datetime($log['created_at']) ?></td>
                                    <td><?= e($log['user_name'] ?: 'Hệ thống') ?></td>
                                    <td><code><?= e($log['action']) ?></code></td>
                                    <td><?= e($log['entity_type']) ?> #<?= (int)$log['entity_id'] ?></td>
                                    <td class="text-muted" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;"><?= e($log['new_values'] ?: $log['old_values']) ?></td>
                                    <td><?= e($log['ip_address']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 10: CẤU HÌNH HỆ THỐNG & GỬI THÔNG BÁO -->
        <div class="tab-pane fade <?= $activeTab === 'settings' ? 'show active' : '' ?>" id="adm-settings">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="gg-card p-4">
                        <h2 class="h4 mb-3">Thiết lập tham số hệ thống</h2>
                        <form action="/admin/settings/update" method="POST">
                            <?= csrf_field() ?>
                            <?php foreach ($settings as $st): ?>
                                <div class="mb-3">
                                    <label class="form-label mb-1"><?= e($st['description'] ?: $st['setting_key']) ?> (<code><?= e($st['setting_key']) ?></code>)</label>
                                    <input type="text" name="settings[<?= e($st['setting_key']) ?>]" class="form-control form-control-sm" value="<?= e($st['setting_value']) ?>">
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-gg-primary btn-sm px-4">Lưu cấu hình</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="gg-card p-4">
                        <h2 class="h4 mb-3">Gửi thông báo điều phối toàn hệ thống</h2>
                        <form action="/admin/notifications/broadcast" method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label">Nhóm tài khoản nhận</label>
                                <select name="target_role" class="form-select form-select-sm">
                                    <option value="">Tất cả thành viên</option>
                                    <option value="volunteer">Tình nguyện viên</option>
                                    <option value="organizer">Đơn vị tổ chức</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tiêu đề thông báo *</label>
                                <input type="text" name="title" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nội dung chi tiết *</label>
                                <textarea name="message" class="form-control form-control-sm" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-gg-primary btn-sm w-100">Phát thông báo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;
    const monthLabels = <?= json_encode($charts['month_labels'], JSON_UNESCAPED_UNICODE) ?>;

    new Chart(document.getElementById('admEventsChart'), {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{ label: 'Chương trình', data: <?= json_encode($charts['events_by_month']) ?>, backgroundColor: '#15803D', borderRadius: 4 }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('admParticipationsChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{ label: 'Lượt đăng ký', data: <?= json_encode($charts['participations_by_month']) ?>, borderColor: '#16A34A', backgroundColor: 'rgba(22,163,74,0.1)', fill: true, tension: 0.25 }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('admHoursChart'), {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{ label: 'Giờ tình nguyện', data: <?= json_encode($charts['hours_by_month']) ?>, backgroundColor: '#1E293B', borderRadius: 4 }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    const catData = <?= json_encode($charts['categories'], JSON_UNESCAPED_UNICODE) ?>;
    new Chart(document.getElementById('admCategoryChart'), {
        type: 'doughnut',
        data: {
            labels: catData.map(c => c.name),
            datasets: [{
                data: catData.map(c => Number(c.event_count)),
                backgroundColor: ['#15803D', '#16A34A', '#0284C7', '#D97706', '#475569', '#DC2626']
            }]
        }
    });
});
</script>
