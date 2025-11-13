<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    FlashMessage::set('Invalid security token.', 'danger');
    header("Location: index.php");
    exit();
}

try {
    $taskManager = new TaskManager($_SESSION['user_id']);
    
    $taskData = [
        'task_item' => trim($_POST['task_item']),
        'category' => !empty($_POST['category']) ? trim($_POST['category']) : null,
        'priority' => $_POST['priority'] ?? 'medium',
        'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
        'tags' => !empty($_POST['tags']) ? trim($_POST['tags']) : null,
        'status' => !empty($_POST['status']) ? trim($_POST['status']) : null
    ];

    // Validate required fields
    if (empty($taskData['task_item'])) {
        throw new Exception("Task description is required.");
    }

    // Handle task creation or update
    if (!empty($_POST['task_id'])) {
        $taskManager->updateTask((int)$_POST['task_id'], $taskData);
        FlashMessage::set('Task updated successfully!', 'success');
    } else {
        $taskManager->addTask($taskData);
        FlashMessage::set('Task created successfully!', 'success');
    }

    header("Location: index.php");
    exit();

} catch (Exception $e) {
    FlashMessage::set($e->getMessage(), 'danger');
    header("Location: " . (!empty($_POST['task_id']) ? "edit_task.php?id={$_POST['task_id']}" : "index.php"));
    exit();
}