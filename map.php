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
            width: 30px;
            height: 30px;
            margin-left: 10px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .legend-color i {
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        /* تنسيق العلامات المخصصة */
        .custom-marker-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);
            border: 3px solid white;
            transition: all 0.3s ease;
            width: 36px !important;
            height: 36px !important;
        }
        .custom-marker-icon:hover {
            transform: scale(1.3);
            z-index: 1001;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.8);
        }
        .marker-private {
            background-color: #4CAF50;
        }
        .marker-government {
            background-color: #2196F3;
        }
        .custom-marker-icon i {
            color: white;
            font-size: 18px !important;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
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
                    <label for="cityFilter" class="form-label">المدينة</label>
                    <select class="form-select form-select-sm" id="cityFilter">
                        <option value="">الكل</option>
                        <!-- سيتم تحميل المدن ديناميكياً عند اختيار الدولة -->
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
                <div class="mb-3">
                    <label for="placeFilter" class="form-label">الأماكن</label>
                    <select class="form-select form-select-sm" id="placeFilter">
                        <option value="">الكل</option>
                        <!-- سيتم تحميل الأماكن ديناميكياً -->
                    </select>
                </div>
            </div>

            <!-- Legend -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #4CAF50; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 2px solid white; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);">
                        <i class="mdi mdi-home" style="color: white; font-size: 16px; font-weight: bold;"></i>
                    </div>
                    <span>خاص</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #2196F3; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 2px solid white; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);">
                        <i class="mdi mdi-office-building" style="color: white; font-size: 16px; font-weight: bold;"></i>
                    </div>
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
            let cities = [];
            let places = []; // مصفوفة لتخزين جميع الأماكن

            /**
             * تحميل المدن المتاحة بناءً على الدولة المختارة
             * @param {number} countryId - معرف الدولة المختارة
             */
            function loadCities(countryId) {
                // تفريغ قائمة المدن
                $('#cityFilter').empty().append('<option value="">الكل</option>');
                
                if (!countryId) {
                    return;
                }
                
                // تحميل المدن الفريدة من البيانات المتاحة
                const uniqueCities = [...new Set(cities.filter(city => city.country_id == countryId).map(city => city.city))];
                uniqueCities.sort().forEach(city => {
                    $('#cityFilter').append(`<option value="${city}">${city}</option>`);
                });
            }
            
            /**
             * تحميل الأماكن المتاحة بناءً على الدولة والمدينة المختارة
             * @param {number} countryId - معرف الدولة المختارة
             * @param {string} cityName - اسم المدينة المختارة
             */
            function loadPlaceOptions(countryId, cityName) {
                // تفريغ قائمة الأماكن
                $('#placeFilter').empty().append('<option value="">الكل</option>');
                
                // تصفية الأماكن حسب الدولة والمدينة
                let filteredPlaces = places;
                
                if (countryId) {
                    filteredPlaces = filteredPlaces.filter(place => place.country_id == countryId);
                }
                
                if (cityName) {
                    filteredPlaces = filteredPlaces.filter(place => place.city === cityName);
                }
                
                // تحميل الأماكن الفريدة
                const uniquePlaces = filteredPlaces.map(place => ({
                    id: place.id,
                    name: place.name
                }));
                
                // ترتيب الأماكن أبجدياً
                uniquePlaces.sort((a, b) => a.name.localeCompare(b.name));
                
                // إضافة الأماكن إلى القائمة المنسدلة
                uniquePlaces.forEach(place => {
                    $('#placeFilter').append(`<option value="${place.id}">${place.name}</option>`);
                });
            }

            /**
             * إنشاء أيقونة مخصصة للعلامة على الخريطة
             * @param {string} type - نوع المكان (خاص أو حكومة أو private أو government)
             * @returns {L.DivIcon} - أيقونة مخصصة للعلامة
             */
            function createCustomIcon(type) {
                // التحقق من نوع المكان بغض النظر عن اللغة
                const isPrivate = type === 'خاص' || type === 'private';
                const iconClass = isPrivate ? 'marker-private' : 'marker-government';
                const iconName = isPrivate ? 'mdi-home' : 'mdi-office-building';
                
                return L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="custom-marker-icon ${iconClass}"><i class="mdi ${iconName}"></i></div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                    popupAnchor: [0, -18]
                });
            }

            /**
             * تحويل نوع المكان من الإنجليزية إلى العربية
             * @param {string} type - نوع المكان بالإنجليزية (private أو government)
             * @returns {string} - نوع المكان بالعربية (خاص أو حكومة)
             */
            function translatePlaceType(type) {
                if (!type) return 'غير محدد';
                
                switch(type.toLowerCase()) {
                    case 'private': return 'خاص';
                    case 'government': return 'حكومة';
                    default: return type; // إرجاع القيمة الأصلية إذا كانت بالفعل بالعربية أو غير معروفة
                }
            }
            
            /**
             * تحميل الأماكن وعرضها على الخريطة
             */
            function loadPlaces() {
                const countryId = $('#countryFilter').val();
                const cityName = $('#cityFilter').val();
                const type = $('#typeFilter').val();
                const placeId = $('#placeFilter').val();
                
                // تفريغ العلامات الموجودة
                markers.clearLayers();
                allMarkers = [];

                // جلب الأماكن من API
                $.get('api/places.php', function(response) {
                    // تخزين جميع المدن والأماكن للاستخدام في التصفية
                    cities = response.data;
                    places = response.data;
                    
                    response.data.forEach(place => {
                        // تحويل نوع المكان من الإنجليزية إلى العربية
                        place.type = translatePlaceType(place.type);
                        
                        // تطبيق التصفية
                        if ((countryId && place.country_id != countryId) || 
                            (cityName && place.city != cityName) ||
                            (type && place.type != type && translatePlaceType(place.type) != type) ||
                            (placeId && place.id != placeId)) {
                            return;
                        }

                        // إنشاء العلامة بأيقونة مخصصة
                        const marker = L.marker([place.latitude, place.longitude], {
                            icon: createCustomIcon(place.type)
                        });

                        // إضافة نافذة منبثقة للعلامة مع تنسيق محسن
                        marker.bindPopup(`
                            <div class="text-center">
                                <h6 class="mb-2">${place.name}</h6>
                                <p class="mb-1">
                                    <strong>الدولة:</strong> ${place.country_name || 'غير محدد'}<br>
                                    <strong>المدينة:</strong> ${place.city || 'غير محدد'}<br>
                                    <strong>النوع:</strong> <span class="fw-bold ${place.type === 'خاص' ? 'text-success' : place.type === 'حكومة' ? 'text-primary' : ''}">${place.type || 'غير محدد'}</span><br>
                                    <strong>العدد:</strong> ${place.total || '0'}
                                </p>
                            </div>
                        `);

                        allMarkers.push(marker);
                        markers.addLayer(marker);
                    });

                    map.addLayer(markers);

                    // ضبط حدود الخريطة إذا كانت هناك علامات
                    if (allMarkers.length > 0) {
                        const group = L.featureGroup(allMarkers);
                        map.fitBounds(group.getBounds());
                    }
                    
                    // تحميل المدن إذا تم اختيار دولة
                    if (countryId && !cityName) {
                        loadCities(countryId);
                    }
                    
                    // تحميل الأماكن إذا تم اختيار دولة أو مدينة
                    if (countryId || cityName) {
                        loadPlaceOptions(countryId, cityName);
                    }
                });
            }

            // معالجة تغييرات التصفية
            $('#countryFilter').on('change', function() {
                // إعادة تعيين تصفية المدينة عند تغيير الدولة
                $('#cityFilter').empty().append('<option value="">الكل</option>');
                // إعادة تعيين تصفية الأماكن عند تغيير الدولة
                $('#placeFilter').empty().append('<option value="">الكل</option>');
                loadPlaces();
            });
            
            $('#cityFilter').on('change', function() {
                // إعادة تعيين تصفية الأماكن عند تغيير المدينة
                $('#placeFilter').empty().append('<option value="">الكل</option>');
                loadPlaces();
            });
            
            $('#typeFilter, #placeFilter').on('change', loadPlaces);

            // التحميل الأولي
            loadPlaces();
        });
    </script>
</body>
</html>