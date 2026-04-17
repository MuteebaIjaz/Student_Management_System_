<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header p-4 d-flex flex-column align-items-center">
            <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="img-fluid mb-2" style="max-height: 45px;">
            <h5 class="text-dark fw-bold mb-0">Zenith <span class="text-primary">Learn</span></h5>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Principal Menu</label>
                </li>
                
                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/admin.php" class="nxl-link" data-tooltip="Dashboard">
                        <span class="nxl-micon"><i class="feather-grid"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="nxl-item <?php echo (basename($_SERVER['PHP_SELF']) == 'announcements.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/announcements.php" class="nxl-link" data-tooltip="Announcements">
                        <span class="nxl-micon"><i class="feather-megaphone"></i></span>
                        <span class="nxl-mtext">Announcements</span>
                    </a>
                </li>

                <?php 
                    $current_page = basename($_SERVER['PHP_SELF']);
                    $students_active = in_array($current_page, ['registration_request.php', 'students.php', 'Classes.php']);
                ?>
                <li class="nxl-item nxl-hasmenu <?php echo $students_active ? 'active nxl-trigger' : ''; ?>">
                    <a href="javascript:void(0);" class="nxl-link" data-tooltip="Students">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">Students</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu" style="<?php echo $students_active ? 'display: block;' : ''; ?>">
                        <li class="nxl-item <?php echo ($current_page == 'registration_request.php') ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/registration_request.php">Approval Requests</a></li>
                        <li class="nxl-item <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/students.php">All Students</a></li>
                        <li class="nxl-item <?php echo ($current_page == 'Classes.php') ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/Classes.php">Class Rosters</a></li>
                    </ul>
                </li>

                <?php 
                    $faculty_active = in_array($current_page, ['view_teachers.php', 'add_teacher.php']);
                ?>
                <li class="nxl-item nxl-hasmenu <?php echo $faculty_active ? 'active nxl-trigger' : ''; ?>">
                    <a href="javascript:void(0);" class="nxl-link" data-tooltip="Faculty">
                        <span class="nxl-micon"><i class="feather-briefcase"></i></span>
                        <span class="nxl-mtext">Faculty</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu" style="<?php echo $faculty_active ? 'display: block;' : ''; ?>">
                        <li class="nxl-item <?php echo ($current_page == 'view_teachers.php' && !isset($_GET['action'])) ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/view_teachers.php">Faculty Directory</a></li>
                        <li class="nxl-item <?php echo (isset($_GET['action']) && $_GET['action'] == 'add') ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/view_teachers.php?action=add">Add Faculty</a></li>
                    </ul>
                </li>

                <?php 
                    $academic_active = in_array($current_page, ['subjects.php']);
                ?>
                <li class="nxl-item nxl-hasmenu <?php echo $academic_active ? 'active nxl-trigger' : ''; ?>">
                    <a href="javascript:void(0);" class="nxl-link" data-tooltip="Academic">
                        <span class="nxl-micon"><i class="feather-book"></i></span>
                        <span class="nxl-mtext">Academic</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu" style="<?php echo $academic_active ? 'display: block;' : ''; ?>">
                        <li class="nxl-item <?php echo ($current_page == 'Classes.php' && !isset($_GET['class_id'])) ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/Classes.php">Manage Classes</a></li>
                        <li class="nxl-item <?php echo ($current_page == 'subjects.php') ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/subjects.php">Manage Subjects</a></li>
                    </ul>
                </li>

                <li class="nxl-item nxl-caption">
                    <label>Operations</label>
                </li>

                <li class="nxl-item">
                    <a href="<?php echo BASE_URL; ?>admin/mark_attendance.php" class="nxl-link" data-tooltip="Attendance">
                        <span class="nxl-micon"><i class="feather-check-square"></i></span>
                        <span class="nxl-mtext">Attendance</span>
                    </a>
                </li>

                <li class="nxl-item">
                    <a href="<?php echo BASE_URL; ?>admin/assignment.php" class="nxl-link" data-tooltip="Assignments">
                        <span class="nxl-micon"><i class="feather-file-text"></i></span>
                        <span class="nxl-mtext">Assignments</span>
                    </a>
                </li>

                <?php 
                    $finance_active = in_array($current_page, ['fee_type.php', 'record_payment.php']);
                ?>
                <li class="nxl-item nxl-hasmenu <?php echo $finance_active ? 'active nxl-trigger' : ''; ?>">
                    <a href="javascript:void(0);" class="nxl-link" data-tooltip="Financials">
                        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                        <span class="nxl-mtext">Financials</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu" style="<?php echo $finance_active ? 'display: block;' : ''; ?>">
                        <li class="nxl-item <?php echo ($current_page == 'fee_type.php') ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/fee_type.php">Fee Setup</a></li>
                        <li class="nxl-item <?php echo ($current_page == 'record_payment.php') ? 'active' : ''; ?>"><a class="nxl-link" href="<?php echo BASE_URL; ?>admin/record_payment.php">Payments</a></li>
                    </ul>
                </li>
                
                <li class="nxl-item nxl-caption">
                    <label>Settings</label>
                </li>
                
                <li class="nxl-item">
                    <a href="<?php echo BASE_URL; ?>Logout.php" class="nxl-link text-danger-soft">
                        <span class="nxl-micon text-danger"><i class="feather-log-out"></i></span>
                        <span class="nxl-mtext text-danger">Logout</span>
                    </a>
                </li>
            </ul>
            
            <div class="p-4 mt-5">
                <div class="card bg-primary text-white border-0 shadow-sm" style="border-radius: var(--radius);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="feather-shield-reveal me-2"></i>
                            <span class="small fw-bold">PRO Active</span>
                        </div>
                        <p class="fs-11 mb-0 opacity-75">Zenith Learn SMS is running in production-ready mode.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
