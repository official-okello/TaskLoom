<?php
    class Dbh {
        private $dbhost;
        private $dbuser;
        private $dbpass;
        private $dbname;

        public function __construct($dbhost="localhost", $dbuser="root", $dbpass="371675", $dbname="TaskLoom") {
            $this->dbhost = $dbhost;
            $this->dbuser = $dbuser;
            $this->dbpass = $dbpass;
            $this->dbname = $dbname;
        }

        public function connect() {
            try {
                $dsn = "mysql:host={$this->dbhost};dbname={$this->dbname};charset=utf8mb4";
                $conn = new PDO($dsn, $this->dbuser, $this->dbpass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $conn;
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
    }
