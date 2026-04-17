<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Revenue Collection";

if (isset($_POST['Record'])) {
    $student_id   = $_POST['student_id'];
    $fee_type_id  = $_POST['fee_type_id'];
    $amount_paid  = $_POST['amount_paid'];
    $payment_date = $_POST['payment_date'];
    $status       = $_POST['status'];
    $remarks      = $_POST['remarks'];
    $recorded_by  = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO fee_payments (student_id, fee_type_id, amount_paid, payment_date, status, remarks, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $fee_type_id, $amount_paid, $payment_date, $status, $remarks, $recorded_by]);
        $_SESSION['success'] = "Transaction recorded successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to log payment: " . $e->getMessage();
    }
    header("Location: record_payment.php");
    exit();
}

// Fetch lists for form and summary
try {
    $students = $pdo->query("
        SELECT s.student_id, u.Name, s.Roll_no 
        FROM students s 
        JOIN users u ON s.user_id = u.user_id 
        ORDER BY u.Name ASC
    ")->fetchAll();

    $fee_types = $pdo->query("SELECT * FROM fee_types ORDER BY due_date DESC")->fetchAll();

    $recent_payments = $pdo->query("
        SELECT fp.*, u.Name as student_name, ft.name as fee_name 
        FROM fee_payments fp
        JOIN students s ON fp.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        JOIN fee_types ft ON fp.fee_type_id = ft.fee_type_id
        ORDER BY fp.created_at DESC LIMIT 10
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
                    <h4 class="m-b-5 fw-bold">Financial Records</h4>
                    <p class="text-muted small">Capture student payments and manage institutional revenue streams.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Payment Entry Form -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-5">
                            <h5 class="fw-bold mb-4">Record New Payment</h5>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger py-2 small mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success py-2 small mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Student Account</label>
                                    <select name="student_id" class="form-control select2" required>
                                        <option value="" disabled selected>Search by name or roll no...</option>
                                        <?php foreach ($students as $row): ?>
                                            <option value="<?php echo $row['student_id']; ?>">
                                                <?php echo htmlspecialchars($row['Name'] . ' (' . $row['Roll_no'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Fee Category</label>
                                    <select name="fee_type_id" class="form-control" required>
                                        <option value="" disabled selected>Select fee type...</option>
                                        <?php foreach ($fee_types as $row): ?>
                                            <option value="<?php echo $row['fee_type_id']; ?>">
                                                <?php echo htmlspecialchars($row['name'] . ' (Rs. ' . number_format($row['amount']) . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Amount Received</label>
                                        <input type="number" name="amount_paid" class="form-control" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Payment Date</label>
                                        <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Payment Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="Paid">Fully Paid</option>
                                        <option value="Partial">Partial Payment</option>
                                        <option value="Unpaid">Logged as Unpaid</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Internal Remarks</label>
                                    <textarea name="remarks" class="form-control" placeholder="Cheque number, reference, etc." rows="2"></textarea>
                                </div>

                                <button type="submit" name="Record" class="btn btn-primary w-100 py-3 fw-bold shadow-sm rounded-pill">
                                    <i class="feather-credit-card me-2"></i> Submit Payment Record
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Recent Transactions</h5>
                            <a href="all_payments.php" class="small text-primary text-decoration-none fw-bold">View Ledger</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase">Student</th>
                                            <th class="py-3 text-muted small text-uppercase">Type</th>
                                            <th class="py-3 text-muted small text-uppercase">Amount</th>
                                            <th class="pe-4 py-3 text-end text-muted small text-uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($recent_payments) > 0): ?>
                                            <?php foreach ($recent_payments as $row): ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                                        <div class="small text-muted"><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></div>
                                                    </td>
                                                    <td><span class="small"><?php echo htmlspecialchars($row['fee_name']); ?></span></td>
                                                    <td class="fw-bold">Rs. <?php echo number_format($row['amount_paid']); ?></td>
                                                    <td class="pe-4 text-end">
                                                        <?php 
                                                            $badgeClass = ($row['status'] == 'Paid') ? 'bg-success-soft text-success' : (($row['status'] == 'Partial') ? 'bg-warning-soft text-warning' : 'bg-danger-soft text-danger');
                                                        ?>
                                                        <span class="badge <?php echo $badgeClass; ?> px-3 rounded-pill">
                                                            <?php echo $row['status']; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-5">No recent transactions recorded.</td></tr>
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
