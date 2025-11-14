<?php
// Environment settings
define('DEVELOPMENT_MODE', true); // Set to false in production

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'TaskLoom');
define('DB_USER', 'root');
define('DB_PASS', '371675');

// Security settings
define('CSRF_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutes

// Application settings
define('TASKS_PER_PAGE', 10);
define('MAX_TASK_LENGTH', 1000);
define('MAX_CATEGORY_LENGTH', 50);
define('MAX_TAGS_LENGTH', 255);

// API settings
define('API_RATE_LIMIT', 100); // requests per hour
define('API_TOKEN_EXPIRY', 86400); // 24 hours

// File upload settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);
define('UPLOAD_PATH', APP_ROOT . '/uploads');