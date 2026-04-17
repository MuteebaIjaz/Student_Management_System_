<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('student');

$pageTitle = "Announcements Hub";
$user_id = $_SESSION['user_id'];

try {
    // 1. Get Student's Class ID
    $studentStmt = $pdo->prepare("SELECT class_id FROM students WHERE user_id = ?");
    $studentStmt->execute([$user_id]);
    $student = $studentStmt->fetch();

    if (!$student) {
        // Redirect to profile completion if record is missing
        header("Location: ../Complete_profile.php");
        exit();
    }

    $class_id = $student['class_id'];

    // 2. Fetch Announcements for this Class
    $announcementsStmt = $pdo->prepare("
        SELECT * FROM announcements 
        WHERE target_audience = ? OR target_audience = 'all'
        ORDER BY created_at DESC
    ");
    $announcementsStmt->execute([$class_id]);
    $announcements = $announcementsStmt->fetchAll();

} catch (Exception $e) {
    die("Announcement Retrieval Error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/student_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Notice Board</h4>
                    <p class="text-muted small">Stay informed with the latest updates and archival notices from the administration.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <div class="d-flex align-items-center">
                    <span class="badge bg-warning-soft text-warning px-3 py-2 rounded-pill fw-bold me-2">
                        <i class="feather-bell me-1"></i> <?php echo count($announcements); ?> Total
                    </span>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-lg-8">
                    <?php if (count($announcements) > 0): ?>
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($announcements as $row): 
                                $isRecent = (time() - strtotime($row['created_at'])) < (86400 * 3);
                            ?>
                                <div class="card border-0 shadow-sm position-relative overflow-hidden" style="border-radius: var(--radius);">
                                    <?php if ($isRecent): ?>
                                        <div class="position-absolute top-0 start-0 h-100 bg-primary" style="width: 4px;"></div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-gray-100 text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="feather-message-square"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($row['title']); ?></h6>
                                                    <div class="text-muted fs-11"><?php echo date('M d, Y • h:i A', strtotime($row['created_at'])); ?></div>
                                                </div>
                                            </div>
                                            <?php if ($isRecent): ?>
                                                <span class="badge bg-primary-soft text-primary rounded-pill small fw-bold px-3">Recent</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="ps-5">
                                            <p class="text-secondary mb-0" style="line-height: 1.6;">
                                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                            <div class="card-body py-5 text-center">
                                <div class="opacity-10 mb-4"><i class="feather-bell-off" style="font-size: 5rem;"></i></div>
                                <h5 class="text-muted fw-bold">Silence is golden.</h5>
                                <p class="small text-muted">No official announcements have been recorded for your class yet.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius); background: linear-gradient(135deg, var(--primary) 0%, #4a2fa0 100%);">
                        <div class="card-body p-4 text-white">
                            <h6 class="fw-bold mb-3">Need Assistance?</h6>
                            <p class="small mb-4 opacity-75">If you have questions regarding any official notice, please contact the student affairs office directly.</p>
                            <a href="mailto:support@zenithlearn.edu" class="btn btn-white btn-sm fw-bold rounded-pill px-4 text-primary">Contact Support</a>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h6 class="fw-bold mb-0">Subscription Settings</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" checked id="emailNotify">
                                <label class="form-check-label fs-11 fw-bold text-dark" for="emailNotify">Email Notifications</label>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" checked id="pushNotify">
                                <label class="form-check-label fs-11 fw-bold text-dark" for="pushNotify">Web Push Alerts</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
