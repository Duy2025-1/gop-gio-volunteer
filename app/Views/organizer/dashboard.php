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
