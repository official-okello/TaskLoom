<?php
    require_once __DIR__ . '/dbh.inc.php';
    
    class TaskOperations extends Dbh {
        private $task_creator_id;
        private $task_id;
        private $user_task;

        public function __construct($task_creator_id, $task_id, $user_task) {
            parent::__construct();
            $this->task_creator_id = $task_creator_id;
            $this->task_id = $task_id;
            $this->user_task = $user_task;
        }

        public function editTask($task_creator_id, $task_id, $user_task) {
            echo '<form action="editTask.php" method="post">';
            // CSRF token field (bootstrap must be required by caller)
            echo getCsrfInputField();
            echo '<input type="hidden" name="task_id" value="' . $task_id . '">';
            echo '<input type="hidden" name="task_creator_id" value="' . $task_creator_id . '">';
            echo '<textarea id="task" class="enter-task" name="task" rows="5" cols="60">' . htmlspecialchars($user_task) . '</textarea>';
            echo '<br/>';
            echo '<br/>';
            echo '<input type="submit" name="update" value="Update Task">';
            echo '<input type="button" value="Cancel" onclick="window.location.href=\'index.php\'">';
            echo '</form>';
        }

        public function markComplete($task_creator_id, $task_id) {
            // Use DB enum value 'done' for completed tasks
            $stmt = $this->connect()->prepare("UPDATE tasks SET status='done' WHERE id=:task_id AND creator_id=:task_creator_id");
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':task_creator_id', $task_creator_id);
            $stmt->execute();
            header("Location: index.php");
            exit();

        }
        public function deleteTask($task_creator_id, $task_id) {
            $stmt = $this->connect()->prepare("DELETE FROM tasks WHERE id=:task_id AND creator_id=:task_creator_id");
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':task_creator_id', $task_creator_id);
            $stmt->execute();
            echo "Task deleted successfully.";
            sleep(1);
            header("Location: index.php");
            exit();
        }
        public function __destruct() {
            // Optional cleanup   
        }
    }