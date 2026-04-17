<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('student');

$pageTitle = "Academic Achievements";
$user_id = $_SESSION['user_id'];

try {
    // Fetch Student ID and Class
    $studentStmt = $pdo->prepare("SELECT student_id, class_id FROM students WHERE user_id = ?");
    $studentStmt->execute([$user_id]);
    $student = $studentStmt->fetch();

    if (!$student) {
        // Redirect to profile completion if record is missing
        header("Location: ../Complete_profile.php");
        exit();
    }

    $student_id = $student['student_id'];
    $class_id = $student['class_id'];

    // Fetch All Marks
    $marksStmt = $pdo->prepare("
        SELECT 
            s.subject_name, s.code, s.type,
            u.Name AS teacher_name,
            m.exam_type, m.date, m.total_marks, m.marks,
            ROUND((m.marks / m.total_marks) * 100, 1) AS percentage
        FROM marks m
        JOIN subject s ON s.subject_id = m.subject_id
        JOIN class_subject_teacher cst ON cst.subject_id = m.subject_id AND cst.class_id = ?
        JOIN users u ON u.user_id = cst.teacher_id
        WHERE m.student_id = ?
        ORDER BY m.date DESC
    ");
    $marksStmt->execute([$class_id, $student_id]);
    $allMarks = $marksStmt->fetchAll();

    // Grouping by Subject for Summary Cards
    $bySubject = [];
    foreach ($allMarks as $m) {
        $bySubject[$m['subject_name']][] = $m;
    }

    // Overall Calculation
    $totalObtained = array_sum(array_column($allMarks, 'marks'));
    $totalPossible = array_sum(array_column($allMarks, 'total_marks'));
    $overallPerc = ($totalPossible > 0) ? round(($totalObtained / $totalPossible) * 100, 2) : 0;

} catch (Exception $e) {
    die("Result Fetch Error: " . $e->getMessage());
}

function calculateGrade($p) {
    if ($p >= 90) return ['A+', 'bg-success-soft text-success'];
    if ($p >= 80) return ['A', 'bg-success-soft text-success'];
    if ($p >= 70) return ['B+', 'bg-primary-soft text-primary'];
    if ($p >= 60) return ['B', 'bg-primary-soft text-primary'];
    if ($p >= 50) return ['C', 'bg-warning-soft text-warning'];
    if ($p >= 40) return ['D', 'bg-warning-soft text-warning'];
    return ['F', 'bg-danger-soft text-danger'];
}

[$overallGrade, $overallClass] = calculateGrade($overallPerc);
?>

<?php include __DIR__ . '/../includes/navbar/student_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Academic Transcript</h4>
                    <p class="text-muted small">Tracking your journey through educational milestones and excellence.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <div class="d-flex align-items-center">
                    <div class="text-end me-3">
                        <div class="text-muted small text-uppercase fw-bold fs-10">Current Standing</div>
                        <div class="fw-bold fs-4 text-dark"><?php echo $overallPerc; ?>%</div>
                    </div>
                    <div class="badge <?php echo $overallClass; ?> px-4 py-3 rounded-pill fs-5">
                        <?php echo $overallGrade; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <?php if (empty($bySubject)): ?>
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                    <div class="card-body py-5 text-center">
                        <div class="opacity-10 mb-4"><i class="feather-award" style="font-size: 6rem;"></i></div>
                        <h5 class="text-muted fw-bold">Academic records are currently empty.</h5>
                        <p class="small text-muted">Check back later once examination results are published.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Subject Cards -->
                <div class="row g-4 mb-5">
                    <?php foreach ($bySubject as $name => $exams): 
                        $sObt = array_sum(array_column($exams, 'marks'));
                        $sTot = array_sum(array_column($exams, 'total_marks'));
                        $sPerc = round(($sObt / $sTot) * 100, 1);
                        [$sGrade, $sClass] = calculateGrade($sPerc);
                    ?>
                        <div class="col-xl-4 col-md-6">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($name); ?></h6>
                                        <span class="badge <?php echo $sClass; ?> rounded-pill"><?php echo $sGrade; ?></span>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <div>
                                            <div class="fs-4 fw-bold text-primary"><?php echo $sPerc; ?>%</div>
                                            <div class="text-muted small"><?php echo $sObt; ?>/<?php echo $sTot; ?> Total Marks</div>
                                        </div>
                                        <div class="text-end text-muted small">
                                            <?php echo count($exams); ?> Assessments
                                        </div>
                                    </div>
                                    <div class="progress mt-3" style="height: 6px; border-radius: 10px;">
                                        <div class="progress-bar" style="width: <?php echo $sPerc; ?>%; background: var(--primary);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Detailed History -->
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-0">Detailed Assessment History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="ps-4 py-3 text-muted small text-uppercase">Course / Subject</th>
                                        <th class="py-3 text-muted small text-uppercase">Category</th>
                                        <th class="py-3 text-muted small text-uppercase text-center">Score</th>
                                        <th class="py-3 text-muted small text-uppercase text-center">Date</th>
                                        <th class="pe-4 py-3 text-end text-muted small text-uppercase">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allMarks as $row): 
                                        [$rowGrade, $rowClass] = calculateGrade($row['percentage']);
                                    ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['subject_name']); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($row['code'] . ' • ' . $row['teacher_name']); ?></div>
                                            </td>
                                            <td><span class="badge bg-gray-100 text-dark rounded-pill fw-bold"><?php echo htmlspecialchars($row['exam_type']); ?></span></td>
                                            <td class="text-center fw-bold">
                                                <?php echo $row['marks']; ?> / <?php echo $row['total_marks']; ?>
                                                <div class="text-muted fs-11"><?php echo $row['percentage']; ?>%</div>
                                            </td>
                                            <td class="text-center text-muted small"><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                            <td class="pe-4 text-end">
                                                <span class="badge <?php echo $rowClass; ?> px-3 rounded-pill"><?php echo $rowGrade; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
