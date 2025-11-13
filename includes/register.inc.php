<?php
    require_once __DIR__ . '/dbh.inc.php';

    class RegisterUser extends Dbh {
        private $username;
        private $password;
        private $email;

        public function __construct($username, $password, $email = '') {
            parent::__construct();
            $this->username = $username;
            $this->password = $password;
            $this->email = $email;
        }

        private function usernameExists($username) {
            $conn = $this->connect();
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function registerUser() {
            $username = $this->username;
            $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);
            $email = $this->email;

            // Prevent duplicate usernames
            if ($this->usernameExists($username)) {
                return false;
            }

            $conn = $this->connect();
            $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':email', $email);

            return $stmt->execute();
        }
    }
