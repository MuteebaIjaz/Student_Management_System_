<?php
require_once __DIR__ . '/config/config.php';
// No auth check here since students land here before approval
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Pending | Zenith Learn</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
</head>
<body>
    <main class="auth-wrapper">
        <div class="glass-card text-center py-5">
            <div class="mb-4">
                <i class="feather-clock text-warning" style="font-size: 3.5rem;"></i>
            </div>
            <h2 class="fw-bold mb-3">Verification Pending</h2>
            <p class="text-muted mb-4 px-3">
                Your registration request has been received. Our administrative team is currently reviewing your application.
            </p>
            
            <div class="bg-gray-100 rounded-pill py-3 px-4 mb-4 mx-3">
                <p class="small text-dark mb-0 fw-bold">
                    <i class="feather-mail me-2"></i> We will notify you via email once approved.
                </p>
            </div>
            
            <p class="small text-muted mb-5">Thank you for your patience.</p>
            
            <div class="px-3">
                <a href="<?php echo BASE_URL; ?>Login.php" class="btn btn-primary w-100 fw-bold shadow-sm">
                    Return to Login
                </a>
            </div>
        </div>
    </main>
</body>
</html>
