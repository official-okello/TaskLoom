<?php
// Central bootstrap for the TaskLoom app
// Sets up sessions and common includes.
define('APP_ROOT', __DIR__);

// Start secure session and related settings
require_once __DIR__ . '/includes/config_session.inc.php';

// CSRF helpers
require_once __DIR__ . '/includes/csrf.inc.php';

// For flash messages
require_once __DIR__ . '/includes/flash.inc.php';

// For Task management
require_once __DIR__ . '/includes/TaskManager.php';