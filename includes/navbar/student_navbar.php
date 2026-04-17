<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header p-4 d-flex flex-column align-items-center">
            <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="img-fluid mb-2" style="max-height: 45px;">
            <h5 class="text-dark fw-bold mb-0">Zenith <span class="text-primary">Learn</span></h5>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Main Menu</label>
                </li>
                
                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'student.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>student/student.php" class="nxl-link" data-tooltip="Dashboard">
                        <span class="nxl-micon"><i class="feather-grid"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'subjects.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>student/subjects.php" class="nxl-link" data-tooltip="My Subjects">
                        <span class="nxl-micon"><i class="feather-book"></i></span>
                        <span class="nxl-mtext">My Subjects</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'announcements.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>student/announcements.php" class="nxl-link" data-tooltip="Announcements">
                        <span class="nxl-micon"><i class="feather-megaphone"></i></span>
                        <span class="nxl-mtext">Announcements</span>
                    </a>
                </li>

                <li class="nxl-item nxl-caption">
                    <label>Academics & Finance</label>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'result.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>student/result.php" class="nxl-link" data-tooltip="Exams">
                        <span class="nxl-micon"><i class="feather-award"></i></span>
                        <span class="nxl-mtext">Examination Results</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'attendance.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>student/attendance.php" class="nxl-link" data-tooltip="Attendance">
                        <span class="nxl-micon"><i class="feather-calendar"></i></span>
                        <span class="nxl-mtext">Attendance Log</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'fee.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>student/fee.php" class="nxl-link" data-tooltip="Fees">
                        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                        <span class="nxl-mtext">Fee Details</span>
                    </a>
                </li>
                
                <li class="nxl-item nxl-caption">
                    <label>Profile</label>
                </li>
                
                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php' || basename($_SERVER['PHP_SELF']) == 'profile_edit.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>student/profile.php" class="nxl-link" data-tooltip="Profile">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">My Profile</span>
                    </a>
                </li>

                <li class="nxl-item">
                    <a href="<?php echo BASE_URL; ?>change_password.php" class="nxl-link" data-tooltip="Security">
                        <span class="nxl-micon"><i class="feather-lock"></i></span>
                        <span class="nxl-mtext">Change Password</span>
                    </a>
                </li>

                <li class="nxl-item">
                    <a href="<?php echo BASE_URL; ?>Logout.php" class="nxl-link text-danger-soft">
                        <span class="nxl-micon text-danger"><i class="feather-log-out"></i></span>
                        <span class="nxl-mtext text-danger">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
