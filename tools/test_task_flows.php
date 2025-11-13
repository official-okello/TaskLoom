<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/TaskManager.php';
require __DIR__ . '/../includes/TaskValidator.php';

$userId = 1;
$tm = new TaskManager($userId);

echo "Starting scripted task flows for user {$userId}\n";

// Create task
$now = date('Ymd_His');
$taskData = [
    'task_item' => "Automated test task {$now}",
    'due_date' => null,
    'category' => 'testing',
    'tags' => 'auto,test',
    'priority' => 'medium'
];
$newId = $tm->addTask($taskData);
echo "Created task id: {$newId}\n";

// Verify creation appears in getTasks
$tasks = $tm->getTasks([],'date_created','DESC');
$found = false;
foreach ($tasks as $t) {
    if ($t['id'] == $newId) { $found = true; break; }
}
echo "Found in getTasks after create: " . ($found ? 'YES' : 'NO') . "\n";

// Update task (change task_item)
$updateData = [
    'task_item' => "Automated test task UPDATED {$now}",
    'due_date' => null,
    'category' => 'testing-updated',
    'tags' => 'auto,update',
    'priority' => 'high'
];
$ok = $tm->updateTask($newId, $updateData);
echo "UpdateTask returned: " . ($ok ? 'true' : 'false') . "\n";

$task = $tm->getTask($newId);
echo "After update, task_item: " . ($task ? $task['task_item'] : '(not found)') . "\n";

// Mark complete (direct SQL to avoid header()/exit in TaskOperations)
$db = $tm->connect();
$stmt = $db->prepare("UPDATE tasks SET status='done' WHERE id = :id AND creator_id = :uid");
$stmt->execute(['id' => $newId, 'uid' => $userId]);
echo "Marked complete (rows affected): " . $stmt->rowCount() . "\n";

$task = $tm->getTask($newId);
echo "After mark complete, status: " . ($task ? $task['status'] : '(not found)') . "\n";

// Delete the task
$stmt = $db->prepare("DELETE FROM tasks WHERE id = :id AND creator_id = :uid");
$stmt->execute(['id' => $newId, 'uid' => $userId]);
echo "Deleted (rows affected): " . $stmt->rowCount() . "\n";

$task = $tm->getTask($newId);
echo "After delete, found: " . ($task ? 'YES' : 'NO') . "\n";

echo "Scripted flows completed.\n";