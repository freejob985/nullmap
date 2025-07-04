<?php
require_once __DIR__ . '/helpers/database.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/validation.php';

$auth = Auth::getInstance();
$db = Database::getInstance();
$validation = Validation::getInstance();

// Require authentication
$auth->requireLogin();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المواقع الجغرافية</title>
    
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Material Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <!-- Toastify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">نظام إدارة المواقع الجغرافية</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#mapSection">
                            <i class="mdi mdi-map me-1"></i>
                            الخريطة
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#countriesSection">
                            <i class="mdi mdi-earth me-1"></i>
                            الدول
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#placesSection">
                            <i class="mdi mdi-map-marker me-1"></i>
                            الأماكن
                        </a>
                    </li>
                    <?php if ($auth->isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#usersSection">
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

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <!-- Map Section -->
        <section id="mapSection" class="mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">الخريطة</h5>
                </div>
                <div class="card-body p-0">
                    <div id="map" class="map-container"></div>
                </div>
            </div>
        </section>

        <!-- Countries Section -->
        <section id="countriesSection" class="mb-4">
            <?php include 'modals/countries.php'; ?>
        </section>

        <!-- Places Section -->
        <section id="placesSection" class="mb-4">
            <?php include 'modals/places.php'; ?>
        </section>

        <!-- Users Section -->
        <?php if ($auth->isAdmin()): ?>
        <section id="usersSection" class="mb-4">
            <?php include 'modals/users.php'; ?>
        </section>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Toastify -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
</body>
</html>
