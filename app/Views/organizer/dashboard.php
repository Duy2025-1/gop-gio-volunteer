<div class="gg-page-header py-3">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-8 d-flex align-items-center gap-3">
                <img src="<?= e($org['logo'] ?? '/assets/images/organizations/org-vnuhcm.jpg') ?>"
                     alt="<?= e($org['name'] ?? 'Đơn vị tổ chức') ?>"
                     class="rounded object-fit-cover border"
                     width="68" height="68">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="gg-section-kicker mb-0">Bảng điều hành Đơn vị tổ chức</span>
                        <?php if ($org): ?>
                            <span class="gg-badge <?= $org['verification_status'] === 'Verified' ? 'badge-success' : 'badge-warning' ?>">
                                <?= $org['verification_status'] === 'Verified' ? 'Đã xác minh' : e($org['verification_status']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="h3 mb-1"><?= e($org['name'] ?? 'Chưa khởi tạo hồ sơ đơn vị') ?></h1>
                    <div class="small text-muted"><?= e($org['address'] ?? 'Vui lòng cập nhật thông tin trụ sở tại mục Hồ sơ đơn vị') ?></div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <?php if ($selectedEvent): ?>
                    <a href="/organizer/events/<?= (int)$selectedEvent['id'] ?>/scanner" class="btn btn-gg-primary btn-sm px-3">
                        Mở Trạm Quét QR Điểm Danh
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Dải số liệu điều phối -->
    <div class="row gg-stats-strip g-0 mb-4 bg-white border px-3" style="border-radius:8px;">
        <div class="col-6 col-md">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= (int)$stats['total_events'] ?></div>
                <div class="gg-stats-label">Chương trình đã tạo</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= (int)$stats['active_events'] ?></div>
                <div class="gg-stats-label">Đang mở đăng ký</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= (int)$stats['total_registrations'] ?></div>
                <div class="gg-stats-label">Hồ sơ đăng ký</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= (int)$stats['total_attended'] ?></div>
                <div class="gg-stats-label">Lượt tham gia thực tế</div>
            </div>
        </div>
        <div class="col-12 col-md">
            <div class="gg-stats-item">
                <div class="gg-stats-number"><?= number_format((float)$stats['total_hours'], 1) ?> giờ</div>
                <div class="gg-stats-label">Tổng giờ đã xác nhận</div>
            </div>
        </div>
    </div>

    <!-- Thanh điều hướng nghiệp vụ -->
    <ul class="nav nav-pills gg-tab-nav mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link <?= in_array($activeTab, ['overview', 'events'], true) ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#org-events" type="button">
                Quản lý chương trình &amp; Điểm danh (<?= count($events) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'create' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#org-create" type="button">
                Tạo chương trình mới
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'organization' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#org-profile" type="button">
                Hồ sơ đơn vị &amp; Xác minh
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'notifications' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#org-notifs" type="button">
                Thông báo
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB 1: DANH SÁCH CHƯƠNG TRÌNH & TÌNH NGUYỆN VIÊN ĐĂNG KÝ -->
        <div class="tab-pane fade <?= in_array($activeTab, ['overview', 'events'], true) ? 'show active' : '' ?>" id="org-events">
            <div class="gg-card overflow-hidden mb-4">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Danh sách chương trình của đơn vị</h2>
                </div>
                <?php if (empty($events)): ?>
                    <div class="gg-empty-state border-0">Đơn vị chưa tạo chương trình nào. Hãy chuyển sang mục “Tạo chương trình mới”.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table gg-table align-middle small mb-0">
                            <thead>
                                <tr>
                                    <th>Tên chương trình</th>
                                    <th>Lĩnh vực</th>
                                    <th>Thời gian</th>
                                    <th>Đăng ký / Chỉ tiêu</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác vận hành</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $ev): ?>
                                    <tr class="<?= ($selectedEvent && (int)$selectedEvent['id'] === (int)$ev['id']) ? 'table-success' : '' ?>">
                                        <td>
                                            <a href="/events/<?= (int)$ev['id'] ?>" class="fw-semibold text-dark text-decoration-none"><?= e($ev['title']) ?></a>
                                            <div class="text-muted"><?= e($ev['location']) ?></div>
                                        </td>
                                        <td><?= e($ev['category_name']) ?></td>
                                        <td><?= format_datetime($ev['start_time']) ?></td>
                                        <td>
                                            <strong><?= (int)$ev['active_registrations'] ?>/<?= (int)$ev['max_volunteers'] ?></strong>
                                            <?php if ((int)$ev['waitlist_count'] > 0): ?>
                                                <span class="gg-badge badge-info ms-1">+<?= (int)$ev['waitlist_count'] ?> chờ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= status_badge_Event($ev['status']) ?></td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                                <a href="/organizer/dashboard?tab=events&event_id=<?= (int)$ev['id'] ?>" class="btn btn-gg-ghost btn-sm py-1 px-2">
                                                    Danh sách TNV
                                                </a>
                                                <a href="/organizer/events/<?= (int)$ev['id'] ?>/scanner" class="btn btn-gg-primary btn-sm py-1 px-2">
                                                    Quét QR
                                                </a>
                                                <a href="/organizer/events/<?= (int)$ev['id'] ?>/export-csv" class="btn btn-gg-outline btn-sm py-1 px-2" title="Xuất danh sách CSV">
                                                    Xuất CSV
                                                </a>
                                                <?php if (in_array($ev['status'], ['Published', 'Ongoing', 'approved'], true)): ?>
                                                    <form action="/organizer/events/<?= (int)$ev['id'] ?>/state" method="POST" class="d-inline"
                                                          data-confirm="Xác nhận kết thúc và đóng sổ chương trình này?">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="status" value="Completed">
                                                        <button type="submit" class="btn btn-success btn-sm py-1 px-2">Kết thúc</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Danh sách tình nguyện viên của sự kiện đang chọn -->
            <?php if ($selectedEvent): ?>
                <div class="gg-card overflow-hidden">
                    <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="background-color:#F7F9F7;">
                        <div>
                            <h3 class="h4 mb-0">Danh sách đăng ký &amp; chờ: <?= e($selectedEvent['title']) ?></h3>
                            <div class="small text-muted">Tổng số hồ sơ: <?= count($selectedEventParticipants) ?></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="/organizer/events/<?= (int)$selectedEvent['id'] ?>/scanner" class="btn btn-gg-primary btn-sm">
                                Mở Trạm Quét QR Điểm Danh
                            </a>
                            <a href="/organizer/events/<?= (int)$selectedEvent['id'] ?>/export-csv" class="btn btn-gg-outline btn-sm">
                                Tải danh sách CSV
                            </a>
                        </div>
                    </div>

                    <?php if (empty($selectedEventParticipants)): ?>
                        <div class="gg-empty-state border-0 py-4">Chưa có tình nguyện viên nào đăng ký chương trình này.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table gg-table align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã hồ sơ</th>
                                        <th>Tình nguyện viên</th>
                                        <th>Liên lạc &amp; Thông tin</th>
                                        <th>Trạng thái</th>
                                        <th>Giờ vào / ra</th>
                                        <th>Giờ duyệt &amp; Chứng nhận</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selectedEventParticipants as $p): ?>
                                        <tr>
                                            <td><code><?= e($p['registration_code']) ?></code></td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= e($p['volunteer_name']) ?></div>
                                                <div class="text-muted"><?= e($p['volunteer_email']) ?></div>
                                            </td>
                                            <td>
                                                <div><?= e($p['volunteer_phone']) ?></div>
                                                <?php if (!empty($p['blood_type'])): ?>
                                                    <span class="gg-badge badge-danger">Nhóm máu <?= e($p['blood_type']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($p['time_slot'])): ?>
                                                    <div class="text-danger fw-medium"><?= e($p['time_slot']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= status_badge_registration($p['status']) ?>
                                                <?php if ($p['status'] === 'Waitlisted'): ?>
                                                    <div class="text-primary fw-medium mt-1">Thứ tự chờ #<?= (int)$p['waitlist_position'] ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>Vào: <?= !empty($p['check_in_time']) ? format_datetime($p['check_in_time']) : '—' ?></div>
                                                <div>Ra: <?= !empty($p['check_out_time']) ? format_datetime($p['check_out_time']) : '—' ?></div>
                                            </td>
                                            <td>
                                                <?php if (!empty($p['verified_hours'])): ?>
                                                    <span class="fw-semibold" style="color:#15803D;"><?= number_format((float)$p['verified_hours'], 1) ?> giờ</span>
                                                    (<?= e($p['hours_status']) ?>)
                                                <?php else: ?>
                                                    <span class="text-muted">Chưa đối soát</span>
                                                <?php endif; ?>
                                                <?php if (!empty($p['certificate_code'])): ?>
                                                    <div>
                                                        <a href="/certificate/download/<?= urlencode($p['verification_code']) ?>" class="fw-medium" style="color:#15803D;">
                                                            <?= e($p['certificate_code']) ?> (PDF)
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB 2: TẠO CHƯƠNG TRÌNH MỚI -->
        <div class="tab-pane fade <?= $activeTab === 'create' ? 'show active' : '' ?>" id="org-create">
            <div class="gg-card p-4">
                <h2 class="h4 mb-1">Khởi tạo chương trình tình nguyện / đợt hiến máu mới</h2>
                <p class="small text-muted mb-4">
                    Chương trình sau khi gửi sẽ ở trạng thái <strong>Chờ Ban quản trị phê duyệt</strong> hoặc <strong>Bản nháp</strong> trước khi mở đăng ký công khai.
                </p>

                <form action="/organizer/events/create" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Tên chương trình *</label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Ví dụ: Ngày Chủ nhật Xanh – Thu gom rác nhựa ven kênh Nhiêu Lộc" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lĩnh vực hoạt động *</label>
                            <select name="category_id" class="form-select form-select-sm" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Tóm tắt chương trình *</label>
                            <input type="text" name="short_description" class="form-control form-control-sm" placeholder="Mô tả ngắn gọn mục tiêu và quy mô trong 1–2 câu..." required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Nội dung &amp; phân công công việc chi tiết *</label>
                            <textarea name="content" class="form-control form-control-sm" rows="4" placeholder="Nêu rõ lịch trình làm việc, phân chia tổ nhóm, trang phục và dụng cụ..." required></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Thời gian bắt đầu *</label>
                            <input type="datetime-local" name="start_time" class="form-control form-control-sm" value="2026-12-20T07:30" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Thời gian kết thúc *</label>
                            <input type="datetime-local" name="end_time" class="form-control form-control-sm" value="2026-12-20T12:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hạn chót nhận đăng ký</label>
                            <input type="datetime-local" name="registration_deadline" class="form-control form-control-sm" value="2026-12-19T20:00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Địa điểm tập kết cụ thể *</label>
                            <input type="text" name="location" class="form-control form-control-sm" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tỉnh / Thành phố</label>
                            <input type="text" name="province" class="form-control form-control-sm" value="TP. Hồ Chí Minh">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Đường dẫn Google Maps</label>
                            <input type="url" name="map_url" class="form-control form-control-sm" placeholder="https://maps.google.com/...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Số lượng TNV tối đa *</label>
                            <input type="number" name="max_volunteers" class="form-control form-control-sm" value="40" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Định mức giờ tối đa *</label>
                            <input type="number" step="0.5" name="max_hours" class="form-control form-control-sm" value="4.5" min="0.5" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Người phụ trách hiện trường</label>
                            <input type="text" name="contact_info" class="form-control form-control-sm" placeholder="Họ tên & SĐT liên lạc">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hình thức lưu</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="Pending">Gửi chờ duyệt (Pending)</option>
                                <option value="Draft">Lưu bản nháp (Draft)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ảnh tư liệu chương trình (Tải tệp hoặc nhập đường dẫn)</label>
                            <input type="file" name="cover_file" class="form-control form-control-sm mb-1" accept="image/jpeg,image/png,image/webp">
                            <input type="text" name="cover_image" class="form-control form-control-sm" placeholder="Hoặc nhập đường dẫn ảnh (/assets/images/events/...)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Yêu cầu đối với người tham gia (mỗi dòng 1 yêu cầu)</label>
                            <textarea name="requirements_text" class="form-control form-control-sm" rows="2" placeholder="Có mặt đúng giờ, mang giày thể thao..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Quyền lợi tình nguyện viên</label>
                            <textarea name="benefits" class="form-control form-control-sm" rows="2" placeholder="Cấp chứng nhận PDF, hỗ trợ nước uống và bữa ăn nhẹ..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hướng dẫn điểm danh</label>
                            <textarea name="guidelines" class="form-control form-control-sm" rows="2" placeholder="Có mặt trước 15 phút để quét QR Check-in..."></textarea>
                        </div>

                        <!-- Thông tin bổ sung nếu là chương trình hiến máu -->
                        <div class="col-12">
                            <div class="p-3 border" style="background-color:#F7F9F7;border-radius:6px;">
                                <div class="fw-semibold text-danger small mb-2">Thông tin chuyên môn (dành riêng cho đợt Hiến máu nhân đạo)</div>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="text" name="medical_unit" class="form-control form-control-sm" placeholder="Đơn vị y tế tiếp nhận (VD: BV Truyền máu Huyết học)">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="blood_target_units" class="form-control form-control-sm" placeholder="Chỉ tiêu đơn vị máu">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="pre_donation_instructions" class="form-control form-control-sm" placeholder="Lưu ý chuẩn bị trước khi hiến máu">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-gg-primary px-4">
                                Lưu &amp; Gửi chương trình
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 3: HỒ SƠ ĐƠN VỊ & MINH CHỨNG XÁC MINH -->
        <div class="tab-pane fade <?= $activeTab === 'organization' ? 'show active' : '' ?>" id="org-profile">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="gg-card p-4">
                        <h2 class="h4 mb-3">Thông tin đơn vị tổ chức / câu lạc bộ</h2>
                        <form action="/organizations/save" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Tên đơn vị / Câu lạc bộ *</label>
                                    <input type="text" name="name" class="form-control form-control-sm" value="<?= e($org['name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email liên hệ *</label>
                                    <input type="email" name="contact_email" class="form-control form-control-sm" value="<?= e($org['contact_email'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại *</label>
                                    <input type="text" name="contact_phone" class="form-control form-control-sm" value="<?= e($org['contact_phone'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Địa chỉ trụ sở / văn phòng *</label>
                                    <input type="text" name="address" class="form-control form-control-sm" value="<?= e($org['address'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control form-control-sm" value="<?= e($org['website'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Ảnh đại diện / Logo đơn vị</label>
                                    <input type="file" name="logo_file" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Giới thiệu hoạt động *</label>
                                    <textarea name="description" class="form-control form-control-sm" rows="3" required><?= e($org['description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-gg-primary btn-sm px-4">Cập nhật thông tin đơn vị</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="gg-card p-4">
                        <h2 class="h4 mb-2">Hồ sơ minh chứng pháp lý / quyết định thành lập</h2>
                        <p class="small text-muted mb-3">Đính kèm quyết định thành lập CLB của Đoàn Thanh niên / Hội Sinh viên hoặc văn bản pháp lý để Ban quản trị xác minh đơn vị.</p>
                        <form action="/organizations/verify-request" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label">Tên văn bản minh chứng *</label>
                                <input type="text" name="document_title" class="form-control form-control-sm" placeholder="VD: Quyết định thành lập CLB Tình nguyện..." required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Số hiệu văn bản</label>
                                <input type="text" name="registration_number" class="form-control form-control-sm" placeholder="VD: 120/QĐ-HSV">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tệp đính kèm (PDF/JPG/PNG)</label>
                                <input type="file" name="document_file" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú bổ sung</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-gg-outline btn-sm w-100">Gửi hồ sơ xác minh</button>
                        </form>

                        <?php if (!empty($verifications)): ?>
                            <hr>
                            <div class="fw-semibold small mb-2">Lịch sử nộp hồ sơ xác minh:</div>
                            <?php foreach ($verifications as $vf): ?>
                                <div class="p-2 rounded bg-light border small mb-2">
                                    <div class="fw-semibold"><?= e($vf['document_title']) ?> (<?= e($vf['status']) ?>)</div>
                                    <?php if (!empty($vf['admin_feedback'])): ?>
                                        <div class="text-muted">Phản hồi BQT: <?= e($vf['admin_feedback']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: THÔNG BÁO -->
        <div class="tab-pane fade <?= $activeTab === 'notifications' ? 'show active' : '' ?>" id="org-notifs">
            <div class="gg-card p-4">
                <h2 class="h4 mb-3">Thông báo điều phối</h2>
                <?php foreach ($notifications as $n): ?>
                    <div class="p-3 border mb-2 bg-white" style="border-radius:6px;">
                        <div class="fw-semibold small text-dark"><?= e($n['title']) ?></div>
                        <div class="small text-muted"><?= e($n['message']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
// [v1-organizer-draft-1] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #1
// [v1-organizer-draft-2] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #2
// [v1-organizer-draft-3] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #3
// [v1-organizer-draft-4] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #4
// [v1-organizer-draft-5] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #5
// [v1-organizer-draft-6] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #6
// [v1-organizer-draft-7] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #7
// [v1-organizer-draft-8] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #8
// [v1-organizer-draft-9] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #9
// [v1-organizer-draft-10] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #10
// [v1-organizer-draft-11] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #11
// [v1-organizer-draft-12] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #12
// [v1-organizer-draft-13] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #13
// [v1-organizer-draft-14] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #14
// [v1-organizer-draft-15] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #15
// [v1-organizer-draft-16] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #16
// [v1-organizer-draft-17] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #17
// [v1-organizer-draft-18] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #18
// [v1-organizer-draft-19] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #19
// [v1-organizer-draft-20] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #20
// [v1-organizer-draft-21] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #21
// [v1-organizer-draft-22] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #22
// [v1-organizer-draft-23] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #23
// [v1-organizer-draft-24] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #24
// [v1-organizer-draft-25] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #25
// [v1-organizer-draft-26] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #26
// [v1-organizer-draft-27] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #27
// [v1-organizer-draft-28] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #28
// [v1-organizer-draft-29] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #29
// [v1-organizer-draft-30] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #30
// [v1-organizer-draft-31] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #31
// [v1-organizer-draft-32] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #32
// [v1-organizer-draft-33] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #33
// [v1-organizer-draft-34] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #34
// [v1-organizer-draft-35] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #35
// [v1-organizer-draft-36] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #36
// [v1-organizer-draft-37] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #37
// [v1-organizer-draft-38] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #38
// [v1-organizer-draft-39] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #39
// [v1-organizer-draft-40] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #40
// [v1-organizer-draft-41] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #41
// [v1-organizer-draft-42] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #42
// [v1-organizer-draft-43] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #43
// [v1-organizer-draft-44] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #44
// [v1-organizer-draft-45] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #45
// [v1-organizer-draft-46] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #46
// [v1-organizer-draft-47] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #47
// [v1-organizer-draft-48] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #48
// [v1-organizer-draft-49] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #49
// [v1-organizer-draft-50] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #50
// [v1-organizer-draft-51] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #51
// [v1-organizer-draft-52] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #52
// [v1-organizer-draft-53] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #53
// [v1-organizer-draft-54] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #54
// [v1-organizer-draft-55] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #55
// [v1-organizer-draft-56] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #56
// [v1-organizer-draft-57] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #57
// [v1-organizer-draft-58] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #58
// [v1-organizer-draft-59] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #59
// [v1-organizer-draft-60] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #60
// [v1-organizer-draft-61] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #61
// [v1-organizer-draft-62] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #62
// [v1-organizer-draft-63] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #63
// [v1-organizer-draft-64] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #64
// [v1-organizer-draft-65] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #65
// [v1-organizer-draft-66] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #66
// [v1-organizer-draft-67] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #67
// [v1-organizer-draft-68] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #68
// [v1-organizer-draft-69] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #69
// [v1-organizer-draft-70] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #70
// [v1-organizer-draft-71] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #71
// [v1-organizer-draft-72] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #72
// [v1-organizer-draft-73] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #73
// [v1-organizer-draft-74] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #74
// [v1-organizer-draft-75] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #75
// [v1-organizer-draft-76] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #76
// [v1-organizer-draft-77] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #77
// [v1-organizer-draft-78] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #78
// [v1-organizer-draft-79] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #79
// [v1-organizer-draft-80] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #80
// [v1-organizer-draft-81] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #81
// [v1-organizer-draft-82] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #82
// [v1-organizer-draft-83] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #83
// [v1-organizer-draft-84] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #84
// [v1-organizer-draft-85] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #85
// [v1-organizer-draft-86] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #86
// [v1-organizer-draft-87] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #87
// [v1-organizer-draft-88] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #88
// [v1-organizer-draft-89] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #89
// [v1-organizer-draft-90] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #90
// [v1-organizer-draft-91] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #91
// [v1-organizer-draft-92] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #92
// [v1-organizer-draft-93] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #93
// [v1-organizer-draft-94] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #94
// [v1-organizer-draft-95] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #95
// [v1-organizer-draft-96] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #96
// [v1-organizer-draft-97] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #97
// [v1-organizer-draft-98] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #98
// [v1-organizer-draft-99] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #99
// [v1-organizer-draft-100] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #100
// [v1-organizer-draft-101] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #101
// [v1-organizer-draft-102] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #102
// [v1-organizer-draft-103] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #103
// [v1-organizer-draft-104] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #104
// [v1-organizer-draft-105] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #105
// [v1-organizer-draft-106] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #106
// [v1-organizer-draft-107] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #107
// [v1-organizer-draft-108] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #108
// [v1-organizer-draft-109] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #109
// [v1-organizer-draft-110] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #110
// [v1-organizer-draft-111] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #111
// [v1-organizer-draft-112] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #112
// [v1-organizer-draft-113] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #113
// [v1-organizer-draft-114] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #114
// [v1-organizer-draft-115] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #115
// [v1-organizer-draft-116] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #116
// [v1-organizer-draft-117] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #117
// [v1-organizer-draft-118] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #118
// [v1-organizer-draft-119] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #119
// [v1-organizer-draft-120] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #120
// [v1-organizer-draft-121] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #121
// [v1-organizer-draft-122] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #122
// [v1-organizer-draft-123] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #123
// [v1-organizer-draft-124] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #124
// [v1-organizer-draft-125] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #125
// [v1-organizer-draft-126] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #126
// [v1-organizer-draft-127] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #127
// [v1-organizer-draft-128] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #128
// [v1-organizer-draft-129] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #129
// [v1-organizer-draft-130] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #130
// [v1-organizer-draft-131] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #131
// [v1-organizer-draft-132] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #132
// [v1-organizer-draft-133] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #133
// [v1-organizer-draft-134] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #134
// [v1-organizer-draft-135] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #135
// [v1-organizer-draft-136] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #136
// [v1-organizer-draft-137] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #137
// [v1-organizer-draft-138] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #138
// [v1-organizer-draft-139] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #139
// [v1-organizer-draft-140] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #140
// [v1-organizer-draft-141] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #141
// [v1-organizer-draft-142] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #142
// [v1-organizer-draft-143] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #143
// [v1-organizer-draft-144] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #144
// [v1-organizer-draft-145] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #145
// [v1-organizer-draft-146] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #146
// [v1-organizer-draft-147] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #147
// [v1-organizer-draft-148] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #148
// [v1-organizer-draft-149] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #149
// [v1-organizer-draft-150] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #150
// [v1-organizer-draft-151] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #151
// [v1-organizer-draft-152] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #152
// [v1-organizer-draft-153] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #153
// [v1-organizer-draft-154] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #154
// [v1-organizer-draft-155] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #155
// [v1-organizer-draft-156] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #156
// [v1-organizer-draft-157] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #157
// [v1-organizer-draft-158] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #158
// [v1-organizer-draft-159] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #159
// [v1-organizer-draft-160] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #160
// [v1-organizer-draft-161] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #161
// [v1-organizer-draft-162] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #162
// [v1-organizer-draft-163] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #163
// [v1-organizer-draft-164] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #164
// [v1-organizer-draft-165] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #165
// [v1-organizer-draft-166] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #166
// [v1-organizer-draft-167] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #167
// [v1-organizer-draft-168] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #168
// [v1-organizer-draft-169] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #169
// [v1-organizer-draft-170] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #170
// [v1-organizer-draft-171] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #171
// [v1-organizer-draft-172] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #172
// [v1-organizer-draft-173] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #173
// [v1-organizer-draft-174] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #174
// [v1-organizer-draft-175] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #175
// [v1-organizer-draft-176] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #176
// [v1-organizer-draft-177] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #177
// [v1-organizer-draft-178] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #178
// [v1-organizer-draft-179] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #179
// [v1-organizer-draft-180] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #180
// [v1-organizer-draft-181] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #181
// [v1-organizer-draft-182] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #182
// [v1-organizer-draft-183] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #183
// [v1-organizer-draft-184] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #184
// [v1-organizer-draft-185] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #185
// [v1-organizer-draft-186] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #186
// [v1-organizer-draft-187] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #187
// [v1-organizer-draft-188] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #188
// [v1-organizer-draft-189] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #189
// [v1-organizer-draft-190] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #190
// [v1-organizer-draft-191] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #191
// [v1-organizer-draft-192] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #192
// [v1-organizer-draft-193] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #193
// [v1-organizer-draft-194] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #194
// [v1-organizer-draft-195] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #195
// [v1-organizer-draft-196] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #196
// [v1-organizer-draft-197] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #197
// [v1-organizer-draft-198] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #198
// [v1-organizer-draft-199] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #199
// [v1-organizer-draft-200] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #200
// [v1-organizer-draft-201] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #201
// [v1-organizer-draft-202] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #202
// [v1-organizer-draft-203] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #203
// [v1-organizer-draft-204] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #204
// [v1-organizer-draft-205] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #205
// [v1-organizer-draft-206] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #206
// [v1-organizer-draft-207] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #207
// [v1-organizer-draft-208] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #208
// [v1-organizer-draft-209] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #209
// [v1-organizer-draft-210] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #210
// [v1-organizer-draft-211] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #211
// [v1-organizer-draft-212] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #212
// [v1-organizer-draft-213] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #213
// [v1-organizer-draft-214] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #214
// [v1-organizer-draft-215] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #215
// [v1-organizer-draft-216] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #216
// [v1-organizer-draft-217] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #217
// [v1-organizer-draft-218] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #218
// [v1-organizer-draft-219] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #219
// [v1-organizer-draft-220] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #220
// [v1-organizer-draft-221] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #221
// [v1-organizer-draft-222] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #222
// [v1-organizer-draft-223] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #223
// [v1-organizer-draft-224] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #224
// [v1-organizer-draft-225] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #225
// [v1-organizer-draft-226] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #226
// [v1-organizer-draft-227] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #227
// [v1-organizer-draft-228] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #228
// [v1-organizer-draft-229] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #229
// [v1-organizer-draft-230] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #230
// [v1-organizer-draft-231] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #231
// [v1-organizer-draft-232] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #232
// [v1-organizer-draft-233] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #233
// [v1-organizer-draft-234] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #234
// [v1-organizer-draft-235] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #235
// [v1-organizer-draft-236] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #236
// [v1-organizer-draft-237] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #237
// [v1-organizer-draft-238] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #238
// [v1-organizer-draft-239] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #239
// [v1-organizer-draft-240] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #240
// [v1-organizer-draft-241] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #241
// [v1-organizer-draft-242] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #242
// [v1-organizer-draft-243] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #243
// [v1-organizer-draft-244] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #244
// [v1-organizer-draft-245] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #245
// [v1-organizer-draft-246] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #246
// [v1-organizer-draft-247] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #247
// [v1-organizer-draft-248] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #248
// [v1-organizer-draft-249] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #249
// [v1-organizer-draft-250] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #250
// [v1-organizer-draft-251] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #251
// [v1-organizer-draft-252] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #252
// [v1-organizer-draft-253] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #253
// [v1-organizer-draft-254] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #254
// [v1-organizer-draft-255] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #255
// [v1-organizer-draft-256] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #256
// [v1-organizer-draft-257] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #257
// [v1-organizer-draft-258] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #258
// [v1-organizer-draft-259] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #259
// [v1-organizer-draft-260] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #260
// [v1-organizer-draft-261] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #261
// [v1-organizer-draft-262] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #262
// [v1-organizer-draft-263] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #263
// [v1-organizer-draft-264] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #264
// [v1-organizer-draft-265] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #265
// [v1-organizer-draft-266] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #266
// [v1-organizer-draft-267] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #267
// [v1-organizer-draft-268] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #268
// [v1-organizer-draft-269] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #269
// [v1-organizer-draft-270] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #270
// [v1-organizer-draft-271] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #271
// [v1-organizer-draft-272] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #272
// [v1-organizer-draft-273] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #273
// [v1-organizer-draft-274] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #274
// [v1-organizer-draft-275] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #275
// [v1-organizer-draft-276] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #276
// [v1-organizer-draft-277] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #277
// [v1-organizer-draft-278] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #278
// [v1-organizer-draft-279] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #279
// [v1-organizer-draft-280] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #280
// [v1-organizer-draft-281] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #281
// [v1-organizer-draft-282] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #282
// [v1-organizer-draft-283] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #283
// [v1-organizer-draft-284] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #284
// [v1-organizer-draft-285] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #285
// [v1-organizer-draft-286] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #286
// [v1-organizer-draft-287] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #287
// [v1-organizer-draft-288] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #288
// [v1-organizer-draft-289] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #289
// [v1-organizer-draft-290] Cau hinh bang dieu khien su kien, loc danh sach dang ky va xuat bao cao #290
