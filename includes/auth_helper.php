<?php
/**
 * Authentication and Role-Based Access Control Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Validates if a user is logged in. 
 * If not, redirects to login page.
 * If role is specified, checks if the user has that role.
 * 
 * @param string|null $requiredRole The role required to access the page (admin, teacher, student)
 */
function protectPage($requiredRole = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "Login.php");
        exit();
    }

    if ($requiredRole !== null && $_SESSION['user_role'] !== $requiredRole) {
        // Redirect to their own dashboard if they try to access another role's page
        switch ($_SESSION['user_role']) {
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
                header("Location: " . BASE_URL . "Login.php");
                break;
        }
        exit();
    }
}

/**
 * Redirects logged-in users away from auth pages (login/register)
 */
function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        switch ($_SESSION['user_role']) {
            case 'admin':
                header("Location: " . BASE_URL . "admin/admin.php");
                break;
            case 'teacher':
                header("Location: " . BASE_URL . "teacher/teacher.php");
                break;
            case 'student':
                header("Location: " . BASE_URL . "student/student.php");
                break;
        }
        exit();
    }
}

?>
