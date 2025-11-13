<?php
if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken(): string {
        // Session should already be started by init.php
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session not active when generating CSRF token');
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken(?string $token): bool {
        // Session should already be started by init.php
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session not active when verifying CSRF token');
        }
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('refreshCsrfToken')) {
    function refreshCsrfToken(): string {
        // Session should already be started by init.php
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session not active when refreshing CSRF token');
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('getCsrfInputField')) {
    function getCsrfInputField(): string {
        $token = generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }
}
