<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Centralized entry point redirector
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "Login.php");
    exit();
}

$role = $_SESSION['user_role'] ?? '';

switch ($role) {
    case 'admin':
        header("Location: " . BASE_URL . "admin/admin.php");
        break;
    case 'teacher':
        header("Location: " . BASE_URL . "teacher/teacher.php");
        break;
    case 'student':
        header("Location: " . BASE_URL . "student/student.php");
        break;
    default:
        session_destroy();
        header("Location: " . BASE_URL . "Login.php");
        break;
}
exit();
?>
