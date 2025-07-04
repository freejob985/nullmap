<?php
/**
 * Places Modal Template
 * 
 * This file contains the modal templates for managing places:
 * - List view
 * - Add form
 * - Edit form
 */
?>
<!-- Places List Modal -->
<div class="modal fade" id="placesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إدارة الأماكن</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlaceModal">
                        <i class="fas fa-plus"></i> إضافة مكان
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table id="placesTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المكان</th>
                                <th>الإجمالي</th>
                                <th>النوع</th>
                                <th>المدينة</th>
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

<!-- Add Place Modal -->
<div class="modal fade" id="addPlaceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مكان</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="placeForm">
                    <div class="mb-3">
                        <label for="placeName" class="form-label">اسم المكان</label>
                        <input type="text" class="form-control" id="placeName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="total" class="form-label">الإجمالي</label>
                        <input type="number" class="form-control" id="total" name="total" min="0" required>
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
                        <label for="countryId" class="form-label">الدولة</label>
                        <select class="form-select" id="countryId" name="country_id" required>
                            <option value="">اختر الدولة</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="placeCity" class="form-label">المدينة</label>
                        <input type="text" class="form-control" id="placeCity" name="city" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="placeLatitude" class="form-label">خط العرض</label>
                        <input type="number" class="form-control" id="placeLatitude" name="latitude"
                               step="0.000001" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="placeLongitude" class="form-label">خط الطول</label>
                        <input type="number" class="form-control" id="placeLongitude" name="longitude"
                               step="0.000001" required>
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

<!-- Edit Place Modal -->
<div class="modal fade" id="editPlaceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل مكان</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPlaceForm">
                    <input type="hidden" id="editPlaceId" name="id">
                    
                    <div class="mb-3">
                        <label for="editPlaceName" class="form-label">اسم المكان</label>
                        <input type="text" class="form-control" id="editPlaceName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editTotal" class="form-label">الإجمالي</label>
                        <input type="number" class="form-control" id="editTotal" name="total" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editType" class="form-label">النوع</label>
                        <select class="form-select" id="editType" name="type" required>
                            <option value="خاص">خاص</option>
                            <option value="حكومة">حكومة</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCountryId" class="form-label">الدولة</label>
                        <select class="form-select" id="editCountryId" name="country_id" required>
                            <option value="">اختر الدولة</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editPlaceCity" class="form-label">المدينة</label>
                        <input type="text" class="form-control" id="editPlaceCity" name="city" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editPlaceLatitude" class="form-label">خط العرض</label>
                        <input type="number" class="form-control" id="editPlaceLatitude" name="latitude"
                               step="0.000001" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editPlaceLongitude" class="form-label">خط الطول</label>
                        <input type="number" class="form-control" id="editPlaceLongitude" name="longitude"
                               step="0.000001" required>
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