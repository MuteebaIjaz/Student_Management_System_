<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth_helper.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "Login.php");
    exit();
}

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

if (isset($_POST['Save_Password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    if (strlen($newPassword) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters!";
        header("Location: " . BASE_URL . "change_password.php");
        exit();
    }
    
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: " . BASE_URL . "change_password.php");
        exit();
    }

    try {
        $hashedpassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_first_login = 0 WHERE user_id = ?");
        $stmt->execute([$hashedpassword, $user_id]);

        $_SESSION['success'] = "Password changed successfully! Redirecting...";
        
        // Auto redirect based on role
        $role = $_SESSION['user_role'];
        $redirect = BASE_URL . ($role == 'admin' ? 'admin/admin.php' : ($role == 'teacher' ? 'teacher/teacher.php' : 'student/student.php'));
        
        header("Refresh: 2; URL=" . $redirect);
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to update password. Try again.";
        header("Location: " . BASE_URL . "change_password.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Password Update | Zenith Learn</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
</head>
<body>
    <main class="auth-wrapper">
        <div class="glass-card text-center">
            <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="auth-logo">
            <h2 class="fw-bold mb-1">Security Update</h2>
            <p class="text-muted mb-4">Please set your new secure password</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success py-2 small mb-4 text-start">
                    <i class="feather-check-circle me-2"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" class="text-start">
                <div class="mb-3">
                    <label class="form-label small fw-bold">New Password</label>
                    <input type="password" class="form-control" name="new_password" placeholder="••••••••" required autofocus>
                    <div class="form-text fs-11 text-muted">Minimum 6 characters recommended.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" name="Save_Password" class="btn btn-primary w-100 mb-3">
                    Update Password
                </button>
            </form>
            
            <p class="small text-muted mb-0">
                Logged in as <b><?php echo htmlspecialchars($_SESSION['user_name']); ?></b>
            </p>
        </div>
    </main>
</body>
</html>
