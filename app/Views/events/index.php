<div class="gg-page-header">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
            <div>
                <div class="gg-section-kicker">Danh bạ chương trình</div>
                <h1 class="h2 mb-1">Hoạt động tình nguyện &amp; hiến máu</h1>
                <p class="gg-section-desc mb-0">Tra cứu theo lĩnh vực, khu vực địa lý, ngày diễn ra hoặc trạng thái tiếp nhận đăng ký.</p>
            </div>
            <div class="small text-muted">
                Hiển thị <strong class="text-dark"><?= (int)$pagination['total'] ?></strong> chương trình phù hợp
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Thanh tìm kiếm và lọc -->
    <div class="gg-filter-bar mb-4">
        <form action="/events" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label mb-1">Từ khóa</label>
                <input type="text" name="keyword" class="form-control form-control-sm"
                       value="<?= e($filters['keyword']) ?>" placeholder="Tên chương trình, địa điểm...">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label mb-1">Lĩnh vực</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Tất cả lĩnh vực</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (string)$filters['category_id'] === (string)$cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label mb-1">Khu vực / Quận huyện</label>
                <input type="text" name="province" class="form-control form-control-sm"
                       value="<?= e($filters['province']) ?>" placeholder="VD: Thủ Đức, Cần Giờ...">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label mb-1">Từ ngày</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($filters['date_from']) ?>">
            </div>
            <div class="col-6 col-md-4 col-lg-1">
                <label class="form-label mb-1">Trạng thái</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    <option value="Published" <?= $filters['status'] === 'Published' ? 'selected' : '' ?>>Đang mở</option>
                    <option value="Completed" <?= $filters['status'] === 'Completed' ? 'selected' : '' ?>>Đã kết thúc</option>
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-1">
                <label class="form-label mb-1">Sắp xếp</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="upcoming" <?= $filters['sort'] === 'upcoming' ? 'selected' : '' ?>>Sắp diễn ra</option>
                    <option value="popular" <?= $filters['sort'] === 'popular' ? 'selected' : '' ?>>Nhiều đăng ký</option>
                    <option value="hours" <?= $filters['sort'] === 'hours' ? 'selected' : '' ?>>Số giờ cao</option>
                </select>
            </div>
            <div class="col-6 col-md-12 col-lg-1 d-flex gap-1">
                <button type="submit" class="btn btn-gg-primary btn-sm w-100">Lọc</button>
                <a href="/events" class="btn btn-gg-ghost btn-sm" title="Đặt lại bộ lọc">Đặt lại</a>
            </div>
        </form>
    </div>

    <!-- Danh sách hoạt động -->
    <?php if (empty($events)): ?>
        <div class="gg-empty-state">
            <h3 class="h4 mb-2">Không tìm thấy hoạt động nào khớp với điều kiện lọc</h3>
            <p class="text-muted small mb-3">Bạn có thể thử xóa bớt từ khóa hoặc chọn xem tất cả các lĩnh vực hoạt động.</p>
            <a href="/events" class="btn btn-gg-outline btn-sm">Xem toàn bộ hoạt động</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($events as $ev): ?>
                <?php
                $maxV = max(1, (int)$ev['max_volunteers']);
                $regV = (int)$ev['registered_count'];
                $remaining = max(0, $maxV - $regV);
                $pct = min(100, round(($regV / $maxV) * 100));
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
                                <?= status_badge_Event($ev['status']) ?>
                            </div>
                        </div>
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <div class="gg-event-org mb-1">
                                Đơn vị: <span class="text-dark"><?= e($ev['organization_name']) ?></span>
                            </div>
                            <h3 class="gg-event-title">
                                <a href="/events/<?= (int)$ev['id'] ?>"><?= e($ev['title']) ?></a>
                            </h3>
                            <p class="text-muted small mb-3 flex-grow-1" style="line-height:1.55;"><?= e($ev['short_description']) ?></p>

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

                            <div class="mt-auto pt-3 border-top">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Đã đăng ký: <strong><?= $regV ?>/<?= $maxV ?></strong></span>
                                    <span class="<?= $remaining > 0 ? 'text-success' : 'text-danger' ?> fw-medium">
                                        <?= $remaining > 0 ? 'Còn ' . $remaining . ' chỗ' : 'Hết chỗ trống' ?>
                                    </span>
                                </div>
                                <div class="gg-progress mb-3">
                                    <div class="gg-progress-bar" style="width: <?= $pct ?>%;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">Định mức: <strong class="text-dark"><?= number_format((float)$ev['max_hours'], 1) ?> giờ</strong></span>
                                    <a href="/events/<?= (int)$ev['id'] ?>" class="btn btn-gg-primary btn-sm">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Phân trang -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="mt-4 d-flex justify-content-center" aria-label="Phân trang danh sách hoạt động">
                <ul class="pagination pagination-sm">
                    <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                        <?php
                        $queryParams = array_merge($filters, ['page' => $p]);
                        $url = '/events?' . http_build_query($queryParams);
                        ?>
                        <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= e($url) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
