<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('teacher');

$pageTitle = "Grade Management";
$teacher_id = $_SESSION['user_id'];

$selected_class_id = $_GET['class_id'] ?? null;
$selected_subject_id = $_GET['subject_id'] ?? null;
$selected_exam_type = $_GET['exam_type'] ?? 'Quiz';
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Process Submission
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['save_marks'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $exam_type = $_POST['exam_type'];
    $date = $_POST['date'];
    $marks_data = $_POST['marks'] ?? [];
    $total_marks_data = $_POST['total_marks'] ?? [];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            INSERT INTO marks (student_id, subject_id, exam_type, date, marks, total_marks) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE marks = ?, total_marks = ?
        ");

        foreach ($marks_data as $student_id => $marks) {
            $total = $total_marks_data[$student_id] ?? 100;
            // Only save if marks are not empty string
            if ($marks !== "") {
                $stmt->execute([$student_id, $subject_id, $exam_type, $date, $marks, $total, $marks, $total]);
            }
        }
        $pdo->commit();
        $_SESSION['success'] = "Academic records synchronized successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Sync failed: " . $e->getMessage();
    }
    header("Location: result.php?class_id=$class_id&subject_id=$subject_id&exam_type=$exam_type&date=$date");
    exit();
}

// Result Deletion Logic
if (isset($_GET['delete_mark_id'])) {
    $delete_id = $_GET['delete_mark_id'];
    $class_id = $_GET['class_id'];
    $subject_id = $_GET['subject_id'];
    $exam_type = $_GET['exam_type'];
    $date = $_GET['date'];

    try {
        $stmt = $pdo->prepare("DELETE FROM marks WHERE marks_id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = "Result record purged.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Purge failed: " . $e->getMessage();
    }
    header("Location: result.php?class_id=$class_id&subject_id=$subject_id&exam_type=$exam_type&date=$date");
    exit();
}

// Fetch Data
try {
    $classes = $pdo->prepare("
        SELECT DISTINCT c.class_id, c.class_name, c.section 
        FROM classes c 
        JOIN class_subject_teacher cst ON cst.class_id = c.class_id
        WHERE cst.teacher_id = ?
    ");
    $classes->execute([$teacher_id]);
    $myClasses = $classes->fetchAll();

    $subjects = [];
    if ($selected_class_id) {
        $subStmt = $pdo->prepare("
            SELECT s.subject_id, s.subject_name 
            FROM subject s 
            JOIN class_subject_teacher cst ON s.subject_id = cst.subject_id 
            WHERE cst.teacher_id = ? AND cst.class_id = ?
        ");
        $subStmt->execute([$teacher_id, $selected_class_id]);
        $subjects = $subStmt->fetchAll();
    }

    $students = [];
    if ($selected_class_id && $selected_subject_id) {
        $studentStmt = $pdo->prepare("
            SELECT st.student_id, st.Roll_no, u.Name, m.marks, m.total_marks, m.marks_id
            FROM students st
            JOIN users u ON u.user_id = st.user_id
            LEFT JOIN marks m ON m.student_id = st.student_id 
                AND m.subject_id = ? 
                AND m.exam_type = ? 
                AND m.date = ?
            WHERE st.class_id = ? 
            ORDER BY st.Roll_no ASC
        ");
        $studentStmt->execute([$selected_subject_id, $selected_exam_type, $selected_date, $selected_class_id]);
        $students = $studentStmt->fetchAll();
    }
} catch (Exception $e) {
    die("Gradefetch error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/teacher_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Assessment Registry</h4>
                    <p class="text-muted small">Record and validate student academic performance metrics.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <!-- Filter Bar -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Class</label>
                            <select name="class_id" class="form-control" onchange="this.form.submit()" required>
                                <option value="" disabled <?php echo !$selected_class_id ? 'selected' : ''; ?>>Select Class</option>
                                <?php foreach ($myClasses as $row): ?>
                                    <option value="<?php echo $row['class_id']; ?>" <?php echo ($selected_class_id == $row['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Subject</label>
                            <select name="subject_id" class="form-control" required <?php echo !$selected_class_id ? 'disabled' : ''; ?>>
                                <option value="" disabled <?php echo !$selected_subject_id ? 'selected' : ''; ?>>Select Subject</option>
                                <?php foreach ($subjects as $row): ?>
                                    <option value="<?php echo $row['subject_id']; ?>" <?php echo ($selected_subject_id == $row['subject_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['subject_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Exam Category</label>
                            <select name="exam_type" class="form-control">
                                <?php foreach (['Quiz','Mid','Final','Assignment','Project'] as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo $selected_exam_type === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Assessment Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-dark w-100 py-2"><i class="feather-search mr-2"></i>Load</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($selected_class_id && $selected_subject_id): ?>
                <form method="POST">
                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                    <input type="hidden" name="subject_id" value="<?php echo $selected_subject_id; ?>">
                    <input type="hidden" name="exam_type" value="<?php echo $selected_exam_type; ?>">
                    <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                    
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Grade Entry Sheet</h5>
                            <div class="d-flex align-items-center">
                                <span class="small text-muted me-2">Bulk Total Marks:</span>
                                <input type="number" id="bulk_total" class="form-control form-control-sm" style="width: 80px;" placeholder="100" oninput="applyBulk(this.value)">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase" style="width: 100px;">Roll No</th>
                                            <th class="py-3 text-muted small text-uppercase">Student Name</th>
                                            <th class="py-3 text-muted small text-uppercase" style="width: 150px;">Total Marks</th>
                                            <th class="py-3 text-muted small text-uppercase" style="width: 150px;">Obtained Marks</th>
                                            <th class="py-3 text-center text-muted small text-uppercase" style="width: 100px;">Status</th>
                                            <th class="pe-4 py-3 text-end text-muted small text-uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $row): 
                                            $is_entered = $row['marks'] !== null;
                                        ?>
                                            <tr>
                                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($row['Roll_no']); ?></td>
                                                <td><span class="fw-semibold"><?php echo htmlspecialchars($row['Name']); ?></span></td>
                                                <td>
                                                    <input type="number" name="total_marks[<?php echo $row['student_id']; ?>]" class="form-control total-input" value="<?php echo $is_entered ? $row['total_marks'] : ''; ?>">
                                                </td>
                                                <td>
                                                    <input type="number" name="marks[<?php echo $row['student_id']; ?>]" class="form-control fw-bold text-primary" value="<?php echo $is_entered ? $row['marks'] : ''; ?>">
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($is_entered): ?>
                                                        <span class="badge bg-success-soft text-success px-3 rounded-pill">Synchronized</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-soft text-warning px-3 rounded-pill">Awaiting</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <?php if ($is_entered): ?>
                                                        <a href="?delete_mark_id=<?php echo $row['marks_id']; ?>&class_id=<?php echo $selected_class_id; ?>&subject_id=<?php echo $selected_subject_id; ?>&exam_type=<?php echo $selected_exam_type; ?>&date=<?php echo $selected_date; ?>" 
                                                           class="btn btn-sm btn-danger-soft text-danger rounded-circle p-2" 
                                                           onclick="return confirm('Are you sure you want to purge this record? This action cannot be undone.')"
                                                           title="Delete Record">
                                                            <i class="feather-trash-2" style="font-size: 14px;"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted opacity-50"><i class="feather-minus"></i></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top p-4 text-end">
                            <button type="submit" name="save_marks" class="btn btn-primary px-5 py-2 fw-bold shadow-sm rounded-pill">
                                <i class="feather-save me-2"></i> Commit Records
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center py-5 mt-5">
                    <div class="opacity-10 mb-4"><i class="feather-edit-3" style="font-size: 5rem;"></i></div>
                    <h5 class="text-muted fw-bold">Select parameters above to start grading.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
function applyBulk(val) {
    document.querySelectorAll('.total-input').forEach(input => {
        input.value = val;
    });
}
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
