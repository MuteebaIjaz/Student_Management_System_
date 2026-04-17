<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Attendance Control";
date_default_timezone_set("Asia/Karachi");

$class_id = $_GET['class_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;
$attendance_date = $_GET['attendance_date'] ?? date('Y-m-d');

// Processing Saves/Updates
if (isset($_POST['save_attendance']) || isset($_POST['edit_attendance'])) {
    $c_id = $_POST['class_id'];
    $s_id = $_POST['subject_id'];
    $date = $_POST['attendance_date'];
    $teacher_id = $_SESSION['user_id'];
    $status_data = $_POST['status'] ?? [];

    try {
        $pdo->beginTransaction();
        foreach ($status_data as $student_id => $status) {
            // Upsert logic for PDO
            $stmt = $pdo->prepare("
                INSERT INTO attendance (class_id, student_id, subject_id, teacher_id, date, status) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = ?, teacher_id = ?
            ");
            $stmt->execute([$c_id, $student_id, $s_id, $teacher_id, $date, $status, $status, $teacher_id]);
        }
        $pdo->commit();
        $_SESSION['success'] = "Attendance records synchronized successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Sync failed: " . $e->getMessage();
    }
    header("Location: mark_attendance.php?class_id=$c_id&subject_id=$s_id&attendance_date=$date");
    exit();
}

// Data Fetching
try {
    $classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();
    $subjects = [];
    $students = [];
    $existing_records = [];

    if ($class_id) {
        $stmtS = $pdo->prepare("
            SELECT DISTINCT s.subject_id, s.subject_name 
            FROM subject s 
            JOIN class_subject_teacher cst ON cst.subject_id = s.subject_id 
            WHERE cst.class_id = ?
        ");
        $stmtS->execute([$class_id]);
        $subjects = $stmtS->fetchAll();
    }

    if ($class_id && $subject_id) {
        $stmtSt = $pdo->prepare("SELECT student_id, Roll_no, user_id FROM students WHERE class_id = ? ORDER BY Roll_no ASC");
        $stmtSt->execute([$class_id]);
        $students = $stmtSt->fetchAll();

        $stmtEx = $pdo->prepare("SELECT student_id, status FROM attendance WHERE subject_id = ? AND date = ?");
        $stmtEx->execute([$subject_id, $attendance_date]);
        $existing_records = $stmtEx->fetchAll(PDO::FETCH_KEY_PAIR);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Daily Attendance</h4>
                    <p class="text-muted small">Record presence and track academic engagement in real-time.</p>
                </div>
            </div>
        </div>

    <div class="container-fluid mt-4">
        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
            <div class="card-body p-4">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Class & Section</label>
                        <select name="class_id" class="form-control" onchange="this.form.submit()" required>
                            <option value="" disabled <?php echo !$class_id ? 'selected' : ''; ?>>Select Class</option>
                            <?php foreach ($classes as $row): ?>
                                <option value="<?php echo $row['class_id']; ?>" <?php echo ($class_id == $row['class_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Subject</label>
                        <select name="subject_id" class="form-control" required <?php echo !$class_id ? 'disabled' : ''; ?>>
                            <option value="" disabled <?php echo !$subject_id ? 'selected' : ''; ?>>Select Subject</option>
                            <?php foreach ($subjects as $row): ?>
                                <option value="<?php echo $row['subject_id']; ?>" <?php echo ($subject_id == $row['subject_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Date</label>
                        <input type="date" name="attendance_date" class="form-control" value="<?php echo $attendance_date; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark w-100 py-2"><i class="feather-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($class_id && $subject_id): ?>
            <form method="POST">
                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                <input type="hidden" name="attendance_date" value="<?php echo $attendance_date; ?>">
                
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">Class Roster</h5>
                            <p class="text-muted small mb-0"><?php echo count($students); ?> Students found</p>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 me-2" onclick="markAll('Present')">All Present</button>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="markAll('Absent')">All Absent</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="ps-4 py-3 text-muted small text-uppercase" style="width: 80px;">Roll No</th>
                                        <th class="py-3 text-muted small text-uppercase">Student Name</th>
                                        <th class="py-3 text-muted small text-uppercase text-center" style="width: 200px;">Status Selection</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($students) > 0): ?>
                                        <?php foreach ($students as $row): 
                                            $status = $existing_records[$row['student_id']] ?? '';
                                        ?>
                                            <tr>
                                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($row['Roll_no']); ?></td>
                                                <td>
                                                    <?php 
                                                        // Quick name fetch for display
                                                        $nameStmt = $pdo->prepare("SELECT Name FROM users WHERE user_id = ?");
                                                        $nameStmt->execute([$row['user_id']]);
                                                        echo htmlspecialchars($nameStmt->fetchColumn());
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group w-100">
                                                        <input type="radio" class="btn-check" name="status[<?php echo $row['student_id']; ?>]" id="p_<?php echo $row['student_id']; ?>" value="Present" autocomplete="off" <?php echo $status=='Present' ? 'checked' : ''; ?> required>
                                                        <label class="btn btn-sm btn-outline-success py-2" for="p_<?php echo $row['student_id']; ?>">Present</label>

                                                        <input type="radio" class="btn-check" name="status[<?php echo $row['student_id']; ?>]" id="a_<?php echo $row['student_id']; ?>" value="Absent" autocomplete="off" <?php echo $status=='Absent' ? 'checked' : ''; ?> required>
                                                        <label class="btn btn-sm btn-outline-danger py-2" for="a_<?php echo $row['student_id']; ?>">Absent</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center py-5">No students found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top p-4 text-end">
                        <button type="submit" name="<?php echo $existing_records ? 'edit_attendance' : 'save_attendance'; ?>" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                            <i class="feather-save me-2"></i> <?php echo $existing_records ? 'Sync Updates' : 'Confirm Attendance'; ?>
                        </button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center py-5 mt-5">
                <div class="opacity-10 mb-4"><i class="feather-calendar" style="font-size: 6rem;"></i></div>
                <h5 class="text-muted fw-bold">Select parameters above to load the attendance sheet.</h5>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function markAll(status) {
    const radios = document.querySelectorAll(`input[value="${status}"]`);
    radios.forEach(r => r.checked = true);
}
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
