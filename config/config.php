<?php
/**
 * Student Management System - Global Configuration
 */

// Base URL (Always include trailing slash)
define('BASE_URL', 'http://localhost/Student_Management_System/');

// Database Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_management_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Session Security Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Global Constants
define('APP_NAME', 'Zenith Learn');
define('APP_TAGLINE', 'Elite Student Management System');

?>
