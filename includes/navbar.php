<?php
/**
 * Shared Navigation Bar
 * 
 * This file contains the navigation bar that is shared across all pages.
 * It includes links to all main sections and user menu.
 */

// Get current page filename
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="mdi mdi-map-marker-radius me-2"></i>
            نظام إدارة المواقع الجغرافية
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="mdi mdi-view-dashboard me-1"></i>
                        لوحة التحكم
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'map.php' ? 'active' : ''; ?>" href="map.php">
                        <i class="mdi mdi-map me-1"></i>
                        الخريطة
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'countries.php' ? 'active' : ''; ?>" href="countries.php">
                        <i class="mdi mdi-earth me-1"></i>
                        الدول
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'places.php' ? 'active' : ''; ?>" href="places.php">
                        <i class="mdi mdi-map-marker me-1"></i>
                        الأماكن
                    </a>
                </li>
                <?php if ($auth->isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>" href="users.php">
                        <i class="mdi mdi-account-group me-1"></i>
                        المستخدمون
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="mdi mdi-account-circle me-1"></i>
                        <?php echo htmlspecialchars($auth->getCurrentUser()['name']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="login.php?logout=1">
                            <i class="mdi mdi-logout me-1"></i>
                            تسجيل الخروج
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav> 