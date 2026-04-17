<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('teacher');

$pageTitle = "Faculty Communications";
$teacher_id = $_SESSION['user_id'];

// Process New Announcement
if (isset($_POST['Add'])) {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $class_id = $_POST['class_id'];
    $role = $_SESSION['user_role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, message, sender_id, sender_role, target_audience) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $message, $teacher_id, $role, $class_id]);
        $_SESSION['success'] = "Broadcast dispatched successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Transmission failed: " . $e->getMessage();
    }
    header("Location: announcements.php");
    exit();
}

// Fetch Data
try {
    // My Assigned Classes for the form
    $classStmt = $pdo->prepare("
        SELECT DISTINCT c.class_id, c.class_name, c.section 
        FROM classes c 
        JOIN class_subject_teacher cst ON cst.class_id = c.class_id
        WHERE cst.teacher_id = ?
    ");
    $classStmt->execute([$teacher_id]);
    $myClasses = $classStmt->fetchAll();

    // My Sent Announcements
    $sentStmt = $pdo->prepare("
        SELECT a.*, c.class_name, c.section 
        FROM announcements a
        LEFT JOIN classes c ON a.target_audience = c.class_id
        WHERE a.sender_id = ? AND a.sender_role = 'teacher'
        ORDER BY a.created_at DESC
    ");
    $sentStmt->execute([$teacher_id]);
    $myAnnouncements = $sentStmt->fetchAll();
} catch (Exception $e) {
    die("Communication error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/teacher_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Broadcast Center</h4>
                    <p class="text-muted small">Dispatch vital updates and academic notices to your enrolled cohorts.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal">
                    <i class="feather-plus me-2"></i>New Announcement
                </button>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Previous Broadcasts -->
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                        <h5 class="fw-bold mb-0">Outgoing History</h5>
                        <div class="text-muted small">Total Sent: <b><?php echo count($myAnnouncements); ?></b></div>
                    </div>
                    <?php if (count($myAnnouncements) > 0): ?>
                        <div class="row g-3">
                            <?php foreach ($myAnnouncements as $row): ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                                        <div class="card-body p-4 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="bg-primary-soft text-primary p-2 rounded-circle" style="width:38px;height:38px;display:flex;align-items:center;justify-content:center;">
                                                    <i class="feather-megaphone"></i>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                                                        <i class="feather-more-horizontal"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                        <li><a class="dropdown-item py-2 small" href="#"><i class="feather-edit-3 me-2 text-primary"></i> Edit</a></li>
                                                        <li><a class="dropdown-item py-2 small text-danger" href="#"><i class="feather-trash-2 me-2"></i> Remove</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($row['title']); ?></h6>
                                            <div class="d-flex align-items-center small text-muted mb-3">
                                                <i class="feather-users me-1"></i> 
                                                <?php echo htmlspecialchars($row['class_name'] . ' (' . $row['section'] . ')'); ?>
                                                <span class="mx-2">•</span>
                                                <i class="feather-clock me-1"></i>
                                                <?php echo date('M d, y', strtotime($row['created_at'])); ?>
                                            </div>
                                            <p class="text-secondary small mb-0 flex-grow-1" style="line-height: 1.6;">
                                                <?php echo nl2br(htmlspecialchars(substr($row['message'], 0, 150))); ?><?php echo strlen($row['message']) > 150 ? '...' : ''; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                            <div class="card-body py-5 text-center">
                                <div class="opacity-10 mb-3"><i class="feather-mail fs-1"></i></div>
                                <h6 class="text-muted fw-bold">No announcements sent yet.</h6>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- New Announcement Modal -->
<div class="modal fade" id="newAnnouncementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Compose New Broadcast</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Notice Title</label>
                        <input type="text" name="title" class="form-control bg-gray-100 border-0" placeholder="e.g. End of Term Quiz Details" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Target Audience</label>
                        <select name="class_id" class="form-control bg-gray-100 border-0" required>
                            <option value="" disabled selected>Select a class...</option>
                            <?php foreach ($myClasses as $row): ?>
                                <option value="<?php echo $row['class_id']; ?>">
                                    <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Detailed Message</label>
                        <textarea name="message" class="form-control bg-gray-100 border-0" rows="6" placeholder="Type your message here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" name="Add" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-send me-2"></i> Dispatch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'add') {
        var myModal = new bootstrap.Modal(document.getElementById('newAnnouncementModal'));
        myModal.show();
    }
});
</script>div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
