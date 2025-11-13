<?php
require_once __DIR__ . '/config_session.inc.php';

class Logout {
    public function logoutUser(): bool {
        // Ensure a session is active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session variables
        $_SESSION = [];

        // If sessions use cookies, attempt to remove the session cookie from the client
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
                $options = [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ];

                setcookie(session_name(), '', $options);
            } else {
                $path = $params['path'];
                if (!empty($params['samesite'])) {
                    $path .= '; samesite=' . $params['samesite'];
                }

                setcookie(session_name(), '', time() - 42000, $path, $params['domain'], $params['secure'], $params['httponly']);
            }
        }

        // Destroy the session data on the server
        $result = session_destroy();

        return (bool) $result;
    }
}
