<?php
declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id",
            ['id' => $id]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE LOWER(u.email) = LOWER(:email)",
            ['email' => trim($email)]
        );
    }

    public function findRoleIdBySlug(string $slug): int
    {
        $id = $this->fetchValue("SELECT id FROM roles WHERE slug = :slug", ['slug' => $slug]);
        return $id ? (int)$id : 3;
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $roleId = $data['role_id'] ?? $this->findRoleIdBySlug($data['role_slug'] ?? 'volunteer');

        $this->execute(
            "INSERT INTO users (
                role_id, full_name, email, password_hash, phone, birth_date, gender,
                avatar, bio, address, blood_type, status, failed_login_attempts, created_at, updated_at
             ) VALUES (
                :role_id, :full_name, :email, :password_hash, :phone, :birth_date, :gender,
                :avatar, :bio, :address, :blood_type, :status, 0, :now, :now
             )",
            [
                'role_id' => $roleId,
                'full_name' => trim((string)$data['full_name']),
                'email' => strtolower(trim((string)$data['email'])),
                'password_hash' => $data['password_hash'],
                'phone' => $data['phone'] ?? null,
                'birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
                'gender' => !empty($data['gender']) ? $data['gender'] : null,
                'avatar' => $data['avatar'] ?? '/assets/images/avatars/user-vol1.jpg',
                'bio' => $data['bio'] ?? null,
                'address' => $data['address'] ?? null,
                'blood_type' => $data['blood_type'] ?? null,
                'status' => $data['status'] ?? 'Active',
                'now' => $now,
            ]
        );
        return $this->lastInsertId();
    }

    public function updateProfile(int $userId, array $data): bool
    {
        return $this->execute(
            "UPDATE users
             SET full_name = :full_name,
                 phone = :phone,
                 birth_date = :birth_date,
                 gender = :gender,
                 bio = :bio,
                 address = :address,
                 blood_type = :blood_type,
                 avatar = COALESCE(:avatar, avatar),
                 updated_at = :now
             WHERE id = :id",
            [
                'full_name' => trim((string)$data['full_name']),
                'phone' => $data['phone'] ?? null,
                'birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
                'gender' => !empty($data['gender']) ? $data['gender'] : null,
                'bio' => $data['bio'] ?? null,
                'address' => $data['address'] ?? null,
                'blood_type' => $data['blood_type'] ?? null,
                'avatar' => $data['avatar'] ?? null,
                'now' => date('Y-m-d H:i:s'),
                'id' => $userId,
            ]
        );
    }

    public function updatePassword(int $userId, string $newPasswordHash): bool
    {
        return $this->execute(
            "UPDATE users SET password_hash = :hash, reset_token = NULL, reset_token_expires_at = NULL, updated_at = :now WHERE id = :id",
            ['hash' => $newPasswordHash, 'now' => date('Y-m-d H:i:s'), 'id' => $userId]
        );
    }

    public function recordFailedLogin(int $userId, int $attempts, ?string $lockedUntil = null): void
    {
        $this->execute(
            "UPDATE users SET failed_login_attempts = :attempts, locked_until = :locked_until, updated_at = :now WHERE id = :id",
            [
                'attempts' => $attempts,
                'locked_until' => $lockedUntil,
                'now' => date('Y-m-d H:i:s'),
                'id' => $userId,
            ]
        );
    }

    public function resetFailedLogins(int $userId): void
    {
        $this->execute(
            "UPDATE users SET failed_login_attempts = 0, locked_until = NULL, updated_at = :now WHERE id = :id",
            ['now' => date('Y-m-d H:i:s'), 'id' => $userId]
        );
    }

    public function setResetToken(int $userId, string $token, string $expiresAt): void
    {
        $this->execute(
            "UPDATE users SET reset_token = :token, reset_token_expires_at = :exp, updated_at = :now WHERE id = :id",
            ['token' => $token, 'exp' => $expiresAt, 'now' => date('Y-m-d H:i:s'), 'id' => $userId]
        );
    }

    public function findByResetToken(string $token): ?array
    {
        return $this->fetchOne(
            "SELECT u.*, r.slug AS role_slug FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.reset_token = :token",
            ['token' => $token]
        );
    }

    public function getAllUsers(string $search = '', string $roleSlug = '', string $status = ''): array
    {
        $sql = "SELECT u.*, r.name AS role_name, r.slug AS role_slug,
                       COALESCE((SELECT SUM(vh.hours) FROM volunteer_hours vh WHERE vh.user_id = u.id AND vh.status = 'Approved'), 0) AS total_hours
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (u.full_name LIKE :q OR u.email LIKE :q OR u.phone LIKE :q)";
            $params['q'] = '%' . $search . '%';
        }
        if ($roleSlug !== '') {
            $sql .= " AND r.slug = :role";
            $params['role'] = $roleSlug;
        }
        if ($status !== '') {
            $sql .= " AND u.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY u.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function updateStatus(int $userId, string $status): bool
    {
        return $this->execute(
            "UPDATE users SET status = :status, updated_at = :now WHERE id = :id",
            ['status' => $status, 'now' => date('Y-m-d H:i:s'), 'id' => $userId]
        );
    }
}
