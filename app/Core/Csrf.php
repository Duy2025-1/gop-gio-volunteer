<?php
declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        Session::start();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['_csrf_token'];
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function verify(?string $submittedToken = null): bool
    {
        Session::start();
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        if ($sessionToken === '') {
            return false;
        }
        if ($submittedToken === null) {
            $submittedToken = $_POST['csrf_token']
                ?? $_SERVER['HTTP_X_CSRF_TOKEN']
                ?? null;
        }
        if (!is_string($submittedToken) || $submittedToken === '') {
            return false;
        }
        return hash_equals($sessionToken, $submittedToken);
    }
}
