<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Faculty Directory";

// Handle Deletion
if (isset($_GET['delete'])) {
    $teacher_id = $_GET['delete'];
    try {
        $pdo->beginTransaction();
        // Delete from users table (cascading cleanup would be better, but we'll do manual for safety if needed)
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND Role = 'teacher'");
        $stmt->execute([$teacher_id]);
        $pdo->commit();
        $_SESSION['success'] = "Faculty member removed successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Could not remove teacher: " . $e->getMessage();
    }
    header("Location: view_teachers.php");
    exit();
}

// Handle Registration via Modal
if (isset($_POST['Add'])) {
    $Name = trim($_POST['Name']);
    $Email = trim($_POST['Email']);
    $Password = $_POST['Password'];

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE Email = ?");
        $stmt->execute([$Email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "A user with this email already exists.";
        } else {
            $hashed_password = password_hash($Password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (Name, Email, Password, Role, Status, profile_status, is_first_login) VALUES (?, ?, ?, 'teacher', 'Approved', 1, 1)");
            $insert->execute([$Name, $Email, $hashed_password]);
            $_SESSION['success'] = "Teacher '$Name' account created successfully!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "System error: " . $e->getMessage();
    }
    header("Location: view_teachers.php");
    exit();
}

// Handle Update via Modal
if (isset($_POST['Update'])) {
    $user_id = $_POST['user_id'];
    $Name = trim($_POST['Name']);
    $Email = trim($_POST['Email']);
    $Status = $_POST['Status'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET Name = ?, Email = ?, Status = ? WHERE user_id = ? AND Role = 'teacher'");
        if ($stmt->execute([$Name, $Email, $Status, $user_id])) {
            $_SESSION['success'] = "Teacher information updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update information.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: view_teachers.php");
    exit();
}

// Fetch Teachers
try {
    $teachers = $pdo->query("SELECT * FROM users WHERE Role = 'teacher' ORDER BY Name ASC")->fetchAll();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Faculty Directory</h4>
                    <p class="text-muted small">Manage institutional educators and their system access.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                    <i class="feather-user-plus me-2"></i>New Teacher
                </button>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="feather-alert-octagon me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="feather-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="ps-4 py-3 text-muted small text-uppercase">Faculty Member</th>
                                    <th class="py-3 text-muted small text-uppercase">Email Address</th>
                                    <th class="py-3 text-muted small text-uppercase text-center">Status</th>
                                    <th class="pe-4 py-3 text-end text-muted small text-uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($teachers) > 0): ?>
                                    <?php foreach ($teachers as $row): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary-soft text-primary rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px;">
                                                        <?php echo strtoupper(substr($row['Name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['Name']); ?></div>
                                                        <div class="fs-11 text-muted">Joined: <?php echo isset($row['created_at']) ? date('M Y', strtotime($row['created_at'])) : '---'; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted small"><?php echo htmlspecialchars($row['Email']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-soft text-success px-3 rounded-pill">Active</span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="javascript:void(0);" onclick='editTeacher(<?php echo json_encode($row); ?>)' class="btn btn-action-edit btn-sm rounded-pill" title="Edit Info" data-bs-toggle="tooltip">
                                                        <i class="feather-edit-3"></i>
                                                    </a>
                                                    <a href="view_teachers.php?delete=<?php echo $row['user_id']; ?>" class="btn btn-action-delete btn-sm rounded-pill" onclick="return confirm('Are you sure you want to remove this faculty member?')" title="Remove" data-bs-toggle="tooltip">
                                                        <i class="feather-trash-2"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="opacity-10 mb-3"><i class="feather-users fs-1"></i></div>
                                            <h6 class="text-muted fw-bold">No teachers registered in the system.</h6>
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
</main>

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Register New Faculty</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="Name" class="form-control bg-gray-100 border-0" placeholder="e.g. Dr. John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Institutional Email</label>
                        <input type="email" name="Email" class="form-control bg-gray-100 border-0" placeholder="e.g. j.doe@zenith.edu" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Temporary Password</label>
                        <input type="password" name="Password" class="form-control bg-gray-100 border-0" placeholder="••••••••" required>
                        <div class="form-text fs-11 mt-1 text-primary-soft">Teacher will reset this during first login.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="Add" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-save me-2"></i> Onboard Faculty
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Edit Faculty Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="Name" id="edit_name" class="form-control bg-gray-100 border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                        <input type="email" name="Email" id="edit_email" class="form-control bg-gray-100 border-0" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Account Status</label>
                        <select name="Status" id="edit_status" class="form-control bg-gray-100 border-0">
                            <option value="Approved">Approved / Active</option>
                            <option value="Pending">Pending Approval</option>
                            <option value="Rejected">Rejected / Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="Update" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-check-circle me-2"></i> Update Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTeacher(data) {
    document.getElementById('edit_user_id').value = data.user_id;
    document.getElementById('edit_name').value = data.Name;
    document.getElementById('edit_email').value = data.Email;
    document.getElementById('edit_status').value = data.Status;
    new bootstrap.Modal(document.getElementById('editTeacherModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'add') {
        var myModal = new bootstrap.Modal(document.getElementById('addTeacherModal'));
        myModal.show();
    }
});
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
