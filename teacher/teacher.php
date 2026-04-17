<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('teacher');

$pageTitle = "Faculty Executive Dashboard";
$teacher_id = $_SESSION['user_id'];

try {
    // 1. Count My Classes
    $classStmt = $pdo->prepare("SELECT COUNT(DISTINCT class_id) FROM class_subject_teacher WHERE teacher_id = ?");
    $classStmt->execute([$teacher_id]);
    $myClassesCount = $classStmt->fetchColumn();

    // 2. Count My Students (Total students in all assigned classes)
    $studentStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT s.student_id) 
        FROM students s
        JOIN class_subject_teacher cst ON s.class_id = cst.class_id
        WHERE cst.teacher_id = ?
    ");
    $studentStmt->execute([$teacher_id]);
    $myStudentsCount = $studentStmt->fetchColumn();

    // 3. Count Recent Announcements
    $announcementStmt = $pdo->query("SELECT COUNT(*) FROM announcements WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recentNotices = $announcementStmt->fetchColumn();

    // 4. Tasks/Pending Results (Placeholder for complex logic, showing simple count for now)
    $pendingResults = 0; // Logic for pending results based on assignments without marks could go here

} catch (Exception $e) {
    die("Dashboard Stats Error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/teacher_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <!-- Page Header -->
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Academic Performance Hub</h4>
                    <p class="text-muted small">Welcome back, <b><?php echo htmlspecialchars($_SESSION['user_name']); ?></b>. Monitoring your academic impact.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <a href="announcements.php?action=add" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
                    <i class="feather-plus me-2"></i>New Broadcast
                </a>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <!-- Stats Ribbon -->
            <div class="row g-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="bg-primary-soft text-primary p-3 rounded-circle me-3">
                                <i class="feather-layers fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo $myClassesCount; ?></h3>
                                <p class="text-muted fs-11 mb-0 text-uppercase fw-bold">Assigned Classes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="bg-success-soft text-success p-3 rounded-circle me-3">
                                <i class="feather-users fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo $myStudentsCount; ?></h3>
                                <p class="text-muted fs-11 mb-0 text-uppercase fw-bold">Total Students</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="bg-warning-soft text-warning p-3 rounded-circle me-3">
                                <i class="feather-bell fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo $recentNotices; ?></h3>
                                <p class="text-muted fs-11 mb-0 text-uppercase fw-bold">Recent Notices</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="bg-danger-soft text-danger p-3 rounded-circle me-3">
                                <i class="feather-award fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo $pendingResults; ?></h3>
                                <p class="text-muted fs-11 mb-0 text-uppercase fw-bold">Results Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="fw-bold mb-0">Upcoming Schedule</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-5 opacity-25">
                                <i class="feather-calendar fs-1 mb-3"></i>
                                <h6>Schedule Module integration pending...</h6>
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
                            <div class="d-grid gap-2">
                                <a href="mark_attendance.php" class="btn btn-outline-primary text-start p-3 border-0 bg-primary-soft">
                                    <i class="feather-check-square me-2"></i> Mark Attendance
                                </a>
                                <a href="result.php" class="btn btn-outline-dark text-start p-3 border-0 bg-gray-100">
                                    <i class="feather-edit me-2"></i> Enter Grades
                                </a>
                                <a href="Students.php" class="btn btn-outline-dark text-start p-3 border-0 bg-gray-100">
                                    <i class="feather-user me-2"></i> Student Search
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
