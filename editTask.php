<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/TaskManager.php';
require_once __DIR__ . '/includes/flash.inc.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$isNewTask = !isset($_GET['task_id']);
$task = null;
$error = null;

if (!$isNewTask) {
    $taskId = (int)$_GET['task_id'];
    $taskManager = new TaskManager($_SESSION['user_id']);
    
    try {
        $task = $taskManager->getTask($taskId);
        if (!$task) {
            FlashMessage::set('Task not found.', 'danger');
            header("Location: index.php");
            exit();
        }
    } catch (Exception $e) {
        FlashMessage::set('Error loading task: ' . $e->getMessage(), 'danger');
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isNewTask ? 'Add New Task' : 'Edit Task'; ?> - MyToDo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1><?php echo $isNewTask ? 'Add New Task' : 'Edit Task'; ?></h1>
        <div class="header-actions">
            <a href="index.php" class="btn btn-secondary">Back to Tasks</a>
        </div>
    </div>

    <?php
    echo FlashMessage::display();
    ?>

    <div class="form-container">
        <form action="saveTask.php" method="post" class="task-form">
            <?php echo getCsrfInputField(); ?>
            
            <?php if (!$isNewTask): ?>
                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="task">Task Description:</label>
                <textarea id="task" name="task" required rows="3"><?php echo $task ? htmlspecialchars($task['task_item']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="priority">Priority:</label>
                <select id="priority" name="priority">
                    <option value="low" <?php echo ($task && $task['priority'] === 'low') ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo (!$task || $task['priority'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo ($task && $task['priority'] === 'high') ? 'selected' : ''; ?>>High</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category">Category (optional):</label>
                <input type="text" id="category" name="category" value="<?php echo $task ? htmlspecialchars($task['category'] ?? '') : ''; ?>">
            </div>

            <div class="form-group">
                <label for="due_date">Due Date (optional):</label>
                <input type="datetime-local" id="due_date" name="due_date" 
                       value="<?php echo $task && $task['due_date'] ? date('Y-m-d\TH:i', strtotime($task['due_date'])) : ''; ?>">
            </div>

            <div class="form-actions">
                <button type="submit" name="save" class="btn btn-primary">
                    <?php echo $isNewTask ? 'Add Task' : 'Save Changes'; ?>
                </button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 100px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .form-actions .btn {
            min-width: 120px;
        }
    </style>
</body>
</html>