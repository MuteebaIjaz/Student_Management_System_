<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db.php';

// Fetch User details for the top bar
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'User';
$user_role = $_SESSION['user_role'] ?? 'Guest';

// Fetch profile picture logic
$Default_Avatar = BASE_URL . "assets/images/profile/default_avatar.png";
$Profile_Picture = $Default_Avatar;

if ($user_id) {
    try {
        if ($user_role === 'student') {
            $stmt = $pdo->prepare("SELECT Profile_Image FROM students WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $student_data = $stmt->fetch();
            if ($student_data && !empty($student_data['Profile_Image'])) {
                $path = "assets/images/profile/" . $student_data['Profile_Image'];
                if (file_exists(__DIR__ . "/../" . $path)) {
                    $Profile_Picture = BASE_URL . $path;
                }
            }
        }
        // Fallback or future role-specific logic (e.g., teacher profile)
    } catch (Exception $e) {
        $Profile_Picture = $Default_Avatar;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $pageTitle ?? 'Zenith Learn'; ?> | SMS</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png" />
    
    <!-- Vendor CSS -->
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/vendors/css/vendors.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/vendors/css/daterangepicker.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/theme.min.css" />
    
    <!-- Premium Custom Styles -->
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/main.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/main.css'); ?>" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
</head>
<body>
