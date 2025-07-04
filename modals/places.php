<?php if ($auth->isAdmin()): ?>
<!-- Places Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">الأماكن</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#placeModal">
            <i class="mdi mdi-plus me-1"></i>
            إضافة مكان
        </button>
    </div>
    <div class="card-body">
        <table id="placesTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>العدد</th>
                    <th>النوع</th>
                    <th>الدولة</th>
                    <th>المدينة</th>
                    <th>الإحداثيات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Place Modal -->
<div class="modal fade" id="placeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مكان</h5>
                <button type="button" class="btn-close ms-0 me-2" data-bs-dismiss="modal"></button>
            </div>
            <form id="placeForm" data-type="places">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="placeName" class="form-label">اسم المكان</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-map-marker"></i>
                            </span>
                            <input type="text" class="form-control" id="placeName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="placeTotal" class="form-label">العدد</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-numeric"></i>
                            </span>
                            <input type="number" class="form-control" id="placeTotal" name="total" min="0" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="placeType" class="form-label">النوع</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-format-list-bulleted-type"></i>
                            </span>
                            <select class="form-select" id="placeType" name="type" required>
                                <option value="">اختر النوع</option>
                                <option value="private">خاص</option>
                                <option value="government">حكومي</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="placeCountryId" class="form-label">الدولة</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-earth"></i>
                            </span>
                            <select class="form-select" id="placeCountryId" name="country_id" required>
                                <option value="">اختر الدولة</option>
                                <?php
                                $stmt = $db->query('SELECT id, name FROM countries ORDER BY name');
                                while ($country = $stmt->fetch()) {
                                    echo '<option value="' . $country['id'] . '">' . htmlspecialchars($country['name']) . '</option>';
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="placeCity" class="form-label">المدينة</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-city"></i>
                            </span>
                            <input type="text" class="form-control" id="placeCity" name="city" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="placeLatitude" class="form-label">خط العرض</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-latitude"></i>
                                    </span>
                                    <input type="number" class="form-control" id="placeLatitude" name="latitude" 
                                           step="0.000001" min="-90" max="90" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="placeLongitude" class="form-label">خط الطول</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-longitude"></i>
                                    </span>
                                    <input type="number" class="form-control" id="placeLongitude" name="longitude" 
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
<!-- Places Table (Read-only) -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">الأماكن</h5>
    </div>
    <div class="card-body">
        <table id="placesTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>العدد</th>
                    <th>النوع</th>
                    <th>الدولة</th>
                    <th>المدينة</th>
                    <th>الإحداثيات</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<?php endif; ?> 