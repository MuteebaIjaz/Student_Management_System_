<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth_helper.php';

// Redirect if already logged in
redirectIfLoggedIn();

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

if (isset($_POST['Login'])) {
    $Email = $_POST['Email'];
    $Password = $_POST['Password'];

    try {
        // SECURE PDO PREPARED STATEMENT
        $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = ?");
        $stmt->execute([$Email]);
        $user = $stmt->fetch();

        if ($user && password_verify($Password, $user['Password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['user_role'] = $user['Role'];

            // Role-based redirection
            switch ($user['Role']) {
                case 'admin':
                    header("Location: " . BASE_URL . "admin/admin.php");
                    break;
                case 'teacher':
                    header("Location: " . BASE_URL . "teacher/teacher.php");
                    break;
                case 'student':
                    if ($user['Status'] == "Approved") {
                        if ($user['profile_status'] == 0) {
                            header("Location: " . BASE_URL . "Complete_profile.php");
                        } else {
                            header("Location: " . BASE_URL . "student/student.php");
                        }
                    } else {
                        $_SESSION['error'] = "Your account is not approved yet!";
                        header("Location: " . BASE_URL . "Pending.php");
                    }
                    break;
            }
            exit();
        } else {
            $error = "Invalid Email or Password!";
        }
    } catch (Exception $e) {
        $error = "System error. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Zenith Learn</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
</head>
<body>
    <main class="auth-wrapper">
        <div class="glass-card text-center">
            <img src="<?php echo BASE_URL; ?>assets/images/Logo.png" alt="Logo" class="auth-logo">
            <h2 class="fw-bold mb-1">Welcome Back</h2>
            <p class="text-muted mb-4">Sign in to Zenith Learn SMS</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small mb-4"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success py-2 small mb-4"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="" method="post" class="text-start">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input type="email" class="form-control" name="Email" placeholder="name@example.com" required>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label small fw-bold">Password</label>
                        <a href="#" class="small text-primary text-decoration-none">Forgot?</a>
                    </div>
                    <input type="password" class="form-control" name="Password" placeholder="••••••••" required>
                </div>
                <div class="mb-4 d-flex align-items-center">
                    <input type="checkbox" class="form-check-input me-2" id="remember">
                    <label class="form-check-label small text-muted" for="remember">Keep me signed in</label>
                </div>
                <button type="submit" name="Login" class="btn btn-primary w-100 mb-3">
                    Sign In
                </button>
            </form>
            
            <p class="small text-muted mb-0">
                Don't have an account? 
                <a href="Register.php" class="text-primary fw-bold text-decoration-none">Create Account</a>
            </p>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>feather.replace();</script>
</body>
</html>
