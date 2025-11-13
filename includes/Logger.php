<?php
require_once __DIR__ . '/bootstrap.php';

class Logger {
    private static $logFile = __DIR__ . '/../logs/app.log';
    
    public static function init(): void {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public static function error(string $message, array $context = []): void {
        self::log('ERROR', $message, $context);
    }
    
    public static function info(string $message, array $context = []): void {
        self::log('INFO', $message, $context);
    }
    
    public static function debug(string $message, array $context = []): void {
        self::log('DEBUG', $message, $context);
    }
    
    private static function log(string $level, string $message, array $context): void {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        $logMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        // Try to log to file, fall back to error_log if we can't write to the log file
        if (!is_writable(dirname(self::$logFile)) || (file_exists(self::$logFile) && !is_writable(self::$logFile))) {
            error_log($logMessage); // Falls back to PHP's error log
            return;
        }
        
        try {
            error_log($logMessage, 3, self::$logFile);
        } catch (Exception $e) {
            error_log($logMessage); // Falls back to PHP's error log
        }
    }
    
    public static function getLogPath(): string {
        return self::$logFile;
    }
}