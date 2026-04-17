<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('admin'); // Only admins allowed

try {
    // Fetch stats using secure PDO
    $stmtStudents = $pdo->query("SELECT COUNT(*) as total FROM students");
    $totalStudents = $stmtStudents->fetch()['total'];

    $stmtTeachers = $pdo->query("SELECT COUNT(*) as total FROM users WHERE Role = 'teacher'");
    $totalTeachers = $stmtTeachers->fetch()['total'];

    $stmtClasses = $pdo->query("SELECT COUNT(*) as total FROM classes");
    $totalClasses = $stmtClasses->fetch()['total'];

    $stmtPending = $pdo->query("SELECT COUNT(*) as total FROM users WHERE Role = 'student' AND Status = 'pending'");
    $totalPending = $stmtPending->fetch()['total'];

    // Fetch Recent Registrations
    $recentRegistrations = $pdo->query("
        SELECT u.Name, c.class_name, u.Status, u.user_id 
        FROM users u 
        LEFT JOIN students s ON u.user_id = s.user_id 
        LEFT JOIN classes c ON s.class_id = c.class_id 
        WHERE u.Role = 'student' 
        ORDER BY u.user_id DESC 
        LIMIT 5
    ")->fetchAll();
} catch (Exception $e) {
    die("Error fetching dashboard statistics: " . $e->getMessage());
}

$pageTitle = "Admin Dashboard";
?>

<?php include __DIR__ . '/../includes/navbar/admin_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <!-- Page Header -->
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Executive Dashboard</h4>
                    <p class="text-muted small">Welcome back, Admin. Here is what's happening today.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <a href="students.php" class="btn btn-primary btn-sm shadow-sm rounded-pill px-4">
                    <i class="feather-printer me-2"></i>Manage Students
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="container-fluid mt-4">
            <div class="row g-4">
                <div class="col-xl-3 col-md-6">
                    <a href="students.php" class="text-decoration-none transition-up">
                        <div class="stat-card hover-shadow transition-all">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary-soft text-primary me-3">
                                    <i class="feather-users"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($totalStudents); ?></h3>
                                    <p class="text-muted small mb-0">Total Students</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="view_teachers.php" class="text-decoration-none transition-up">
                        <div class="stat-card hover-shadow transition-all">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-secondary-soft text-secondary me-3">
                                    <i class="feather-user"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($totalTeachers); ?></h3>
                                    <p class="text-muted small mb-0">Active Teachers</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="Classes.php" class="text-decoration-none transition-up">
                        <div class="stat-card hover-shadow transition-all">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-accent-soft text-warning me-3">
                                    <i class="feather-book-open"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($totalClasses); ?></h3>
                                    <p class="text-muted small mb-0">Current Classes</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6">
                    <a href="registration_request.php" class="text-decoration-none transition-up">
                        <div class="stat-card hover-shadow transition-all">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-danger-soft text-danger me-3">
                                    <i class="feather-clock"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($totalPending); ?></h3>
                                    <p class="text-muted small mb-0">Approval Requests</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity / Placeholder for more content -->
            <div class="row mt-5">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-lg" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Recent Registrations</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase">Student</th>
                                            <th class="py-3 text-muted small text-uppercase">Class</th>
                                            <th class="py-3 text-muted small text-uppercase">Status</th>
                                            <th class="py-3 text-muted small text-uppercase">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($recentRegistrations) > 0): ?>
                                            <?php foreach ($recentRegistrations as $student): ?>
                                            <tr class="border-bottom">
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary-soft text-primary rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; font-size:12px;">
                                                            <?php 
                                                                $initials = "";
                                                                $words = explode(" ", $student['Name']);
                                                                foreach ($words as $w) $initials .= $w[0];
                                                                echo htmlspecialchars(strtoupper(substr($initials, 0, 2)));
                                                            ?>
                                                        </div>
                                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($student['Name']); ?></span>
                                                    </div>
                                                </td>
                                                <td><span class="text-muted small"><?php echo htmlspecialchars($student['class_name'] ?? 'Unassigned'); ?></span></td>
                                                <td>
                                                    <?php if ($student['Status'] == 'Approved'): ?>
                                                        <span class="badge bg-success-soft text-success rounded-pill px-3">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-soft text-warning rounded-pill px-3"><?php echo ucfirst($student['Status']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="students.php" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border-0" style="font-size: 11px;">Manage</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted small">No recent student registrations found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                         <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">System Health</h5>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="feather-shield text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="fw-bold">Security Shield Active</h6>
                            <p class="text-muted small">PDO Prepared Statements are protecting your database from SQL Injection.</p>
                            <button class="btn btn-outline-primary btn-sm rounded-pill w-100 mt-3">Run Audit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
