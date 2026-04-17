<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Registration Requests";

try {
    // SECURE PDO FETCH
    $stmt = $pdo->query("SELECT * FROM users WHERE Role = 'student' AND Status = 'Pending' ORDER BY user_id DESC");
    $requests = $stmt->fetchAll();
} catch (Exception $e) {
    $requests = [];
    $error = "Error fetching requests.";
}
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Admission Desk</h4>
                    <p class="text-muted small">Verify and manage incoming student registration requests.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase">SNO</th>
                                            <th class="py-3 text-muted small text-uppercase">Applicant</th>
                                            <th class="py-3 text-muted small text-uppercase">Email</th>
                                            <th class="py-3 text-muted small text-uppercase">Status</th>
                                            <th class="py-3 text-muted small text-uppercase text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($requests) > 0): ?>
                                            <?php foreach ($requests as $index => $data): ?>
                                                <tr class="border-bottom">
                                                    <td class="ps-4"><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary-soft text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width:32px; height:32px; font-size:11px;">
                                                                <?php echo strtoupper(substr($data['Name'], 0, 2)); ?>
                                                            </div>
                                                            <span class="fw-bold"><?php echo htmlspecialchars($data['Name']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td><span class="text-muted small"><?php echo htmlspecialchars($data['Email']); ?></span></td>
                                                    <td><span class="badge bg-warning-soft text-warning rounded-pill px-3">Pending</span></td>
                                                    <td class="text-end pe-4">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <a href="../Controllers/adminController.php?approve=<?php echo $data['user_id']?>" class="btn btn-success btn-sm rounded-pill px-3 py-1 fw-bold" style="font-size: 11px;">
                                                                <i class="feather-check me-1"></i> Approve
                                                            </a>
                                                            <a href="../Controllers/adminController.php?reject=<?php echo $data['user_id']?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 fw-bold" style="font-size: 11px;">
                                                                <i class="feather-x me-1"></i> Reject
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="text-muted mb-3"><i class="feather-inbox fs-1"></i></div>
                                                    <h6 class="text-muted fw-bold">No Pending Requests</h6>
                                                    <p class="text-muted small">New applicants will appear here for verification.</p>
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
