<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('teacher');

$pageTitle = "Student Roster";
$teacher_id = $_SESSION['user_id'];
$class_id = $_GET['class_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;

try {
    // Fetch assigned classes
    $classStmt = $pdo->prepare("
        SELECT DISTINCT c.class_id, c.class_name, c.section 
        FROM classes c 
        JOIN class_subject_teacher cst ON cst.class_id = c.class_id
        WHERE cst.teacher_id = ?
    ");
    $classStmt->execute([$teacher_id]);
    $myClasses = $classStmt->fetchAll();

    $subjects = [];
    if ($class_id) {
        $subStmt = $pdo->prepare("
            SELECT s.subject_id, s.subject_name 
            FROM subject s 
            JOIN class_subject_teacher cst ON s.subject_id = cst.subject_id 
            WHERE cst.teacher_id = ? AND cst.class_id = ?
        ");
        $subStmt->execute([$teacher_id, $class_id]);
        $subjects = $subStmt->fetchAll();
    }

    $students = [];
    if ($class_id && $subject_id) {
        $studentStmt = $pdo->prepare("
            SELECT s.student_id, s.Roll_no, u.Name, u.Email 
            FROM students s 
            JOIN users u ON s.user_id = u.user_id 
            WHERE s.class_id = ? 
            ORDER BY s.Roll_no ASC
        ");
        $studentStmt->execute([$class_id]);
        $students = $studentStmt->fetchAll();
    }
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
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Academic Cohorts</h4>
                    <p class="text-muted small">Access student profiles and enrollment data across your assigned divisions.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <!-- Filter Section -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Academic Class</label>
                            <select name="class_id" class="form-control" onchange="this.form.submit()" required>
                                <option value="" disabled <?php echo !$class_id ? 'selected' : ''; ?>>Select a class...</option>
                                <?php foreach ($myClasses as $row): ?>
                                    <option value="<?php echo $row['class_id']; ?>" <?php echo ($class_id == $row['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Assigned Subject</label>
                            <select name="subject_id" class="form-control" required <?php echo !$class_id ? 'disabled' : ''; ?>>
                                <option value="" disabled <?php echo !$subject_id ? 'selected' : ''; ?>>Select a subject...</option>
                                <?php foreach ($subjects as $row): ?>
                                    <option value="<?php echo $row['subject_id']; ?>" <?php echo ($subject_id == $row['subject_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['subject_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Load Roster</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Student List -->
            <?php if ($class_id && $subject_id): ?>
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Enrollment List</h5>
                        <div class="badge bg-gray-100 text-dark px-3 py-2 rounded-pill small">
                            <?php echo count($students); ?> Total Students
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="ps-4 py-3 text-muted small text-uppercase" style="width: 100px;">Roll No</th>
                                        <th class="py-3 text-muted small text-uppercase">Student Name</th>
                                        <th class="py-3 text-muted small text-uppercase">Contact Email</th>
                                        <th class="pe-4 py-3 text-end text-muted small text-uppercase">Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($students) > 0): ?>
                                        <?php foreach ($students as $row): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($row['Roll_no']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-gray-100 text-muted rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold small" style="width:32px; height:32px;">
                                                            <?php echo strtoupper(substr($row['Name'], 0, 1)); ?>
                                                        </div>
                                                        <span class="fw-semibold"><?php echo htmlspecialchars($row['Name']); ?></span>
                                                    </div>
                                                </td>
                                                <td><span class="text-muted small"><?php echo htmlspecialchars($row['Email']); ?></span></td>
                                                <td class="pe-4 text-end">
                                                    <a href="student_profile.php?id=<?php echo $row['student_id']; ?>" class="btn btn-action-view btn-sm rounded-pill">
                                                        <i class="feather-eye"></i> View Profile
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center py-5">No students currently enrolled in this group.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5 mt-5">
                    <div class="opacity-10 mb-4"><i class="feather-user-check" style="font-size: 5rem;"></i></div>
                    <h5 class="text-muted fw-bold">Apply filters above to view the student directory.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
