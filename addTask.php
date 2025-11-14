<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$taskId = $_GET['id'] ?? null;
$taskManager = new TaskManager($_SESSION['user_id']);

// Get categories for dropdown
$categories = $taskManager->getCategories();

// If editing, get task details
$task = null;
if ($taskId) {
    try {
        $task = $taskManager->getTask($taskId);
        if (!$task || $task['creator_id'] != $_SESSION['user_id']) {
            FlashMessage::set('Task not found or access denied.', 'danger');
            header("Location: index.php");
            exit();
        }
    } catch (Exception $e) {
        FlashMessage::set('Error loading task.', 'danger');
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
    <title><?php echo $taskId ? 'Edit Task' : 'New Task'; ?> - TaskLoom</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h1><?php echo $taskId ? 'Edit Task' : 'Create New Task'; ?></h1>
        
        <?php echo FlashMessage::display(); ?>

        <form action="process_task.php" method="post" class="task-form">
            <?php echo getCsrfInputField(); ?>
            <input type="hidden" name="task_id" value="<?php echo $taskId ?? ''; ?>">

            <div class="form-group">
                <label for="task_item">Task Description*</label>
                <textarea id="task_item" name="task_item" rows="3" required><?php echo $task['task_item'] ?? ''; ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" list="categories" 
                           value="<?php echo $task['category'] ?? ''; ?>">
                    <datalist id="categories">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="low" <?php echo ($task['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo (!isset($task['priority']) || $task['priority'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo ($task['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="datetime-local" id="due_date" name="due_date" 
                       value="<?php echo $task['due_date'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="tags">Tags (comma separated)</label>
                <input type="text" id="tags" name="tags" 
                       value="<?php echo $task['tags'] ?? ''; ?>" 
                       placeholder="e.g., work, urgent, meeting">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?php echo $taskId ? 'Update Task' : 'Create Task'; ?>
                </button>
                <button type="submit" class="btn btn-secondary">
                    <a href="index.php">Cancel</a>
                </button>
            </div>
        </form>
    </div>
</body>
</html>