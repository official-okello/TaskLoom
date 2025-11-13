<?php
require_once __DIR__ . '/bootstrap.php';

class ApiResponse {
    public static function success($data = null, string $message = 'Success'): void {
        self::send(200, true, $message, $data);
    }
    
    public static function error(string $message, int $code = 400, $data = null): void {
        self::send($code, false, $message, $data);
    }
    
    public static function unauthorized(string $message = 'Unauthorized'): void {
        self::send(401, false, $message);
    }
    
    public static function forbidden(string $message = 'Forbidden'): void {
        self::send(403, false, $message);
    }
    
    public static function notFound(string $message = 'Not found'): void {
        self::send(404, false, $message);
    }
    
    public static function validationError(array $errors): void {
        self::send(422, false, 'Validation failed', ['errors' => $errors]);
    }
    
    private static function send(int $statusCode, bool $success, string $message, $data = null): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit();
    }
}