<div class="gg-page-header py-3">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="gg-section-kicker mb-1">TRẠM ĐIỂM DANH QR &amp; ĐỐI SOÁT GIỜ TÌNH NGUYỆN</div>
                <h1 class="h3 mb-1"><?= e($event['title']) ?></h1>
                <div class="small text-muted">
                    Thời gian: <?= format_datetime($event['start_time']) ?> &rarr; <?= format_datetime($event['end_time']) ?>
                    &bull; Định mức tối đa: <strong><?= number_format((float)$event['max_hours'], 1) ?> giờ</strong>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="/organizer/events/<?= (int)$event['id'] ?>/export-csv" class="btn btn-gg-outline btn-sm">
                    Xuất danh sách CSV
                </a>
                <a href="/organizer/dashboard?tab=events&event_id=<?= (int)$event['id'] ?>" class="btn btn-gg-ghost btn-sm">
                    &larr; Quay lại Bảng điều hành
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">
        <!-- Cột trái: Camera quét mã QR & ô nhập mã trực tiếp -->
        <div class="col-lg-5">
            <div class="gg-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Quét mã QR tại hiện trường</h2>
                    <span class="gg-badge badge-neutral">Camera / Máy quét</span>
                </div>

                <!-- Chọn chế độ Check-in / Check-out -->
                <div class="btn-group w-100 mb-3" role="group" aria-label="Chọn chế độ điểm danh">
                    <input type="radio" class="btn-check" name="qrScanModeRadio" id="modeCheckin" value="checkin" checked>
                    <label class="btn btn-outline-success btn-sm fw-semibold py-2" for="modeCheckin">
                        Check-in (Ghi nhận giờ vào)
                    </label>
                    <input type="radio" class="btn-check" name="qrScanModeRadio" id="modeCheckout" value="checkout">
                    <label class="btn btn-outline-primary btn-sm fw-semibold py-2" for="modeCheckout">
                        Check-out (Ghi nhận giờ ra)
                    </label>
                </div>

                <!-- Khung hiển thị camera -->
                <div id="qrReaderBox" class="border mb-3 overflow-hidden text-center p-3" style="min-height:230px;background-color:#F7F9F7;border-radius:6px;">
                    <div id="qr-reader" style="width:100%;"></div>
                    <div id="qrCameraPlaceholder" class="py-4 text-muted small">
                        <div class="mb-2 fw-semibold text-dark">Chưa bật camera quét mã</div>
                        Nhấn nút <strong>“Bật Camera Quét QR”</strong> bên dưới để sử dụng camera điện thoại hoặc webcam máy tính tại bàn điều phối.
                    </div>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button type="button" id="btnStartCamera" class="btn btn-gg-primary btn-sm flex-grow-1">
                        Bật Camera Quét QR
                    </button>
                    <button type="button" id="btnStopCamera" class="btn btn-gg-ghost btn-sm d-none">
                        Tắt Camera
                    </button>
                </div>

                <!-- Thông báo kết quả quét trực tiếp -->
                <div id="scanResultAlert" class="alert d-none small mb-3"></div>

                <!-- Nhập mã QR Token trực tiếp (hỗ trợ súng bắn mã vạch hoặc kiểm thử nhanh) -->
                <form action="/api/attendance/scan" method="POST" id="manualTokenForm" class="pt-3 border-top">
                    <?= csrf_field() ?>
                    <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                    <label class="form-label mb-1">Quét bằng máy đọc mã vạch hoặc dán mã QR Token:</label>
                    <div class="input-group input-group-sm mb-2">
                        <select name="scan_mode" id="formScanMode" class="form-select" style="max-width:125px;">
                            <option value="checkin">Check-in</option>
                            <option value="checkout">Check-out</option>
                        </select>
                        <input type="text" name="qr_token" id="qrTokenInput" class="form-control" placeholder="Nhập chuỗi ggqr_..." required>
                        <button type="submit" class="btn btn-gg-primary">Ghi nhận</button>
                    </div>
                    <div class="text-muted" style="font-size:0.75rem;">
                        Hệ thống tự động kiểm tra tính hợp lệ của mã theo đúng sự kiện và chặn quét trùng lặp.
                    </div>
                </form>
            </div>
        </div>

        <!-- Cột phải: Danh sách tình nguyện viên, điểm danh thủ công & duyệt giờ -->
        <div class="col-lg-7">
            <div class="gg-card overflow-hidden">
                <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="background-color:#F7F9F7;">
                    <h2 class="h4 mb-0">Danh sách điểm danh &amp; phê duyệt giờ (<?= count($participants) ?>)</h2>
                    <input type="text" id="filterParticipantInput" class="form-control form-control-sm" style="max-width:230px;"
                           placeholder="Lọc theo tên, SĐT, mã hồ sơ..." onkeyup="filterParticipantsTable()">
                </div>

                <div class="table-responsive">
                    <table class="table gg-table align-middle small mb-0" id="participantsTable">
                        <thead>
                            <tr>
                                <th>Tình nguyện viên</th>
                                <th>Mã QR Token</th>
                                <th>Giờ vào / ra</th>
                                <th>Giờ &amp; Chứng nhận</th>
                                <th class="text-end">Xử lý tại bàn BTC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($p['volunteer_name']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= e($p['registration_code']) ?> &bull; <?= e($p['volunteer_phone']) ?></div>
                                        <div class="mt-1"><?= status_badge_registration($p['status']) ?></div>
                                        <?php if (!empty($p['time_slot'])): ?>
                                            <span class="gg-badge badge-danger mt-1"><?= e($p['time_slot']) ?> (<?= e($p['blood_status']) ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code class="d-inline-block text-truncate" style="max-width:105px;" title="<?= e($p['qr_token']) ?>"><?= e($p['qr_token']) ?></code>
                                        <div>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size:0.74rem;color:#15803D;"
                                                    onclick="document.getElementById('qrTokenInput').value='<?= e($p['qr_token']) ?>';GopGioApp.showToast('Đã điền mã QR của <?= e($p['volunteer_name']) ?> vào ô kiểm tra.','info');">
                                                Điền mã vào ô quét
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div><strong>Vào:</strong> <?= !empty($p['check_in_time']) ? format_datetime($p['check_in_time']) . ' (' . e($p['check_in_method']) . ')' : '<span class="text-muted">Chưa</span>' ?></div>
                                        <div><strong>Ra:</strong> <?= !empty($p['check_out_time']) ? format_datetime($p['check_out_time']) . ' (' . e($p['check_out_method']) . ')' : '<span class="text-muted">Chưa</span>' ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['volunteer_hour_id'])): ?>
                                            <div class="fw-bold" style="color:#15803D;"><?= number_format((float)$p['verified_hours'], 2) ?> giờ</div>
                                            <span class="gg-badge <?= $p['hours_status'] === 'Approved' ? 'badge-success' : 'badge-warning' ?>">
                                                <?= $p['hours_status'] === 'Approved' ? 'Đã duyệt' : 'Chờ duyệt' ?>
                                            </span>
                                            <?php if (!empty($p['certificate_code'])): ?>
                                                <div class="mt-1">
                                                    <a href="/certificate/verify/<?= urlencode($p['verification_code']) ?>" class="small fw-semibold" style="color:#15803D;">
                                                        <?= e($p['certificate_code']) ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa hoàn tất</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($p['status'] === 'Confirmed' && empty($p['check_in_time'])): ?>
                                            <form action="/api/attendance/scan" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                                                <input type="hidden" name="registration_id" value="<?= (int)$p['id'] ?>">
                                                <input type="hidden" name="scan_mode" value="checkin">
                                                <input type="hidden" name="notes" value="Điểm danh thủ công bởi BTC (Ghi Audit Log)">
                                                <button type="submit" class="btn btn-gg-primary btn-sm py-1 px-2">
                                                    Check-in
                                                </button>
                                            </form>
                                        <?php elseif (!empty($p['check_in_time']) && empty($p['check_out_time'])): ?>
                                            <form action="/api/attendance/scan" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                                                <input type="hidden" name="registration_id" value="<?= (int)$p['id'] ?>">
                                                <input type="hidden" name="scan_mode" value="checkout">
                                                <button type="submit" class="btn btn-primary btn-sm py-1 px-2">
                                                    Check-out
                                                </button>
                                            </form>
                                        <?php elseif (!empty($p['volunteer_hour_id']) && $p['hours_status'] !== 'Approved'): ?>
                                            <form action="/attendance/hours/approve" method="POST" class="d-inline-flex gap-1 align-items-center">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="volunteer_hour_id" value="<?= (int)$p['volunteer_hour_id'] ?>">
                                                <input type="hidden" name="issue_certificate" value="1">
                                                <input type="number" step="0.5" min="0" max="<?= (float)$event['max_hours'] ?>"
                                                       name="hours" value="<?= (float)$p['verified_hours'] ?>"
                                                       class="form-control form-control-sm" style="width:68px;">
                                                <button type="submit" class="btn btn-gg-primary btn-sm py-1 px-2 text-nowrap">
                                                    Duyệt &amp; Cấp CN
                                                </button>
                                            </form>
                                        <?php elseif (!empty($p['volunteer_hour_id']) && $p['hours_status'] === 'Approved' && empty($p['certificate_code'])): ?>
                                            <form action="/certificates/issue" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="registration_id" value="<?= (int)$p['id'] ?>">
                                                <button type="submit" class="btn btn-gg-outline btn-sm py-1 px-2">
                                                    Cấp chứng nhận
                                                </button>
                                            </form>
                                        <?php elseif (!empty($p['certificate_code'])): ?>
                                            <a href="/certificate/download/<?= urlencode($p['verification_code']) ?>" class="btn btn-gg-ghost btn-sm py-1 px-2">
                                                Tải PDF
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!empty($p['blood_appointment_id']) && $p['blood_status'] !== 'Completed'): ?>
                                            <form action="/attendance/blood/update" method="POST" class="mt-1">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="appointment_id" value="<?= (int)$p['blood_appointment_id'] ?>">
                                                <input type="hidden" name="status" value="Completed">
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size:0.72rem;">
                                                    Xác nhận đã hiến máu
                                                </button>
                                            </form>
                                        <?php endif; ?>
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

<!-- html5-qrcode Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrScanner = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const currentEventId = <?= (int)$event['id'] ?>;

function getSelectedScanMode() {
    return document.getElementById('modeCheckout').checked ? 'checkout' : 'checkin';
}

document.querySelectorAll('input[name="qrScanModeRadio"]').forEach(r => {
    r.addEventListener('change', () => {
        document.getElementById('formScanMode').value = getSelectedScanMode();
    });
});

async function submitScannedQrToken(decodedText) {
    const alertBox = document.getElementById('scanResultAlert');
    alertBox.className = 'alert alert-info small mb-3';
    alertBox.textContent = 'Đang đối chiếu mã QR trên máy chủ...';
    alertBox.classList.remove('d-none');

    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('event_id', currentEventId);
    formData.append('scan_mode', getSelectedScanMode());
    formData.append('qr_token', decodedText.trim());

    try {
        const res = await fetch('/api/attendance/scan', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrfToken },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            alertBox.className = 'alert alert-success small mb-3 fw-semibold';
            alertBox.textContent = data.message;
            GopGioApp.showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            alertBox.className = 'alert alert-danger small mb-3 fw-semibold';
            alertBox.textContent = data.message || 'Mã QR không hợp lệ.';
            GopGioApp.showToast(data.message || 'Lỗi quét QR', 'error');
        }
    } catch (err) {
        alertBox.className = 'alert alert-danger small mb-3';
        alertBox.textContent = 'Lỗi kết nối máy chủ khi quét QR.';
    }
}

document.getElementById('btnStartCamera')?.addEventListener('click', () => {
    if (typeof Html5Qrcode === 'undefined') {
        GopGioApp.showToast('Không thể khởi tạo thư viện camera. Vui lòng dùng ô nhập mã QR bên dưới.', 'warning');
        return;
    }
    document.getElementById('qrCameraPlaceholder').classList.add('d-none');
    document.getElementById('btnStartCamera').classList.add('d-none');
    document.getElementById('btnStopCamera').classList.remove('d-none');

    html5QrScanner = new Html5Qrcode("qr-reader");
    html5QrScanner.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 220, height: 220 } },
        (decodedText) => {
            html5QrScanner.stop().then(() => {
                submitScannedQrToken(decodedText);
            });
        },
        () => {}
    ).catch((err) => {
        GopGioApp.showToast('Không tìm thấy thiết bị camera hoặc trình duyệt chưa cấp quyền. Bạn có thể chọn "Điền mã vào ô quét" trong bảng danh sách.', 'warning');
        document.getElementById('qrCameraPlaceholder').classList.remove('d-none');
        document.getElementById('btnStartCamera').classList.remove('d-none');
        document.getElementById('btnStopCamera').classList.add('d-none');
    });
});

document.getElementById('btnStopCamera')?.addEventListener('click', () => {
    if (html5QrScanner) {
        html5QrScanner.stop().then(() => {
            document.getElementById('qrCameraPlaceholder').classList.remove('d-none');
            document.getElementById('btnStartCamera').classList.remove('d-none');
            document.getElementById('btnStopCamera').classList.add('d-none');
        });
    }
});

function filterParticipantsTable() {
    const q = (document.getElementById('filterParticipantInput')?.value || '').toLowerCase();
    document.querySelectorAll('#participantsTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
