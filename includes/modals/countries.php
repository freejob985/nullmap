<?php
/**
 * Countries Modal Template
 * 
 * This file contains the modal templates for managing countries:
 * - List view
 * - Add form
 * - Edit form
 */
?>
<!-- Countries List Modal -->
<div class="modal fade" id="countriesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إدارة الدول والمدن</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCountryModal">
                        <i class="fas fa-plus"></i> إضافة دولة
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table id="countriesTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الدولة</th>
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

<!-- Add Country Modal -->
<div class="modal fade" id="addCountryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة دولة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="countryForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">اسم الدولة</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="city" class="form-label">المدينة</label>
                        <input type="text" class="form-control" id="city" name="city" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="latitude" class="form-label">خط العرض</label>
                        <input type="number" class="form-control" id="latitude" name="latitude" 
                               step="0.000001" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="longitude" class="form-label">خط الطول</label>
                        <input type="number" class="form-control" id="longitude" name="longitude"
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

<!-- Edit Country Modal -->
<div class="modal fade" id="editCountryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل دولة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCountryForm">
                    <input type="hidden" id="editCountryId" name="id">
                    
                    <div class="mb-3">
                        <label for="editName" class="form-label">اسم الدولة</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCity" class="form-label">المدينة</label>
                        <input type="text" class="form-control" id="editCity" name="city" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editLatitude" class="form-label">خط العرض</label>
                        <input type="number" class="form-control" id="editLatitude" name="latitude"
                               step="0.000001" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editLongitude" class="form-label">خط الطول</label>
                        <input type="number" class="form-control" id="editLongitude" name="longitude"
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