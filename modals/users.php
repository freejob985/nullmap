<?php if ($auth->isAdmin()): ?>
<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">المستخدمون</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="mdi mdi-plus me-1"></i>
            إضافة مستخدم
        </button>
    </div>
    <div class="card-body">
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

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مستخدم</h5>
                <button type="button" class="btn-close ms-0 me-2" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" data-type="users">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="userName" class="form-label">الاسم</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-account"></i>
                            </span>
                            <input type="text" class="form-control" id="userName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="userEmail" class="form-label">البريد الإلكتروني</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-email"></i>
                            </span>
                            <input type="email" class="form-control" id="userEmail" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="userPassword" class="form-label">كلمة المرور</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="userPassword" name="password" 
                                   minlength="8" autocomplete="new-password">
                            <div class="invalid-feedback"></div>
                        </div>
                        <small class="form-text text-muted">
                            اترك حقل كلمة المرور فارغاً إذا كنت لا تريد تغييرها
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="userRole" class="form-label">الدور</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-shield-account"></i>
                            </span>
                            <select class="form-select" id="userRole" name="role" required>
                                <option value="">اختر الدور</option>
                                <option value="user">مستخدم</option>
                                <option value="admin">مدير</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="userIsActive" name="is_active" value="1" checked>
                            <label class="form-check-label" for="userIsActive">
                                <i class="mdi mdi-account-check me-1"></i>
                                حساب نشط
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>
                        إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?> 