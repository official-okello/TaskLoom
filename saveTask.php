<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/TaskManager.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    FlashMessage::set('Invalid form submission.', 'danger');
    header("Location: index.php");
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    FlashMessage::set('Please log in to manage tasks.', 'danger');
    header("Location: login.php");
    exit();
}

try {
    $taskManager = new TaskManager($_SESSION['user_id']);
    $isNewTask = !isset($_POST['task_id']);
    
    // Validate required fields
    if (empty($_POST['task'])) {
        throw new Exception('Task description is required.');
    }

    // Prepare task data
    $taskData = [
        'task_item' => $_POST['task'],
        'category' => $_POST['category'] ?? '',
        'priority' => $_POST['priority'] ?? 'medium',
        'status' => $_POST['status'] ?? 'open',
        'due_date' => !empty($_POST['due_date']) ? date('Y-m-d H:i:s', strtotime($_POST['due_date'])) : null
    ];

    if ($isNewTask) {
        // Add new task
        $taskManager->addTask($taskData);
        FlashMessage::set('Task added successfully!', 'success');
    } else {
        // Update existing task
        $taskManager->updateTask((int)$_POST['task_id'], $taskData);
        FlashMessage::set('Task updated successfully!', 'success');
    }

    header("Location: index.php");
    exit();
    
} catch (Exception $e) {
    FlashMessage::set($e->getMessage(), 'danger');
    if ($isNewTask) {
        header("Location: editTask.php");
    } else {
        header("Location: editTask.php?task_id=" . urlencode($_POST['task_id']));
    }
    exit();
}