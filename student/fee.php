<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('student');

$pageTitle = "Financial Ledger";
$user_id = $_SESSION['user_id'];

try {
    // 1. Get Student ID
    $studentStmt = $pdo->prepare("SELECT student_id, class_id FROM students WHERE user_id = ?");
    $studentStmt->execute([$user_id]);
    $student = $studentStmt->fetch();

    if (!$student) {
        // Redirect to profile completion if record is missing
        header("Location: ../Complete_profile.php");
        exit();
    }

    $student_id = $student['student_id'];

    // 2. Fetch Fee Records
    $feeStmt = $pdo->prepare("
        SELECT ft.name, ft.amount, ft.due_date,
               fp.amount_paid, fp.payment_date, fp.status, fp.remarks
        FROM fee_types ft
        LEFT JOIN fee_payments fp ON ft.fee_type_id = fp.fee_type_id AND fp.student_id = ?
        ORDER BY ft.due_date ASC
    ");
    $feeStmt->execute([$student_id]);
    $fees = $feeStmt->fetchAll();

    // 3. Financial Summary
    $totalBilled = 0;
    $totalPaid = 0;
    foreach ($fees as $f) {
        $totalBilled += $f['amount'];
        $totalPaid += $f['amount_paid'] ?? 0;
    }
    $totalPending = $totalBilled - $totalPaid;

} catch (Exception $e) {
    die("Fee Retrieval Error: " . $e->getMessage());
}

function getFeeBadge($status) {
    switch ($status) {
        case 'Paid': return 'bg-success-soft text-success';
        case 'Partial': return 'bg-warning-soft text-warning';
        default: return 'bg-danger-soft text-danger';
    }
}
?>

<?php include __DIR__ . '/../includes/navbar/student_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Financial Statement</h4>
                    <p class="text-muted small">Transparency in academic investment. View your dues and transaction history.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <div class="d-flex align-items-center bg-white p-2 rounded-pill shadow-sm px-3 border">
                    <div class="me-3">
                        <div class="text-muted fs-10 text-uppercase fw-bold">Outstanding Balance</div>
                        <div class="fw-bold text-danger">Rs. <?php echo number_format($totalPending); ?></div>
                    </div>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="feather-credit-card"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <!-- Financial Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Total Billed</div>
                            <h3 class="fw-bold text-dark">Rs. <?php echo number_format($totalBilled); ?></h3>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-dark" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Total Paid</div>
                            <h3 class="fw-bold text-success">Rs. <?php echo number_format($totalPaid); ?></h3>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: <?php echo ($totalBilled > 0 ? ($totalPaid/$totalBilled)*100 : 0); ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Pending Dues</div>
                            <h3 class="fw-bold text-danger">Rs. <?php echo number_format($totalPending); ?></h3>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-danger" style="width: <?php echo ($totalBilled > 0 ? ($totalPending/$totalBilled)*100 : 0); ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Ledger Table -->
            <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0">Transaction Ledger</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="ps-4 py-3 text-muted small text-uppercase">Fee Description</th>
                                    <th class="py-3 text-muted small text-uppercase">Due Date</th>
                                    <th class="py-3 text-muted small text-uppercase text-center">Billed</th>
                                    <th class="py-3 text-muted small text-uppercase text-center">Paid</th>
                                    <th class="py-3 text-muted small text-uppercase">Status</th>
                                    <th class="pe-4 py-3 text-end text-muted small text-uppercase">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($fees) > 0): ?>
                                    <?php foreach ($fees as $row): 
                                        $statusClass = getFeeBadge($row['status'] ?? 'Unpaid');
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td class="text-muted small"><?php echo $row['due_date'] ? date('M d, Y', strtotime($row['due_date'])) : 'N/A'; ?></td>
                                            <td class="text-center fw-bold">Rs. <?php echo number_format($row['amount']); ?></td>
                                            <td class="text-center text-success fw-bold">Rs. <?php echo number_format($row['amount_paid'] ?? 0); ?></td>
                                            <td><span class="badge <?php echo $statusClass; ?> px-3 rounded-pill"><?php echo $row['status'] ?? 'Unpaid'; ?></span></td>
                                            <td class="pe-4 text-end small text-muted fst-italic"><?php echo htmlspecialchars($row['remarks'] ?? '—'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-5">No financial records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
