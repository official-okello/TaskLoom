<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/TaskManager.php';
require_once __DIR__ . '/../includes/UserPreferences.php';

echo "Running smoke tests...\n";

try {
    // Create fake user id 1 (doesn't hit DB unless methods query)
    $userId = 1;
    $tm = new TaskManager($userId);
    echo "TaskManager instantiated OK\n";

    $prefs = new UserPreferences($userId);
    echo "UserPreferences instantiated OK\n";

    echo "getCategories(): ";
    $cats = $tm->getCategories();
    var_export($cats);
    echo "\nSmoke tests completed.\n";
} catch (Exception $e) {
    echo "Smoke test failed: " . $e->getMessage() . "\n";
    exit(1);
}
