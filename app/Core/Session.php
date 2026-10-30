<?php
declare(strict_types=1);

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $sessionName = (string)env('SESSION_NAME', 'GOPGIO_SESSID');
            session_name($sessionName);
            session_set_cookie_params([
                'lifetime' => (int)env('SESSION_LIFETIME', 7200),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_destroy();
        }
    }

    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['_flash'][$type] = $message;
    }

    public static function getFlashes(): array
    {
        self::start();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    public static function user(): ?array
    {
        return self::get('auth_user');
    }

    public static function userId(): ?int
    {
        $user = self::user();
        return isset($user['id']) ? (int)$user['id'] : null;
    }

    public static function userRole(): string
    {
        $user = self::user();
        return isset($user['role_slug']) ? (string)$user['role_slug'] : 'guest';
    }
}
