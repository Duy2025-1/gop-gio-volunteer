<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="gg-card p-4 p-lg-5">
                <div class="gg-section-kicker">Hỗ trợ tài khoản</div>
                <h1 class="h3 mb-2">Khôi phục mật khẩu</h1>
                <p class="small text-muted mb-4">Nhập địa chỉ email đã đăng ký tài khoản Góp Gió Volunteer để tạo mã xác thực đặt lại mật khẩu.</p>

                <?php if (empty($demoResetToken)): ?>
                    <form action="/forgot-password" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Email tài khoản</label>
                            <input type="email" name="email" class="form-control" placeholder="email@gopgio.vn" required>
                        </div>
                        <button type="submit" class="btn btn-gg-primary w-100 py-2">Tạo yêu cầu đặt lại mật khẩu</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success small">
                        Mã xác thực khôi phục mật khẩu cho tài khoản <strong><?= e($email ?? '') ?></strong> đã được khởi tạo.
                    </div>
                    <form action="/reset-password" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Mã xác thực khôi phục</label>
                            <input type="text" name="reset_token" class="form-control bg-light" value="<?= e($demoResetToken) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới *</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nhập lại mật khẩu mới *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-gg-primary w-100 py-2">Lưu mật khẩu mới</button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-4 pt-3 border-top small">
                    <a href="/login" class="fw-medium" style="color:#15803D;">&larr; Quay lại trang đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</div>
