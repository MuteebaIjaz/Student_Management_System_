<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('teacher');

$pageTitle = "Academic Assignments";
$teacher_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.class_name,
            c.section,
            s.subject_name,
            s.code,
            s.type,
            cst.id AS cst_id,
            (SELECT COUNT(*) FROM students WHERE class_id = c.class_id) as student_count
        FROM class_subject_teacher cst
        JOIN subject s ON s.subject_id = cst.subject_id
        JOIN classes c ON c.class_id = cst.class_id
        WHERE cst.teacher_id = ?
    ");
    $stmt->execute([$teacher_id]);
    $assignedClasses = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error fetching assignments: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/teacher_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">My Courses</h4>
                    <p class="text-muted small">Overview of your pedagogical assignments and class rosters.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <?php if (count($assignedClasses) > 0): ?>
                <div class="row g-4">
                    <?php foreach ($assignedClasses as $row): ?>
                        <div class="col-xl-4 col-md-6">
                            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: var(--radius);">
                                <!-- Decorative accent -->
                                <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: var(--primary);"></div>
                                
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <span class="badge bg-primary-soft text-primary px-3 rounded-pill mb-2 small fw-bold">
                                                <?php echo htmlspecialchars($row['code']); ?>
                                            </span>
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($row['subject_name']); ?></h5>
                                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($row['class_name'] . ' - Section ' . $row['section']); ?></p>
                                        </div>
                                        <div class="text-end">
                                            <div class="bg-gray-100 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <i class="feather-book-open text-muted"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="py-3 border-top border-bottom my-3">
                                        <div class="row text-center">
                                            <div class="col-6 border-end">
                                                <div class="fw-bold text-dark"><?php echo $row['student_count']; ?></div>
                                                <div class="text-muted fs-11 text-uppercase">Students</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="fw-bold text-dark text-capitalize"><?php echo strtolower($row['type']); ?></div>
                                                <div class="text-muted fs-11 text-uppercase">Type</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="mark_attendance.php?class_id=<?php echo $row['cst_id']; ?>" class="btn btn-dark btn-sm py-2 shadow-sm">
                                            <i class="feather-check-circle me-1"></i> Attendance
                                        </a>
                                        <a href="result.php?class_id=<?php echo $row['cst_id']; ?>" class="btn btn-outline-primary btn-sm py-2">
                                            <i class="feather-award me-1"></i> Add Results
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 mt-5">
                    <div class="opacity-10 mb-4"><i class="feather-box" style="font-size: 5rem;"></i></div>
                    <h5 class="text-muted fw-bold">You are not currently assigned to any courses.</h5>
                    <p class="small text-muted">Contact the administrator if you believe this is an error.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
