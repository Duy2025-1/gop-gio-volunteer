<div class="gg-page-header">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="gg-section-kicker" style="color:#B91C1C;">Chương trình hiến máu tình nguyện</div>
                <h1 class="h2 mb-2">Lịch hiến máu nhân đạo &amp; đăng ký khung giờ</h1>
                <p class="text-muted mb-3" style="line-height:1.65;">
                    Đăng ký trước khung giờ có mặt giúp các bệnh viện và Hội Chữ thập đỏ chủ động bố trí bàn khám sàng lọc, giảm thời gian chờ đợi của người hiến máu và tình nguyện viên điều phối.
                </p>
                <div class="p-3 small" style="background-color:#FEF2F2;border:1px solid #FECACA;color:#991B1B;border-radius:6px;line-height:1.6;">
                    <strong>Lưu ý chuyên môn y tế:</strong>
                    <?= e($disclaimer ?: 'Nền tảng Góp Gió Volunteer không tự chẩn đoán khả năng hiến máu và không lưu trữ bệnh án. Việc khám sàng lọc huyết áp, huyết sắc tố và kết luận đủ điều kiện hiến máu do bác sĩ tại điểm tiếp nhận thực hiện.') ?>
                </div>
            </div>
            <div class="col-lg-5">
                <figure class="gg-hero-figure mb-0">
                    <img src="/assets/images/events/blood-donation.jpg"
                         alt="Điểm tiếp nhận hiến máu nhân đạo"
                         class="gg-hero-photo"
                         style="height:260px;"
                         loading="lazy">
                    <figcaption class="gg-hero-caption">
                        Khám sàng lọc và tiếp nhận máu toàn phần tại ngày hội hiến máu tình nguyện.
                    </figcaption>
                </figure>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Hướng dẫn chuẩn bị trước và sau khi hiến máu -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="gg-card p-3 h-100">
                <h3 class="h5 mb-2" style="color:#15803D;">1. Trước khi đến điểm hiến</h3>
                <p class="small text-muted mb-0" style="line-height:1.6;">
                    Đêm trước ngày hiến máu cần ngủ đủ tối thiểu 6 giờ; ăn sáng nhẹ ít dầu mỡ, uống thêm nước lọc và mang theo Căn cước công dân hoặc ứng dụng VNeID định danh mức 2.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="gg-card p-3 h-100">
                <h3 class="h5 mb-2 text-danger">2. Các trường hợp tạm hoãn</h3>
                <p class="small text-muted mb-0" style="line-height:1.6;">
                    Không sử dụng rượu bia trước khi hiến máu; tạm hoãn nếu đang dùng thuốc kháng sinh, vừa tiêm vắc-xin hoặc chưa đủ 12 tuần kể từ lần hiến máu toàn phần gần nhất.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="gg-card p-3 h-100">
                <h3 class="h5 mb-2 text-dark">3. Chăm sóc sau khi hiến máu</h3>
                <p class="small text-muted mb-0" style="line-height:1.6;">
                    Nghỉ tại khu vực theo dõi ít nhất 15 phút, uống nhiều nước, giữ băng ép vị trí lấy máu tối thiểu 4–6 giờ và hạn chế mang vác nặng trong 24 giờ đầu.
                </p>
            </div>
        </div>
    </div>

    <!-- Danh sách các điểm hiến máu -->
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <div class="gg-section-kicker">Danh sách đợt tiếp nhận</div>
            <h2 class="h3 mb-0">Các điểm hiến máu đang mở đăng ký</h2>
        </div>
    </div>

    <?php if (empty($bloodEvents)): ?>
        <div class="gg-empty-state">
            <h3 class="h4 mb-1">Hiện chưa có lịch hiến máu mới</h3>
            <p class="small text-muted mb-0">Lịch tiếp nhận máu lưu động sẽ được cập nhật ngay khi có thông báo từ Hội Chữ thập đỏ và các bệnh viện.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($bloodEvents as $ev): ?>
                <?php
                $maxV = max(1, (int)$ev['max_volunteers']);
                $regV = (int)$ev['registered_count'];
                $remaining = max(0, $maxV - $regV);
                ?>
                <div class="col-lg-6">
                    <article class="gg-card gg-card-hover p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="gg-badge badge-danger">Điểm hiến máu nhân đạo</span>
                            <?= status_badge_Event($ev['status']) ?>
                        </div>
                        <h3 class="h4 mb-2">
                            <a href="/events/<?= (int)$ev['id'] ?>" class="text-dark text-decoration-none"><?= e($ev['title']) ?></a>
                        </h3>
                        <p class="small text-muted mb-3" style="line-height:1.6;"><?= e($ev['short_description']) ?></p>

                        <div class="p-3 border mb-3 small d-flex flex-column gap-2" style="background-color:#F7F9F7;border-radius:6px;">
                            <div><strong>Đơn vị y tế phụ trách:</strong> <?= e($ev['medical_unit'] ?: $ev['organization_name']) ?></div>
                            <div><strong>Thời gian tiếp nhận:</strong> <?= format_datetime($ev['start_time']) ?> &rarr; <?= format_datetime($ev['end_time']) ?></div>
                            <div><strong>Địa điểm:</strong> <?= e($ev['location']) ?></div>
                            <div>
                                <strong>Chỉ tiêu tiếp nhận:</strong> <?= (int)($ev['blood_target_units'] ?: $maxV) ?> đơn vị
                                <span class="ms-2 text-muted">(Đã đăng ký: <?= $regV ?>/<?= $maxV ?> — Còn <?= $remaining ?> chỗ)</span>
                            </div>
                            <?php if (!empty($ev['pre_donation_instructions'])): ?>
                                <div class="pt-2 border-top text-muted">
                                    <strong>Hướng dẫn tại điểm hiến:</strong> <?= e($ev['pre_donation_instructions']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
                            <span class="small text-muted">Liên hệ: <strong class="text-dark"><?= e($ev['contact_info'] ?: '028 3855 4137') ?></strong></span>
                            <a href="/events/<?= (int)$ev['id'] ?>" class="btn btn-gg-primary btn-sm px-3">
                                Chọn khung giờ &amp; Đăng ký
                            </a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
