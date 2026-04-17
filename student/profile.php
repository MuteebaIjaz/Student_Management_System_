<?php
require_once __DIR__ . '/../includes/layout_header.php';
protectPage('student');

$pageTitle = "Personal Identity";
$user_id = $_SESSION['user_id'];

try {
    // Fetch Student & User Data
    $stmt = $pdo->prepare("
        SELECT s.*, u.Email, u.Name, s.Profile_Image AS User_Image, c.class_name, c.section
        FROM students s 
        JOIN users u ON s.user_id = u.user_id 
        LEFT JOIN classes c ON s.class_id = c.class_id
        WHERE s.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch();

    if (!$student) {
        // Redirect to profile completion if record is missing
        header("Location: ../Complete_profile.php");
        exit();
    }

    $profileImage = !empty($student['User_Image']) ? BASE_URL . 'assets/images/profile/' . $student['User_Image'] : $Default_Avatar;

} catch (Exception $e) {
    die("Profile Retrieval Error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/navbar/student_navbar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header px-4 pt-4">
            <div class="page-header-left">
                <div class="page-header-title">
                    <h4 class="m-b-5 fw-bold">Student Profile</h4>
                    <p class="text-muted small">Your official academic identity and contact record.</p>
                </div>
            </div>
            <div class="page-header-right ms-auto">
                <a href="profile_edit.php" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="feather-edit-3 me-2"></i> Edit Profile
                </a>
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Profile Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius); overflow: hidden;">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4 position-relative d-inline-block">
                                <img src="<?php echo $profileImage; ?>" class="rounded-circle border border-4 border-white shadow" style="width: 151px; height: 151px; object-fit: cover;">
                                <div class="position-absolute bottom-0 end-0 bg-success border border-white border-3 rounded-circle" style="width: 25px; height: 25px;"></div>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($student['Name']); ?></h4>
                            <p class="text-muted small mb-3">Enrolled Student</p>
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold"><?php echo htmlspecialchars($student['class_name'] . ' - ' . $student['section']); ?></span>
                                <span class="badge bg-dark-soft text-dark px-3 py-2 rounded-pill fw-bold">#<?php echo htmlspecialchars($student['Roll_no']); ?></span>
                            </div>
                        </div>
                        <div class="bg-gray-100 p-4 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small fw-bold">Student ID</span>
                                <span class="text-dark small fw-bold"><?php echo str_pad($student['student_id'], 5, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small fw-bold">Academic Status</span>
                                <span class="text-success small fw-bold">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Column -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="fw-bold mb-0">Identity Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="flex-grow-1">
                                        <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Full Legal Name</div>
                                        <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($student['Name']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="flex-grow-1">
                                        <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Email Correspondence</div>
                                        <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($student['Email']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="flex-grow-1">
                                        <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Mobile Contact</div>
                                        <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($student['phone'] ?: 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="flex-grow-1">
                                        <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Date of Birth</div>
                                        <div class="fw-bold text-dark fs-5"><?php echo $student['dob'] ? date('M d, Y', strtotime($student['dob'])) : 'N/A'; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="flex-grow-1">
                                        <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Gender Expression</div>
                                        <div class="fw-bold text-dark fs-5"><?php echo ucfirst(htmlspecialchars($student['gender'] ?: 'N/A')); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="flex-grow-1 pt-3 border-top mt-2">
                                        <div class="text-muted fs-11 text-uppercase fw-bold mb-1">Residential Address</div>
                                        <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($student['address'] ?: 'No address specified in record.'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4" style="border-radius: var(--radius);">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-soft text-primary rounded-circle p-3 me-3">
                                    <i class="feather-shield fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Data Security Notice</h6>
                                    <p class="text-muted small mb-0">Your profile information is only visible to you and the academic administration department.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
