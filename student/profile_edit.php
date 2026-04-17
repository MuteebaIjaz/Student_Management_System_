<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('student');

$pageTitle = "Modify Account Details";
$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

try {
    // 1. Fetch current data
    $stmt = $pdo->prepare("
        SELECT s.*, u.Email, u.Name, s.Profile_Image AS User_Image
        FROM students s 
        JOIN users u ON s.user_id = u.user_id 
        WHERE s.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch();

    if (!$student) {
        // Redirect to profile completion if record is missing
        header("Location: ../Complete_profile.php");
        exit();
    }

    // 2. Fetch Classes for dropdown
    $classesStmt = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC");
    $classes = $classesStmt->fetchAll();

    // 3. Handle Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Update_Profile'])) {
        $class_id = $_POST['class_id'];
        $RollNo   = trim($_POST['RollNo']);
        $Gender   = $_POST['Gender'];
        $DOB      = $_POST['DOB'];
        $PhoneNo  = trim($_POST['PhoneNo']);
        $Address  = trim($_POST['Address']);

        $pdo->beginTransaction();

        $updateStmt = $pdo->prepare("
            UPDATE students SET 
                class_id = ?, Roll_no = ?, gender = ?, 
                dob = ?, phone = ?, address = ? 
            WHERE user_id = ?
        ");
        
        if ($updateStmt->execute([$class_id, $RollNo, $Gender, $DOB, $PhoneNo, $Address, $user_id])) {
            
            // Image Upload Handling
            if (!empty($_FILES['Profile_Image']['name'])) {
                $file = $_FILES['Profile_Image'];
                $allowed = ['jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    if ($file['size'] <= 2 * 1024 * 1024) { // 2MB Limit
                        $newFileName = "student_" . $user_id . "_" . time() . "." . $ext;
                        $uploadPath = __DIR__ . "/../assets/images/profile/" . $newFileName;

                        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                            // Update students table for personal profile image
                            $imgUpdate = $pdo->prepare("UPDATE students SET Profile_Image = ? WHERE user_id = ?");
                            $imgUpdate->execute([$newFileName, $user_id]);
                        } else {
                            $errors[] = "Failed to move uploaded file.";
                        }
                    } else {
                        $errors[] = "Image size must be less than 2MB.";
                    }
                } else {
                    $errors[] = "Invalid image format. Allowed: JPG, PNG.";
                }
            }

            if (empty($errors)) {
                $pdo->commit();
                $_SESSION['success_msg'] = "Profile synchronized successfully.";
                header("Location: profile.php");
                exit();
            } else {
                $pdo->rollBack();
            }
        } else {
            $pdo->rollBack();
            $errors[] = "Database synchronization failed.";
        }
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $errors[] = "System Error: " . $e->getMessage();
}

$currentImage = !empty($student['User_Image']) ? BASE_URL . 'assets/images/profile/' . $student['User_Image'] : $Default_Avatar;
?>

<?php include __DIR__ . '/../includes/navbar/student_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Update Identity</h4>
                    <p class="text-muted small">Maintain your academic profile with accurate and up-to-date information.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-11">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-0">
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="row g-0">
                                    <!-- Left Column: Image -->
                                    <div class="col-md-4 border-end bg-gray-50 p-5 text-center">
                                        <div class="mb-4">
                                            <img src="<?php echo $currentImage; ?>" id="previewImg" class="rounded-circle border border-4 border-white shadow-sm mb-3" style="width: 140px; height: 140px; object-fit: cover;">
                                            <div class="small text-muted mb-3">Professional Identification</div>
                                            <label class="btn btn-dark btn-sm rounded-pill px-4 cursor-pointer">
                                                Change Photo
                                                <input type="file" name="Profile_Image" class="d-none" onchange="previewFile(this)">
                                            </label>
                                            <p class="fs-10 text-muted mt-3">Recommended: Square PNG/JPG, Max 2MB.</p>
                                        </div>
                                    </div>

                                    <!-- Right Column: Fields -->
                                    <div class="col-md-8 p-5">
                                        <?php if (!empty($errors)): ?>
                                            <div class="alert alert-danger border-0 small py-2"><?php echo implode('<br>', $errors); ?></div>
                                        <?php endif; ?>

                                        <div class="row g-4">
                                            <div class="col-md-6 text-start">
                                                <label class="form-label fs-11 fw-bold text-uppercase text-muted">Roll Number</label>
                                                <input type="text" name="RollNo" class="form-control" value="<?php echo htmlspecialchars($student['Roll_no']); ?>" required>
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="form-label fs-11 fw-bold text-uppercase text-muted">Academic Class</label>
                                                <select name="class_id" class="form-select" required>
                                                    <?php foreach ($classes as $c): ?>
                                                        <option value="<?php echo $c['class_id']; ?>" <?php echo ($student['class_id'] == $c['class_id'] ? 'selected' : ''); ?>>
                                                            <?php echo htmlspecialchars($c['class_name'] . ' - ' . $c['section']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="form-label fs-11 fw-bold text-uppercase text-muted">Gender Expression</label>
                                                <select name="Gender" class="form-select" required>
                                                    <option value="Male" <?php echo ($student['gender'] === 'Male' ? 'selected' : ''); ?>>Male</option>
                                                    <option value="Female" <?php echo ($student['gender'] === 'Female' ? 'selected' : ''); ?>>Female</option>
                                                    <option value="Other" <?php echo ($student['gender'] === 'Other' ? 'selected' : ''); ?>>Other</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="form-label fs-11 fw-bold text-uppercase text-muted">Date of Birth</label>
                                                <input type="date" name="DOB" class="form-control" value="<?php echo $student['dob']; ?>" required>
                                            </div>
                                            <div class="col-md-12 text-start">
                                                <label class="form-label fs-11 fw-bold text-uppercase text-muted">Mobile Contact (+92)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-gray-100">+92</span>
                                                    <input type="text" name="PhoneNo" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>" pattern="[0-9]{10}" placeholder="3XXXXXXXXX" required>
                                                </div>
                                            </div>
                                            <div class="col-md-12 text-start">
                                                <label class="form-label fs-11 fw-bold text-uppercase text-muted">Residential Address</label>
                                                <textarea name="Address" class="form-control" rows="3" required><?php echo htmlspecialchars($student['address']); ?></textarea>
                                            </div>
                                        </div>

                                        <div class="mt-5 pt-4 border-top d-flex justify-content-between">
                                            <a href="profile.php" class="btn btn-light rounded-pill px-4 fw-bold">Discard Changes</a>
                                            <button type="submit" name="Update_Profile" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function previewFile(input) {
    var file = input.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function() {
            document.getElementById("previewImg").src = reader.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
