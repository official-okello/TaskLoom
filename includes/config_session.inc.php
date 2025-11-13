<?php
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

// Configure session cookie params if session not already started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/',
        'domain' => 'localhost',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();

    if (!isset($_SESSION["last_regeneration"])) {
        // First time initialization
        session_regenerate_id(true);
        $_SESSION["last_regeneration"] = time();
    } else {
        $interval = 60 * 30; // 30 minutes
        if (time() - $_SESSION["last_regeneration"] >= $interval) {
            session_regenerate_id(true);
            $_SESSION["last_regeneration"] = time();
        }
    }
}
