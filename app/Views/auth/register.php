<?php $old = $old ?? []; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="gg-card p-4 p-lg-5">
                <div class="mb-4">
                    <div class="gg-section-kicker">Gia nhập mạng lưới tình nguyện</div>
                    <h1 class="h3 mb-1">Đăng ký tài khoản Góp Gió Volunteer</h1>
                    <p class="small text-muted mb-0">Điền thông tin cá nhân chính xác để phục vụ việc điểm danh QR và cấp giấy chứng nhận giờ hoạt động xã hội.</p>
                </div>

                <form action="/register" method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nhóm tài khoản đăng ký *</label>
                            <select name="account_type" id="accountTypeSelect" class="form-select" onchange="document.getElementById('orgFieldsBox').classList.toggle('d-none', this.value !== 'organizer')">
                                <option value="volunteer" <?= ($old['account_type'] ?? 'volunteer') === 'volunteer' ? 'selected' : '' ?>>Tình nguyện viên cá nhân</option>
                                <option value="organizer" <?= ($old['account_type'] ?? '') === 'organizer' ? 'selected' : '' ?>>Đại diện Câu lạc bộ / Đơn vị tổ chức</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên đầy đủ *</label>
                            <input type="text" name="full_name" class="form-control" value="<?= e($old['full_name'] ?? '') ?>" placeholder="Ví dụ: Nguyễn Minh Anh" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ Email *</label>
                            <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>" placeholder="sinhvien@edu.vn" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại liên lạc *</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($old['phone'] ?? '') ?>" placeholder="0912345678" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="birth_date" class="form-control" value="<?= e($old['birth_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giới tính (không bắt buộc)</label>
                            <select name="gender" class="form-select">
                                <option value="">-- Chọn --</option>
                                <option value="Male" <?= ($old['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Nam</option>
                                <option value="Female" <?= ($old['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Nữ</option>
                                <option value="Other" <?= ($old['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nhóm máu (nếu biết)</label>
                            <select name="blood_type" class="form-select">
                                <option value="">Chưa rõ</option>
                                <?php foreach (['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $bt): ?>
                                    <option value="<?= $bt ?>" <?= ($old['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Nơi ở hiện tại (Quận/Huyện, Tỉnh/Thành phố)</label>
                            <input type="text" name="address" class="form-control" value="<?= e($old['address'] ?? '') ?>" placeholder="Ví dụ: Phường Linh Trung, TP. Thủ Đức, TP.HCM">
                        </div>

                        <div id="orgFieldsBox" class="col-12 <?= ($old['account_type'] ?? 'volunteer') === 'organizer' ? '' : 'd-none' ?>">
                            <div class="p-3 border" style="background-color:#F7F9F7;border-radius:6px;">
                                <h3 class="h5 mb-2" style="color:#15803D;">Thông tin đăng ký Câu lạc bộ / Đơn vị tổ chức</h3>
                                <div class="mb-2">
                                    <label class="form-label">Tên chính thức của Câu lạc bộ / Đơn vị</label>
                                    <input type="text" name="organization_name" class="form-control" value="<?= e($old['organization_name'] ?? '') ?>" placeholder="Ví dụ: Câu lạc bộ Tình nguyện Sinh viên ĐHQG">
                                </div>
                                <div>
                                    <label class="form-label">Mô tả lĩnh vực hoạt động chính</label>
                                    <textarea name="organization_description" class="form-control" rows="2" placeholder="Giới thiệu ngắn gọn về đơn vị và các chương trình thường niên..."><?= e($old['organization_description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu * (tối thiểu 6 ký tự)</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nhập lại mật khẩu *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-gg-primary w-100 py-2">
                                Hoàn tất đăng ký tài khoản
                            </button>
                        </div>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top small text-muted">
                    Đã có tài khoản trên hệ thống? <a href="/login" class="fw-semibold" style="color:#15803D;">Đăng nhập tại đây</a>
                </div>
            </div>
        </div>
    </div>
</div>
