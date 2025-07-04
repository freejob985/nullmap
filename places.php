<?php
/**
 * Places Page
 * 
 * Displays a list of all places in a DataTable with CRUD operations.
 */

require_once __DIR__ . '/helpers/database.php';
require_once __DIR__ . '/helpers/auth.php';

// Initialize helpers
$db = Database::getInstance();
$auth = Auth::getInstance();

// Require authentication
$auth->requireLogin();

// Get countries for dropdown
$stmt = $db->getConnection()->query('SELECT id, name FROM countries ORDER BY name');
$countries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الأماكن - نظام إدارة المواقع الجغرافية</title>
    
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
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">الأماكن</h5>
                <?php if ($auth->isAdmin()): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlaceModal">
                    <i class="mdi mdi-plus me-1"></i>
                    إضافة مكان
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="placesTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الدولة</th>
                                <th>المدينة</th>
                                <th>النوع</th>
                                <th>العدد</th>
                                <th>خط العرض</th>
                                <th>خط الطول</th>
                                <th>تاريخ الإضافة</th>
                                <?php if ($auth->isAdmin()): ?>
                                <th>الإجراءات</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if ($auth->isAdmin()): ?>
    <!-- Add Place Modal -->
    <div class="modal fade" id="addPlaceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة مكان</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addPlaceForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">اسم المكان</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="country_id" class="form-label">الدولة</label>
                            <select class="form-select" id="country_id" name="country_id" required>
                                <option value="">اختر الدولة</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo $country['id']; ?>">
                                        <?php echo htmlspecialchars($country['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label">المدينة</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">النوع</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">اختر النوع</option>
                                <option value="خاص">خاص</option>
                                <option value="حكومة">حكومة</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="total" class="form-label">العدد</label>
                            <input type="number" class="form-control" id="total" name="total" min="0" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="latitude" class="form-label">خط العرض</label>
                            <input type="number" class="form-control" id="latitude" name="latitude" step="any" required>
                        </div>
                        <div class="mb-3">
                            <label for="longitude" class="form-label">خط الطول</label>
                            <input type="number" class="form-control" id="longitude" name="longitude" step="any" required>
                        </div>
                        <div id="map" class="map-container mb-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Place Modal -->
    <div class="modal fade" id="editPlaceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل مكان</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPlaceForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editName" class="form-label">اسم المكان</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCountryId" class="form-label">الدولة</label>
                            <select class="form-select" id="editCountryId" name="country_id" required>
                                <option value="">اختر الدولة</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo $country['id']; ?>">
                                        <?php echo htmlspecialchars($country['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editCity" class="form-label">المدينة</label>
                            <input type="text" class="form-control" id="editCity" name="city" required>
                        </div>
                        <div class="mb-3">
                            <label for="editType" class="form-label">النوع</label>
                            <select class="form-select" id="editType" name="type" required>
                                <option value="">اختر النوع</option>
                                <option value="خاص">خاص</option>
                                <option value="حكومة">حكومة</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editTotal" class="form-label">العدد</label>
                            <input type="number" class="form-control" id="editTotal" name="total" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLatitude" class="form-label">خط العرض</label>
                            <input type="number" class="form-control" id="editLatitude" name="latitude" step="any" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLongitude" class="form-label">خط الطول</label>
                            <input type="number" class="form-control" id="editLongitude" name="longitude" step="any" required>
                        </div>
                        <div id="editMap" class="map-container mb-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#placesTable').DataTable({
                ajax: {
                    url: 'api/places.php',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'country_name' },
                    { data: 'city' },
                    { data: 'type' },
                    { data: 'total' },
                    { data: 'latitude' },
                    { data: 'longitude' },
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('ar-SA');
                        }
                    },
                    <?php if ($auth->isAdmin()): ?>
                    {
                        data: null,
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-primary edit-btn" data-id="${data.id}">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            `;
                        }
                    }
                    <?php endif; ?>
                ],
                order: [[0, 'desc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
                }
            });

            <?php if ($auth->isAdmin()): ?>
            // Initialize maps
            let addMap = L.map('map').setView([24.7136, 46.6753], 4);
            let editMap = L.map('editMap').setView([24.7136, 46.6753], 4);
            let addMarker, editMarker;

            // Add tile layer to both maps
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(addMap);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(editMap);

            // Handle map clicks for add
            addMap.on('click', function(e) {
                if (addMarker) addMap.removeLayer(addMarker);
                addMarker = L.marker(e.latlng).addTo(addMap);
                $('#latitude').val(e.latlng.lat);
                $('#longitude').val(e.latlng.lng);
            });

            // Handle map clicks for edit
            editMap.on('click', function(e) {
                if (editMarker) editMap.removeLayer(editMarker);
                editMarker = L.marker(e.latlng).addTo(editMap);
                $('#editLatitude').val(e.latlng.lat);
                $('#editLongitude').val(e.latlng.lng);
            });

            // Show modal
            $('#addPlaceModal').on('shown.bs.modal', function() {
                addMap.invalidateSize();
            });

            $('#editPlaceModal').on('shown.bs.modal', function() {
                editMap.invalidateSize();
            });

            // Handle form submission for adding
            $('#addPlaceForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'api/places.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#addPlaceModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            title: 'تم!',
                            text: 'تمت إضافة المكان بنجاح',
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'خطأ!',
                            text: xhr.responseJSON?.error || 'حدث خطأ أثناء إضافة المكان',
                            icon: 'error'
                        });
                    }
                });
            });

            // Handle edit button click
            $('#placesTable').on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                
                $.get(`api/places.php?id=${id}`, function(response) {
                    const place = response.data;
                    $('#editId').val(place.id);
                    $('#editName').val(place.name);
                    $('#editCountryId').val(place.country_id);
                    $('#editCity').val(place.city);
                    $('#editType').val(place.type);
                    $('#editTotal').val(place.total);
                    $('#editLatitude').val(place.latitude);
                    $('#editLongitude').val(place.longitude);
                    
                    if (editMarker) editMap.removeLayer(editMarker);
                    editMarker = L.marker([place.latitude, place.longitude]).addTo(editMap);
                    editMap.setView([place.latitude, place.longitude], 8);
                    
                    $('#editPlaceModal').modal('show');
                });
            });

            // Handle form submission for editing
            $('#editPlaceForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editId').val();
                
                $.ajax({
                    url: `api/places.php?id=${id}`,
                    method: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#editPlaceModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            title: 'تم!',
                            text: 'تم تحديث المكان بنجاح',
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'خطأ!',
                            text: xhr.responseJSON?.error || 'حدث خطأ أثناء تحديث المكان',
                            icon: 'error'
                        });
                    }
                });
            });

            // Handle delete button click
            $('#placesTable').on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: 'لن تتمكن من استعادة هذا المكان!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، احذفه!',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `api/places.php?id=${id}`,
                            method: 'DELETE',
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire({
                                    title: 'تم!',
                                    text: 'تم حذف المكان بنجاح',
                                    icon: 'success'
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'خطأ!',
                                    text: xhr.responseJSON?.error || 'حدث خطأ أثناء حذف المكان',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
            <?php endif; ?>
        });
    </script>
</body>
</html> 