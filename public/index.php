<?php
declare(strict_types=1);

/**
 * Góp Gió Volunteer - Front Controller
 */

// Support PHP built-in development server static asset serving
if (PHP_SAPI === 'cli-server') {
    $urlPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $filePath = __DIR__ . $urlPath;
    if ($urlPath !== '/' && is_file($filePath)) {
        // Security: block direct PHP execution inside /uploads
        if (str_starts_with($urlPath, '/uploads/') && preg_match('/\.(php|phtml|phar)$/i', $urlPath)) {
            http_response_code(403);
            exit('403 Forbidden');
        }
        return false;
    }
}

require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Logger;
use App\Core\Router;
use App\Core\Session;

Session::start();

try {
    $router = new Router();
    require BASE_PATH . '/routes/web.php';
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (\Throwable $e) {
    Logger::error('Unhandled Exception: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Lỗi hệ thống</title></head>';
    echo '<body style="font-family:sans-serif;padding:40px;background:#F8FAFC;color:#1E293B;">';
    echo '<div style="max-width:650px;margin:0 auto;background:#fff;padding:32px;border-radius:12px;border:1px solid #E2E8F0;">';
    echo '<h2 style="color:#DC2626;margin-top:0;">Đã xảy ra lỗi trong quá trình xử lý</h2>';
    if (env('APP_DEBUG', false)) {
        echo '<p><strong>Thông báo:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    } else {
        echo '<p>Hệ thống đang bận hoặc gặp sự cố tạm thời. Vui lòng thử lại sau.</p>';
    }
    echo '<p><a href="/" style="color:#16A34A;font-weight:bold;">&larr; Quay về Trang chủ Góp Gió Volunteer</a></p>';
    echo '</div></body></html>';
}
