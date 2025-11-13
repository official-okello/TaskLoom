<?php
require_once __DIR__ . '/bootstrap.php';

class TaskManager extends Dbh {
    private $userId;

    public function __construct(int $userId) {
        parent::__construct();
        $this->userId = $userId;
    }

    public function getTask(int $taskId): ?array {
        try {
            $conn = $this->connect();
            $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :id AND creator_id = :user_id");
            $stmt->execute(['id' => $taskId, 'user_id' => $this->userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Task retrieval error: " . $e->getMessage());
            throw new Exception("Failed to retrieve task.");
        }
    }

    public function addTask(array $taskData): int {
        try {
            // Validate all inputs
            $validatedData = [
                'task_item' => $taskData['task_item'],
                'due_date' => $taskData['due_date'],
                'category' => $taskData['category'] ?? '',
                'priority' => $taskData['priority'] ?? 'medium',
                'status' => 'open',
                'tags' => $taskData['tags'] ?? ''
            ];

            $conn = $this->connect();
            
            $sql = "INSERT INTO tasks (creator_id, task_item, due_date, category, tags, priority, status) 
                    VALUES (:creator_id, :task_item, :due_date, :category, :tags, :priority, :status)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute(array_merge(
                ['creator_id' => $this->userId],
                $validatedData
            ));
            
            return (int)$conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Task creation error: " . $e->getMessage());
            throw new Exception("Failed to create task.");
        }
    }

    public function updateTask(int $taskId, array $taskData): bool {
        try {
            $conn = $this->connect();
            
            // Verify task ownership
            $task = $this->getTask($taskId);
            if (!$task) {
                throw new Exception("Task not found or access denied.");
            }
            
            $sql = "UPDATE tasks SET 
                    task_item = :task_item,
                    due_date = :due_date,
                    category = :category,
                    tags = :tags,
                    priority = :priority,
                    status = :status
                    WHERE id = :id AND creator_id = :creator_id";
            
            $stmt = $conn->prepare($sql);
            return $stmt->execute([
                'id' => $taskId,
                'creator_id' => $this->userId,
                'task_item' => $taskData['task_item'],
                'due_date' => $taskData['due_date'] ?? null,
                'category' => $taskData['category'] ?? null,
                'status' => $taskData['status'] ?? null,
                'tags' => $taskData['tags'] ?? null,
                'priority' => $taskData['priority'] ?? 'medium'
            ]);
        } catch (PDOException $e) {
            error_log("Task update error: " . $e->getMessage());
            throw new Exception("Failed to update task.");
        }
    }

    public function getTasks(array $filters = [], string $sortBy = 'date_created', string $sortOrder = 'DESC'): array {
        try {
            $conn = $this->connect();
            
            $sql = "SELECT t.*, GROUP_CONCAT(DISTINCT ts.shared_with) as shared_users 
                    FROM tasks t 
                    LEFT JOIN task_sharing ts ON t.id = ts.task_id 
                    WHERE t.creator_id = :user_id";
            $params = ['user_id' => $this->userId];

            // Apply filters
            if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
                // Normalize status comparison to lowercase to avoid case issues
                $sql .= " AND LOWER(t.status) = :status";
                $params['status'] = strtolower($filters['status']);
            }
            if (!empty($filters['category'])) {
                $sql .= " AND t.category = :category";
                $params['category'] = $filters['category'];
            }
            if (!empty($filters['priority'])) {
                $sql .= " AND t.priority = :priority";
                $params['priority'] = $filters['priority'];
            }
            if (!empty($filters['search'])) {
                $sql .= " AND (t.task_item LIKE :search OR t.category LIKE :search OR t.tags LIKE :search)";
                $params['search'] = "%{$filters['search']}%";
            }
            if (!empty($filters['date_range'])) {
                $sql .= " AND t.due_date BETWEEN :start_date AND :end_date";
                $params['start_date'] = $filters['date_range']['start'];
                $params['end_date'] = $filters['date_range']['end'];
            }

            $sql .= " GROUP BY t.id ORDER BY " . $this->sanitizeSortField($sortBy) . " " . ($sortOrder === 'DESC' ? 'DESC' : 'ASC');

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Task retrieval error: " . $e->getMessage());
            throw new Exception("Failed to retrieve tasks.");
        }
    }

    public function shareTask(int $taskId, int $sharedWithId, string $permissions = 'view'): void {
        try {
            $conn = $this->connect();
            
            // Verify task ownership
            $stmt = $conn->prepare("SELECT creator_id FROM tasks WHERE id = :task_id");
            $stmt->execute(['task_id' => $taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($task['creator_id'] !== $this->userId) {
                throw new Exception("You don't have permission to share this task.");
            }
            
            // Check if sharing already exists
            $stmt = $conn->prepare("SELECT id FROM task_sharing 
                                  WHERE task_id = :task_id AND shared_with = :shared_with");
            $stmt->execute(['task_id' => $taskId, 'shared_with' => $sharedWithId]);
            
            if (!$stmt->fetch()) {
                // Create new sharing
                $stmt = $conn->prepare("INSERT INTO task_sharing (task_id, shared_by, shared_with, permissions) 
                                      VALUES (:task_id, :shared_by, :shared_with, :permissions)");
                $stmt->execute([
                    'task_id' => $taskId,
                    'shared_by' => $this->userId,
                    'shared_with' => $sharedWithId,
                    'permissions' => $permissions
                ]);
            }
        } catch (PDOException $e) {
            error_log("Task sharing error: " . $e->getMessage());
            throw new Exception("Failed to share task.");
        }
    }

    public function getCategories(): array {
        try {
            $conn = $this->connect();
            $stmt = $conn->prepare("SELECT DISTINCT category FROM tasks WHERE creator_id = :user_id AND category IS NOT NULL");
            $stmt->execute(['user_id' => $this->userId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Category retrieval error: " . $e->getMessage());
            throw new Exception("Failed to retrieve categories.");
        }
    }

    private function sanitizeSortField(string $field): string {
        $allowedFields = ['date_created', 'due_date', 'priority', 'status', 'category'];
        return in_array($field, $allowedFields) ? $field : 'date_created';
    }

    public function editTask(int $taskId, string $taskItem): bool {
        try {
            $conn = $this->connect();
            
            // Verify task ownership
            $task = $this->getTask($taskId);
            if (!$task) {
                throw new Exception("Task not found or access denied.");
            }
            
            $sql = "UPDATE tasks SET task_item = :task_item WHERE id = :id AND creator_id = :creator_id";
            
            $stmt = $conn->prepare($sql);
            return $stmt->execute([
                'id' => $taskId,
                'creator_id' => $this->userId,
                'task_item' => $taskItem
            ]);
        } catch (PDOException $e) {
            error_log("Task edit error: " . $e->getMessage());
            throw new Exception("Failed to edit task.");
        }
    }
}