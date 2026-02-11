<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_folder = basename(dirname($_SERVER['PHP_SELF']));
?>

<!-- Main Wrapper -->
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark-blue border-right" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 primary-text">
            <h3 class="fw-bold text-white"><i class="bi bi-calendar-check me-2"></i>UniAttend</h3>
            <p class="text-white-50 small">University Management</p>
        </div>
        
        <div class="list-group list-group-flush my-3">
            <?php if (isAdmin()): ?>
                <!-- ========== ADMIN MENU ========== -->
                <a href="<?php echo BASE_URL; ?>modules/admin/dashboard.php" 
                   class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'dashboard.php' && $current_folder == 'admin') ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                
                <a href="<?php echo BASE_URL; ?>modules/admin/students.php" 
                   class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
                    <i class="bi bi-people me-2"></i>Students
                </a>
                
                <a href="<?php echo BASE_URL; ?>modules/admin/manage-classes.php" 
                   class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'manage-classes.php') ? 'active' : ''; ?>">
                    <i class="bi bi-building me-2"></i>Classes
                </a>
                
                <a href="<?php echo BASE_URL; ?>modules/admin/attendance.php" 
                   class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>">
                    <i class="bi bi-clipboard-check me-2"></i>Attendance
                </a>
                
                <a href="<?php echo BASE_URL; ?>modules/admin/reports.php" 
                   class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i>Reports
                </a>
                
            <?php elseif (isTeacher()): ?>
                <!-- ========== TEACHER MENU ========== -->
                <a href="<?php echo BASE_URL; ?>modules/teacher/dashboard.php" 
                   class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'dashboard.php' && $current_folder == 'teacher') ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                
                <a href="<?php echo BASE_URL; ?>modules/teacher/take-attendance.php" 
                   class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'take-attendance.php') ? 'active' : ''; ?>">
                    <i class="bi bi-clipboard-check me-2"></i>Take Attendance
                </a>
                
                <a href="<?php echo BASE_URL; ?>modules/teacher/view-reports.php" 
                   class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'view-reports.php') ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i>My Reports
                </a>
                
            <?php endif; ?>
            
            <!-- ========== COMMON LINKS (BOTH ROLES) ========== -->
            <div class="border-top border-light my-3"></div>
            
            <a href="<?php echo BASE_URL; ?>profile.php" 
               class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-circle me-2"></i>Profile
            </a>
            
            <a href="<?php echo BASE_URL; ?>logout.php" 
               class="list-group-item list-group-item-action bg-transparent text-danger">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->
    
    <!-- Page Content -->
    <div id="page-content-wrapper">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-primary" id="menu-toggle">
                    <i class="bi bi-list"></i>
                </button>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $_SESSION['profile_image'] ?? 'default-avatar.png'; ?>" 
                                 class="rounded-circle me-2 border" width="40" height="40" alt="Profile">
                            <div>
                                <span class="fw-bold d-block text-dark"><?php echo $_SESSION['full_name'] ?? 'User'; ?></span>
                                <span class="badge bg-<?php echo isAdmin() ? 'danger' : 'info'; ?> small">
                                    <?php echo isAdmin() ? 'Administrator' : 'Teacher'; ?>
                                </span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php">
                                    <i class="bi bi-person me-2"></i>My Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content Container -->
        <div class="container-fluid px-4 py-4">