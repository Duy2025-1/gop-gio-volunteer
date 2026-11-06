<!-- 1. HERO SECTION -->
<section class="gg-hero">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <p class="gg-section-kicker mb-2">Góp Gió Volunteer — Nền tảng tình nguyện cộng đồng</p>
                <h1 class="gg-hero-title">
                    Kết nối những tấm lòng, cùng tạo nên thay đổi.
                </h1>
                <p class="gg-hero-lead">
                    Tìm kiếm hoạt động tình nguyện phù hợp với bạn, đăng ký tham gia và cùng chung tay xây dựng cộng đồng tốt đẹp hơn.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/events" class="btn btn-gg-primary px-4 py-2">
                        Khám phá hoạt động
                    </a>
                    <a href="/blood-donation" class="btn btn-gg-outline px-4 py-2">
                        Tìm chương trình hiến máu
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <figure class="gg-hero-figure">
                    <img src="/assets/images/hero/volunteer-team.jpg"
                         alt="Tình nguyện viên Góp Gió chuẩn bị nhu yếu phẩm và phân loại quà tặng cộng đồng"
                         class="gg-hero-photo">
                    <figcaption class="gg-hero-caption d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span>Đội hình tình nguyện viên phân loại phần quà và nhu yếu phẩm cho chương trình hỗ trợ cuối tuần tại TP.HCM.</span>
                        <span class="fw-medium text-dark">Ảnh tư liệu hoạt động</span>
                    </figcaption>
                </figure>
            </div>
        </div>
    </div>
</section>

<!-- 2. KHU VỰC SỐ LIỆU (DỮ LIỆU TRỰC TIẾP TỪ DATABASE - BỐ CỤC DẢI NGANG TINH GỌN) -->
<section class="py-4 border-bottom" style="background-color:#FFFFFF;">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-2">
            <span class="gg-section-kicker">Số liệu ghi nhận thực tế trên hệ thống</span>
            <span class="text-muted" style="font-size:0.78rem;">Tổng hợp tự động từ cơ sở dữ liệu điểm danh và hồ sơ đã xác minh</span>
        </div>
        <div class="row gg-stats-strip g-0">
            <div class="col-6 col-lg-3">
                <div class="gg-stats-item">
                    <div class="gg-stats-number"><?= (int)$communityStats['total_volunteers'] ?></div>
                    <div class="gg-stats-label">Tình nguyện viên đã đăng ký</div>
                    <div class="gg-stats-note">Tài khoản hoạt động trên hệ thống</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="gg-stats-item">
                    <div class="gg-stats-number"><?= (int)$communityStats['total_events'] ?></div>
                    <div class="gg-stats-label">Chương trình &amp; đợt hoạt động</div>
                    <div class="gg-stats-note">Đã được duyệt và công bố</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="gg-stats-item">
                    <div class="gg-stats-number"><?= number_format((float)$communityStats['total_hours'], 1) ?> giờ</div>
                    <div class="gg-stats-label">Giờ tình nguyện đã xác nhận</div>
                    <div class="gg-stats-note">Đối chiếu qua điểm danh QR hai chiều</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="gg-stats-item">
                    <div class="gg-stats-number"><?= (int)$communityStats['total_organizations'] ?></div>
                    <div class="gg-stats-label">Đơn vị tổ chức đã xác minh</div>
                    <div class="gg-stats-note">Câu lạc bộ, hội chữ thập đỏ &amp; đoàn hội</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. HOẠT ĐỘNG NỔI BẬT -->
<section class="gg-section" style="background-color:#FFFFFF;">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-2">
            <div>
                <div class="gg-section-kicker">Lịch hoạt động sắp tới</div>
                <h2 class="mb-1">Hoạt động tình nguyện đang tiếp nhận đăng ký</h2>
                <p class="gg-section-desc mb-0">Thông tin thời gian, địa điểm tập kết, đơn vị phụ trách và số lượng chỗ còn trống được cập nhật theo thời gian thực.</p>
            </div>
            <div>
                <a href="/events" class="btn btn-gg-outline btn-sm">Xem toàn bộ danh sách</a>
            </div>
        </div>

        <?php if (empty($featuredEvents)): ?>
            <div class="gg-empty-state">
                <h4 class="mb-2">Hiện chưa có hoạt động nào đang mở đăng ký</h4>
                <p class="text-muted small mb-3">Các câu lạc bộ và đơn vị tổ chức đang cập nhật kế hoạch cho đợt tiếp theo. Bạn có thể xem lại các chương trình trước đó.</p>
                <a href="/events" class="btn btn-gg-outline btn-sm">Tra cứu lịch sử hoạt động</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($featuredEvents as $ev): ?>
                    <?php
                    $maxV = max(1, (int)$ev['max_volunteers']);
                    $regV = (int)$ev['registered_count'];
                    $remainingSlots = max(0, $maxV - $regV);
                    $pct = min(100, round(($regV / $maxV) * 100));
                    $isOpen = ($ev['status'] === 'approved' && $remainingSlots > 0);
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="gg-card gg-card-hover h-100 d-flex flex-column">
                            <div class="gg-event-img-wrap">
                                <a href="/events/<?= (int)$ev['id'] ?>" class="d-block">
                                    <img src="<?= e($ev['cover_image']) ?>"
                                         alt="<?= e($ev['title']) ?>"
                                         class="gg-event-img"
                                         loading="lazy">
                                </a>
                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="gg-badge" style="background:rgba(255,255,255,0.95);color:#1E293B;border-color:#E5E7EB;">
                                        <?= e($ev['category_name']) ?>
                                    </span>
                                </div>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <?php if ($isOpen): ?>
                                        <span class="gg-badge badge-success">Đang nhận đăng ký</span>
                                    <?php elseif ($remainingSlots === 0 && $ev['status'] === 'approved'): ?>
                                        <span class="gg-badge badge-warning">Đã đủ số lượng</span>
                                    <?php else: ?>
                                        <?= status_badge_Event($ev['status']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <div class="gg-event-org mb-1">
                                    Đơn vị: <span class="text-dark"><?= e($ev['organization_name']) ?></span>
                                </div>
                                <h3 class="gg-event-title">
                                    <a href="/events/<?= (int)$ev['id'] ?>"><?= e($ev['title']) ?></a>
                                </h3>
                                <p class="text-muted small mb-3 flex-grow-1" style="line-height:1.55;">
                                    <?= e($ev['short_description']) ?>
                                </p>

                                <div class="gg-event-meta-list mb-3">
                                    <div class="gg-event-meta-item">
                                        <i data-lucide="calendar" style="width:15px;height:15px;"></i>
                                        <span><?= format_datetime($ev['start_time']) ?></span>
                                    </div>
                                    <div class="gg-event-meta-item">
                                        <i data-lucide="map-pin" style="width:15px;height:15px;"></i>
                                        <span><?= e($ev['location']) ?></span>
                                    </div>
                                </div>

                                <div class="pt-3 border-top mt-auto">
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span class="text-dark fw-medium">Đã đăng ký: <strong><?= $regV ?>/<?= $maxV ?></strong></span>
                                        <span class="<?= $remainingSlots > 0 ? 'text-success' : 'text-danger' ?> fw-medium">
                                            <?= $remainingSlots > 0 ? 'Còn ' . $remainingSlots . ' chỗ' : 'Hết chỗ trống' ?>
                                        </span>
                                    </div>
                                    <div class="gg-progress mb-3">
                                        <div class="gg-progress-bar" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-muted">Định mức: <strong class="text-dark"><?= number_format((float)$ev['max_hours'], 1) ?> giờ</strong></span>
                                        <a href="/events/<?= (int)$ev['id'] ?>" class="btn btn-gg-outline btn-sm">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- 4. DANH MỤC HOẠT ĐỘNG (BỐ CỤC BẤT ĐỐI XỨNG + BẢNG ĐIỀU HƯỚNG CHI TIẾT) -->
<section class="gg-section gg-section-soft border-top border-bottom">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-start">
            <div class="col-lg-5">
                <div class="gg-section-kicker">Lĩnh vực hoạt động</div>
                <h2 class="mb-3">Chọn hoạt động phù hợp với thời gian và khả năng của bạn</h2>
                <p class="text-muted mb-3" style="line-height:1.7;">
                    Mỗi chương trình trên Góp Gió Volunteer đều nêu rõ yêu cầu công việc, khung giờ làm việc tại hiện trường và người phụ trách điều phối để bạn chủ động sắp xếp lịch học tập, làm việc.
                </p>
                <div class="p-3 bg-white border" style="border-radius:6px;">
                    <div class="fw-semibold small text-dark mb-1">Lời khuyên cho tình nguyện viên mới</div>
                    <p class="small text-muted mb-0" style="line-height:1.6;">
                        Nếu bạn lần đầu tham gia, hãy bắt đầu với các buổi hiến máu nhân đạo, phân loại sách vở hoặc hỗ trợ bếp ăn cuối tuần kéo dài từ 3 đến 4 giờ.
                    </p>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="gg-category-list">
                    <?php foreach ($categories as $cat): ?>
                        <a href="/events?category_id=<?= (int)$cat['id'] ?>" class="gg-category-row">
                            <div class="d-flex align-items-start gap-3">
                                <span class="gg-category-icon mt-1">
                                    <i data-lucide="<?= e(lucide_name($cat['icon'])) ?>" style="width:18px;height:18px;"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size:0.96rem;"><?= e($cat['name']) ?></div>
                                    <div class="small text-muted mt-1"><?= e($cat['description'] ?: 'Các chương trình hoạt động thực tế tại địa phương') ?></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-3 flex-shrink-0">
                                <span class="gg-badge badge-neutral"><?= (int)$cat['event_count'] ?> hoạt động</span>
                                <i data-lucide="chevron-right" style="width:16px;height:16px;color:#64748B;"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. CÂU CHUYỆN TÌNH NGUYỆN THỰC ĐỊA (EDITORIAL STORYTELLING) -->
<section class="gg-section" style="background-color:#FFFFFF;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-7">
                <div class="gg-section-kicker">Ghi chép từ thực địa</div>
                <h2 class="mb-1">Những buổi sáng cuối tuần không ngủ của tình nguyện viên</h2>
                <p class="gg-section-desc mb-0">Đằng sau mỗi giờ tình nguyện được ghi nhận trên hệ thống là những công việc cụ thể, thiết thực tại từng con hẻm, điểm trường và cánh rừng ven biển.</p>
            </div>
        </div>

        <div class="row g-4 align-items-stretch">
            <div class="col-lg-7">
                <div class="gg-story-feature h-100 d-flex flex-column">
                    <div class="row g-0 flex-grow-1">
                        <div class="col-md-6 gg-story-img-col">
                            <img src="/assets/images/stories/community-story-1.jpg?v=2"
                                 alt="Tình nguyện viên thu gom rác nhựa và trồng rừng ngập mặn tại Cần Giờ"
                                 class="gg-story-img"
                                 loading="lazy">
                        </div>
                        <div class="col-md-6 p-4 bg-white d-flex flex-column justify-content-between" style="position:relative;z-index:1;">
                            <div>
                                <span class="gg-badge badge-success mb-2">Môi trường &amp; Sinh thái</span>
                                <h3 class="h4 mb-2">600 cây đước non bén rễ tại bãi bồi Cần Giờ sau 5 giờ lội bùn</h3>
                                <p class="small text-muted mb-3" style="line-height:1.65;">
                                    Xuất phát từ 5h30 sáng tại Nhà Văn hóa Sinh viên, 85 bạn trẻ thuộc CLB Sài Gòn Xanh đã phối hợp cùng Ban Quản lý Rừng phòng hộ Cần Giờ hoàn thành chỉ tiêu trồng dặm 600 bầu cây đước và thu gom 420kg rác nhựa trôi dạt.
                                </p>
                            </div>
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 small">
                                <span class="text-muted">Xã Long Hòa, Huyện Cần Giờ</span>
                                <a href="/events?category_id=2" class="fw-semibold" style="color:#15803D;">Xem hoạt động môi trường &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="gg-story-feature h-100 d-flex flex-column p-4" style="background-color:#F7F9F7;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="/assets/images/stories/community-story-2.jpg?v=2"
                             alt="Phân loại nhu yếu phẩm và suất ăn thiện nguyện"
                             class="rounded object-fit-cover border flex-shrink-0"
                             width="88" height="88"
                             loading="lazy">
                        <div>
                            <span class="gg-badge badge-neutral mb-1">An sinh xã hội</span>
                            <h3 class="h4 mb-0">300 suất cháo ấm trao tay người nhà bệnh nhân lúc 6 giờ sáng</h3>
                        </div>
                    </div>
                    <p class="small text-muted mb-3 flex-grow-1" style="line-height:1.65;">
                        “Nhiều cô chú từ tỉnh xa đưa con lên khám bệnh phải thức trắng đêm ở hành lang. Khi nhận hộp cháo nóng và hộp sữa từ tay tụi mình, ánh mắt cô chú nhẹ đi rất nhiều. Đó là lý do nhóm duy trì bếp nấu đều đặn suốt 14 tháng qua.”
                    </p>
                    <div class="pt-3 border-top d-flex align-items-center justify-content-between small">
                        <div class="d-flex align-items-center gap-2">
                            <img src="/assets/images/avatars/user-vol2.jpg" alt="Trần Thị Bảo Ngọc" class="rounded-circle object-fit-cover" width="28" height="28" loading="lazy">
                            <span class="fw-medium text-dark">Trần Thị Bảo Ngọc — Đội trưởng hậu cần</span>
                        </div>
                        <a href="/events" class="fw-semibold" style="color:#15803D;">Tham gia cùng đội &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. QUY TRÌNH THAM GIA (BỐ CỤC DÒNG THỨ TỰ 01-05 RÕ RÀNG) -->
<section class="gg-section gg-section-soft border-top border-bottom">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-4">
                <div class="gg-section-kicker">Hướng dẫn tham gia</div>
                <h2 class="mb-3">Quy trình đăng ký và xác nhận giờ tình nguyện</h2>
                <p class="text-muted small mb-4" style="line-height:1.7;">
                    Hệ thống Góp Gió Volunteer loại bỏ việc điểm danh giấy thủ công dễ thất lạc. Mọi bước từ khi đăng ký đến khi nhận giấy chứng nhận đều có mã định danh đối chiếu rõ ràng.
                </p>
                <a href="/certificate/verify" class="btn btn-gg-outline btn-sm">
                    Kiểm tra mẫu chứng nhận điện tử
                </a>
            </div>
            <div class="col-lg-8">
                <div class="bg-white border px-4 py-2" style="border-radius:8px;">
                    <div class="gg-process-row">
                        <div class="gg-process-num">01</div>
                        <div>
                            <h4 class="mb-1">Tra cứu và chọn hoạt động phù hợp</h4>
                            <p class="small text-muted mb-0">Xem kỹ thời gian tập kết, địa điểm, nhiệm vụ cụ thể và số lượng tình nguyện viên còn nhận trước khi bấm đăng ký.</p>
                        </div>
                    </div>
                    <div class="gg-process-row">
                        <div class="gg-process-num">02</div>
                        <div>
                            <h4 class="mb-1">Nhận mã QR định danh cá nhân</h4>
                            <p class="small text-muted mb-0">Sau khi đăng ký, hệ thống cấp một mã QR riêng biệt cho từng tình nguyện viên theo từng sự kiện trong Bảng điều khiển.</p>
                        </div>
                    </div>
                    <div class="gg-process-row">
                        <div class="gg-process-num">03</div>
                        <div>
                            <h4 class="mb-1">Điểm danh Check-in &amp; Check-out tại hiện trường</h4>
                            <p class="small text-muted mb-0">Ban tổ chức quét mã QR của bạn khi bắt đầu nhận nhiệm vụ và khi kết thúc buổi hoạt động để ghi nhận thời lượng thực tế.</p>
                        </div>
                    </div>
                    <div class="gg-process-row">
                        <div class="gg-process-num">04</div>
                        <div>
                            <h4 class="mb-1">Đối soát và phê duyệt số giờ đóng góp</h4>
                            <p class="small text-muted mb-0">Đơn vị tổ chức kiểm tra nhật ký điểm danh và xác nhận số giờ tình nguyện vào hồ sơ tích lũy của bạn.</p>
                        </div>
                    </div>
                    <div class="gg-process-row">
                        <div class="gg-process-num">05</div>
                        <div>
                            <h4 class="mb-1">Cấp chứng nhận PDF có mã tra cứu công khai</h4>
                            <p class="small text-muted mb-0">Tải về chứng nhận PDF phục vụ xét rèn luyện sinh viên hoặc hồ sơ cá nhân; nhà trường có thể quét mã xác thực trực tuyến.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. TỔ CHỨC ĐANG HOẠT ĐỘNG & GHI NHẬN ĐÓNG GÓP -->
<section class="gg-section" style="background-color:#FFFFFF;">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <!-- Đơn vị tổ chức -->
            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-end mb-3">
                    <div>
                        <div class="gg-section-kicker">Đối tác điều phối</div>
                        <h3 class="mb-0">Các tổ chức &amp; câu lạc bộ đang hoạt động</h3>
                    </div>
                    <a href="/organizations" class="small fw-semibold" style="color:#15803D;">Xem tất cả đơn vị &rarr;</a>
                </div>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($topOrganizations as $org): ?>
                        <div class="gg-card p-3 d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                            <img src="<?= e($org['logo']) ?>"
                                 alt="<?= e($org['name']) ?>"
                                 class="rounded object-fit-cover border flex-shrink-0"
                                 width="64" height="64"
                                 loading="lazy">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <a href="/organizations/<?= (int)$org['id'] ?>" class="fw-semibold text-dark" style="font-size:0.98rem;">
                                        <?= e($org['name']) ?>
                                    </a>
                                    <span class="gg-badge badge-success">Đã xác minh</span>
                                </div>
                                <div class="small text-muted mb-2"><?= e($org['address']) ?></div>
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <span>Đã tổ chức: <strong class="text-dark"><?= (int)$org['total_events'] ?> chương trình</strong></span>
                                    <span>Tổng giờ xác nhận: <strong class="text-dark"><?= number_format((float)$org['total_hours'], 1) ?> giờ</strong></span>
                                    <span>Đánh giá: <strong class="text-dark"><?= number_format((float)$org['avg_rating'], 1) ?>/5.0</strong></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="/organizations/<?= (int)$org['id'] ?>" class="btn btn-gg-ghost btn-sm">Hồ sơ đơn vị</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Ghi nhận tình nguyện viên tích cực -->
            <div class="col-lg-5">
                <div class="d-flex justify-content-between align-items-end mb-3">
                    <div>
                        <div class="gg-section-kicker">Ghi nhận đóng góp</div>
                        <h3 class="mb-0">Tình nguyện viên tích cực</h3>
                    </div>
                    <a href="/leaderboard" class="small fw-semibold" style="color:#15803D;">Bảng xếp hạng &rarr;</a>
                </div>
                <div class="gg-card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table gg-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tình nguyện viên</th>
                                    <th class="text-end">Giờ tích lũy</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topVolunteers as $idx => $vol): ?>
                                    <tr>
                                        <td class="fw-semibold text-muted" style="width:40px;"><?= $idx + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= e($vol['avatar']) ?>"
                                                     alt="<?= e($vol['full_name']) ?>"
                                                     class="rounded-circle object-fit-cover border"
                                                     width="34" height="34"
                                                     loading="lazy">
                                                <div>
                                                    <div class="fw-medium small text-dark"><?= e($vol['full_name']) ?></div>
                                                    <div class="text-muted" style="font-size:0.74rem;"><?= (int)$vol['events_attended'] ?> hoạt động đã hoàn thành</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-semibold text-dark small"><?= number_format((float)$vol['confirmed_hours'], 1) ?> giờ</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 8. ĐÁNH GIÁ CỦA NGƯỜI THAM GIA -->
<section class="gg-section gg-section-soft border-top">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="gg-section-kicker">Phản hồi sau chương trình</div>
                <h2 class="mb-1">Chia sẻ từ người tham gia thực tế</h2>
                <p class="gg-section-desc mb-0">Đánh giá được gửi bởi các tình nguyện viên đã hoàn thành điểm danh tại sự kiện.</p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($reviews as $rv): ?>
                <div class="col-md-4">
                    <div class="gg-quote-block h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-semibold" style="color:#15803D;">Đánh giá <?= (int)$rv['rating'] ?>/5</span>
                                <span class="text-muted" style="font-size:0.76rem;"><?= format_date($rv['created_at'] ?? null) ?></span>
                            </div>
                            <p class="small text-dark mb-3" style="line-height:1.65;">
                                “<?= e($rv['comment']) ?>”
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 pt-3 border-top">
                            <img src="<?= e($rv['user_avatar']) ?>"
                                 alt="<?= e($rv['user_name']) ?>"
                                 class="rounded-circle object-fit-cover border"
                                 width="36" height="36"
                                 loading="lazy">
                            <div>
                                <div class="fw-semibold small text-dark"><?= e($rv['user_name']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;">Tham gia: <?= e($rv['event_title']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 9. KÊU GỌI ĐĂNG KÝ (CTA) -->
<section class="gg-section" style="background-color:#FFFFFF;">
    <div class="container">
        <div class="gg-cta-banner">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <p class="gg-section-kicker mb-2" style="color:#15803D;">Đồng hành cùng Góp Gió Volunteer</p>
                    <h2 class="mb-2">Sẵn sàng bắt đầu hành trình tình nguyện tiếp theo của bạn?</h2>
                    <p class="text-muted mb-0" style="max-width:60ch;">
                        Đăng ký tài khoản miễn phí để theo dõi lịch hoạt động hàng tuần, lưu trữ mã QR điểm danh và quản lý hồ sơ giờ tình nguyện cá nhân.
                    </p>
                </div>
                <div class="col-lg-4 d-flex flex-wrap gap-2 justify-content-lg-end">
                    <?php if (!is_logged_in()): ?>
                        <a href="/register" class="btn btn-gg-primary px-4 py-2">Tạo tài khoản tình nguyện viên</a>
                        <a href="/events" class="btn btn-gg-outline px-3 py-2">Xem lịch hoạt động</a>
                    <?php else: ?>
                        <a href="/events" class="btn btn-gg-primary px-4 py-2">Đăng ký hoạt động mới</a>
                        <a href="/blood-donation" class="btn btn-gg-outline px-3 py-2">Xem lịch hiến máu</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
