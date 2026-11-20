<div class="gg-page-header">
    <div class="container">
        <div class="gg-section-kicker">Tra cứu hồ sơ chứng nhận</div>
        <h1 class="h2 mb-1">Xác minh chứng nhận giờ tình nguyện điện tử</h1>
        <p class="gg-section-desc mb-0">Dành cho phòng công tác sinh viên, đoàn trường và các đơn vị tuyển dụng đối chiếu tính hợp lệ của chứng nhận do Góp Gió Volunteer cấp.</p>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Khung nhập mã tra cứu -->
            <div class="gg-card p-4 mb-4">
                <label class="form-label mb-2">Nhập mã xác minh hoặc số hiệu chứng nhận</label>
                <p class="small text-muted mb-3">
                    Bạn có thể nhập Mã xác minh (ví dụ: <code>GGVERIFY20260001MINHANH4</code>) hoặc Số hiệu chứng nhận (ví dụ: <code>GG-CERT-2026-0001</code>) in ở góc dưới bên phải bản PDF.
                </p>
                <form action="/certificate/verify" method="GET" class="d-flex flex-column flex-sm-row gap-2">
                    <input type="text" name="code" class="form-control"
                           value="<?= e($searchedCode ?? '') ?>"
                           placeholder="Nhập mã chứng nhận cần kiểm tra..." required>
                    <button type="submit" class="btn btn-gg-primary px-4 text-nowrap">
                        Tra cứu hồ sơ
                    </button>
                </form>
            </div>

            <?php if (!empty($searchedCode) && !$certificate): ?>
                <div class="gg-card p-4 text-center" style="border-color:#FECACA;">
                    <h3 class="h4 text-danger mb-2">Không tìm thấy hồ sơ chứng nhận khớp với mã “<?= e($searchedCode) ?>”</h3>
                    <p class="small text-muted mb-0">Vui lòng kiểm tra lại chính xác từng ký tự trên bản PDF hoặc liên hệ Ban điều phối Góp Gió Volunteer để được hỗ trợ đối chiếu.</p>
                </div>
            <?php elseif ($certificate): ?>
                <div class="gg-card p-4 p-lg-5" style="border-top: 4px solid <?= $certificate['status'] === 'Valid' ? '#15803D' : '#DC2626' ?>;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-2">
                        <div>
                            <div class="gg-section-kicker mb-1">Bản ghi đối chiếu chứng nhận</div>
                            <h2 class="h3 mb-0"><?= e($certificate['certificate_code']) ?></h2>
                        </div>
                        <?php if ($certificate['status'] === 'Valid'): ?>
                            <span class="gg-badge badge-success px-3 py-2" style="font-size:0.85rem;">
                                Chứng nhận hợp lệ
                            </span>
                        <?php else: ?>
                            <span class="gg-badge badge-danger px-3 py-2" style="font-size:0.85rem;">
                                Đã thu hồi hiệu lực
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($certificate['status'] === 'Revoked' && !empty($certificate['revoked_reason'])): ?>
                        <div class="alert alert-danger small">
                            <strong>Lý do thu hồi:</strong> <?= e($certificate['revoked_reason']) ?> (Thời điểm thu hồi: <?= format_datetime($certificate['revoked_at']) ?>)
                        </div>
                    <?php endif; ?>

                    <div class="row g-4 align-items-center">
                        <div class="col-md-8">
                            <dl class="row mb-0 small">
                                <dt class="col-sm-4 text-muted py-2 border-bottom">Họ và tên người nhận:</dt>
                                <dd class="col-sm-8 fw-bold text-dark py-2 border-bottom mb-0" style="font-size:0.98rem;"><?= e($certificate['recipient_name']) ?></dd>

                                <dt class="col-sm-4 text-muted py-2 border-bottom">Chương trình tham gia:</dt>
                                <dd class="col-sm-8 fw-semibold py-2 border-bottom mb-0">
                                    <a href="/events/<?= (int)$certificate['event_id'] ?>" style="color:#15803D;">
                                        <?= e($certificate['event_title']) ?>
                                    </a>
                                </dd>

                                <dt class="col-sm-4 text-muted py-2 border-bottom">Đơn vị xác nhận:</dt>
                                <dd class="col-sm-8 fw-medium text-dark py-2 border-bottom mb-0"><?= e($certificate['organization_name']) ?></dd>

                                <dt class="col-sm-4 text-muted py-2 border-bottom">Thời lượng được công nhận:</dt>
                                <dd class="col-sm-8 py-2 border-bottom mb-0">
                                    <span class="gg-badge badge-success"><?= number_format((float)$certificate['verified_hours'], 1) ?> giờ tình nguyện</span>
                                </dd>

                                <dt class="col-sm-4 text-muted py-2 border-bottom">Ngày cấp chứng nhận:</dt>
                                <dd class="col-sm-8 py-2 border-bottom mb-0"><?= format_date($certificate['issued_date']) ?></dd>

                                <dt class="col-sm-4 text-muted py-2">Mã định danh xác thực:</dt>
                                <dd class="col-sm-8 py-2 mb-0"><code><?= e($certificate['verification_code']) ?></code></dd>
                            </dl>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="p-3 border rounded bg-light d-inline-block mb-3">
                                <?= $qrSvg ?>
                                <div class="text-muted mt-1" style="font-size:0.74rem;">Mã QR kiểm tra trực tuyến</div>
                            </div>
                            <?php if ($certificate['status'] === 'Valid'): ?>
                                <div>
                                    <a href="/certificate/download/<?= urlencode($certificate['verification_code']) ?>" class="btn btn-gg-primary btn-sm w-100">
                                        Tải bản chứng nhận PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
