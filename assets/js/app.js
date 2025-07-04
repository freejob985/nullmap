// Initialize Leaflet Map
let map;
let markers = [];

function initMap() {
    // Center on Saudi Arabia
    map = L.map('map').setView([24.7136, 46.6753], 6);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

// Initialize DataTables
function initDataTables() {
    const commonConfig = {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
        },
        order: [[0, 'desc']],
        responsive: true
    };

    // Countries DataTable
    if ($('#countriesTable').length) {
        $('#countriesTable').DataTable({
            ...commonConfig,
            ajax: {
                url: 'api/countries.php',
                method: 'GET'
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'city' },
                { 
                    data: null,
                    render: function(data) {
                        return `${data.latitude}, ${data.longitude}`;
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-primary edit-country" data-id="${data.id}">
                                <i class="mdi mdi-pencil"></i> تعديل
                            </button>
                            <button class="btn btn-sm btn-danger delete-country" data-id="${data.id}">
                                <i class="mdi mdi-delete"></i> حذف
                            </button>
                        `;
                    }
                }
            ]
        });
    }

    // Places DataTable
    if ($('#placesTable').length) {
        $('#placesTable').DataTable({
            ...commonConfig,
            ajax: {
                url: 'api/places.php',
                method: 'GET'
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'total' },
                {
                    data: 'type',
                    render: function(data) {
                        const classes = {
                            'private': 'danger',
                            'government': 'success'
                        };
                        return `<span class="badge bg-${classes[data]}">${data}</span>`;
                    }
                },
                { data: 'country_name' },
                { data: 'city' },
                {
                    data: null,
                    render: function(data) {
                        return `${data.latitude}, ${data.longitude}`;
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-primary edit-place" data-id="${data.id}">
                                <i class="mdi mdi-pencil"></i> تعديل
                            </button>
                            <button class="btn btn-sm btn-danger delete-place" data-id="${data.id}">
                                <i class="mdi mdi-delete"></i> حذف
                            </button>
                        `;
                    }
                }
            ]
        });
    }

    // Users DataTable
    if ($('#usersTable').length) {
        $('#usersTable').DataTable({
            ...commonConfig,
            ajax: {
                url: 'api/users.php',
                method: 'GET'
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'email' },
                {
                    data: 'role',
                    render: function(data) {
                        const classes = {
                            'admin': 'danger',
                            'user': 'primary'
                        };
                        return `<span class="badge bg-${classes[data]}">${data}</span>`;
                    }
                },
                {
                    data: 'is_active',
                    render: function(data) {
                        return data ? 
                            '<span class="badge bg-success">نشط</span>' : 
                            '<span class="badge bg-secondary">غير نشط</span>';
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-primary edit-user" data-id="${data.id}">
                                <i class="mdi mdi-pencil"></i> تعديل
                            </button>
                            <button class="btn btn-sm btn-danger delete-user" data-id="${data.id}">
                                <i class="mdi mdi-delete"></i> حذف
                            </button>
                        `;
                    }
                }
            ]
        });
    }
}

// Map Marker Functions
function addMarker(data, type = 'country') {
    const marker = L.marker([data.latitude, data.longitude], {
        icon: L.divIcon({
            className: `map-marker ${type}`,
            html: `<i class="mdi mdi-map-marker"></i>`
        })
    }).addTo(map);

    marker.bindPopup(`
        <strong>${data.name}</strong><br>
        ${data.city}<br>
        ${type === 'place' ? `النوع: ${data.type}<br>العدد: ${data.total}` : ''}
    `);

    markers.push(marker);
}

function clearMarkers() {
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];
}

function refreshMap() {
    clearMarkers();
    
    // Load countries
    $.get('api/countries.php', function(response) {
        response.data.forEach(country => addMarker(country, 'country'));
    });

    // Load places
    $.get('api/places.php', function(response) {
        response.data.forEach(place => addMarker(place, 'place'));
    });
}

// CRUD Operations
function handleDelete(type, id) {
    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: 'لن تتمكن من استعادة هذا العنصر!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذفه!',
        cancelButtonText: 'إلغاء',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `api/${type}.php?id=${id}`,
                method: 'DELETE',
                success: function() {
                    Toastify({
                        text: 'تم الحذف بنجاح',
                        duration: 3000,
                        gravity: 'top',
                        position: 'center',
                        className: 'bg-success'
                    }).showToast();

                    $(`#${type}Table`).DataTable().ajax.reload();
                    if (type !== 'users') {
                        refreshMap();
                    }
                },
                error: function(xhr) {
                    Toastify({
                        text: xhr.responseJSON?.message || 'حدث خطأ أثناء الحذف',
                        duration: 3000,
                        gravity: 'top',
                        position: 'center',
                        className: 'bg-danger'
                    }).showToast();
                }
            });
        }
    });
}

// Event Handlers
$(document).ready(function() {
    // Initialize components
    initMap();
    initDataTables();
    refreshMap();

    // Delete handlers
    $(document).on('click', '.delete-country', function() {
        handleDelete('countries', $(this).data('id'));
    });

    $(document).on('click', '.delete-place', function() {
        handleDelete('places', $(this).data('id'));
    });

    $(document).on('click', '.delete-user', function() {
        handleDelete('users', $(this).data('id'));
    });

    // Form submissions
    $('form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const type = form.data('type');
        const method = form.data('id') ? 'PUT' : 'POST';
        const id = form.data('id');

        $.ajax({
            url: `api/${type}.php${id ? `?id=${id}` : ''}`,
            method: method,
            data: form.serialize(),
            success: function() {
                $(`#${type}Modal`).modal('hide');
                form[0].reset();
                form.removeData('id');

                Toastify({
                    text: 'تم الحفظ بنجاح',
                    duration: 3000,
                    gravity: 'top',
                    position: 'center',
                    className: 'bg-success'
                }).showToast();

                $(`#${type}Table`).DataTable().ajax.reload();
                if (type !== 'users') {
                    refreshMap();
                }
            },
            error: function(xhr) {
                Toastify({
                    text: xhr.responseJSON?.message || 'حدث خطأ أثناء الحفظ',
                    duration: 3000,
                    gravity: 'top',
                    position: 'center',
                    className: 'bg-danger'
                }).showToast();
            }
        });
    });
}); 