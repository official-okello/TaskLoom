<?php
require_once __DIR__ . '/bootstrap.php';

class UserPreferences extends Dbh {
    private $userId;
    private $defaults = [
        'default_view' => 'all',
        'default_sort' => 'date_created',
        'theme' => 'light',
        'tasks_per_page' => 10
    ];

    public function __construct(int $userId) {
        parent::__construct();
        $this->userId = $userId;
        $this->ensurePreferencesExist();
    }

    private function ensurePreferencesExist(): void {
        try {
            $conn = $this->connect();
            $stmt = $conn->prepare("SELECT user_id FROM user_preferences WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $this->userId]);
            
            if (!$stmt->fetch()) {
                $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, default_view, default_sort, theme, tasks_per_page) 
                                      VALUES (:user_id, :default_view, :default_sort, :theme, :tasks_per_page)");
                $stmt->execute([
                    'user_id' => $this->userId,
                    'default_view' => $this->defaults['default_view'],
                    'default_sort' => $this->defaults['default_sort'],
                    'theme' => $this->defaults['theme'],
                    'tasks_per_page' => $this->defaults['tasks_per_page']
                ]);
            }
        } catch (PDOException $e) {
            error_log("Preference initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize user preferences.");
        }
    }

    public function getPreferences(): array {
        try {
            $conn = $this->connect();
            $stmt = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $this->userId]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
            return $prefs ?: $this->defaults;
        } catch (PDOException $e) {
            error_log("Preference retrieval error: " . $e->getMessage());
            throw new Exception("Failed to retrieve user preferences.");
        }
    }

    public function updatePreferences(array $preferences): void {
        try {
            $conn = $this->connect();
            $validFields = array_keys($this->defaults);
            $updates = array_intersect_key($preferences, array_flip($validFields));
            
            if (empty($updates)) {
                return;
            }

            $sql = "UPDATE user_preferences SET ";
            $sql .= implode(", ", array_map(function($field) {
                return "$field = :$field";
            }, array_keys($updates)));
            $sql .= " WHERE user_id = :user_id";

            $stmt = $conn->prepare($sql);
            $stmt->execute(array_merge($updates, ['user_id' => $this->userId]));
        } catch (PDOException $e) {
            error_log("Preference update error: " . $e->getMessage());
            throw new Exception("Failed to update user preferences.");
        }
    }
}