<?php
declare(strict_types=1);

/**
 * Góp Gió Volunteer - Configuration & Environment Loader
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('DATABASE_PATH', BASE_PATH . '/database');

// Load Composer autoloader if available
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Built-in PSR-4 Autoloader for App\* classes
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load .env or fallback to .env.example
$envFile = file_exists(BASE_PATH . '/.env') ? BASE_PATH . '/.env' : BASE_PATH . '/.env.example';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!isset($_ENV[$key]) && getenv($key) === false) {
                $_ENV[$key] = $val;
                putenv("{$key}={$val}");
            }
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    $val = $_ENV[$key] ?? getenv($key);
    if ($val === false || $val === null || $val === '') {
        return $default;
    }
    $lower = strtolower((string)$val);
    if ($lower === 'true') {
        return true;
    }
    if ($lower === 'false') {
        return false;
    }
    return $val;
}

date_default_timezone_set((string)env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'));

// Load helper functions
require_once APP_PATH . '/Helpers/functions.php';
