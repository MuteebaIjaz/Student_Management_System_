<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Academic Assignments";

if (isset($_POST['assign'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];

    try {
        // Check if assignment already exists
        $stmt = $pdo->prepare("SELECT id FROM class_subject_teacher WHERE class_id = ? AND subject_id = ?");
        $stmt->execute([$class_id, $subject_id]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = "This subject is already assigned to this class!";
        } else {
            $insert = $pdo->prepare("INSERT INTO class_subject_teacher (class_id, subject_id, teacher_id) VALUES (?, ?, ?)");
            $insert->execute([$class_id, $subject_id, $teacher_id]);
            $_SESSION['success'] = "Assignment mapping created successfully!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "System error: " . $e->getMessage();
    }
    header("Location: assignment.php");
    exit();
}

// Fetch data for form and table
try {
    $classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();
    $subjects = $pdo->query("SELECT * FROM subject ORDER BY subject_name ASC")->fetchAll();
    $teachers = $pdo->query("SELECT user_id, Name FROM users WHERE Role='teacher' ORDER BY Name ASC")->fetchAll();
    
    // Fetch existing assignments
    $assignments = $pdo->query("
        SELECT cst.id, c.class_name, c.section, s.subject_name, u.Name as teacher_name
        FROM class_subject_teacher cst
        JOIN classes c ON cst.class_id = c.class_id
        JOIN subject s ON cst.subject_id = s.subject_id
        JOIN users u ON cst.teacher_id = u.user_id
        ORDER BY c.class_name ASC, s.subject_name ASC
    ")->fetchAll();
} catch (Exception $e) {
    die("Data fetch error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Academic Mapping</h4>
                    <p class="text-muted small">Link classes, subjects, and teachers to build the school schedule.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Assignment Form -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="border-radius: var(--radius); top: 100px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Create Assignment</h5>
                            
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger py-2 small mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success py-2 small mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Class & Section</label>
                                    <select name="class_id" class="form-control" required>
                                        <option value="" disabled selected>Choose Class</option>
                                        <?php foreach ($classes as $row): ?>
                                            <option value="<?php echo $row['class_id']; ?>">
                                                <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Subject</label>
                                    <select name="subject_id" class="form-control" required>
                                        <option value="" disabled selected>Choose Subject</option>
                                        <?php foreach ($subjects as $row): ?>
                                            <option value="<?php echo $row['subject_id']; ?>">
                                                <?php echo htmlspecialchars($row['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Assigned Teacher</label>
                                    <select name="teacher_id" class="form-control" required>
                                        <option value="" disabled selected>Choose Teacher</option>
                                        <?php foreach ($teachers as $row): ?>
                                            <option value="<?php echo $row['user_id']; ?>">
                                                <?php echo htmlspecialchars($row['Name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button type="submit" name="assign" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                    <i class="feather-link me-2"></i> Confirm Assignment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Assignment Table -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="fw-bold mb-0">Current Assignments</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase">Class</th>
                                            <th class="py-3 text-muted small text-uppercase">Subject</th>
                                            <th class="py-3 text-muted small text-uppercase">Teacher</th>
                                            <th class="text-end pe-4 py-3 text-muted small text-uppercase">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($assignments) > 0): ?>
                                            <?php foreach ($assignments as $row): ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <span class="fw-bold"><?php echo htmlspecialchars($row['class_name']); ?></span>
                                                        <span class="badge bg-gray-200 text-dark ms-1"><?php echo htmlspecialchars($row['section']); ?></span>
                                                    </td>
                                                    <td class="text-primary fw-medium"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary-soft text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width:28px; height:28px; font-size:10px;">
                                                                <?php echo strtoupper(substr($row['teacher_name'], 0, 1)); ?>
                                                            </div>
                                                            <span class="small"><?php echo htmlspecialchars($row['teacher_name']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <button class="btn btn-link text-danger p-0" title="Remove Assignment">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted small">
                                                    No assignments found. Use the form to link academics.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
