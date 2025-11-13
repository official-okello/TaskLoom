<?php
// Lightweight debug endpoint — only enabled when called with ?debug=1
if (!isset($_GET['debug']) || $_GET['debug'] !== '1') {
    http_response_code(403);
    echo "Debug disabled. Append ?debug=1 to enable.";
    exit();
}

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/TaskManager.php';

header('Content-Type: text/plain; charset=utf-8');

echo "*** SESSION DUMP ***\n";
foreach ($_SESSION as $k => $v) {
    echo "$k => ";
    if (is_scalar($v)) echo $v . "\n";
    else echo print_r($v, true) . "\n";
}

if (!isset($_SESSION['user_id'])) {
    echo "\nNo user logged in (session has no user_id).\n";
    exit();
}

$userId = (int) $_SESSION['user_id'];
$tm = new TaskManager($userId);
$tasks = $tm->getTasks([],'date_created','DESC');

echo "\nLogged in as user_id={$userId}, username=" . (isset($_SESSION['username']) ? $_SESSION['username'] : '(none)') . "\n";
echo "Tasks found: " . count($tasks) . "\n\n";

foreach ($tasks as $t) {
    echo "id={$t['id']} creator_id={$t['creator_id']} status={$t['status']} task_item={$t['task_item']}\n";
}

?>