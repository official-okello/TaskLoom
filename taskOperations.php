<?php
    require_once __DIR__ . '/bootstrap.php';

    $task_creator_id = $_POST["task_creator_id"] ?? null;
    $task_id = $_POST["task_id"] ?? null;
    $user_task = $_POST["task_item"] ?? null;

    // Verify CSRF token before performing any state-changing operation
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        header("Location: index.php?error=csrf");
        exit();
    }

    if (isset($_POST['operation'])) {
    require_once __DIR__ . '/includes/taskOperations.inc.php';
    require_once __DIR__ . '/includes/flash.inc.php';
        
    $taskOperations = new TaskOperations($task_creator_id, $task_id, $user_task);

        try {
            switch ($_POST['operation']) {
                case 'Edit':
                    // Redirect to edit form instead of updating directly
                    header("Location: editTask.php?task_id=" . urlencode($task_id));
                    exit();
                    break;
                    
                case 'Mark as Complete':
                    $taskOperations->markComplete($task_creator_id, $task_id);
                    FlashMessage::set('Task marked as complete!', 'success');
                    break;
                    
                case 'Delete':
                    $taskOperations->deleteTask($task_creator_id, $task_id);
                    FlashMessage::set('Task deleted successfully!', 'success');
                    break;
                    
                default:
                    FlashMessage::set('Invalid operation requested.', 'danger');
            }
            
            // Redirect back to index page after any operation
            header('Location: index.php');
            exit();
            
        } catch (Exception $e) {
            error_log("Task operation error: " . $e->getMessage());
            FlashMessage::set('An error occurred while processing your request.', 'danger');
            header('Location: index.php');
            exit();
        }
    }