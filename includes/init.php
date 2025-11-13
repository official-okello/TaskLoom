<?php
// Error reporting
error_reporting(E_ALL);
// By default do not display errors to users; log them instead.
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Enable display of errors when running on localhost or when ?debug=1 is present.
// This is safe for a local development environment and reversible.
$isLocal = (php_sapi_name() !== 'cli') && (($_SERVER['REMOTE_ADDR'] ?? '') === '127.0.0.1' || ($_SERVER['REMOTE_ADDR'] ?? '') === '::1');
$isDebugParam = (php_sapi_name() !== 'cli') && (isset($_GET['debug']) && $_GET['debug'] === '1');
if ($isLocal || $isDebugParam) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if (!defined('DEVELOPMENT_MODE')) define('DEVELOPMENT_MODE', true);
}

// Define base path first
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Define other paths
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_ROOT . '/config');
if (!defined('INCLUDE_PATH')) define('INCLUDE_PATH', APP_ROOT . '/includes');
if (!defined('LOG_PATH')) define('LOG_PATH', APP_ROOT . '/logs');
if (!defined('CACHE_PATH')) define('CACHE_PATH', APP_ROOT . '/cache');

// Ensure function libraries and DB helper are available early
require_once INCLUDE_PATH . '/dbh.inc.php';
require_once INCLUDE_PATH . '/csrf.inc.php';

// Set default timezone
date_default_timezone_set('Africa/Nairobi');

// Autoload classes
spl_autoload_register(function ($class) {
    $file = INCLUDE_PATH . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize logging
Logger::init();

// Determine whether the connection should be treated as secure
$secure = false;
if (php_sapi_name() !== 'cli') {
    $secure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
}

// Configure and start session with secure settings
function initSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    global $secure;
    $httponly = true;
    $samesite = 'Lax'; // Or 'Strict'

    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Set cookie parameters before starting session
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    } else {
        session_set_cookie_params(
            0,
            '/; samesite=' . $samesite,
            $domain,
            $secure,
            $httponly
        );
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Initialize session
initSession();

// Set security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
if ($secure) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Function to clean input data
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    Logger::error("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return false;
});

// Exception handler
set_exception_handler(function($e) {
    Logger::error("Uncaught Exception: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        throw $e; // Re-throw in development
    } else {
        echo "An error occurred. Please try again later.";
    }
});

// Register shutdown function
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        Logger::error("Fatal Error: " . $error['message'], [
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});