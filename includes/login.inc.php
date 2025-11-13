<?php
require_once __DIR__ . '/bootstrap.php';

class Login extends Dbh {
    private $username;
    private $password;

    public function __construct($username, $password) {
        parent::__construct();
        $this->username = $username;
        $this->password = $password;
    }

    public function loginUser() {
        try {
            $conn = $this->connect();

            // Use prepared statement with named parameters
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($this->password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                // Regenerate session ID for security
                session_regenerate_id(true);
                // Generate new CSRF token after successful login
                refreshCsrfToken();
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            // Log error (in a production environment)
            error_log("Login error: " . $e->getMessage());
            throw new Exception("Login failed. Please try again later.");
        }
    }
}