<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('student');

$pageTitle = "Student Executive Hub";
$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch Student Core Info
    $studentStmt = $pdo->prepare("
        SELECT s.*, c.class_name, c.section 
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.class_id 
        WHERE s.user_id = ?
    ");
    $studentStmt->execute([$user_id]);
    $studentData = $studentStmt->fetch();

    if (!$studentData) {
        // Redirect to profile completion if student record doesn't exist yet
        header("Location: ../Complete_profile.php");
        exit();
    }

    $class_id = $studentData['class_id'] ?? null;

    // 2. Count Subjects
    $subjectCount = 0;
    if ($class_id) {
        $subStmt = $pdo->prepare("SELECT COUNT(*) FROM class_subject_teacher WHERE class_id = ?");
        $subStmt->execute([$class_id]);
        $subjectCount = $subStmt->fetchColumn();
    }

    // 3. Attendance Percentage (Simplified)
    $attendancePerc = 100;
    if ($class_id) {
        $attStmt = $pdo->prepare("
            SELECT 
                (SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 
            FROM attendance 
            WHERE student_id = ?
        ");
        $attStmt->execute([$studentData['student_id']]);
        $res = $attStmt->fetchColumn();
        if ($res !== null) $attendancePerc = round($res, 1);
    }

    // 4. Pending Fees
    $pendingFees = 0;
    if ($class_id) {
        // Calculate Total Dues from fee_types for students class
        $totalDuesStmt = $pdo->prepare("SELECT SUM(amount) FROM fee_types WHERE class_id = ?");
        $totalDuesStmt->execute([$class_id]);
        $totalDues = $totalDuesStmt->fetchColumn() ?? 0;

        // Calculate Total Paid from fee_payments
        $totalPaidStmt = $pdo->prepare("SELECT SUM(amount_paid) FROM fee_payments WHERE student_id = ?");
        $totalPaidStmt->execute([$studentData['student_id']]);
        $totalPaid = $totalPaidStmt->fetchColumn() ?? 0;

        $pendingFees = max(0, $totalDues - $totalPaid);
    }

} catch (Exception $e) {
    die("Student Dashboard Error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/student_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <!-- Page Header -->
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Academic Overview</h4>
                    <p class="text-muted small">Welcome back, <b><?php echo htmlspecialchars($_SESSION['user_name']); ?></b>. Here's your current academic standing.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold">
                    <i class="feather-book-open me-1"></i> <?php echo htmlspecialchars($studentData['class_name'] . ' - ' . $studentData['section']); ?>
                </span>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <!-- Stats Grid -->
            <div class="row g-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="bg-primary-soft text-primary p-3 rounded-circle">
                                    <i class="feather-book fs-4"></i>
                                </div>
                                <div class="text-end">
                                    <h3 class="fw-bold mb-0"><?php echo $subjectCount; ?></h3>
                                    <p class="text-muted fs-11 text-uppercase fw-bold mb-0">Courses</p>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-primary" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="bg-success-soft text-success p-3 rounded-circle">
                                    <i class="feather-check-circle fs-4"></i>
                                </div>
                                <div class="text-end">
                                    <h3 class="fw-bold mb-0"><?php echo $attendancePerc; ?>%</h3>
                                    <p class="text-muted fs-11 text-uppercase fw-bold mb-0">Attendance</p>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $attendancePerc; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="bg-warning-soft text-warning p-3 rounded-circle">
                                    <i class="feather-dollar-sign fs-4"></i>
                                </div>
                                <div class="text-end">
                                    <h3 class="fw-bold mb-0">Rs. <?php echo number_format($pendingFees); ?></h3>
                                    <p class="text-muted fs-11 text-uppercase fw-bold mb-0">Outstanding</p>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo ($pendingFees > 0 ? 50 : 100); ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="bg-info-soft text-info p-3 rounded-circle">
                                    <i class="feather-award fs-4"></i>
                                </div>
                                <div class="text-end">
                                    <h3 class="fw-bold mb-0">Active</h3>
                                    <p class="text-muted fs-11 text-uppercase fw-bold mb-0">Enrollment</p>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius); background: linear-gradient(135deg, #fff 0%, #f9fafb 100%);">
                        <div class="card-body p-5">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="fw-bold text-dark">Prepare for Semester Finals</h3>
                                    <p class="text-muted">High-impact study materials and previous examination papers are now available in your course portal. Boost your GPA by starting early.</p>
                                    <a href="subjects.php" class="btn btn-dark px-4 py-2 mt-2 rounded-pill">Explore Courses</a>
                                </div>
                                <div class="col-md-4 text-center d-none d-md-block">
                                    <div class="bg-primary-soft rounded-circle p-4 mx-auto" style="width: 150px; height:150px;">
                                        <i class="feather-file-text text-primary" style="font-size: 80px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="fw-bold mb-0">Quick Access</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-grid gap-3">
                                <a href="marks.php" class="btn border-0 text-start p-3 bg-gray-100 rounded-lg hover-shadow transition-all">
                                    <div class="d-flex align-items-center">
                                        <i class="feather-activity me-3 text-primary fs-5"></i>
                                        <div>
                                            <div class="fw-bold small text-dark">Academic Report</div>
                                            <div class="fs-11 text-muted">View your latest grades</div>
                                        </div>
                                    </div>
                                </a>
                                <a href="attendance.php" class="btn border-0 text-start p-3 bg-gray-100 rounded-lg hover-shadow transition-all">
                                    <div class="d-flex align-items-center">
                                        <i class="feather-user-check me-3 text-success fs-5"></i>
                                        <div>
                                            <div class="fw-bold small text-dark">Attendance Log</div>
                                            <div class="fs-11 text-muted">Monitor your presence</div>
                                        </div>
                                    </div>
                                </a>
                                <a href="fee_details.php" class="btn border-0 text-start p-3 bg-gray-100 rounded-lg hover-shadow transition-all">
                                    <div class="d-flex align-items-center">
                                        <i class="feather-credit-card me-3 text-warning fs-5"></i>
                                        <div>
                                            <div class="fw-bold small text-dark">Financial Ledger</div>
                                            <div class="fs-11 text-muted">Dues and payment history</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
