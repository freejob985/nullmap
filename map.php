<?php
/**
 * Map Page
 * 
 * Displays an interactive map with all places marked.
 * Users can filter places by country and type.
 */

require_once __DIR__ . '/helpers/database.php';
require_once __DIR__ . '/helpers/auth.php';

// Initialize helpers
$db = Database::getInstance();
$auth = Auth::getInstance();

// Require authentication
$auth->requireLogin();

// Get countries for filter
$stmt = $db->getConnection()->query('SELECT id, name FROM countries ORDER BY name');
$countries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الخريطة - نظام إدارة المواقع الجغرافية</title>
    
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Material Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <!-- MarkerCluster -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        #map {
            height: calc(100vh - 150px);
            min-height: 500px;
        }
        .filter-card {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 300px;
        }
        .legend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .legend-item {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            margin-left: 8px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <!-- Map Container -->
        <div class="position-relative">
            <!-- Filters -->
            <div class="filter-card">
                <h6 class="mb-3">تصفية المواقع</h6>
                <div class="mb-3">
                    <label for="countryFilter" class="form-label">الدولة</label>
                    <select class="form-select form-select-sm" id="countryFilter">
                        <option value="">الكل</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>">
                                <?php echo htmlspecialchars($country['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="typeFilter" class="form-label">النوع</label>
                    <select class="form-select form-select-sm" id="typeFilter">
                        <option value="">الكل</option>
                        <option value="خاص">خاص</option>
                        <option value="حكومة">حكومة</option>
                    </select>
                </div>
            </div>

            <!-- Legend -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #4CAF50;"></div>
                    <span>خاص</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #2196F3;"></div>
                    <span>حكومة</span>
                </div>
            </div>

            <!-- Map -->
            <div id="map"></div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- MarkerCluster -->
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize map
            const map = L.map('map').setView([24.7136, 46.6753], 4);
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Initialize marker cluster group
            const markers = L.markerClusterGroup();
            let allMarkers = [];

            // Load places
            function loadPlaces() {
                const countryId = $('#countryFilter').val();
                const type = $('#typeFilter').val();
                
                // Clear existing markers
                markers.clearLayers();
                allMarkers = [];

                // Fetch places
                $.get('api/places.php', function(response) {
                    response.data.forEach(place => {
                        // Apply filters
                        if ((countryId && place.country_id != countryId) || 
                            (type && place.type != type)) {
                            return;
                        }

                        // Create marker
                        const marker = L.marker([place.latitude, place.longitude], {
                            icon: L.divIcon({
                                className: 'custom-marker',
                                html: `<div style="background-color: ${place.type === 'خاص' ? '#4CAF50' : '#2196F3'}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>`,
                                iconSize: [12, 12]
                            })
                        });

                        // Add popup
                        marker.bindPopup(`
                            <div class="text-center">
                                <h6 class="mb-2">${place.name}</h6>
                                <p class="mb-1">
                                    <strong>الدولة:</strong> ${place.country_name}<br>
                                    <strong>المدينة:</strong> ${place.city}<br>
                                    <strong>النوع:</strong> ${place.type}<br>
                                    <strong>العدد:</strong> ${place.total}
                                </p>
                            </div>
                        `);

                        allMarkers.push(marker);
                        markers.addLayer(marker);
                    });

                    map.addLayer(markers);

                    // Fit bounds if markers exist
                    if (allMarkers.length > 0) {
                        const group = L.featureGroup(allMarkers);
                        map.fitBounds(group.getBounds());
                    }
                });
            }

            // Handle filter changes
            $('#countryFilter, #typeFilter').on('change', loadPlaces);

            // Initial load
            loadPlaces();
        });
    </script>
</body>
</html> 