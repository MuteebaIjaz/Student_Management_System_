<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth_helper.php';

redirectIfLoggedIn();

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

if (isset($_POST['Register'])) {
    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Password = $_POST['Password'];
    $ConfirmPass = $_POST['ConfirmPass'];
    $Role = "student";
    $Status = "pending";

    if ($Password !== $ConfirmPass) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if email exists using PDO
            $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = ?");
            $stmt->execute([$Email]);
            if ($stmt->fetch()) {
                $error = "Email already registered.";
            } else {
                $hashed_password = password_hash($Password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO users (Name, Email, Password, Role, Status) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([$Name, $Email, $hashed_password, $Role, $Status]);
                
                $_SESSION['success'] = "Registration successful! Please wait for admin approval.";
                header("Location: " . BASE_URL . "Login.php");
                exit();
            }
        } catch (Exception $e) {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account | Zenith Learn</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
</head>
<body>
    <main class="auth-wrapper">
        <div class="glass-card text-center">
            <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="auth-logo">
            <h2 class="fw-bold mb-1">Join Zenith Learn</h2>
            <p class="text-muted mb-4">Start your academic journey today</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="post" class="text-start">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" class="form-control" name="Name" placeholder="John Doe" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input type="email" class="form-control" name="Email" placeholder="name@example.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password</label>
                    <input type="password" class="form-control" name="Password" placeholder="••••••••" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Confirm Password</label>
                    <input type="password" class="form-control" name="ConfirmPass" placeholder="••••••••" required>
                </div>
                
                <button type="submit" name="Register" class="btn btn-primary w-100 mb-3">
                    Create Account
                </button>
            </form>
            
            <p class="small text-muted mb-0">
                Already have an account? 
                <a href="Login.php" class="text-primary fw-bold text-decoration-none">Sign In</a>
            </p>
        </div>
    </main>
</body>
</html>
