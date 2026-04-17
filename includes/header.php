<header class="nxl-header">
    <div class="header-wrapper">
        <div class="header-left d-flex align-items-center gap-4">
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>
            <div class="header-search d-none d-lg-flex">
                <i class="feather-search"></i>
                <input type="text" class="form-control" id="globalSearchInput" placeholder="Search anything...">
            </div>
        </div>
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" class="p-2 position-relative" data-bs-toggle="dropdown">
                        <i class="feather-bell fs-18"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-primary border border-light rounded-circle"></span>
                    </a>
                </div>
                
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <img src="<?php echo $Profile_Picture; ?>" alt="user-image" class="img-fluid user-avtar me-0 rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; aspect-ratio: 1/1; border: 2px solid var(--primary);" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown shadow-lg border-0">
                        <div class="dropdown-header bg-gray-100 rounded-top">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $Profile_Picture; ?>" class="rounded-circle me-3 shadow-sm" style="width: 45px; height: 45px; object-fit: cover; aspect-ratio: 1/1; border: 2px solid var(--gray-200);">
                                <div>
                                    <h6 class="text-dark mb-0 fw-bold"><?php echo htmlspecialchars($user_name); ?></h6>
                                    <span class="fs-11 text-muted text-uppercase"><?php echo htmlspecialchars($user_role); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="p-2">
                             <a href="<?php echo BASE_URL; ?>profile_edit.php" class="dropdown-item rounded">
                                 <i class="feather-user me-2"></i>
                                 <span>My Profile</span>
                             </a>
                            <a href="<?php echo BASE_URL; ?>settings.php" class="dropdown-item rounded">
                                <i class="feather-settings me-2"></i>
                                <span>Settings</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo BASE_URL; ?>Logout.php" class="dropdown-item rounded text-danger">
                                <i class="feather-log-out me-2"></i>
                                <span>Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
