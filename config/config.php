<?php
/**
 * Student Management System - Global Configuration
 */

// Database Settings - Uses Railway variables if they exist, otherwise defaults to local
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'student_management_system');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

// Base URL - On Railway, this should be your public URL
// If the RAILWAY_PUBLIC_DOMAIN variable exists, use it.
$public_url = getenv('RAILWAY_PUBLIC_DOMAIN') 
    ? 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') . '/' 
    : 'http://localhost/Student_Management_System/';

define('BASE_URL', $public_url);

// ... rest of your session security and constants ...
define('APP_NAME', 'Zenith Learn');
define('APP_TAGLINE', 'Elite Student Management System');
?>