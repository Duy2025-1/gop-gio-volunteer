<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $pattern, array|callable $handler, array $roles = []): void
    {
        $this->addRoute('GET', $pattern, $handler, $roles);
    }

    public function post(string $pattern, array|callable $handler, array $roles = []): void
    {
        $this->addRoute('POST', $pattern, $handler, $roles);
    }

    private function addRoute(string $method, string $pattern, array|callable $handler, array $roles = []): void
    {
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
            'roles' => $roles,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches)) {
                // Role-based access check at Router level
                if (!empty($route['roles'])) {
                    \App\Middleware\AuthMiddleware::requireRoles($route['roles']);
                }

                // Verify CSRF on POST requests unless explicitly exempted
                if ($method === 'POST' && !Csrf::verify()) {
                    if ($this->isJsonRequest($path)) {
                        http_response_code(403);
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode([
                            'success' => false,
                            'message' => 'Phiên bảo mật (CSRF Token) không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang.'
                        ], JSON_UNESCAPED_UNICODE);
                        return;
                    }
                    Session::setFlash('error', 'Phiên làm việc không hợp lệ (CSRF Token). Vui lòng thử lại.');
                    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
                    header('Location: ' . $referer);
                    exit;
                }

                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $action] = $handler;
                    $controller = new $controllerClass();
                    call_user_func_array([$controller, $action], $params);
                    return;
                }
            }
        }

        http_response_code(404);
        $viewFile = APP_PATH . '/Views/layouts/404.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo '<h1>404 - Không tìm thấy trang</h1>';
        }
    }

    private function isJsonRequest(string $path): bool
    {
        if (str_starts_with($path, '/api/')) {
            return true;
        }
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strtolower($xhr) === 'xmlhttprequest';
    }
}
