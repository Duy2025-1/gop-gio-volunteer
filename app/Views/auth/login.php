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

        <!-- Khung tài khoản mẫu phục vụ kiểm thử phân quyền -->
        <div class="col-md-6 col-lg-4">
            <div class="gg-card p-4" style="background-color:#F7F9F7;">
                <div class="gg-section-kicker mb-1">Tài khoản trải nghiệm</div>
                <h2 class="h5 mb-2">Chọn nhanh tài khoản theo vai trò</h2>
                <p class="small text-muted mb-3">
                    Nhấn vào một tài khoản bên dưới để tự động điền thông tin đăng nhập tương ứng với từng nhóm quyền trên hệ thống:
                </p>

                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-white bg-white border text-start p-2" style="border-radius:6px;"
                            onclick="GopGioApp.fillDemoAccount('minhanh.tnv@gopgio.vn', 'Volunteer@123')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small text-dark">Tình nguyện viên (Nguyễn Minh Anh)</span>
                            <span class="gg-badge badge-success">Tình nguyện viên</span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">minhanh.tnv@gopgio.vn</div>
                    </button>

                    <button type="button" class="btn btn-white bg-white border text-start p-2" style="border-radius:6px;"
                            onclick="GopGioApp.fillDemoAccount('org.dhqg@gopgio.vn', 'Org@123456')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small text-dark">Đơn vị tổ chức (CLB ĐHQG TP.HCM)</span>
                            <span class="gg-badge badge-info">Ban tổ chức</span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">org.dhqg@gopgio.vn</div>
                    </button>

                    <button type="button" class="btn btn-white bg-white border text-start p-2" style="border-radius:6px;"
                            onclick="GopGioApp.fillDemoAccount('org.hienmau@gopgio.vn', 'Org@123456')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small text-dark">Đơn vị tiếp nhận hiến máu</span>
                            <span class="gg-badge badge-warning">Ban tổ chức</span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">org.hienmau@gopgio.vn</div>
                    </button>

                    <button type="button" class="btn btn-white bg-white border text-start p-2" style="border-radius:6px;"
                            onclick="GopGioApp.fillDemoAccount('admin@gopgio.vn', 'Admin@123456')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small text-dark">Quản trị viên điều phối</span>
                            <span class="gg-badge badge-danger">Quản trị viên</span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">admin@gopgio.vn</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
