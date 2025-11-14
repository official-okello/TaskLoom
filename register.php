<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./assets/img/TaskLoom.ico" type="image/x-icon">
    <link rel="stylesheet" href="./assets/css/style.css">
    <title>Register</title>
</head>
<body>
    <h3 class="login-register-title">Registration</h3>
    <div class="form-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <?php require_once __DIR__ . '/bootstrap.php'; echo getCsrfInputField(); ?>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" required name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" required name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" name="register">Register</button>
            </div>
            <p class="redir-to-register">Already have an account? <a href="login.php">Login</a> here. </p>
        </form>
    </div>
</body>
</html>

<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            http_response_code(403);
            exit('Invalid CSRF token');
        }

        $username = trim(filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS));
        $password = trim(filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS));

        if (empty($username) || empty($password)) {
            echo "Username and Password cannot be empty.";
            exit();
        }
        else {
            require_once __DIR__ . '/includes/register.inc.php';
            $register = new RegisterUser($username, $password, "");
            if ($register->registerUser()) {
                echo "Registration successful. You can now <a href='login.php'>login</a>.";
            } else {
                echo "Registration failed. Please try again.";
            }
        }
    }
?>