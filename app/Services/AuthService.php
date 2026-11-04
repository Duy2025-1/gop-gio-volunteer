<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\User;

class AuthService
{
    private User $userModel;
    private Organization $orgModel;
    private AuditLog $auditModel;
    private Notification $notificationModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->orgModel = new Organization();
        $this->auditModel = new AuditLog();
        $this->notificationModel = new Notification();
    }

    public function register(array $input): array
    {
        $validator = new Validator($input);
        $validator->required('full_name', 'Họ và tên')
                  ->minLength('full_name', 3, 'Họ và tên')
                  ->required('email', 'Email')
                  ->email('email', 'Email')
                  ->required('password', 'Mật khẩu')
                  ->minLength('password', 6, 'Mật khẩu')
                  ->matches('password_confirmation', 'password', 'Xác nhận mật khẩu')
                  ->required('phone', 'Số điện thoại')
                  ->phone('phone', 'Số điện thoại');

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->firstError());
        }

        $email = strtolower(trim((string)$input['email']));
        if ($this->userModel->findByEmail($email) !== null) {
            throw new \RuntimeException('Email này đã được đăng ký trên hệ thống. Vui lòng sử dụng email khác.');
        }

        $roleSlug = in_array($input['account_type'] ?? 'volunteer', ['volunteer', 'organizer'], true)
            ? (string)$input['account_type']
            : 'volunteer';

        $userId = $this->userModel->create([
            'role_slug' => $roleSlug,
            'full_name' => $input['full_name'],
            'email' => $email,
            'password_hash' => password_hash((string)$input['password'], PASSWORD_BCRYPT),
            'phone' => $input['phone'],
            'birth_date' => $input['birth_date'] ?? null,
            'gender' => $input['gender'] ?? null,
            'blood_type' => $input['blood_type'] ?? null,
            'address' => $input['address'] ?? null,
        ]);

        // If registering as an Organizer, create their initial organization profile if org_name provided
        if ($roleSlug === 'organizer') {
            $orgName = !empty($input['organization_name'])
                ? trim((string)$input['organization_name'])
                : 'Tổ chức của ' . trim((string)$input['full_name']);
            $this->orgModel->create([
                'owner_user_id' => $userId,
                'name' => $orgName,
                'description' => $input['organization_description'] ?? 'Tổ chức thiện nguyện hoạt động trên nền tảng Góp Gió Volunteer.',
                'address' => $input['address'] ?? 'TP. Hồ Chí Minh',
                'contact_email' => $email,
                'contact_phone' => (string)$input['phone'],
                'verification_status' => 'Pending',
            ]);
        }

        $this->notificationModel->create(
            $userId,
            'Chào mừng bạn đến với Góp Gió Volunteer!',
            'Một hành động nhỏ – Ngàn giá trị lớn. Hãy khám phá ngay các sự kiện thiện nguyện và hiến máu phù hợp với bạn.',
            'System',
            '/events'
        );

        $this->auditModel->log($userId, 'USER_REGISTER', 'User', $userId, null, [
            'email' => $email,
            'role' => $roleSlug,
        ]);

        $user = $this->userModel->findById($userId);
        return $this->sanitizeUserForSession($user);
    }

    public function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        if ($email === '' || $password === '') {
            throw new \InvalidArgumentException('Vui lòng nhập đầy đủ email và mật khẩu.');
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            throw new \RuntimeException('Email hoặc mật khẩu không chính xác.');
        }

        if ($user['status'] === 'Locked') {
            throw new \RuntimeException('Tài khoản của bạn đã bị khóa bởi Quản trị viên. Vui lòng liên hệ bộ phận hỗ trợ.');
        }

        if (!empty($user['locked_until']) && strtotime((string)$user['locked_until']) > time()) {
            $remainingMin = ceil((strtotime((string)$user['locked_until']) - time()) / 60);
            throw new \RuntimeException("Tài khoản tạm thời bị khóa do đăng nhập sai nhiều lần. Vui lòng thử lại sau {$remainingMin} phút.");
        }

        if (!password_verify($password, (string)$user['password_hash'])) {
            $attempts = (int)$user['failed_login_attempts'] + 1;
            $maxAttempts = (int)env('MAX_LOGIN_ATTEMPTS', 5);
            $lockMinutes = (int)env('LOGIN_LOCKOUT_MINUTES', 15);
            $lockedUntil = null;

            if ($attempts >= $maxAttempts) {
                $lockedUntil = date('Y-m-d H:i:s', time() + ($lockMinutes * 60));
            }
            $this->userModel->recordFailedLogin((int)$user['id'], $attempts, $lockedUntil);
            $this->auditModel->log((int)$user['id'], 'LOGIN_FAILED', 'User', (int)$user['id'], null, ['attempts' => $attempts]);

            throw new \RuntimeException('Email hoặc mật khẩu không chính xác.');
        }

        $this->userModel->resetFailedLogins((int)$user['id']);
        $sessionUser = $this->sanitizeUserForSession($user);
        Session::regenerate();
        Session::set('auth_user', $sessionUser);

        $this->auditModel->log((int)$user['id'], 'LOGIN_SUCCESS', 'User', (int)$user['id']);
        return $sessionUser;
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): void
    {
        if (mb_strlen($newPassword) < 6) {
            throw new \InvalidArgumentException('Mật khẩu mới phải có ít nhất 6 ký tự.');
        }
        if ($newPassword !== $confirmPassword) {
            throw new \InvalidArgumentException('Xác nhận mật khẩu mới không trùng khớp.');
        }

        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($currentPassword, (string)$user['password_hash'])) {
            throw new \RuntimeException('Mật khẩu hiện tại không chính xác.');
        }

        $this->userModel->updatePassword($userId, password_hash($newPassword, PASSWORD_BCRYPT));
        $this->auditModel->log($userId, 'CHANGE_PASSWORD', 'User', $userId);
    }

    public function createPasswordResetToken(string $email): ?string
    {
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return null;
        }
        $token = bin2hex(random_bytes(24));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $this->userModel->setResetToken((int)$user['id'], $token, $expiresAt);
        $this->auditModel->log((int)$user['id'], 'REQUEST_PASSWORD_RESET', 'User', (int)$user['id']);
        return $token;
    }

    public function resetPasswordWithToken(string $token, string $newPassword, string $confirmPassword): void
    {
        if (mb_strlen($newPassword) < 6) {
            throw new \InvalidArgumentException('Mật khẩu mới phải có ít nhất 6 ký tự.');
        }
        if ($newPassword !== $confirmPassword) {
            throw new \InvalidArgumentException('Xác nhận mật khẩu mới không khớp.');
        }
        $user = $this->userModel->findByResetToken($token);
        if (!$user || empty($user['reset_token_expires_at']) || strtotime((string)$user['reset_token_expires_at']) < time()) {
            throw new \RuntimeException('Mã khôi phục mật khẩu không hợp lệ hoặc đã hết hạn.');
        }
        $this->userModel->updatePassword((int)$user['id'], password_hash($newPassword, PASSWORD_BCRYPT));
        $this->auditModel->log((int)$user['id'], 'PASSWORD_RESET_COMPLETED', 'User', (int)$user['id']);
    }

    public function sanitizeUserForSession(array $user): array
    {
        return [
            'id' => (int)$user['id'],
            'role_id' => (int)$user['role_id'],
            'role_name' => $user['role_name'] ?? '',
            'role_slug' => $user['role_slug'] ?? 'volunteer',
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'avatar' => $user['avatar'] ?? '',
            'blood_type' => $user['blood_type'] ?? '',
            'status' => $user['status'] ?? 'Active',
        ];
    }
}
