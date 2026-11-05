<div class="gg-page-header">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-2 text-md-center">
                <img src="<?= e($org['logo']) ?>"
                     alt="<?= e($org['name']) ?>"
                     class="rounded object-fit-cover border"
                     width="104" height="104">
            </div>
            <div class="col-md-7">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="gg-badge badge-success"><?= $org['verification_status'] === 'Verified' ? 'Đơn vị đã xác minh' : e($org['verification_status']) ?></span>
                    <span class="gg-badge badge-neutral">Đánh giá trung bình <?= number_format((float)$org['avg_rating'], 1) ?>/5.0</span>
                </div>
                <h1 class="h2 mb-2"><?= e($org['name']) ?></h1>
                <p class="text-muted mb-3" style="line-height:1.65;"><?= e($org['description']) ?></p>
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    <span><strong>Địa chỉ:</strong> <?= e($org['address']) ?></span>
                    <span><strong>Điện thoại:</strong> <?= e($org['contact_phone']) ?></span>
                    <span><strong>Email:</strong> <?= e($org['contact_email']) ?></span>
                    <?php if (!empty($org['website'])): ?>
                        <a href="<?= e($org['website']) ?>" target="_blank" rel="noopener" class="fw-medium" style="color:#15803D;"><?= e($org['website']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white border text-center" style="border-radius:6px;">
                    <div class="gg-stats-number"><?= count($events) ?></div>
                    <div class="small text-muted fw-medium">Chương trình đã triển khai</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <h2 class="h3 mb-3">Chương trình hoạt động của đơn vị</h2>
            <?php if (empty($events)): ?>
                <div class="gg-empty-state">Đơn vị hiện chưa có chương trình nào được công bố.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($events as $ev): ?>
                        <div class="col-md-6">
                            <article class="gg-card gg-card-hover p-3 h-100 d-flex flex-column">
                                <img src="<?= e($ev['cover_image']) ?>"
                                     class="rounded mb-3 object-fit-cover border"
                                     style="height:160px;width:100%;"
                                     alt="<?= e($ev['title']) ?>"
                                     loading="lazy">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="gg-badge badge-neutral"><?= e($ev['category_name']) ?></span>
                                    <?= status_badge_Event($ev['status']) ?>
                                </div>
                                <h3 class="h4 mb-2">
                                    <a href="/events/<?= (int)$ev['id'] ?>" class="text-dark text-decoration-none"><?= e($ev['title']) ?></a>
                                </h3>
                                <div class="small text-muted mb-3">Thời gian: <?= format_datetime($ev['start_time']) ?></div>
                                <a href="/events/<?= (int)$ev['id'] ?>" class="btn btn-gg-outline btn-sm mt-auto">Xem thông tin chương trình</a>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
            <div class="gg-card p-4">
                <h3 class="h4 mb-3">Ban điều phối &amp; đại diện phụ trách</h3>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($members as $m): ?>
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= e($m['avatar']) ?>"
                                 class="rounded-circle object-fit-cover border"
                                 width="40" height="40"
                                 alt="<?= e($m['full_name']) ?>"
                                 loading="lazy">
                            <div>
                                <div class="fw-semibold small text-dark"><?= e($m['full_name']) ?></div>
                                <span class="text-muted" style="font-size:0.76rem;">Vai trò: <?= e($m['member_role']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
