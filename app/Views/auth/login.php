<div class="container py-5">
    <div class="row justify-content-center g-4">
        <div class="col-md-6 col-lg-5">
            <div class="gg-card p-4 p-lg-5">
                <div class="mb-4">
                    <div class="gg-section-kicker">Tài khoản thành viên</div>
                    <h1 class="h3 mb-1">Đăng nhập Góp Gió Volunteer</h1>
                    <p class="small text-muted mb-0">Truy cập bảng điều khiển cá nhân, mã QR điểm danh và chứng nhận giờ tình nguyện.</p>
                </div>

                <form action="/login" method="POST" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="loginEmail">Địa chỉ Email</label>
                        <input type="email" name="email" id="loginEmail" class="form-control"
                               value="<?= e($oldEmail ?? '') ?>" placeholder="email@gopgio.vn" required>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" for="loginPassword">Mật khẩu</label>
                            <a href="/forgot-password" class="small" style="color:#15803D;">Quên mật khẩu?</a>
                        </div>
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Nhập mật khẩu của bạn" required>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                        <label class="form-check-label small text-muted" for="rememberMe">Duy trì đăng nhập trên thiết bị này</label>
                    </div>
                    <button type="submit" class="btn btn-gg-primary w-100 py-2">
                        Đăng nhập
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top small text-muted">
                    Chưa có tài khoản tình nguyện viên? <a href="/register" class="fw-semibold" style="color:#15803D;">Đăng ký tham gia</a>
                </div>
            </div>
        </div>
    </div>
</div>
