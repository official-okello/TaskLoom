<?php
    // Force display of errors for ad-hoc debugging when ?debug=1 is present.
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }

    require_once __DIR__ . '/bootstrap.php';
    // Ensure TaskManager class is available (bootstrap does not auto-include domain classes)
    require_once __DIR__ . '/includes/TaskManager.php';
    if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    else {
        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskLoom</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/filters.css">
    <script>
        function confirmDelete(form, event) {
            const submitter = event.submitter;
            if (submitter && submitter.value === 'Delete') {
                const taskName = form.querySelector('input[name="task_item"]').value;
                if (!confirm(`Are you sure you want to delete task "${taskName}"?`)) {
                    event.preventDefault();
                    return false;
                }
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="./assets/img/TaskLoom.png" alt="">
        </div>
        <div class="button-group">
            <button class="btn btn-success"><a href="edit_task.php">New Task</a></button>
            <button class="btn btn-danger"><a href="logout.php">Logout</a></button>    
        </div>
    </div>
    <span class="welcome-msg">Welcome, <?php echo htmlspecialchars($username); ?></span>
    <?php 
    require_once __DIR__ . '/includes/flash.inc.php';
    echo FlashMessage::display(); 

    // Initialize task manager and get filters
    $taskManager = new TaskManager($_SESSION['user_id']);
    $categories = $taskManager->getCategories();
    
    // Get filter values from query parameters
    $filters = [
        'status' => $_GET['status'] ?? 'all',
        'category' => $_GET['category'] ?? '',
        'priority' => $_GET['priority'] ?? '',
        'search' => $_GET['search'] ?? '',
    ];
    $sortBy = $_GET['sort'] ?? 'date_created';
    $sortOrder = $_GET['order'] ?? 'DESC';
    ?>

    <div class="filters-section">
        <h3>Filter Tasks</h3>
        <form action="" method="get" id="filterForm">
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" 
                           value="<?php echo htmlspecialchars($filters['search']); ?>" 
                           placeholder="Search tasks...">
                </div>
                
                <div class="filter-item">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $filters['status'] === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="open" <?php echo $filters['status'] === 'open' ? 'selected' : ''; ?>>Pending</option>
                        <option value="done" <?php echo $filters['status'] === 'done' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" 
                                    <?php echo $filters['category'] === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="">All Priorities</option>
                        <option value="high" <?php echo $filters['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $filters['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $filters['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort">
                        <option value="date_created" <?php echo $sortBy === 'date_created' ? 'selected' : ''; ?>>Date Created</option>
                        <option value="due_date" <?php echo $sortBy === 'due_date' ? 'selected' : ''; ?>>Due Date</option>
                        <option value="priority" <?php echo $sortBy === 'priority' ? 'selected' : ''; ?>>Priority</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="index.php" class="btn btn-secondary">Clear Filters</a>
        </form>
    </div>
    <hr>
    <h3>Your Tasks:</h3>
    <div class="tasks-container">
        <!-- <h2>Your Tasks</h2> -->
        <div class="display-tasks">
        <?php
            try {
                // Get filtered tasks
                $tasks = $taskManager->getTasks($filters, $sortBy, $sortOrder);
                
                if (count($tasks) > 0) {
                    foreach ($tasks as $task) {
                        // Normalize status classes to DB values 'open' and 'done'
                        $statusClass = strtolower($task['status']) === 'done' ? 'status-complete' : 'status-pending';
                        $priorityClass = 'priority-' . $task['priority'];
                        
                        echo "<div class='task-item {$priorityClass}'>";
                        echo "<div class='task-content'>";
                        
                        if ($task['category']) {
                            echo "<div class='category-badge'>" . htmlspecialchars($task['category']) . "</div>";
                        }
                        
                        echo "<h3>" . htmlspecialchars($task['task_item']) . "</h3>";
                        
                        echo "<div class='task-meta'>";
                        echo "<small>Created: " . date('M j, Y g:i A', strtotime($task['date_created'])) . "</small>";
                        
                        if ($task['due_date']) {
                            $dueClass = strtotime($task['due_date']) < time() ? 'overdue' : '';
                            echo "<small class='due-date {$dueClass}'>Due: " . date('M j, Y g:i A', strtotime($task['due_date'])) . "</small>";
                        }
                        
                        echo "<small class='{$statusClass}'>Status: " . htmlspecialchars(ucfirst($task['status'])) . "</small>";
                        echo "</div>";
                        
                        if ($task['tags']) {
                            echo "<div class='task-tags'>";
                            foreach (explode(',', $task['tags']) as $tag) {
                                echo "<span class='tag'>" . htmlspecialchars(trim($tag)) . "</span>";
                            }
                            echo "</div>";
                        }
                        
                        if (!empty($task['shared_users'])) {
                            echo "<div class='shared-indicator'>Shared with: ";
                            foreach (explode(',', $task['shared_users']) as $userId) {
                                echo "<span class='user-avatar'>" . substr($userId, 0, 2) . "</span>";
                            }
                            echo "</div>";
                        }
                        echo "<div class='task-controls'>";
                        echo "<form action='taskOperations.php' method='post' onsubmit='return confirmDelete(this, event)'>";
                        echo getCsrfInputField();
                        echo "<input type='hidden' name='task_id' value='" . $task['id'] . "'>";
                        echo "<input type='hidden' name='task_creator_id' value='" . $task['creator_id'] . "'>";
                        echo "<input type='hidden' name='task_item' value='" . htmlspecialchars($task['task_item'], ENT_QUOTES) . "'>";
                        
                        echo "<div class='button-group'>";
                        if (strtolower($task['status']) !== 'done') {
                            echo "<button type='submit' name='operation' value='Mark as Complete' class='btn btn-success'>Mark as Complete</button>";
                        }
                        echo "<button type='submit' name='operation' value='Edit' class='btn btn-primary'>Edit</button>";
                        echo "<button type='submit' name='operation' value='Delete' class='btn btn-danger'>Delete</button>";
                        echo "</div>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>\n";
                    }
                } else {
                    echo "<p>No tasks found. Add a task above!</p>";
                }
            } catch (Exception $e) {
                echo "Connection failed: " . $e->getMessage();
                exit();
            }
         ?>
    </div>
    <div class="footer">
        <h5>&copy; <?php echo date('Y')?> Tech Afrika</h5>
    </div>
</body>
</html>