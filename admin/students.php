<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Student Management";

// Handle Student Registration via Modal
if (isset($_POST['AddStudent'])) {
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
            $insert = $pdo->prepare("INSERT INTO users (Name, Email, Password, Role, Status, profile_status, is_first_login) VALUES (?, ?, ?, 'student', 'Approved', 1, 1)");
            $insert->execute([$Name, $Email, $hashed_password]);
            
            $_SESSION['success'] = "Student '$Name' account created successfully!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "System error: " . $e->getMessage();
    }
    header("Location: students.php");
    exit();
}

// Handle Student Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND Role = 'student'");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Student record removed.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: students.php");
    exit();
}

// Handle Student Update
if (isset($_POST['UpdateStudent'])) {
    $id = $_POST['user_id'];
    $name = trim($_POST['Name']);
    $email = trim($_POST['Email']);

    try {
        $stmt = $pdo->prepare("UPDATE users SET Name = ?, Email = ? WHERE user_id = ? AND Role = 'student'");
        $stmt->execute([$name, $email, $id]);
        $_SESSION['success'] = "Student info updated.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
    }
    header("Location: students.php");
    exit();
}

try {
    // SECURE PDO FETCH - Fixed Role from 'students' to 'student'
    $stmt = $pdo->prepare("SELECT * FROM users WHERE Role = ? AND Status = 'approved' ORDER BY Name ASC");
    $stmt->execute(['student']);
    $students = $stmt->fetchAll();
} catch (Exception $e) {
    $students = [];
    $error = "Error fetching students.";
}
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Student Directory</h4>
                    <p class="text-muted small">Manage all enrolled students and their academic assignments.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="feather-user-plus me-2"></i>Add Student
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
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase">SNO</th>
                                            <th class="py-3 text-muted small text-uppercase">Student</th>
                                            <th class="py-3 text-muted small text-uppercase">Email</th>
                                            <th class="py-3 text-muted small text-uppercase">Status</th>
                                            <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($students) > 0): ?>
                                            <?php foreach ($students as $index => $data): ?>
                                                <tr class="border-bottom">
                                                    <td class="ps-4"><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-info-soft text-info rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width:32px; height:32px; font-size:11px;">
                                                                <?php echo strtoupper(substr($data['Name'], 0, 2)); ?>
                                                            </div>
                                                            <span class="fw-bold"><?php echo htmlspecialchars($data['Name']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td><span class="text-muted small"><?php echo htmlspecialchars($data['Email']); ?></span></td>
                                                    <td><span class="badge bg-success-soft text-success rounded-pill px-3">Active</span></td>
                                                     <td class="pe-4">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <a href="assign_classes.php?student_id=<?php echo $data['user_id']?>" class="btn btn-action-view btn-sm rounded-pill" title="Assign Classes" data-bs-toggle="tooltip">
                                                                <i class="feather-book-open"></i>
                                                            </a>
                                                            <a href="javascript:void(0);" onclick='editStudent(<?php echo json_encode($data); ?>)' class="btn btn-action-edit btn-sm rounded-pill" title="Edit Info" data-bs-toggle="tooltip">
                                                                <i class="feather-edit-3"></i>
                                                            </a>
                                                            <a href="students.php?delete=<?php echo $data['user_id']; ?>" class="btn btn-action-delete btn-sm rounded-pill" onclick="return confirm('Delete this student account permanentally?')" title="Remove" data-bs-toggle="tooltip">
                                                                <i class="feather-trash-2"></i>
                                                            </a>
                                                        </div>
                                                     </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="text-muted mb-3"><i class="feather-users fs-1"></i></div>
                                                    <h6 class="text-muted fw-bold">No Students Found</h6>
                                                    <p class="text-muted small">Information about enrolled students will appear here.</p>
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

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Register New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="Name" class="form-control bg-gray-100 border-0" placeholder="e.g. Alex Johnson" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                        <input type="email" name="Email" class="form-control bg-gray-100 border-0" placeholder="e.g. alex.j@zenith.edu" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Initial Password</label>
                        <input type="password" name="Password" class="form-control bg-gray-100 border-0" placeholder="••••••••" required>
                        <div class="form-text fs-11 mt-1 text-primary-soft">Student will complete their profile after first login.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="AddStudent" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-user-plus me-2"></i> Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Edit Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_student_user_id">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="Name" id="edit_student_name" class="form-control bg-gray-100 border-0" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                        <input type="email" name="Email" id="edit_student_email" class="form-control bg-gray-100 border-0" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="UpdateStudent" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-check-circle me-2"></i> Update Info
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStudent(data) {
    document.getElementById('edit_student_user_id').value = data.user_id;
    document.getElementById('edit_student_name').value = data.Name;
    document.getElementById('edit_student_email').value = data.Email;
    new bootstrap.Modal(document.getElementById('editStudentModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
