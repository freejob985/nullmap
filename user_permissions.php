<?php
/**
 * User Permissions Management Page
 * 
 * Allows administrators to manage user permissions.
 */

require_once __DIR__ . '/helpers/database.php';
require_once __DIR__ . '/helpers/auth.php';

// Initialize helpers
$db = Database::getInstance();
$auth = Auth::getInstance();

// Require authentication
$auth->requireLogin();

// Require admin role or manage_permissions permission
if (!$auth->hasPermission('manage_permissions') && !$auth->isAdmin()) {
    header('Location: /index.php');
    exit;
}

// Check if user_id is provided in URL
$selectedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Get all users
$users = $db->fetchAll('SELECT id, name, email, role, is_active FROM users ORDER BY name');

// Get all permissions
$permissions = $auth->getAllPermissions();

// Get selected user details if user_id is provided
$selectedUser = null;
if ($selectedUserId) {
    foreach ($users as $user) {
        if ($user['id'] == $selectedUserId) {
            $selectedUser = $user;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة صلاحيات المستخدمين - نظام إدارة المواقع الجغرافية</title>
    
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Material Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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
                <h5 class="mb-0">إدارة صلاحيات المستخدمين</h5>
                <a href="users.php" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>
                    العودة إلى المستخدمين
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الدور</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">مدير</span>
                                    <?php else: ?>
                                    <span class="badge bg-primary">مستخدم</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">نشط</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">غير نشط</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-permissions-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['name']); ?>">
                                        <i class="mdi mdi-key-variant me-1"></i>
                                        الصلاحيات
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Permissions Modal -->
    <div class="modal fade" id="editPermissionsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إدارة صلاحيات المستخدم: <span id="userName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPermissionsForm">
                    <input type="hidden" id="userId" name="user_id">
                    <div class="modal-body">
                        <div class="row">
                            <?php foreach ($permissions as $permission): ?>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="<?php echo $permission['name']; ?>" id="permission_<?php echo $permission['id']; ?>">
                                    <label class="form-check-label" for="permission_<?php echo $permission['id']; ?>">
                                        <?php echo htmlspecialchars($permission['description']); ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#usersTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
                }
            });
            
            // Auto-open permissions modal if user_id is provided in URL
            <?php if ($selectedUser): ?>
            // Trigger click on the edit permissions button for the selected user
            setTimeout(function() {
                $('.edit-permissions-btn[data-id="<?php echo $selectedUser['id']; ?>"]').click();
            }, 500);
            <?php endif; ?>

            // Handle edit permissions button click
            $('.edit-permissions-btn').on('click', function() {
                const userId = $(this).data('id');
                const userName = $(this).data('name');
                
                // Reset form
                $('#editPermissionsForm')[0].reset();
                $('.permission-checkbox').prop('checked', false);
                
                // Set user ID and name
                $('#userId').val(userId);
                $('#userName').text(userName);
                
                // Get user permissions
                $.ajax({
                    url: 'api/users.php?permissions=true&id=' + userId,
                    method: 'GET',
                    success: function(response) {
                        if (response.data && response.data.permissions) {
                            // Check corresponding checkboxes
                            response.data.permissions.forEach(function(permission) {
                                $('input[value="' + permission + '"]').prop('checked', true);
                            });
                        }
                        
                        // Show modal
                        $('#editPermissionsModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'خطأ!',
                            text: xhr.responseJSON?.error || 'حدث خطأ أثناء جلب صلاحيات المستخدم',
                            icon: 'error'
                        });
                    }
                });
            });
            
            // Handle form submission
            $('#editPermissionsForm').on('submit', function(e) {
                e.preventDefault();
                
                const userId = $('#userId').val();
                const permissions = [];
                
                // Get selected permissions
                $('.permission-checkbox:checked').each(function() {
                    permissions.push($(this).val());
                });
                
                // Update user permissions
                $.ajax({
                    url: 'api/users.php?permissions=true&id=' + userId,
                    method: 'PUT',
                    data: JSON.stringify({ permissions: permissions }),
                    contentType: 'application/json',
                    success: function(response) {
                        $('#editPermissionsModal').modal('hide');
                        
                        Swal.fire({
                            title: 'تم!',
                            text: 'تم تحديث صلاحيات المستخدم بنجاح',
                            icon: 'success'
                        }).then(function() {
                            // Reload page to reflect changes
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'خطأ!',
                            text: xhr.responseJSON?.error || 'حدث خطأ أثناء تحديث صلاحيات المستخدم',
                            icon: 'error'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>