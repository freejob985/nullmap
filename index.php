<?php
/**
 * Main Dashboard Page
 * 
 * Shows statistics and main navigation for the application.
 */

require_once __DIR__ . '/helpers/database.php';
require_once __DIR__ . '/helpers/auth.php';

// Initialize helpers
$db = Database::getInstance();
$auth = Auth::getInstance();

// Require authentication
$auth->requireLogin();

// Get statistics
try {
    // Count countries
    $stmt = $db->getConnection()->query('SELECT COUNT(*) as count FROM countries');
    $countriesCount = $stmt->fetch()['count'];

    // Count places
    $stmt = $db->getConnection()->query('SELECT COUNT(*) as count FROM places');
    $placesCount = $stmt->fetch()['count'];

    // Count users
    $stmt = $db->getConnection()->query('SELECT COUNT(*) as count FROM users');
    $usersCount = $stmt->fetch()['count'];

    // Get recent countries
    $stmt = $db->getConnection()->query('SELECT * FROM countries ORDER BY created_at DESC LIMIT 5');
    $recentCountries = $stmt->fetchAll();

    // Get recent places
    $stmt = $db->getConnection()->query('SELECT p.*, c.name as country_name FROM places p JOIN countries c ON p.country_id = c.id ORDER BY p.created_at DESC LIMIT 5');
    $recentPlaces = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Dashboard statistics error: " . $e->getMessage());
    $error = 'حدث خطأ في جلب الإحصائيات';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - نظام إدارة المواقع الجغرافية</title>
    
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Material Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="mdi mdi-earth text-primary mb-3" style="font-size: 2.5rem;"></i>
                        <h3 class="card-title"><?php echo number_format($countriesCount); ?></h3>
                        <p class="card-text">الدول</p>
                        <a href="countries.php" class="btn btn-primary">
                            <i class="mdi mdi-arrow-left-bold"></i>
                            عرض الدول
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="mdi mdi-map-marker text-success mb-3" style="font-size: 2.5rem;"></i>
                        <h3 class="card-title"><?php echo number_format($placesCount); ?></h3>
                        <p class="card-text">الأماكن</p>
                        <a href="places.php" class="btn btn-success">
                            <i class="mdi mdi-arrow-left-bold"></i>
                            عرض الأماكن
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="mdi mdi-account-group text-info mb-3" style="font-size: 2.5rem;"></i>
                        <h3 class="card-title"><?php echo number_format($usersCount); ?></h3>
                        <p class="card-text">المستخدمون</p>
                        <a href="users.php" class="btn btn-info">
                            <i class="mdi mdi-arrow-left-bold"></i>
                            عرض المستخدمين
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Countries -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">أحدث الدول</h5>
                        <a href="countries.php" class="btn btn-sm btn-primary">عرض الكل</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentCountries)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الاسم</th>
                                            <th>المدينة</th>
                                            <th>تاريخ الإضافة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentCountries as $country): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($country['name']); ?></td>
                                                <td><?php echo htmlspecialchars($country['city']); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($country['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted my-3">لا توجد دول مضافة حديثاً</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Places -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">أحدث الأماكن</h5>
                        <a href="places.php" class="btn btn-sm btn-success">عرض الكل</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentPlaces)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الاسم</th>
                                            <th>الدولة</th>
                                            <th>النوع</th>
                                            <th>تاريخ الإضافة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentPlaces as $place): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($place['name']); ?></td>
                                                <td><?php echo htmlspecialchars($place['country_name']); ?></td>
                                                <td><?php echo htmlspecialchars($place['type']); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($place['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted my-3">لا توجد أماكن مضافة حديثاً</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
