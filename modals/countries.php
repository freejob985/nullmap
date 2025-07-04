<?php if ($auth->isAdmin()): ?>
<!-- Countries Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">الدول</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#countryModal">
            <i class="mdi mdi-plus me-1"></i>
            إضافة دولة
        </button>
    </div>
    <div class="card-body">
        <table id="countriesTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>المدينة</th>
                    <th>الإحداثيات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Country Modal -->
<div class="modal fade" id="countryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة دولة</h5>
                <button type="button" class="btn-close ms-0 me-2" data-bs-dismiss="modal"></button>
            </div>
            <form id="countryForm" data-type="countries">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="countryName" class="form-label">اسم الدولة</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-earth"></i>
                            </span>
                            <input type="text" class="form-control" id="countryName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="countryCity" class="form-label">المدينة</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-city"></i>
                            </span>
                            <input type="text" class="form-control" id="countryCity" name="city" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="countryLatitude" class="form-label">خط العرض</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-latitude"></i>
                                    </span>
                                    <input type="number" class="form-control" id="countryLatitude" name="latitude" 
                                           step="0.000001" min="-90" max="90" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="countryLongitude" class="form-label">خط الطول</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-longitude"></i>
                                    </span>
                                    <input type="number" class="form-control" id="countryLongitude" name="longitude" 
                                           step="0.000001" min="-180" max="180" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
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
<?php else: ?>
<!-- Countries Table (Read-only) -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">الدول</h5>
    </div>
    <div class="card-body">
        <table id="countriesTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>المدينة</th>
                    <th>الإحداثيات</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<?php endif; ?> 