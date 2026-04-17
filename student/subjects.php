<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('student');

$pageTitle = "My Learning Path";
$user_id = $_SESSION['user_id'];

try {
    // 1. Get Class ID
    $studentStmt = $pdo->prepare("SELECT class_id FROM students WHERE user_id = ?");
    $studentStmt->execute([$user_id]);
    $student = $studentStmt->fetch();

    if (!$student) {
        // Redirect to profile completion if record is missing
        header("Location: ../Complete_profile.php");
        exit();
    }

    $class_id = $student['class_id'];

    // 2. Get Class Details
    $classStmt = $pdo->prepare("SELECT * FROM classes WHERE class_id = ?");
    $classStmt->execute([$class_id]);
    $class = $classStmt->fetch();

    // 3. Get Subjects and Teachers
    $subjectsStmt = $pdo->prepare("
        SELECT s.*, u.Name AS teacher_name 
        FROM class_subject_teacher cst
        JOIN subject s ON s.subject_id = cst.subject_id
        JOIN users u ON u.user_id = cst.teacher_id
        WHERE cst.class_id = ?
    ");
    $subjectsStmt->execute([$class_id]);
    $subjects = $subjectsStmt->fetchAll();

} catch (Exception $e) {
    die("Error fetching subjects: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/student_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Academic Curriculum</h4>
                    <p class="text-muted small">Explore and manage your enrolled courses for <b><?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section'] ?? ''); ?></b>.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold">
                    <?php echo count($subjects); ?> Active Courses
                </span>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row g-4">
                <?php if (count($subjects) > 0): ?>
                    <?php foreach ($subjects as $row): 
                        $words = explode(' ', $row['teacher_name']);
                        $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                        $isCore = strtolower($row['type']) === 'core';
                        $avatarUrl = null; // Teachers don't have profile images currently
                    ?>
                        <div class="col-xl-4 col-lg-6">
                            <div class="card border-0 shadow-sm h-100 hover-shadow transition-all" style="border-radius: var(--radius); overflow: hidden;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-primary-soft text-primary rounded-lg d-flex align-items-center justify-content-center fw-bold fs-4 me-3" style="width: 50px; height: 50px;">
                                            <?php echo strtoupper($row['subject_name'][0]); ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($row['subject_name']); ?></h6>
                                            <span class="text-muted small fw-medium"><?php echo htmlspecialchars($row['code']); ?></span>
                                        </div>
                                        <div class="ms-auto">
                                            <span class="badge <?php echo $isCore ? 'bg-success-soft text-success' : 'bg-info-soft text-info'; ?> rounded-pill small">
                                                <?php echo ucfirst($row['type']); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-3 bg-gray-100 rounded-lg mb-4">
                                        <div class="d-flex align-items-center">
                                            <?php if ($avatarUrl): ?>
                                                <img src="<?php echo $avatarUrl; ?>" class="rounded-circle me-3 border border-white shadow-sm" style="width: 35px; height: 35px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center me-3 small" style="width: 35px; height: 35px;">
                                                    <?php echo $initials; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fs-11 text-muted text-uppercase fw-bold">Assigned Faculty</div>
                                                <div class="fw-bold text-dark small"><?php echo htmlspecialchars($row['teacher_name']); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="result.php" class="btn btn-outline-primary btn-sm rounded-pill fw-bold">
                                            <i class="feather-award me-1"></i> View Performance
                                        </a>
                                        <a href="attendance.php" class="btn btn-light btn-sm rounded-pill fw-bold text-muted">
                                            <i class="feather-user-check me-1"></i> Check Attendance
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="opacity-10 mb-4"><i class="feather-book-open" style="font-size: 5rem;"></i></div>
                        <h5 class="text-muted">No subjects assigned to your class yet.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
