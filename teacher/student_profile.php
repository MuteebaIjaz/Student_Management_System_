<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('teacher');

$student_user_id = $_GET['id'] ?? null;
$teacher_id = $_SESSION['user_id'];

if (!$student_user_id) {
    header("Location: Students.php");
    exit();
}

try {
    // Fetch detailed student info including class
    $stmt = $pdo->prepare("
        SELECT u.Name, u.Email, s.Roll_no, c.class_name, c.section, s.Profile_Image, s.dob, s.gender, s.phone
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        JOIN classes c ON s.class_id = c.class_id
        WHERE s.student_id = ?
    ");
    $stmt->execute([$student_user_id]);
    $student = $stmt->fetch();

    if (!$student) {
        die("Student not found.");
    }

    $pageTitle = "Student Profile: " . htmlspecialchars($student['Name']);
} catch (Exception $e) {
    die("Data error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/teacher_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 small mb-2">
                        <li class="breadcrumb-item"><a href="Students.php">Students</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profile View</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0">Academic Profile</h4>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Profile Summary Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius-lg);">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4 position-relative d-inline-block">
                                <?php if (!empty($student['Profile_Image'])): ?>
                                    <img src="<?php echo BASE_URL . 'assets/images/profile/' . $student['Profile_Image']; ?>" class="rounded-circle shadow" style="width: 150px; height: 150px; object-fit: cover; border: 5px solid var(--white);">
                                <?php else: ?>
                                    <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 150px; height: 150px; font-size: 4rem; border: 5px solid var(--white);">
                                        <?php echo strtoupper(substr($student['Name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($student['Name']); ?></h4>
                            <p class="text-muted small mb-4">Roll Number: <span class="fw-bold text-primary"><?php echo htmlspecialchars($student['Roll_no']); ?></span></p>
                            
                            <div class="d-grid gap-2">
                                <a href="mailto:<?php echo $student['Email']; ?>" class="btn btn-primary rounded-pill">
                                    <i class="feather-mail me-2"></i>Send Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Information -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius-lg);">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="fw-bold mb-0">Institutional Record</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Full Legal Name</label>
                                    <div class="bg-gray-100 p-3 rounded-pill fw-semibold text-dark">
                                        <?php echo htmlspecialchars($student['Name']); ?>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Authorized Email</label>
                                    <div class="bg-gray-100 p-3 rounded-pill fw-semibold text-dark">
                                        <?php echo htmlspecialchars($student['Email']); ?>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Primary Enrollment</label>
                                    <div class="bg-primary-soft p-3 rounded-pill fw-bold text-primary">
                                        <?php echo htmlspecialchars($student['class_name'] . " (" . $student['section'] . ")"); ?>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Date of Birth</label>
                                    <div class="bg-gray-100 p-3 rounded-pill fw-semibold text-dark">
                                        <?php echo !empty($student['dob']) ? date('F d, Y', strtotime($student['dob'])) : '<span class="text-muted fst-italic">Not provided</span>'; ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($student['gender']) || !empty($student['phone'])): ?>
                                <div class="col-sm-6">
                                    <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Gender</label>
                                    <div class="bg-gray-100 p-3 rounded-pill fw-semibold text-dark">
                                        <?php echo !empty($student['gender']) ? htmlspecialchars($student['gender']) : '<span class="text-muted fst-italic">Not provided</span>'; ?>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Contact Number</label>
                                    <div class="bg-gray-100 p-3 rounded-pill fw-semibold text-dark">
                                        <?php echo !empty($student['phone']) ? htmlspecialchars($student['phone']) : '<span class="text-muted fst-italic">Not provided</span>'; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="col-12 mt-3">
                                    <div class="alert bg-gray-100 border-0 p-4" style="border-left: 4px solid var(--primary) !important;">
                                        <div class="d-flex align-items-center">
                                            <i class="feather-info fs-4 text-primary me-3"></i>
                                            <div>
                                                <h6 class="fw-bold mb-1">Academic Status Note</h6>
                                                <p class="small text-muted mb-0">This student is currently in good standing. Attendance and grading records can be accessed through the designated faculty modules.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
