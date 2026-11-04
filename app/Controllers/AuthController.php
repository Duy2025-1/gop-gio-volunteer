<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;
    private User $userModel;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (is_logged_in()) {
            $this->redirect($this->dashboardRouteForRole(Session::userRole()));
        }
        $this->render('auth/login', [
            'pageTitle' => 'Đăng nhập – Góp Gió Volunteer',
        ]);
    }

    public function login(): void
    {
        try {
            $email = (string)($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $user = $this->authService->login($email, $password);

            Session::setFlash('success', 'Chào mừng ' . $user['full_name'] . ' quay trở lại!');
            $this->redirect($this->dashboardRouteForRole($user['role_slug']));
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            $this->render('auth/login', [
                'pageTitle' => 'Đăng nhập – Góp Gió Volunteer',
                'oldEmail' => $_POST['email'] ?? '',
            ]);
        }
    }

    public function showRegister(): void
    {
        if (is_logged_in()) {
            $this->redirect($this->dashboardRouteForRole(Session::userRole()));
        }
        $this->render('auth/register', [
            'pageTitle' => 'Đăng ký Tài khoản – Góp Gió Volunteer',
        ]);
    }

    public function register(): void
    {
        try {
            $user = $this->authService->register($_POST);
            Session::regenerate();
            Session::set('auth_user', $user);
            Session::setFlash('success', 'Đăng ký tài khoản thành công! Chào mừng ' . $user['full_name'] . ' đến với Góp Gió Volunteer.');
            $this->redirect($this->dashboardRouteForRole($user['role_slug']));
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            $this->render('auth/register', [
                'pageTitle' => 'Đăng ký Tài khoản – Góp Gió Volunteer',
                'old' => $_POST,
            ]);
        }
    }

    public function logout(): void
    {
        Session::destroy();
        Session::start();
        Session::setFlash('success', 'Bạn đã đăng xuất thành công.');
        $this->redirect('/login');
    }

    public function showForgotPassword(): void
    {
        $this->render('auth/forgot_password', [
            'pageTitle' => 'Quên mật khẩu – Góp Gió Volunteer',
        ]);
    }

    public function sendResetLink(): void
    {
        try {
            $email = trim((string)($_POST['email'] ?? ''));
            if ($email === '') {
                throw new \InvalidArgumentException('Vui lòng nhập địa chỉ email đã đăng ký.');
            }
            $token = $this->authService->createPasswordResetToken($email);
            if ($token !== null) {
                Session::setFlash('success', 'Hệ thống đã tạo mã đặt lại mật khẩu. Trong môi trường demo, bạn có thể đặt lại mật khẩu ngay bên dưới.');
                $this->render('auth/forgot_password', [
                    'pageTitle' => 'Đặt lại mật khẩu – Góp Gió Volunteer',
                    'demoResetToken' => $token,
                    'email' => $email,
                ]);
                return;
            }
            Session::setFlash('warning', 'Nếu email tồn tại trên hệ thống, hướng dẫn đặt lại mật khẩu đã được gửi.');
            $this->redirect('/forgot-password');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    public function resetPassword(): void
    {
        try {
            $token = trim((string)($_POST['reset_token'] ?? ''));
            $newPassword = (string)($_POST['new_password'] ?? '');
            $confirmPassword = (string)($_POST['password_confirmation'] ?? '');
            $this->authService->resetPasswordWithToken($token, $newPassword, $confirmPassword);
            Session::setFlash('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới.');
            $this->redirect('/login');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    public function updateProfile(): void
    {
        $sessionUser = AuthMiddleware::requireAuth();
        try {
            $avatarUrl = null;
            if (!empty($_FILES['avatar_file']['tmp_name'])) {
                $avatarUrl = handle_secure_upload($_FILES['avatar_file'], 'avatars');
            } elseif (!empty($_POST['avatar_url'])) {
                $avatarUrl = trim((string)$_POST['avatar_url']);
            }

            $this->userModel->updateProfile((int)$sessionUser['id'], [
                'full_name' => $_POST['full_name'] ?? $sessionUser['full_name'],
                'phone' => $_POST['phone'] ?? $sessionUser['phone'],
                'birth_date' => $_POST['birth_date'] ?? null,
                'gender' => $_POST['gender'] ?? null,
                'bio' => $_POST['bio'] ?? null,
                'address' => $_POST['address'] ?? null,
                'blood_type' => $_POST['blood_type'] ?? null,
                'avatar' => $avatarUrl,
            ]);

            $freshUser = $this->userModel->findById((int)$sessionUser['id']);
            if ($freshUser) {
                Session::set('auth_user', $this->authService->sanitizeUserForSession($freshUser));
            }
            Session::setFlash('success', 'Đã cập nhật hồ sơ cá nhân thành công.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/volunteer/dashboard');
    }

    public function changePassword(): void
    {
        $sessionUser = AuthMiddleware::requireAuth();
        try {
            $this->authService->changePassword(
                (int)$sessionUser['id'],
                (string)($_POST['current_password'] ?? ''),
                (string)($_POST['new_password'] ?? ''),
                (string)($_POST['password_confirmation'] ?? '')
            );
            Session::setFlash('success', 'Đổi mật khẩu thành công.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/volunteer/dashboard');
    }

    private function dashboardRouteForRole(string $roleSlug): string
    {
        return match ($roleSlug) {
            'admin' => '/admin/dashboard',
            'organizer' => '/organizer/dashboard',
            default => '/volunteer/dashboard',
        };
    }
}
