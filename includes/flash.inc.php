<?php
// Flash message handler for consistent user feedback
class FlashMessage {
    public static function set(string $message, string $type = 'info'): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    public static function display(): ?string {
        if (empty($_SESSION['flash_message'])) {
            return null;
        }

        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);

        $cssClass = htmlspecialchars($message['type']);
        $text = htmlspecialchars($message['message']);

        return "<div class='alert alert-{$cssClass}'>{$text}</div>";
    }
}