<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/logout.inc.php';

try {
    $logout = new Logout();
    $success = $logout->logoutUser();

    // Start a fresh session to set a flash message for the next request (only if none active)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if ($success) {
        $_SESSION['message'] = 'You have been logged out.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Logout completed, but session destroy reported an issue.';
        $_SESSION['message_type'] = 'error';
    }

    header('Location: login.php');
    exit();
} catch (Exception $e) {
    // Show a simple error and stop.
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    exit();
}