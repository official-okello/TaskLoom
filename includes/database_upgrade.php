<?php
require_once __DIR__ . '/bootstrap.php';

class DatabaseUpgrade extends Dbh {
    private $dryRun = false;

    public function __construct(bool $dryRun = false) {
        parent::__construct();
        $this->dryRun = $dryRun;
    }

    private function tableExists(string $tableName): bool {
        $conn = $this->connect();
        $stmt = $conn->prepare("SHOW TABLES LIKE :table");
        $stmt->execute(['table' => $tableName]);
        return (bool) $stmt->fetch();
    }

    private function columnExists(string $tableName, string $columnName): bool {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column");
        $stmt->execute(['table' => $tableName, 'column' => $columnName]);
        return (bool) $stmt->fetchColumn();
    }

    private function runOrPrint(string $sql) {
        if ($this->dryRun) {
            echo "DRY-RUN SQL: " . $sql . PHP_EOL;
            return;
        }
        $this->connect()->exec($sql);
    }

    public function upgrade() {
        try {
            $conn = $this->connect();

            // Backup tasks table if it exists and we're applying changes
            if (!$this->dryRun && $this->tableExists('tasks')) {
                $ts = date('Ymd_His');
                $backupSql = "CREATE TABLE IF NOT EXISTS tasks_backup_{$ts} AS SELECT * FROM tasks";
                $conn->exec($backupSql);
                echo "Created tasks backup: tasks_backup_{$ts}\n";
            }

            // Ensure user_preferences table exists
            $createPrefsTable = "CREATE TABLE IF NOT EXISTS user_preferences (
                user_id INT PRIMARY KEY,
                default_view VARCHAR(20) DEFAULT 'all',
                default_sort VARCHAR(20) DEFAULT 'date_created',
                theme VARCHAR(20) DEFAULT 'light',
                tasks_per_page INT DEFAULT 10,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $this->runOrPrint($createPrefsTable);

            // Ensure task_sharing table exists
            $createSharingTable = "CREATE TABLE IF NOT EXISTS task_sharing (
                id INT PRIMARY KEY AUTO_INCREMENT,
                task_id INT,
                shared_by INT,
                shared_with INT,
                permissions ENUM('view', 'edit') DEFAULT 'view',
                shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
                FOREIGN KEY (shared_by) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (shared_with) REFERENCES users(id) ON DELETE CASCADE
            )";
            $this->runOrPrint($createSharingTable);

            // Add individual columns to tasks if missing
            $columnsToAdd = [
                'due_date' => "DATETIME NULL",
                'category' => "VARCHAR(50) NULL",
                'tags' => "TEXT NULL",
                'priority' => "ENUM('low','medium','high') DEFAULT 'medium'",
                'shared_with' => "TEXT NULL",
                'last_modified' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            ];

            foreach ($columnsToAdd as $col => $definition) {
                if (!$this->columnExists('tasks', $col)) {
                    $sql = "ALTER TABLE tasks ADD COLUMN {$col} {$definition}";
                    $this->runOrPrint($sql);
                } else {
                    echo "Column {$col} already exists on tasks, skipping." . PHP_EOL;
                }
            }

            return true;

        } catch (PDOException $e) {
            error_log("Database upgrade error: " . $e->getMessage());
            throw new Exception("Failed to upgrade database structure: " . $e->getMessage());
        }
    }
}

// CLI handling: support --dry-run
$dry = false;
if (PHP_SAPI === 'cli') {
    $args = $_SERVER['argv'];
    if (in_array('--dry-run', $args, true) || in_array('-n', $args, true)) {
        $dry = true;
    }
}

// Run the upgrade
try {
    $upgrader = new DatabaseUpgrade($dry);
    $upgrader->upgrade();
    echo $dry ? "Dry-run completed. No changes applied." . PHP_EOL : "Database structure updated successfully." . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}