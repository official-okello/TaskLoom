<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Initialize error message
$error_message = '';

// Check for error parameters
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalidlogin':
            $error_message = 'Invalid username or password.';
            break;
        case 'stmtfailed':
            $error_message = 'System error. Please try again later.';
            break;
        case 'csrf':
            $error_message = 'Security validation failed. Please try again.';
            break;
    }
}

// Generate CSRF token
$csrf_token = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Simple ToDo</title>
    <style>
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h3>User Login</h3>
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required 
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        <br/><br/>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br/><br/>
        
        <button type="submit" name="login">Login</button>
        <p class="redir-to-register">Don't have an account? <a href="register.php">Register</a> here.</p>
    </form>
</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            header("Location: login.php?error=csrf");
            exit();
        }

        $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($username) || empty($password)) {
            throw new Exception("Username and Password cannot be empty.");
        }

                require_once __DIR__ . '/includes/login.inc.php';
        $login = new Login($username, $password);
        $user = $login->loginUser();

        if ($user) {
            // Redirect to index page (login.inc.php handles session)
            header("Location: index.php");
            exit();
        } else {
            header("Location: login.php?error=invalidlogin");
            exit();
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: login.php?error=stmtfailed");
        exit();
    }
}
?>