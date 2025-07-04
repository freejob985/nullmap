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
    
    // Get places by type for pie chart
    $stmt = $db->getConnection()->query('SELECT type, COUNT(*) as count FROM places GROUP BY type');
    $placesByType = $stmt->fetchAll();
    
    // Get top 5 countries by places count for bar chart
    $stmt = $db->getConnection()->query('SELECT c.name, COUNT(p.id) as count 
                                        FROM countries c 
                                        JOIN places p ON c.id = p.country_id 
                                        GROUP BY c.id 
                                        ORDER BY count DESC 
                                        LIMIT 5');
    $topCountriesByPlaces = $stmt->fetchAll();
    
    // Get places added per month for line chart (last 6 months)
    $stmt = $db->getConnection()->query('SELECT DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count 
                                        FROM places 
                                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                                        GROUP BY month 
                                        ORDER BY month ASC');
    $placesPerMonth = $stmt->fetchAll();
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
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

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">الرسوم البيانية</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Places by Type Pie Chart -->
                            <div class="col-md-4">
                                <div class="chart-container">
                                    <canvas id="placesByTypeChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Top Countries Bar Chart -->
                            <div class="col-md-4">
                                <div class="chart-container">
                                    <canvas id="topCountriesChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Places per Month Line Chart -->
                            <div class="col-md-4">
                                <div class="chart-container">
                                    <canvas id="placesPerMonthChart"></canvas>
                                </div>
                            </div>
                        </div>
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
    
    <!-- Chart.js Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set Chart.js default options for RTL support
            Chart.defaults.font.family = 'Tajawal, sans-serif';
            Chart.defaults.color = '#666';
            
            // Places by Type Pie Chart
            const placesByTypeCtx = document.getElementById('placesByTypeChart').getContext('2d');
            const placesByTypeChart = new Chart(placesByTypeCtx, {
                type: 'pie',
                data: {
                    labels: [
                        <?php 
                        $typeLabels = [];
                        foreach ($placesByType as $type) {
                            // Translate type values
                            $label = $type['type'] === 'private' ? 'خاص' : 'حكومي';
                            $typeLabels[] = "'$label'";
                        }
                        echo implode(',', $typeLabels);
                        ?>
                    ],
                    datasets: [{
                        data: [
                            <?php 
                            $typeCounts = [];
                            foreach ($placesByType as $type) {
                                $typeCounts[] = $type['count'];
                            }
                            echo implode(',', $typeCounts);
                            ?>
                        ],
                        backgroundColor: [
                            '#4e73df',
                            '#1cc88a',
                            '#36b9cc',
                            '#f6c23e',
                            '#e74a3b'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'توزيع الأماكن حسب النوع'
                        }
                    }
                }
            });
            
            // Top Countries Bar Chart
            const topCountriesCtx = document.getElementById('topCountriesChart').getContext('2d');
            const topCountriesChart = new Chart(topCountriesCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php 
                        $countryNames = [];
                        foreach ($topCountriesByPlaces as $country) {
                            $countryNames[] = "'" . htmlspecialchars($country['name']) . "'";
                        }
                        echo implode(',', $countryNames);
                        ?>
                    ],
                    datasets: [{
                        label: 'عدد الأماكن',
                        data: [
                            <?php 
                            $countryCounts = [];
                            foreach ($topCountriesByPlaces as $country) {
                                $countryCounts[] = $country['count'];
                            }
                            echo implode(',', $countryCounts);
                            ?>
                        ],
                        backgroundColor: '#36b9cc',
                        borderColor: '#2c9faf',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'أعلى الدول من حيث عدد الأماكن'
                        }
                    }
                }
            });
            
            // Places per Month Line Chart
            const placesPerMonthCtx = document.getElementById('placesPerMonthChart').getContext('2d');
            const placesPerMonthChart = new Chart(placesPerMonthCtx, {
                type: 'line',
                data: {
                    labels: [
                        <?php 
                        $months = [];
                        foreach ($placesPerMonth as $month) {
                            // Convert YYYY-MM to Arabic month name
                            $date = new DateTime($month['month'] . '-01');
                            $monthName = $date->format('F Y');
                            // You can add a function to translate month names to Arabic if needed
                            $months[] = "'$monthName'";
                        }
                        echo implode(',', $months);
                        ?>
                    ],
                    datasets: [{
                        label: 'الأماكن المضافة',
                        data: [
                            <?php 
                            $monthlyCounts = [];
                            foreach ($placesPerMonth as $month) {
                                $monthlyCounts[] = $month['count'];
                            }
                            echo implode(',', $monthlyCounts);
                            ?>
                        ],
                        fill: false,
                        borderColor: '#4e73df',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'الأماكن المضافة شهرياً'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
