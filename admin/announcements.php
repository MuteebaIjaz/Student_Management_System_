<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Announcements";
$selected_class = $_GET['class_id'] ?? null;
$announcements = [];

try {
    // Handle NEW Announcement Processing
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_announcement'])) {
        $title = trim($_POST['title']);
        $message = trim($_POST['message']);
        $class_id = $_POST['class_id'];
        $sender_id = $_SESSION['user_id'];
        $sender_role = $_SESSION['user_role'];

        if (!empty($title) && !empty($message) && !empty($class_id)) {
            $stmt = $pdo->prepare("INSERT INTO announcements (title, message, target_audience, sender_id, sender_role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $message, $class_id, $sender_id, $sender_role])) {
                $_SESSION['success'] = "Announcement broadcasted successfully!";
                header("Location: announcements.php?class_id=" . $class_id);
                exit();
            }
        } else {
            $_SESSION['error'] = "All fields are required.";
        }
    }

    $classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

    if ($selected_class) {
        $stmt = $pdo->prepare("
            SELECT a.*, u.Name as sender_name 
            FROM announcements a 
            LEFT JOIN users u ON a.sender_id = u.user_id
            WHERE a.target_audience = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$selected_class]);
        $announcements = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $error = "System error: " . $e->getMessage();
}
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Communications</h4>
                    <p class="text-muted small">Broadcast important updates and track class-wide announcements.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal">
                    <i class="feather-plus me-2"></i>New Broadcast
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
            <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Filter by Class</label>
                            <select name="class_id" class="form-control select2" required>
                                <option value="" disabled <?php echo !$selected_class ? 'selected' : ''; ?>>Select target class...</option>
                                <?php foreach ($classes as $row): ?>
                                    <option value="<?php echo $row['class_id']; ?>" <?php echo ($selected_class == $row['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">
                                <i class="feather-search me-2"></i> View History
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($selected_class): ?>
                <div class="row">
                    <?php if (count($announcements) > 0): ?>
                        <?php foreach ($announcements as $row): ?>
                            <div class="col-12 mb-3">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 4px solid var(--primary) !important;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                                                <div class="d-flex align-items-center small text-muted">
                                                    <span class="me-3"><i class="feather-user me-1"></i> <?php echo htmlspecialchars($row['sender_name'] ?? 'System'); ?></span>
                                                    <span><i class="feather-calendar me-1"></i> <?php echo date('M d, Y', strtotime($row['created_at'])); ?> at <?php echo date('h:i A', strtotime($row['created_at'])); ?></span>
                                                </div>
                                            </div>
                                            <span class="badge bg-primary-soft text-primary rounded-pill px-3"><?php echo ucfirst($row['sender_role']); ?></span>
                                        </div>
                                        <div class="text-secondary" style="line-height: 1.6;">
                                            <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <div class="opacity-25 mb-3"><i class="feather-megaphone fs-1"></i></div>
                            <h6 class="text-muted fw-bold">No announcements found for this class.</h6>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 mt-5">
                    <i class="feather-inbox text-muted mb-4" style="font-size: 4rem; opacity: 0.2;"></i>
                    <h5 class="text-muted fw-bold">Select a class to view its broadcast history.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- New Announcement Modal -->
<div class="modal fade" id="newAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold">Broadcast New Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Target Audience</label>
                        <select name="class_id" class="form-control bg-gray-100 border-0" required>
                            <option value="" disabled selected>Select class roster...</option>
                            <?php foreach ($classes as $row): ?>
                                <option value="<?php echo $row['class_id']; ?>" <?php echo ($selected_class == $row['class_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Subject Title</label>
                        <input type="text" name="title" class="form-control bg-gray-100 border-0" placeholder="e.g. Schedule for Final Examinations" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Detailed Notice</label>
                        <textarea name="message" class="form-control bg-gray-100 border-0" rows="6" placeholder="Type your announcement content here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" name="post_announcement" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="feather-send me-2"></i> Dispatch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>