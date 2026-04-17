<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin');

$pageTitle = "Financial Setup";

if (isset($_POST['Add'])) {
    $name = trim($_POST['name']);
    $amount = $_POST['amount'];
    $class_id = $_POST['class_id'];
    $due_date = $_POST['due_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO fee_types (name, amount, class_id, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $amount, $class_id, $due_date]);
        $_SESSION['success'] = "Fee structure created successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to create fee type: " . $e->getMessage();
    }
    header("Location: fee_type.php");
    exit();
}

// Fetch Classes and existing Fee Types
try {
    $classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();
    
    $fee_types = $pdo->query("
        SELECT ft.*, c.class_name, c.section 
        FROM fee_types ft 
        JOIN classes c ON ft.class_id = c.class_id 
        ORDER BY ft.due_date DESC
    ")->fetchAll();
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
                    <h4 class="m-b-5 fw-bold">Fee Management</h4>
                    <p class="text-muted small">Configure tuition and subsidiary fees for different classes.</p>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Form Column -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-body p-5">
                            <h5 class="fw-bold mb-4">New Fee Structure</h5>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger py-2 small mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success py-2 small mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Fee Title</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Monthly Tuition - June" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Amount (PKR)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">Rs.</span>
                                        <input type="number" name="amount" class="form-control" placeholder="5000" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Applicable Class</label>
                                    <select name="class_id" class="form-control" required>
                                        <option value="" disabled selected>Choose class...</option>
                                        <?php foreach ($classes as $row): ?>
                                            <option value="<?php echo $row['class_id']; ?>">
                                                <?php echo htmlspecialchars($row['class_name'] . " - " . $row['section']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Payment Deadline</label>
                                    <input type="date" name="due_date" class="form-control" required>
                                </div>
                                <button type="submit" name="Add" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                    <i class="feather-plus-circle me-2"></i> Register Fee Type
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Table Column -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="fw-bold mb-0">Existing Fee Types</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase">Fee Name</th>
                                            <th class="py-3 text-muted small text-uppercase text-center">Class</th>
                                            <th class="py-3 text-muted small text-uppercase">Amount</th>
                                            <th class="py-3 text-muted small text-uppercase">Due Date</th>
                                            <th class="pe-4 py-3 text-end text-muted small text-uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($fee_types) > 0): ?>
                                            <?php foreach ($fee_types as $row): ?>
                                                <tr>
                                                    <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary-soft text-primary rounded-pill px-3">
                                                            <?php echo htmlspecialchars($row['class_name'] . " (" . $row['section'] . ")"); ?>
                                                        </span>
                                                    </td>
                                                    <td class="fw-bold">Rs. <?php echo number_format($row['amount']); ?></td>
                                                    <td class="small text-muted"><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                                                    <td class="pe-4 text-end">
                                                        <?php if (strtotime($row['due_date']) < time()): ?>
                                                            <span class="badge bg-danger-soft text-danger px-2">Expired</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success-soft text-success px-2">Active</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted small">No fee types configured yet.</td>
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
