<div class="gg-page-header">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <div class="gg-section-kicker">Mạng lưới đối tác điều phối</div>
                <h1 class="h2 mb-1">Các đơn vị tổ chức &amp; câu lạc bộ tình nguyện</h1>
                <p class="gg-section-desc mb-0">Danh sách các câu lạc bộ sinh viên, Hội Chữ thập đỏ và các nhóm thiện nguyện đã xác minh thông tin hoạt động trên Góp Gió Volunteer.</p>
            </div>
            <form action="/organizations" method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($search) ?>" placeholder="Tìm tên đơn vị hoặc câu lạc bộ..." style="min-width:220px;">
                <button type="submit" class="btn btn-gg-primary btn-sm px-3">Tìm kiếm</button>
            </form>
        </div>
    </div>
</div>

<div class="container py-4">
    <?php if (empty($organizations)): ?>
        <div class="gg-empty-state">
            <h3 class="h4 mb-1">Không tìm thấy đơn vị tổ chức phù hợp</h3>
            <p class="small text-muted mb-3">Vui lòng thử lại với từ khóa khác hoặc xem toàn bộ danh bạ đơn vị.</p>
            <a href="/organizations" class="btn btn-gg-outline btn-sm">Xem tất cả đơn vị</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($organizations as $org): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="gg-card gg-card-hover p-4 h-100 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="<?= e($org['logo']) ?>"
                                 alt="<?= e($org['name']) ?>"
                                 class="rounded object-fit-cover border flex-shrink-0"
                                 width="64" height="64"
                                 loading="lazy">
                            <div>
                                <h3 class="h4 mb-1">
                                    <a href="/organizations/<?= (int)$org['id'] ?>" class="text-dark text-decoration-none"><?= e($org['name']) ?></a>
                                </h3>
                                <span class="gg-badge <?= $org['verification_status'] === 'Verified' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $org['verification_status'] === 'Verified' ? 'Đã xác minh' : 'Đang thẩm định' ?>
                                </span>
                            </div>
                        </div>
                        <p class="small text-muted flex-grow-1 mb-3" style="line-height:1.6;"><?= e($org['description']) ?></p>
                        <div class="small text-muted mb-3 d-flex flex-column gap-1">
                            <div><strong>Trụ sở / Liên lạc:</strong> <?= e($org['address']) ?></div>
                            <div><strong>Email:</strong> <?= e($org['contact_email']) ?></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top small">
                            <span>Đã tổ chức: <strong class="text-dark"><?= (int)$org['total_events'] ?> chương trình</strong></span>
                            <span class="fw-semibold" style="color:#15803D;"><?= number_format((float)$org['total_hours'], 1) ?> giờ xác nhận</span>
                            <a href="/organizations/<?= (int)$org['id'] ?>" class="btn btn-gg-ghost btn-sm py-1 px-2">Hồ sơ &rarr;</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
