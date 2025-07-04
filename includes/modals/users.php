<?php
/**
 * Users Modal Template
 * 
 * This file contains the modal templates for managing users:
 * - List view
 * - Add form
 * - Edit form
 */
?>
<!-- Users List Modal -->
<div class="modal fade" id="usersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إدارة المستخدمين</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus"></i> إضافة مستخدم
                    </button>
                </div>
                
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
                        <tbody></tbody>
                    </table>
                </div>
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
            <div class="modal-body">
                <form id="userForm">
                    <div class="mb-3">
                        <label for="userName" class="form-label">الاسم</label>
                        <input type="text" class="form-control" id="userName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userPassword" class="form-label">كلمة المرور</label>
                        <input type="password" class="form-control" id="userPassword" name="password" required>
                        <div class="form-text">يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userRole" class="form-label">الدور</label>
                        <select class="form-select" id="userRole" name="role" required>
                            <option value="">اختر الدور</option>
                            <option value="admin">مدير</option>
                            <option value="user">مستخدم</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="userActive" name="is_active" checked>
                            <label class="form-check-label" for="userActive">
                                حساب نشط
                            </label>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
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
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId" name="id">
                    
                    <div class="mb-3">
                        <label for="editUserName" class="form-label">الاسم</label>
                        <input type="text" class="form-control" id="editUserName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editUserEmail" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="editUserEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editUserPassword" class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="editUserPassword" name="password">
                        <div class="form-text">اتركها فارغة إذا لم ترد تغيير كلمة المرور</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editUserRole" class="form-label">الدور</label>
                        <select class="form-select" id="editUserRole" name="role" required>
                            <option value="admin">مدير</option>
                            <option value="user">مستخدم</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editUserActive" name="is_active">
                            <label class="form-check-label" for="editUserActive">
                                حساب نشط
                            </label>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 