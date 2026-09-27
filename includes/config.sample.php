<?php
// NikahConnect Sample Configuration
// Copy this file to config.php and update your database credentials

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nikahconnect');

define('SITE_NAME', 'NikahConnect');
define('SITE_URL', 'http://localhost/nikahconnect');
define('SITE_EMAIL', 'info@nikahconnect.com');

// Session
session_start();

// Timezone
date_default_timezone_set('Asia/Karachi');

// Error reporting (set display_errors to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
