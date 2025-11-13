<?php
    require_once __DIR__ . '/bootstrap.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            header("Location: index.php?error=csrf");
            exit();
        }
        $task_creator_id = $_SESSION["user_id"];
        $task = trim($_POST["task"]);

        if (empty($task)) {
            echo "Task cannot be empty.";
            header("Location: index.php");
            exit();
        }
        else {
            require_once __DIR__ . '/includes/addTask.inc.php';
            try {
                $addTask = new AddTask($task_creator_id, $task);
                $addTask->addTask();
                header("Location: index.php");
                exit();
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
                header("Location: index.php");
                exit();
            }
        }
    }
    else {
        header("Location: index.php");
        exit();
    }