<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Class Management";
$selected_class = $_GET['class_id'] ?? null;
$students = [];

try {
    // Handle NEW CLASS addition via Modal
    if (isset($_POST['add_class'])) {
        $name = trim($_POST['class_name']);
        $section = trim($_POST['section']);
        
        if (!empty($name) && !empty($section)) {
            $stmt = $pdo->prepare("INSERT INTO classes (class_name, section) VALUES (?, ?)");
            if ($stmt->execute([$name, $section])) {
                $_SESSION['success'] = "New class '$name ($section)' added successfully!";
                header("Location: Classes.php");
                exit();
            }
        }
    }

    // Handle Class Update
    if (isset($_POST['update_class'])) {
        $id = $_POST['class_id'];
        $name = trim($_POST['class_name']);
        $section = trim($_POST['section']);

        $stmt = $pdo->prepare("UPDATE classes SET class_name = ?, section = ? WHERE class_id = ?");
        $stmt->execute([$name, $section, $id]);
        $_SESSION['success'] = "Class updated successfully.";
        header("Location: Classes.php");
        exit();
    }

    // Handle Class Delete
    if (isset($_GET['delete_class'])) {
        $id = $_GET['delete_class'];
        try {
            $stmt = $pdo->prepare("DELETE FROM classes WHERE class_id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Class deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Delete failed: Class might have enrolled students.";
        }
        header("Location: Classes.php");
        exit();
    }

    // Fetch all classes for dropdown
    $classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
    // ... remaining fetch logic ...

    if ($selected_class) {
        // Fetch students for selected class using PDO
        $stmt = $pdo->prepare("
            SELECT s.*, u.Name, u.Email 
            FROM students s 
            JOIN users u ON s.user_id = u.user_id 
            WHERE s.class_id = ? 
            ORDER BY s.Roll_no ASC
        ");
        $stmt->execute([$selected_class]);
        $students = $stmt->fetchAll();
        
        // Fetch class details for header
        $stmtClass = $pdo->prepare("SELECT * FROM classes WHERE class_id = ?");
        $stmtClass->execute([$selected_class]);
        $classInfo = $stmtClass->fetch();
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <!-- Page Header -->
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Academic Rosters</h4>
                    <p class="text-muted small">View and manage student enrollment by class and section.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addClassModal">
                    <i class="feather-plus me-2"></i>New Class
                </button>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="feather-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="feather-alert-octagon me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <!-- Filter Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Select Class & Section</label>
                            <select name="class_id" class="form-control select2" required>
                                <option value="" disabled <?php echo !$selected_class ? 'selected' : ''; ?>>Choose a class...</option>
                                <?php foreach ($classes as $row): ?>
                                    <option value="<?php echo $row['class_id']; ?>" <?php echo ($selected_class == $row['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">
                                <i class="feather-filter me-2"></i> Fetch List
                            </button>
                        </div>
                        <?php if ($selected_class): ?>
                            <div class="col-md-2">
                                <a href="Classes.php" class="btn btn-outline-secondary w-100 py-2 fw-bold">Reset</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <?php if ($selected_class): ?>
                <!-- Results Table -->
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            Class List: <span class="text-primary"><?php echo htmlspecialchars($classInfo['class_name'] . " (" . $classInfo['section'] . ")"); ?></span>
                        </h5>
                        <span class="badge bg-primary-soft text-primary rounded-pill px-3"><?php echo count($students); ?> Students</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="ps-4 py-3 text-muted small text-uppercase">Roll No</th>
                                        <th class="py-3 text-muted small text-uppercase">Full Name</th>
                                        <th class="py-3 text-muted small text-uppercase">Gender</th>
                                        <th class="py-3 text-muted small text-uppercase">Contact</th>
                                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($students) > 0): ?>
                                        <?php foreach ($students as $row): ?>
                                            <tr class="border-bottom">
                                                <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($row['Roll_no']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary-soft text-primary rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; font-size:12px;">
                                                            <?php echo strtoupper(substr($row['Name'], 0, 2)); ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold mb-0"><?php echo htmlspecialchars($row['Name']); ?></div>
                                                            <div class="small text-muted"><?php echo htmlspecialchars($row['Email']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-gray-200 text-dark small px-3"><?php echo $row['gender']; ?></span></td>
                                                <td><span class="text-muted small"><?php echo htmlspecialchars($row['phone']); ?></span></td>
                                                <td class="text-end pe-4">
                                                    <a href="../teacher/student_profile.php?id=<?php echo $row['student_id']; ?>" class="btn btn-action-view btn-sm rounded-pill">
                                                        <i class="feather-eye"></i> View Profile
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted mb-3"><i class="feather-users fs-1"></i></div>
                                                <h6 class="fw-bold text-muted">No students enrolled yet.</h6>
                                                <p class="text-muted small">Select a different class or add new students.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Classes Directory -->
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Classes Directory</h5>
                        <span class="badge bg-primary-soft text-primary rounded-pill px-3"><?php echo count($classes); ?> Active Classes</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="ps-4 py-3 text-muted small text-uppercase">ID</th>
                                        <th class="py-3 text-muted small text-uppercase">Class Name</th>
                                        <th class="py-3 text-muted small text-uppercase">Section</th>
                                        <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $c): ?>
                                    <tr class="border-bottom">
                                        <td class="ps-4 text-muted small">#<?php echo $c['class_id']; ?></td>
                                        <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($c['class_name']); ?></span></td>
                                        <td><span class="badge bg-info-soft text-info rounded-pill px-3 small"><?php echo htmlspecialchars($c['section']); ?></span></td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="Classes.php?class_id=<?php echo $c['class_id']; ?>" class="btn btn-action-view btn-sm rounded-pill" title="View Students">
                                                    <i class="feather-users"></i>
                                                </a>
                                                <a href="javascript:void(0);" onclick='editClass(<?php echo json_encode($c); ?>)' class="btn btn-action-edit btn-sm rounded-pill" title="Edit Class">
                                                    <i class="feather-edit-3"></i>
                                                </a>
                                                <a href="Classes.php?delete_class=<?php echo $c['class_id']; ?>" class="btn btn-action-delete btn-sm rounded-pill" onclick="return confirm('Delete this class?')" title="Remove">
                                                    <i class="feather-trash-2"></i>
                                                </a>
                                            </div>
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

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Register New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Class Name</label>
                        <input type="text" name="class_name" class="form-control bg-gray-100 border-0" placeholder="e.g. Grade 10, Computer Science" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Section / Group</label>
                        <input type="text" name="section" class="form-control bg-gray-100 border-0" placeholder="e.g. A, Morning, Alpha" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_class" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-save me-2"></i> Create Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Class Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Edit Class Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="class_id" id="edit_class_id">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Class Name</label>
                        <input type="text" name="class_name" id="edit_class_name" class="form-control bg-gray-100 border-0" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Section / Group</label>
                        <input type="text" name="section" id="edit_section" class="form-control bg-gray-100 border-0" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_class" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-check-circle me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editClass(data) {
    document.getElementById('edit_class_id').value = data.class_id;
    document.getElementById('edit_class_name').value = data.class_name;
    document.getElementById('edit_section').value = data.section;
    new bootstrap.Modal(document.getElementById('editClassModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>