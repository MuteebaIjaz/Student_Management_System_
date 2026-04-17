<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/send_email.php';
require_once __DIR__ . '/../includes/auth_helper.php';

// Ensure only admin can perform actions
protectPage('admin');

if (isset($_GET['approve'])) {
    $id = $_GET['approve'];

    try {
        // Fetch student details
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();

        if ($student) {
            // Update status
            $update = $pdo->prepare("UPDATE users SET Status = 'approved' WHERE user_id = ?");
            $update->execute([$id]);

            // Email Notification
            $subject = "Account Approved - Zenith Learn";
            $message = "
                <h3 style='font-family: sans-serif;'>Hello {$student['Name']},</h3>
                <p style='font-family: sans-serif;'>Welcome to Zenith Learn! Your account has been <b style='color:green;'>APPROVED</b>.</p>
                <p style='font-family: sans-serif;'>You can now login and explore your academic dashboard.</p>
                <br>
                <a href='" . BASE_URL . "Login.php' style='background-color:#6366f1; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; font-family:sans-serif;'>Login Now</a>
            ";

            sendEmail($student['Email'], $student['Name'], $subject, $message);
            $_SESSION['success'] = "Student approved and email notification sent.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Action failed: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "admin/registration_request.php");
    exit();
}

if (isset($_GET['reject'])) {
    $id = $_GET['reject'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();

        if ($student) {
            $update = $pdo->prepare("UPDATE users SET Status = 'Rejected' WHERE user_id = ?");
            $update->execute([$id]);

            $subject = "Account Update - Zenith Learn";
            $message = "
                <h3 style='font-family: sans-serif;'>Hello {$student['Name']},</h3>
                <p style='font-family: sans-serif;'>We regret to inform you that your registration request has been <b style='color:red;'>REJECTED</b>.</p>
                <p style='font-family: sans-serif;'>If you believe this is a mistake, please contact the administration office.</p>
            ";

            sendEmail($student['Email'], $student['Name'], $subject, $message);
            $_SESSION['error'] = "Student request rejected.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Action failed: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "admin/registration_request.php");
    exit();
}
?>
