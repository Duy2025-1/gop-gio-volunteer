<?php
declare(strict_types=1);

namespace App\Core;

class Logger
{
    public static function log(string $level, string $message, array $context = []): void
    {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
        $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $ctx = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $entry = "[{$timestamp}] [{$level}] {$message}{$ctx}" . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
}
