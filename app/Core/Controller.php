<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = APP_PATH . '/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View [{$view}] not found at {$viewPath}");
        }

        if ($layout === null) {
            require $viewPath;
            return;
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = APP_PATH . '/Views/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            echo $content;
            return;
        }

        require $layoutPath;
    }

    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function isAjax(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strtolower($xhr) === 'xmlhttprequest';
    }
}
