<div class="gg-page-header">
    <div class="container">
        <div class="gg-section-kicker">Ghi nhận đóng góp thực tế</div>
        <h1 class="h2 mb-1">Bảng xếp hạng tình nguyện viên &amp; đơn vị tổ chức</h1>
        <p class="gg-section-desc mb-0">
            Số liệu tổng hợp tự động dựa trên <strong>số giờ tình nguyện đã được Ban tổ chức đối soát và phê duyệt</strong> sau mỗi chương trình. Không tính các lượt đăng ký vắng mặt hoặc đang chờ duyệt.
        </p>
    </div>
</div>

<div class="container py-4">
    <!-- Thanh chọn kỳ thống kê -->
    <ul class="nav nav-pills gg-tab-nav mb-4" id="leaderboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-all" type="button">
                Tích lũy toàn thời gian
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-month" type="button">
                Trong tháng hiện tại
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-year" type="button">
                Trong năm <?= date('Y') ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-orgs" type="button">
                Đơn vị tổ chức tích cực
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <?php
        $volunteerTables = [
            'tab-all' => ['title' => 'Ghi nhận giờ tình nguyện tích lũy toàn thời gian', 'rows' => $topAllTime, 'active' => true],
            'tab-month' => ['title' => 'Tình nguyện viên tích cực trong tháng', 'rows' => $topMonth, 'active' => false],
            'tab-year' => ['title' => 'Tình nguyện viên tích cực năm ' . date('Y'), 'rows' => $topYear, 'active' => false],
        ];
        ?>
        <?php foreach ($volunteerTables as $tabId => $tbl): ?>
            <div class="tab-pane fade <?= $tbl['active'] ? 'show active' : '' ?>" id="<?= $tabId ?>">
                <div class="gg-card overflow-hidden">
                    <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                        <h2 class="h4 mb-0"><?= e($tbl['title']) ?></h2>
                    </div>
                    <?php if (empty($tbl['rows'])): ?>
                        <div class="gg-empty-state border-0">Chưa có bản ghi giờ tình nguyện được xác nhận trong kỳ thống kê này.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table gg-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:70px;">Thứ tự</th>
                                        <th>Tình nguyện viên</th>
                                        <th>Khu vực</th>
                                        <th>Mức đóng góp</th>
                                        <th class="text-center">Hoạt động hoàn thành</th>
                                        <th class="text-center">Chứng nhận đã cấp</th>
                                        <th class="text-end">Giờ đã xác nhận</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tbl['rows'] as $i => $row): ?>
                                        <?php $rankBadge = volunteer_rank_badge((float)$row['confirmed_hours']); ?>
                                        <tr>
                                            <td>
                                                <span class="fw-semibold px-2 py-1 rounded small"
                                                      style="background:<?= $i < 3 ? '#DCFCE7' : '#F1F5F9' ?>;color:<?= $i < 3 ? '#15803D' : '#475569' ?>;">
                                                    #<?= $i + 1 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= e($row['avatar']) ?>"
                                                         class="rounded-circle object-fit-cover border"
                                                         width="38" height="38"
                                                         alt="<?= e($row['full_name']) ?>"
                                                         loading="lazy">
                                                    <div class="fw-semibold text-dark"><?= e($row['full_name']) ?></div>
                                                </div>
                                            </td>
                                            <td class="small text-muted"><?= e($row['address'] ?: 'TP. Hồ Chí Minh') ?></td>
                                            <td>
                                                <span class="gg-badge badge-neutral"><?= e($rankBadge['name']) ?></span>
                                            </td>
                                            <td class="text-center fw-medium"><?= (int)$row['events_attended'] ?></td>
                                            <td class="text-center"><?= (int)$row['certificates_count'] ?></td>
                                            <td class="text-end fw-bold" style="color:#15803D;"><?= number_format((float)$row['confirmed_hours'], 1) ?> giờ</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Bảng đơn vị tổ chức -->
        <div class="tab-pane fade" id="tab-orgs">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Đơn vị tổ chức &amp; câu lạc bộ hoạt động tích cực</h2>
                </div>
                <div class="table-responsive">
                    <table class="table gg-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px;">Thứ tự</th>
                                <th>Đơn vị tổ chức</th>
                                <th>Xác minh</th>
                                <th class="text-center">Số chương trình</th>
                                <th class="text-center">Lượt TNV tham gia</th>
                                <th class="text-center">Đánh giá trung bình</th>
                                <th class="text-end">Tổng giờ điều phối</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topOrganizations as $idx => $org): ?>
                                <tr>
                                    <td><span class="fw-semibold text-muted">#<?= $idx + 1 ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= e($org['logo']) ?>"
                                                 class="rounded object-fit-cover border"
                                                 width="40" height="40"
                                                 alt="<?= e($org['name']) ?>"
                                                 loading="lazy">
                                            <div>
                                                <a href="/organizations/<?= (int)$org['id'] ?>" class="fw-semibold text-dark text-decoration-none"><?= e($org['name']) ?></a>
                                                <div class="small text-muted"><?= e($org['address']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="gg-badge badge-success">Đã xác minh</span></td>
                                    <td class="text-center fw-medium"><?= (int)$org['total_events'] ?></td>
                                    <td class="text-center"><?= (int)$org['total_participants'] ?></td>
                                    <td class="text-center fw-medium"><?= number_format((float)$org['avg_rating'], 1) ?>/5.0</td>
                                    <td class="text-end fw-bold" style="color:#15803D;"><?= number_format((float)$org['total_hours'], 1) ?> giờ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
