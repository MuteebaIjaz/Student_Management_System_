<?php
/**
 * Student Management System - InfinityFree Configuration
 */

// These values are found in your InfinityFree Control Panel
define('DB_HOST', 'sql201.infinityfree.com'); // Replace with your 'MySQL Hostname'
define('DB_NAME', 'if0_41747606_student_management_system');
define('DB_USER', 'if0_41747606');        // Replace with your 'vPanel Username'
define('DB_PASS', 'Pakistan24may');  // Replace with your hosting account password
define('DB_PORT', '3306');                   // Default is usually 3306

// Base URL for InfinityFree
// Replace with your actual assigned sub-domain
define('BASE_URL', 'http://muteebaijaz.infinityfreeapp.com/');

define('APP_NAME', 'Zenith Learn');
define('APP_TAGLINE', 'Elite Student Management System');

// Connect using PDO (Recommended)
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>