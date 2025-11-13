<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h3>User Registration</h3>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <?php require_once __DIR__ . '/bootstrap.php'; echo getCsrfInputField(); ?>
        <label for="username">Username:</label>
        <input type="text" id="username" required name="username" required>
        <br/><br/>
        <label for="password">Password:</label>
        <input type="password" id="password" required name="password" required>
        <br/><br/>
        <button type="submit" name="register">Register</button>
        <p class="redir-to-register">Already have an account? <a href="login.php">Login</a> here. </p>
    </form>
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