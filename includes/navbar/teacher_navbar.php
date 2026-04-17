<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header p-4 d-flex flex-column align-items-center">
            <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="img-fluid mb-2" style="max-height: 45px;">
            <h5 class="text-dark fw-bold mb-0">Zenith <span class="text-primary">Learn</span></h5>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Faculty Menu</label>
                </li>
                
                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'teacher.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>teacher/teacher.php" class="nxl-link" data-tooltip="Dashboard">
                        <span class="nxl-micon"><i class="feather-grid"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Students.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>teacher/Students.php" class="nxl-link" data-tooltip="My Students">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">My Students</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Classes.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>teacher/Classes.php" class="nxl-link" data-tooltip="My Classes">
                        <span class="nxl-micon"><i class="feather-book-open"></i></span>
                        <span class="nxl-mtext">My Classes</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'announcements.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>teacher/announcements.php" class="nxl-link" data-tooltip="Announcements">
                        <span class="nxl-micon"><i class="feather-megaphone"></i></span>
                        <span class="nxl-mtext">Announcements</span>
                    </a>
                </li>

                <li class="nxl-item nxl-caption">
                    <label>Academic Tools</label>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'result.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>teacher/result.php" class="nxl-link" data-tooltip="Manage Results">
                        <span class="nxl-micon"><i class="feather-award"></i></span>
                        <span class="nxl-mtext">Manage Results</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'mark_attendance.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>teacher/mark_attendance.php" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-check-square"></i></span>
                        <span class="nxl-mtext">Attendance Tracking</span>
                    </a>
                </li>
                
                <li class="nxl-item nxl-caption">
                    <label>Account</label>
                </li>
                
                <li class="nxl-item">
                    <a href="<?php echo BASE_URL; ?>change_password.php" class="nxl-link">
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
