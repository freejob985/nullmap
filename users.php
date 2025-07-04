<?php
/**
 * Users Page
 * 
 * Displays a list of all users in a DataTable with CRUD operations.
 * Only accessible by administrators.
 */

require_once __DIR__ . '/helpers/database.php';
require_once __DIR__ . '/helpers/auth.php';

// Initialize helpers
$db = Database::getInstance();
$auth = Auth::getInstance();

// Require admin authentication
$auth->requireAdmin();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المستخدمون - نظام إدارة المواقع الجغرافية</title>
    
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
                <h5 class="mb-0">المستخدمون</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="mdi mdi-plus me-1"></i>
                    إضافة مستخدم
                </button>
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
                                <th>تاريخ الإضافة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة مستخدم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addUserForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">الاسم</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">الدور</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">مستخدم</option>
                                <option value="admin">مدير</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="is_active" class="form-label">الحالة</label>
                            <select class="form-select" id="is_active" name="is_active" required>
                                <option value="1">نشط</option>
                                <option value="0">غير نشط</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل مستخدم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editName" class="form-label">الاسم</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" id="editPassword" name="password" placeholder="اتركه فارغاً إذا لم ترد تغيير كلمة المرور">
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">الدور</label>
                            <select class="form-select" id="editRole" name="role" required>
                                <option value="user">مستخدم</option>
                                <option value="admin">مدير</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editIsActive" class="form-label">الحالة</label>
                            <select class="form-select" id="editIsActive" name="is_active" required>
                                <option value="1">نشط</option>
                                <option value="0">غير نشط</option>
                            </select>
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
                ajax: {
                    url: 'api/users.php',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email' },
                    { 
                        data: 'role',
                        render: function(data) {
                            return data === 'admin' ? 'مدير' : 'مستخدم';
                        }
                    },
                    { 
                        data: 'is_active',
                        render: function(data) {
                            return data == 1 ? 
                                '<span class="badge bg-success">نشط</span>' : 
                                '<span class="badge bg-danger">غير نشط</span>';
                        }
                    },
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('ar-SA');
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            // Prevent actions on own account
                            if (data.id == <?php echo $auth->getCurrentUser()['id']; ?>) {
                                return '<span class="text-muted">لا يمكن تعديل حسابك</span>';
                            }
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
                ],
                order: [[0, 'desc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
                }
            });

            // Handle form submission for adding
            $('#addUserForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'api/users.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(Object.fromEntries(new FormData(this))),
                    success: function(response) {
                        $('#addUserModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            title: 'تم!',
                            text: 'تمت إضافة المستخدم بنجاح',
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'خطأ!',
                            text: xhr.responseJSON?.error || 'حدث خطأ أثناء إضافة المستخدم',
                            icon: 'error'
                        });
                    }
                });
            });

            // Handle edit button click
            $('#usersTable').on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                
                $.get(`api/users.php?id=${id}`, function(response) {
                    const user = response.data;
                    $('#editId').val(user.id);
                    $('#editName').val(user.name);
                    $('#editEmail').val(user.email);
                    $('#editRole').val(user.role);
                    $('#editIsActive').val(user.is_active);
                    $('#editPassword').val('');
                    
                    $('#editUserModal').modal('show');
                });
            });

            // Handle form submission for editing
            $('#editUserForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editId').val();
                
                // Remove empty password from data if not changed
                const formData = new FormData(this);
                const data = Object.fromEntries(formData);
                if (!data.password) delete data.password;
                
                $.ajax({
                    url: `api/users.php?id=${id}`,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(response) {
                        $('#editUserModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            title: 'تم!',
                            text: 'تم تحديث المستخدم بنجاح',
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'خطأ!',
                            text: xhr.responseJSON?.error || 'حدث خطأ أثناء تحديث المستخدم',
                            icon: 'error'
                        });
                    }
                });
            });

            // Handle delete button click
            $('#usersTable').on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: 'لن تتمكن من استعادة هذا المستخدم!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، احذفه!',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `api/users.php?id=${id}`,
                            method: 'DELETE',
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire({
                                    title: 'تم!',
                                    text: 'تم حذف المستخدم بنجاح',
                                    icon: 'success'
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'خطأ!',
                                    text: xhr.responseJSON?.error || 'حدث خطأ أثناء حذف المستخدم',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });

            // Reset forms on modal close
            $('#addUserModal').on('hidden.bs.modal', function() {
                $('#addUserForm')[0].reset();
            });

            $('#editUserModal').on('hidden.bs.modal', function() {
                $('#editUserForm')[0].reset();
            });
        });
    </script>
</body>
</html> 