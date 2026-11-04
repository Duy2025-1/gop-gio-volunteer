<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware
{
    public static function requireAuth(): array
    {
        $user = Session::user();
        if (!$user) {
            if (self::isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để thực hiện chức năng này.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: /login');
            exit;
        }
        return $user;
    }

    public static function requireRoles(array $allowedRoles): array
    {
        $user = self::requireAuth();
        $userRole = (string)($user['role_slug'] ?? '');
        if (!in_array($userRole, $allowedRoles, true)) {
            if (self::isAjax()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập vào chức năng này.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            http_response_code(403);
            Session::setFlash('error', 'Truy cập bị từ chối: Tài khoản của bạn không có quyền thực hiện thao tác này.');
            header('Location: /');
            exit;
        }
        return $user;
    }

    /**
     * Check role in business service layer (throws exception for unit/integration tests and APIs)
     */
    public static function assertRole(array $user, array $allowedRoles, string $actionLabel = 'thao tác này'): void
    {
        $role = (string)($user['role_slug'] ?? '');
        if (!in_array($role, $allowedRoles, true)) {
            throw new \RuntimeException("Truy cập trái quyền: Vai trò [{$role}] không được phép thực hiện {$actionLabel}.");
        }
    }

    private static function isAjax(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_starts_with($uri, '/api/')
            || str_contains($accept, 'application/json')
            || strtolower($xhr) === 'xmlhttprequest';
    }
}
