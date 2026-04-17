<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth_helper.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

if (isset($_POST['Save_Profile'])) {
    $class_id = $_POST['class_id'];
    $RollNo = trim($_POST['RollNo']);
    $Gender = $_POST['Gender'];
    $DOB = $_POST['DOB'];
    $PhoneNo = trim($_POST['PhoneNo']);
    $Address = trim($_POST['Address']);
    
    // Image Handling
    $fileName = $_FILES['Profile_Image']['name'];
    $tmp = $_FILES['Profile_Image']['tmp_name'];
    $fileSize = $_FILES['Profile_Image']['size'];
    $uploadDir = __DIR__ . "/assets/images/profile/";
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $typesAllowed = ['png', 'jpeg', 'jpg'];

    if (!in_array($fileExt, $typesAllowed)) {
        $_SESSION['error'] = "Only JPG, JPEG & PNG files are allowed.";
        header("Location: Complete_profile.php");
        exit();
    }

    if ($fileSize > 2 * 1024 * 1024) {
        $_SESSION['error'] = "Image size must be less than 2MB.";
        header("Location: Complete_profile.php");
        exit();
    }

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFileName = "student_" . $user_id . "_" . time() . "." . $fileExt;

    if (move_uploaded_file($tmp, $uploadDir . $newFileName)) {
        try {
            // Check if profile exists
            $check = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
            $check->execute([$user_id]);
            
            if ($check->fetch()) {
                $_SESSION['error'] = "Profile already completed!";
                header("Location: " . BASE_URL . "student/student.php");
                exit();
            }

            // Insert Student Records
            $pdo->beginTransaction();

            $insert = $pdo->prepare("INSERT INTO students (user_id, Roll_no, class_id, gender, dob, phone, address, Profile_Image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$user_id, $RollNo, $class_id, $Gender, $DOB, '+92' . $PhoneNo, $Address, $newFileName]);

            $updateUser = $pdo->prepare("UPDATE users SET profile_status = 1 WHERE user_id = ?");
            $updateUser->execute([$user_id]);

            $pdo->commit();

            header("Location: " . BASE_URL . "student/student.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Data entry error: " . $e->getMessage();
            header("Location: Complete_profile.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Failed to upload image. Please check folder permissions.";
        header("Location: Complete_profile.php");
        exit();
    }
}

// Fetch Classes for dropdown
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complete Your Profile | Zenith Learn</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <style>
        body { background: #f8f9fa; font-family: 'Inter', sans-serif; }
        .auth-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .glass-card { background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); width: 100%; max-width: 600px; padding: 3rem; border: 1px solid #eee; }
        .auth-logo { width: 60px; height: 60px; object-fit: contain; }
        .form-control, .form-select { border-radius: 10px; padding: 0.75rem 1rem; border: 1px solid #e0e0e0; font-size: 0.9rem; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
        .btn-primary { border-radius: 10px; padding: 0.75rem; font-weight: 700; background: var(--primary); border: none; }
        .btn-primary:hover { background: #4338ca; transform: translateY(-1px); }
    </style>
</head>
<body>
    <main class="auth-wrapper">
        <div class="glass-card">
            <div class="text-center mb-4">
                <img src="<?php echo BASE_URL; ?>assets/images/favicon.png" alt="Logo" class="auth-logo mb-3">
                <h2 class="fw-bold mb-1">Final Step</h2>
                <p class="text-muted">Complete your academic profile to continue</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small mb-4 border-0"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Roll Number</label>
                        <input type="text" class="form-control" name="RollNo" placeholder="e.g. 2024-CS-01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Gender</label>
                        <select name="Gender" class="form-select" required>
                            <option value="" disabled selected>Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Assigned Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="" disabled selected>Select Class & Section</option>
                            <?php foreach ($classes as $row): ?>
                                <option value="<?php echo $row['class_id']; ?>">
                                    <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Date of Birth</label>
                        <input type="date" class="form-control" name="DOB" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text small bg-light">+92</span>
                            <input type="text" name="PhoneNo" class="form-control" placeholder="3XXXXXXXXX" pattern="[0-9]{10}" maxlength="10" required title="Please enter 10 digits starting after +92">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Permanent Address</label>
                        <input type="text" class="form-control" name="Address" placeholder="123 Academic Way, City" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Profile Photo</label>
                        <input type="file" name="Profile_Image" class="form-control" required accept="image/png, image/jpeg">
                        <div class="form-text fs-11 mt-1 text-muted">Recommended: Square PNG or JPG, Max 2MB.</div>
                    </div>
                </div>
                
                <button type="submit" name="Save_Profile" class="btn btn-primary w-100 mt-4 py-2 fw-bold text-white">
                    Finish & Access Dashboard
                </button>
            </form>
        </div>
    </main>
</body>
</html>
