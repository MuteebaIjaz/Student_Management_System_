<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Subject Management";

try {
    // Handle Subject Addition
    if (isset($_POST['Add'])) {
        $subject = trim($_POST['subject']);
        $code = trim($_POST['code'] ?? '');
        $type = $_POST['type'] ?? 'core';
        
        $check = $pdo->prepare("SELECT * FROM subject WHERE subject_name = ?");
        $check->execute([$subject]);
        
        if ($check->fetch()) {
            $_SESSION['error'] = "This subject already exists.";
        } else {
            $insert = $pdo->prepare("INSERT INTO subject (subject_name, code, type) VALUES (?, ?, ?)");
            $insert->execute([$subject, $code, $type]);
            $_SESSION['success'] = "Subject added successfully!";
        }
        header("Location: subjects.php");
        exit();
    }

    // Handle Subject Update
    if (isset($_POST['Update'])) {
        $id = $_POST['subject_id'];
        $name = trim($_POST['subject_name']);
        $code = trim($_POST['code']);
        $type = $_POST['type'];

        $stmt = $pdo->prepare("UPDATE subject SET subject_name = ?, code = ?, type = ? WHERE subject_id = ?");
        if ($stmt->execute([$name, $code, $type, $id])) {
            $_SESSION['success'] = "Subject updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update subject.";
        }
        header("Location: subjects.php");
        exit();
    }

    // Handle Subject Deletion
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM subject WHERE subject_id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = "Subject removed successfully.";
        } else {
            $_SESSION['error'] = "Could not delete subject.";
        }
        header("Location: subjects.php");
        exit();
    }

    // Fetch existing subjects
    $stmt = $pdo->query("SELECT * FROM subject ORDER BY subject_name ASC");
    $subjects = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "System error: " . $e->getMessage();
    $subjects = [];
}
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Curriculum Manager</h4>
                    <p class="text-muted small">Define and organize academic subjects for the current semester.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row g-4">
                <!-- Add Subject Form -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Add New Subject</h5>
                            
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger py-2 small"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success py-2 small"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                            <?php endif; ?>

                            <form action="" method="post">
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Subject Name</label>
                                    <input type="text" class="form-control" name="subject" placeholder="e.g. Advanced Mathematics" required>
                                </div>
                                <button type="submit" name="Add" class="btn btn-primary w-100 py-2 fw-bold">
                                    <i class="feather-plus me-2"></i> Register Subject
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Subjects List -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-0">
                            <div class="p-4 border-bottom">
                                <h5 class="fw-bold mb-0">Active Subjects</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase">ID</th>
                                            <th class="py-3 text-muted small text-uppercase">Subject Name</th>
                                            <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($subjects) > 0): ?>
                                            <?php foreach ($subjects as $row): ?>
                                                <tr class="border-bottom">
                                                    <td class="ps-4 text-muted small">#<?php echo $row['subject_id']; ?></td>
                                                    <td>
                                                        <span class="fw-bold"><?php echo htmlspecialchars($row['subject_name']); ?></span>
                                                    </td>
                                                    <td class="pe-4">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <a href="javascript:void(0);" onclick='editSubject(<?php echo json_encode($row); ?>)' class="btn btn-action-edit btn-sm rounded-pill" title="Edit Subject">
                                                                <i class="feather-edit-3"></i>
                                                            </a>
                                                            <a href="subjects.php?delete=<?php echo $row['subject_id']; ?>" class="btn btn-action-delete btn-sm rounded-pill" onclick="return confirm('Delete this subject?')" title="Delete">
                                                                <i class="feather-trash-2"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-5">
                                                    <p class="text-muted small mb-0">No subjects defined yet.</p>
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

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Edit Subject Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="subject_id" id="edit_subject_id">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Subject Name</label>
                        <input type="text" name="subject_name" id="edit_subject_name" class="form-control bg-gray-100 border-0" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Code</label>
                            <input type="text" name="code" id="edit_subject_code" class="form-control bg-gray-100 border-0" placeholder="e.g. CS-101">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Type</label>
                            <select name="type" id="edit_subject_type" class="form-control bg-gray-100 border-0">
                                <option value="core">Core</option>
                                <option value="elective">Elective</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="Update" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-check-circle me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSubject(data) {
    document.getElementById('edit_subject_id').value = data.subject_id;
    document.getElementById('edit_subject_name').value = data.subject_name;
    document.getElementById('edit_subject_code').value = data.code || '';
    document.getElementById('edit_subject_type').value = data.type || 'core';
    new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
