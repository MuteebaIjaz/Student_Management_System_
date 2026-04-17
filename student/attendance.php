<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('student');

$pageTitle = "Attendance Insights";
$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch Student/Class info
    $studentStmt = $pdo->prepare("SELECT student_id, class_id FROM students WHERE user_id = ?");
    $studentStmt->execute([$user_id]);
    $student = $studentStmt->fetch();

    if (!$student) {
        // Redirect to profile completion if record is missing
        header("Location: ../Complete_profile.php");
        exit();
    }

    $student_id = $student['student_id'];

    // 2. Fetch Subject-wise Attendance
    $attStmt = $pdo->prepare("
        SELECT 
            s.subject_name, s.code, s.type,
            MAX(u.Name) AS teacher_name,
            COUNT(a.attendance_id) AS total_held,
            SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS attended
        FROM attendance a
        JOIN subject s ON s.subject_id = a.subject_id
        JOIN users u ON u.user_id = a.teacher_id
        WHERE a.student_id = ?
        GROUP BY a.subject_id, s.subject_name, s.code, s.type
    ");
    $attStmt->execute([$student_id]);
    $subjectAttendance = $attStmt->fetchAll();

    // 3. Fetch Recent Attendance Log (Last 15 records)
    $logStmt = $pdo->prepare("
        SELECT a.date, a.status, s.subject_name 
        FROM attendance a 
        JOIN subject s ON a.subject_id = s.subject_id 
        WHERE a.student_id = ? 
        ORDER BY a.date DESC LIMIT 15
    ");
    $logStmt->execute([$student_id]);
    $recentLog = $logStmt->fetchAll();

    // 4. Totals
    $totalHeld = array_sum(array_column($subjectAttendance, 'total_held'));
    $totalAttended = array_sum(array_column($subjectAttendance, 'attended'));
    $overallPerc = ($totalHeld > 0) ? round(($totalAttended / $totalHeld) * 100, 1) : 0;

} catch (Exception $e) {
    die("Attendance Data Error: " . $e->getMessage());
}

function getAttClass($p) {
    if ($p >= 75) return 'text-success bg-success-soft';
    if ($p >= 60) return 'text-warning bg-warning-soft';
    return 'text-danger bg-danger-soft';
}
?>

<?php include __DIR__ . '/../includes/navbar/student_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Presence Analytics</h4>
                    <p class="text-muted small">Comprehensive monitoring of your academic engagement and participation.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <div class="d-flex align-items-center bg-white p-2 rounded-pill shadow-sm px-3 border">
                    <div class="me-3">
                        <div class="text-muted fs-10 text-uppercase fw-bold">Final Average</div>
                        <div class="fw-bold text-dark"><?php echo $overallPerc; ?>%</div>
                    </div>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="feather-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <!-- Summary Row -->
            <div class="row g-4 mb-4">
                <?php foreach ($subjectAttendance as $row): 
                    $perc = ($row['total_held'] > 0) ? round(($row['attended'] / $row['total_held']) * 100, 1) : 0;
                    $class = getAttClass($perc);
                ?>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                            <div class="card-body p-4">
                                <div class="badge <?php echo $class; ?> rounded-pill mb-2 small fw-bold"><?php echo $perc; ?>%</div>
                                <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['subject_name']); ?></h6>
                                <p class="text-muted fs-11 mb-2"><?php echo htmlspecialchars($row['teacher_name']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small text-muted"><?php echo $row['attended']; ?>/<?php echo $row['total_held']; ?> Lect.</div>
                                    <div class="progress flex-grow-1 ms-3" style="height: 4px;">
                                        <div class="progress-bar <?php echo str_replace('text', 'bg', explode(' ', $class)[0]); ?>" style="width: <?php echo $perc; ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <!-- Detailed History -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="fw-bold mb-0">Recent Activity Log</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase">Date</th>
                                            <th class="py-3 text-muted small text-uppercase">Course Name</th>
                                            <th class="pe-4 py-3 text-end text-muted small text-uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($recentLog) > 0): ?>
                                            <?php foreach ($recentLog as $row): ?>
                                                <tr>
                                                    <td class="ps-4 fw-bold small"><?php echo date('D, M d Y', strtotime($row['date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                                    <td class="pe-4 text-end">
                                                        <?php if ($row['status'] == 'Present'): ?>
                                                            <span class="badge bg-success-soft text-success px-3 rounded-pill"><i class="feather-check me-1"></i> Present</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger-soft text-danger px-3 rounded-pill"><i class="feather-x me-1"></i> Absent</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center py-5">No attendance records found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insights Sidebar -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm bg-dark text-white" style="border-radius: var(--radius);">
                        <div class="card-body p-4 text-center">
                            <div class="bg-white-50 rounded-circle p-3 mx-auto mb-3" style="width: 60px; height: 60px;">
                                <i class="feather-info fs-4 text-white"></i>
                            </div>
                            <h6 class="fw-bold">Attendance Policy</h6>
                            <p class="small opacity-75">Maintain a minimum of 75% attendance in each course to be eligible for final examinations.</p>
                            <hr class="border-white opacity-10">
                            <div class="text-start">
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span>Safe Standing (75%+)</span>
                                    <span class="badge bg-success">Success</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span>Probation (60%-74%)</span>
                                    <span class="badge bg-warning">Warning</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span>Ineligible (<60%)</span>
                                    <span class="badge bg-danger">Critical</span>
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
