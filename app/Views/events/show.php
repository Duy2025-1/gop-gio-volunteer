<?php
$maxV = max(1, (int)$event['max_volunteers']);
$regV = (int)$event['registered_count'];
$remaining = max(0, $maxV - $regV);
$pct = min(100, round(($regV / $maxV) * 100));
$isBloodEvent = (int)($event['is_blood_donation'] ?? 0) === 1;
?>
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3 small">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none" style="color:#15803D;">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="/events" class="text-decoration-none" style="color:#15803D;">Hoạt động</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width:420px;"><?= e($event['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Cột thông tin chi tiết bên trái -->
        <div class="col-lg-8">
            <article class="gg-card overflow-hidden mb-4">
                <img src="<?= e($event['cover_image']) ?>"
                     alt="<?= e($event['title']) ?>"
                     class="w-100 object-fit-cover border-bottom"
                     style="height:360px;">
                <div class="p-4">
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                        <span class="gg-badge badge-neutral"><?= e($event['category_name']) ?></span>
                        <?= status_badge_Event($event['status']) ?>
                        <span class="gg-badge badge-info">
                            Định mức tối đa <?= number_format((float)$event['max_hours'], 1) ?> giờ tình nguyện
                        </span>
                        <span class="gg-badge badge-warning">
                            Đánh giá <?= number_format((float)$avgRating, 1) ?>/5.0 (<?= count($reviews) ?> phản hồi)
                        </span>
                    </div>

                    <h1 class="h2 mb-3"><?= e($event['title']) ?></h1>
                    <p class="text-muted fw-medium mb-4" style="font-size:1rem;line-height:1.65;"><?= e($event['short_description']) ?></p>

                    <h3 class="h4 border-bottom pb-2 mb-3">Nội dung và nhiệm vụ cụ thể</h3>
                    <div class="text-dark mb-4" style="white-space:pre-line;line-height:1.75;"><?= e($event['content']) ?></div>

                    <?php if ($isBloodEvent): ?>
                        <div class="p-3 mb-4" style="background-color:#FEF2F2;border:1px solid #FECACA;border-radius:6px;">
                            <h4 class="h5 text-danger mb-2">Thông tin chuyên môn chương trình hiến máu</h4>
                            <div class="small text-dark mb-1"><strong>Đơn vị y tế tiếp nhận:</strong> <?= e($event['medical_unit'] ?: $event['organization_name']) ?></div>
                            <div class="small text-dark mb-1"><strong>Chỉ tiêu tiếp nhận dự kiến:</strong> <?= (int)($event['blood_target_units'] ?: $event['max_volunteers']) ?> đơn vị máu</div>
                            <?php if (!empty($event['pre_donation_instructions'])): ?>
                                <div class="small text-dark mb-2"><strong>Lưu ý chuẩn bị trước khi đến điểm hiến:</strong> <?= e($event['pre_donation_instructions']) ?></div>
                            <?php endif; ?>
                            <div class="small text-danger">
                                Lưu ý: Hệ thống không tự chẩn đoán điều kiện hiến máu. Bác sĩ tại điểm tiếp nhận sẽ khám sàng lọc trực tiếp trước khi lấy máu.
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border h-100" style="background-color:#F7F9F7;border-radius:6px;">
                                <h4 class="h5 mb-2" style="color:#15803D;">Quyền lợi tình nguyện viên</h4>
                                <p class="small text-muted mb-0" style="line-height:1.6;"><?= e($event['benefits'] ?: 'Cấp chứng nhận giờ tình nguyện điện tử Góp Gió, hỗ trợ nước uống và hậu cần tại điểm hoạt động.') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border h-100" style="background-color:#F7F9F7;border-radius:6px;">
                                <h4 class="h5 mb-2 text-dark">Hướng dẫn điểm danh tại hiện trường</h4>
                                <p class="small text-muted mb-0" style="line-height:1.6;"><?= e($event['guidelines'] ?: 'Có mặt trước 15 phút tại bàn điều phối để xuất trình mã QR Check-in và nhận phân công nhiệm vụ.') ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($event['requirements'])): ?>
                        <h4 class="h5 mb-2">Yêu cầu đối với người tham gia</h4>
                        <ul class="small text-muted mb-4" style="line-height:1.7;">
                            <?php foreach ($event['requirements'] as $req): ?>
                                <li class="mb-1"><?= e($req['requirement_text']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($event['images'])): ?>
                        <h4 class="h5 mb-3">Hình ảnh tư liệu từ hoạt động</h4>
                        <div class="row g-3 mb-2">
                            <?php foreach ($event['images'] as $img): ?>
                                <div class="col-md-6">
                                    <figure class="mb-0">
                                        <img src="<?= e($img['image_url']) ?>"
                                             alt="<?= e($img['caption']) ?>"
                                             class="img-fluid rounded border"
                                             style="height:190px;width:100%;object-fit:cover;"
                                             loading="lazy">
                                        <?php if (!empty($img['caption'])): ?>
                                            <figcaption class="text-muted mt-1" style="font-size:0.78rem;"><?= e($img['caption']) ?></figcaption>
                                        <?php endif; ?>
                                    </figure>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <!-- Đánh giá từ tình nguyện viên đã tham gia -->
            <section class="gg-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h4 mb-0">Phản hồi từ tình nguyện viên đã tham gia (<?= count($reviews) ?>)</h3>
                    <button class="btn btn-gg-ghost btn-sm text-danger" data-bs-toggle="modal" data-bs-target="#reportEventModal">
                        Phản ánh thông tin
                    </button>
                </div>

                <?php if ($userRegistration && $userRegistration['status'] === 'Attended'): ?>
                    <form action="/events/<?= (int)$event['id'] ?>/review" method="POST" class="p-3 border mb-4" style="background-color:#F7F9F7;border-radius:6px;">
                        <?= csrf_field() ?>
                        <div class="fw-semibold small text-dark mb-2">Gửi nhận xét của bạn sau khi tham gia chương trình</div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="rating" class="form-select form-select-sm">
                                    <option value="5">5/5 – Rất tốt</option>
                                    <option value="4">4/5 – Tốt</option>
                                    <option value="3">3/5 – Đạt yêu cầu</option>
                                    <option value="2">2/5 – Cần điều chỉnh</option>
                                    <option value="1">1/5 – Chưa tốt</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="comment" class="form-control form-control-sm" placeholder="Chia sẻ cụ thể công tác tổ chức, hậu cần..." required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-gg-primary btn-sm w-100">Gửi nhận xét</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if (empty($reviews)): ?>
                    <div class="text-muted small py-2">Chương trình này chưa có phản hồi từ người tham gia.</div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($reviews as $rv): ?>
                            <div class="p-3 border bg-white" style="border-radius:6px;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= e($rv['user_avatar']) ?>" class="rounded-circle object-fit-cover border" width="32" height="32" alt="<?= e($rv['user_name']) ?>" loading="lazy">
                                        <span class="fw-semibold small text-dark"><?= e($rv['user_name']) ?></span>
                                    </div>
                                    <span class="gg-badge badge-neutral">Đánh giá <?= (int)$rv['rating'] ?>/5</span>
                                </div>
                                <p class="small text-muted mb-0" style="line-height:1.6;"><?= e($rv['comment']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Cột đăng ký & lịch trình bên phải -->
        <div class="col-lg-4">
            <div class="gg-card p-4 mb-4">
                <h3 class="h4 mb-3">Thời gian &amp; đăng ký tham gia</h3>

                <div class="small d-flex flex-column gap-2 mb-3 text-muted">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span>Thời gian bắt đầu:</span>
                        <strong class="text-dark"><?= format_datetime($event['start_time']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span>Thời gian kết thúc:</span>
                        <strong class="text-dark"><?= format_datetime($event['end_time']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span>Hạn chót đăng ký:</span>
                        <strong class="text-dark"><?= format_datetime($event['registration_deadline']) ?></strong>
                    </div>
                    <div class="pt-1">
                        <span class="d-block mb-1">Địa điểm tập kết:</span>
                        <strong class="text-dark"><?= e($event['location']) ?></strong>
                    </div>
                    <?php if (!empty($event['map_url'])): ?>
                        <div>
                            <a href="<?= e($event['map_url']) ?>" target="_blank" rel="noopener" class="fw-medium" style="color:#15803D;">
                                Mở vị trí trên bản đồ Google Maps &rarr;
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($event['contact_info'])): ?>
                        <div class="pt-1">
                            <span>Điều phối viên:</span>
                            <strong class="text-dark"><?= e($event['contact_info']) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Số lượng chỗ -->
                <div class="p-3 border mb-3" style="background-color:#F7F9F7;border-radius:6px;">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Đã tiếp nhận: <strong><?= $regV ?>/<?= $maxV ?> người</strong></span>
                        <span class="fw-semibold <?= $remaining > 0 ? 'text-success' : 'text-warning' ?>">
                            <?= $remaining > 0 ? "Còn {$remaining} chỗ trống" : 'Đã đủ chỗ (Mở danh sách chờ)' ?>
                        </span>
                    </div>
                    <div class="gg-progress">
                        <div class="gg-progress-bar" style="width: <?= $pct ?>%;"></div>
                    </div>
                </div>

                <!-- Khu vực nút hành động đăng ký -->
                <?php if (!is_logged_in()): ?>
                    <a href="/login" class="btn btn-gg-primary w-100 py-2">
                        Đăng nhập để đăng ký tham gia
                    </a>
                <?php elseif ($userRegistration && $userRegistration['status'] !== 'Cancelled'): ?>
                    <div class="p-3 border mb-3" style="background-color:#F7F9F7;border-radius:6px;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold text-dark">Trạng thái hồ sơ:</span>
                            <?= status_badge_registration($userRegistration['status']) ?>
                        </div>
                        <div class="small text-muted mb-2">Mã đăng ký: <code><?= e($userRegistration['registration_code']) ?></code></div>
                        <?php if ($userRegistration['status'] === 'Waitlisted'): ?>
                            <div class="small text-primary fw-medium mb-2">
                                Thứ tự danh sách chờ: #<?= (int)$userRegistration['waitlist_position'] ?> (Hệ thống tự động chuyển sang Đã xác nhận ngay khi có người hủy)
                            </div>
                        <?php endif; ?>
                        <?php if ($userRegistration['status'] === 'Confirmed'): ?>
                            <div class="text-center bg-white p-3 rounded border mb-2">
                                <img src="/registrations/<?= (int)$userRegistration['id'] ?>/qr.svg" alt="Mã QR Điểm danh" width="150" height="150">
                                <div class="text-muted mt-1" style="font-size:0.75rem;">Đưa mã QR này cho Ban tổ chức để quét Check-in và Check-out</div>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array($userRegistration['status'], ['Confirmed', 'Waitlisted', 'Pending'], true)): ?>
                            <form action="/registrations/<?= (int)$userRegistration['id'] ?>/cancel" method="POST"
                                  data-confirm="Bạn có chắc chắn muốn hủy đăng ký tham gia chương trình này? Nếu hủy, vị trí của bạn sẽ được nhường cho người trong danh sách chờ.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="cancel_reason" value="Tình nguyện viên chủ động hủy trước sự kiện">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100 mt-1">
                                    Hủy đăng ký tham gia
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php elseif (has_role('volunteer')): ?>
                    <?php if (in_array($event['status'], ['Published', 'Approved', 'Ongoing', 'approved'], true)): ?>
                        <form action="/events/<?= (int)$event['id'] ?>/register" method="POST">
                            <?= csrf_field() ?>
                            <?php if ($isBloodEvent): ?>
                                <div class="mb-2">
                                    <label class="form-label mb-1">Khung giờ dự kiến có mặt *</label>
                                    <select name="time_slot" class="form-select form-select-sm" required>
                                        <option value="Ca 1: 07:30 - 09:00">Ca 1: 07:30 - 09:00</option>
                                        <option value="Ca 2: 09:00 - 10:30">Ca 2: 09:00 - 10:30</option>
                                        <option value="Ca 3: 13:30 - 15:00">Ca 3: 13:30 - 15:00</option>
                                    </select>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label mb-1">Nhóm máu tự khai</label>
                                        <select name="blood_group_declared" class="form-select form-select-sm">
                                            <option value="Chưa rõ">Chưa rõ</option>
                                            <option value="O+">O+</option>
                                            <option value="A+">A+</option>
                                            <option value="B+">B+</option>
                                            <option value="AB+">AB+</option>
                                            <option value="O-">O-</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1">Định mức đăng ký</label>
                                        <select name="donation_volume_ml" class="form-select form-select-sm">
                                            <option value="350">350 ml</option>
                                            <option value="250">250 ml</option>
                                            <option value="450">450 ml</option>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <label class="form-label mb-1">Số điện thoại liên hệ khẩn cấp (không bắt buộc)</label>
                                <input type="text" name="emergency_contact" class="form-control form-control-sm" placeholder="SĐT người thân khi cần liên lạc...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label mb-1">Ghi chú kỹ năng hoặc vị trí hỗ trợ</label>
                                <textarea name="motivation_note" class="form-control form-control-sm" rows="2" placeholder="VD: Có phương tiện cá nhân, từng hỗ trợ điều phối..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-gg-primary w-100 py-2">
                                <?= $remaining > 0 ? 'Xác nhận đăng ký tham gia' : 'Đăng ký vào Danh sách chờ (Waitlist)' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-secondary small mb-0 text-center">
                            Chương trình này đã kết thúc hoặc tạm ngừng tiếp nhận đăng ký.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="p-3 border small" style="background-color:#F7F9F7;border-radius:6px;">
                        Bạn đang truy cập bằng tài khoản quản lý (<strong><?= e(strtoupper((string)(current_user()['role_slug'] ?? ''))) ?></strong>).
                        <?php if (has_role(['organizer', 'admin'])): ?>
                            <a href="/organizer/events/<?= (int)$event['id'] ?>/scanner" class="btn btn-gg-primary btn-sm w-100 mt-2">
                                Mở Trạm Quét QR Điểm Danh
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Thông tin đơn vị tổ chức -->
            <div class="gg-card p-4">
                <div class="gg-section-kicker mb-2">Đơn vị chịu trách nhiệm</div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="<?= e($event['organization_logo']) ?>"
                         class="rounded object-fit-cover border"
                         width="56" height="56"
                         alt="<?= e($event['organization_name']) ?>"
                         loading="lazy">
                    <div>
                        <a href="/organizations/<?= (int)$event['organization_id'] ?>" class="fw-semibold text-dark text-decoration-none">
                            <?= e($event['organization_name']) ?>
                        </a>
                        <div class="mt-1">
                            <span class="gg-badge badge-success">Đơn vị đã xác minh</span>
                        </div>
                    </div>
                </div>
                <a href="/organizations/<?= (int)$event['organization_id'] ?>" class="btn btn-gg-outline btn-sm w-100">
                    Xem hồ sơ đơn vị tổ chức
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal phản ánh thông tin sự kiện -->
<div class="modal fade" id="reportEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/events/<?= (int)$event['id'] ?>/report" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="target_type" value="Event">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Gửi phản ánh thông tin hoạt động</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nội dung cần kiểm tra *</label>
                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="VD: Địa điểm hoặc thời gian tập kết có thay đổi..." required>
                </div>
                <div>
                    <label class="form-label">Mô tả chi tiết</label>
                    <textarea name="details" class="form-control form-control-sm" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-gg-ghost btn-sm" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-gg-danger btn-sm">Gửi phản ánh</button>
            </div>
        </form>
    </div>
</div>
