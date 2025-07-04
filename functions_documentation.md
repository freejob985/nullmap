# توثيق الدوال المستخدمة في نظام إدارة المواقع الجغرافية

## التحديثات الجديدة

### مشكلة عدم إضافة نوع المكان (حكومي أو خاص)

#### وصف المشكلة
عند إضافة مكان جديد، لم يكن يتم حفظ نوع المكان (حكومي أو خاص) في قاعدة البيانات بشكل صحيح.

#### سبب المشكلة
تم اكتشاف عدم تطابق بين قيم النوع المستخدمة في واجهة المستخدم وقيم النوع المتوقعة في واجهة برمجة التطبيقات (API):

- في واجهة المستخدم (places.php): تم استخدام القيم العربية "خاص" و"حكومة"
- في واجهة برمجة التطبيقات (api/places.php): يتم التحقق من القيم الإنجليزية "private" و"government"

#### الحل المطبق
1. تم تعديل نموذج إضافة المكان في ملف places.php (الأسطر 116-119) لاستخدام القيم الإنجليزية كقيم للنموذج مع الاحتفاظ بالنص العربي للعرض:
   - تغيير `value="خاص"` إلى `value="private"`
   - تغيير `value="حكومة"` إلى `value="government"`

2. تم تعديل نموذج تعديل المكان في ملف places.php (الأسطر 177-180) بنفس الطريقة:
   - تغيير `value="خاص"` إلى `value="private"`
   - تغيير `value="حكومة"` إلى `value="government"`

#### الدوال المتأثرة

##### 1. نموذج إضافة المكان (places.php)
```html
<div class="mb-3">
    <label for="type" class="form-label">النوع</label>
    <select class="form-select" id="type" name="type" required>
        <option value="">اختر النوع</option>
        <option value="private">خاص</option>
        <option value="government">حكومة</option>
    </select>
</div>
```

##### 2. نموذج تعديل المكان (places.php)
```html
<div class="mb-3">
    <label for="editType" class="form-label">النوع</label>
    <select class="form-select" id="editType" name="type" required>
        <option value="">اختر النوع</option>
        <option value="private">خاص</option>
        <option value="government">حكومة</option>
    </select>
</div>
```

##### 3. التحقق من النوع في API (api/places.php)
```php
$rules = [
    'name' => ['required', 'string', 'max:255'],
    'total' => ['required', 'integer', 'min:0'],
    'type' => ['required', 'in:private,government'],
    'country_id' => ['required', 'integer', 'exists:countries,id'],
    'city' => ['required', 'string', 'max:255'],
    'latitude' => ['required', 'numeric', 'between:-90,90'],
    'longitude' => ['required', 'numeric', 'between:-180,180']
];
```

#### تدفق البيانات
1. المستخدم يختار نوع المكان من القائمة المنسدلة في نموذج الإضافة أو التعديل
2. عند اختيار "خاص"، يتم إرسال القيمة "private" إلى الخادم
3. عند اختيار "حكومة"، يتم إرسال القيمة "government" إلى الخادم
4. يتحقق API من القيمة المرسلة ويتأكد من أنها إما "private" أو "government"
5. يتم حفظ القيمة في قاعدة البيانات
6. عند عرض البيانات، يتم تحويل القيمة المخزنة إلى النص العربي المقابل للعرض

### تحسينات صفحة الخريطة (map.php)

#### 1. إضافة تصفية حسب المدينة

- **التحسين**: تم إضافة خيار تصفية الأماكن حسب المدينة مع تحميل المدن ديناميكياً بناءً على الدولة المختارة
- **الملف**: `map.php`
- **التنفيذ**:
  ```javascript
  /**
   * تحميل المدن المتاحة بناءً على الدولة المختارة
   * @param {number} countryId - معرف الدولة المختارة
   */
  function loadCities(countryId) {
      // تفريغ قائمة المدن
      $('#cityFilter').empty().append('<option value="">الكل</option>');
      
      if (!countryId) {
          return;
      }
      
      // تحميل المدن الفريدة من البيانات المتاحة
      const uniqueCities = [...new Set(cities.filter(city => city.country_id == countryId).map(city => city.city))];
      uniqueCities.sort().forEach(city => {
          $('#cityFilter').append(`<option value="${city}">${city}</option>`);
      });
  }
  ```

#### 2. تحسين عرض العلامات على الخريطة

- **التحسين**: تم تحسين مظهر العلامات على الخريطة بإضافة أيقونات مميزة وتكبير حجمها وإضافة تأثيرات تفاعلية
- **الملف**: `map.php`
- **التنفيذ**:
  ```javascript
  /**
   * إنشاء أيقونة مخصصة للعلامة على الخريطة
   * @param {string} type - نوع المكان (خاص أو حكومة)
   * @returns {L.DivIcon} - أيقونة مخصصة للعلامة
   */
  function createCustomIcon(type) {
      const isPrivate = type === 'خاص';
      const iconClass = isPrivate ? 'marker-private' : 'marker-government';
      const iconName = isPrivate ? 'mdi-home' : 'mdi-office-building';
      
      return L.divIcon({
          className: 'custom-marker',
          html: `<div class="custom-marker-icon ${iconClass}" style="width: 24px; height: 24px;"><i class="mdi ${iconName}" style="color: white; font-size: 14px;"></i></div>`,
          iconSize: [24, 24],
          iconAnchor: [12, 12],
          popupAnchor: [0, -12]
      });
  }
  ```

  ```css
  /* تنسيق العلامات المخصصة */
  .custom-marker-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      box-shadow: 0 1px 5px rgba(0, 0, 0, 0.4);
      border: 2px solid white;
      transition: transform 0.2s;
  }
  .custom-marker-icon:hover {
      transform: scale(1.2);
      z-index: 1001;
  }
  .marker-private {
      background-color: #4CAF50;
  }
  .marker-government {
      background-color: #2196F3;
  }
  ```

#### 3. تحسين تنظيم الكود وتوثيقه

- **التحسين**: تم إعادة تنظيم الكود وفصل المنطق إلى دوال مستقلة ذات مسؤوليات محددة وإضافة تعليقات توثيقية
- **الملف**: `map.php`
- **التنفيذ**:
  ```javascript
  /**
   * تحميل الأماكن وعرضها على الخريطة
   */
  function loadPlaces() {
      const countryId = $('#countryFilter').val();
      const cityName = $('#cityFilter').val();
      const type = $('#typeFilter').val();
      
      // تفريغ العلامات الموجودة
      markers.clearLayers();
      allMarkers = [];

      // جلب الأماكن من API
      $.get('api/places.php', function(response) {
          // تخزين جميع المدن للاستخدام في التصفية
          cities = response.data;
          
          response.data.forEach(place => {
              // تطبيق التصفية
              if ((countryId && place.country_id != countryId) || 
                  (cityName && place.city != cityName) ||
                  (type && place.type != type)) {
                  return;
              }

              // إنشاء العلامة بأيقونة مخصصة
              const marker = L.marker([place.latitude, place.longitude], {
                  icon: createCustomIcon(place.type)
              });

              // إضافة نافذة منبثقة للعلامة
              marker.bindPopup(`
                  <div class="text-center">
                      <h6 class="mb-2">${place.name}</h6>
                      <p class="mb-1">
                          <strong>الدولة:</strong> ${place.country_name}<br>
                          <strong>المدينة:</strong> ${place.city}<br>
                          <strong>النوع:</strong> ${place.type}<br>
                          <strong>العدد:</strong> ${place.total}
                      </p>
                  </div>
              `);

              allMarkers.push(marker);
              markers.addLayer(marker);
          });

          map.addLayer(markers);

          // ضبط حدود الخريطة إذا كانت هناك علامات
          if (allMarkers.length > 0) {
              const group = L.featureGroup(allMarkers);
              map.fitBounds(group.getBounds());
          }
          
          // تحميل المدن إذا تم اختيار دولة
          if (countryId && !cityName) {
              loadCities(countryId);
          }
      });
  }
  ```

### التحسينات المنفذة

#### 1. تحديث تلقائي للإحداثيات عند اختيار الدولة في صفحة الدول

- **التحسين**: تم إضافة قاموس للإحداثيات الافتراضية للدول المحددة وتحديث الإحداثيات تلقائيًا عند اختيار الدولة
- **الملف**: `countries.php`
- **التنفيذ**:
  ```javascript
  // قاموس إحداثيات الدول
  const countryCoordinates = {
      'مصر': { lat: 30.0444, lng: 31.2357 },
      'ماليزيا': { lat: 3.1390, lng: 101.6869 },
      'قطر': { lat: 25.2854, lng: 51.5310 },
      'جورجيا': { lat: 41.7151, lng: 44.8271 },
      'قبرص': { lat: 35.1856, lng: 33.3823 },
      'المانيا': { lat: 52.5200, lng: 13.4050 },
      'هولندا': { lat: 52.3676, lng: 4.9041 },
      'بريطانيا': { lat: 51.5074, lng: -0.1278 },
      'ج افريقيا': { lat: -33.9249, lng: 18.4241 }
  };
  
  // دالة تحديث الإحداثيات بناءً على الدولة المحددة
  function updateCoordinatesByCountry(formPrefix, countryName) {
      if (countryCoordinates[countryName]) {
          const coords = countryCoordinates[countryName];
          $(`#${formPrefix}Latitude`).val(coords.lat);
          $(`#${formPrefix}Longitude`).val(coords.lng);
          
          // تحديث الخريطة
          if (formPrefix === 'add') {
              addMarker.setLatLng([coords.lat, coords.lng]);
              addMap.setView([coords.lat, coords.lng], 10);
          } else if (formPrefix === 'edit') {
              editMarker.setLatLng([coords.lat, coords.lng]);
              editMap.setView([coords.lat, coords.lng], 10);
          }
      }
  }
  
  // معالج حدث تغيير الدولة في نموذج الإضافة
  $('#addCountryName').on('change', function() {
      const countryName = $(this).val();
      updateCoordinatesByCountry('add', countryName);
  });
  
  // معالج حدث تغيير الدولة في نموذج التعديل
  $('#editCountryName').on('change', function() {
      const countryName = $(this).val();
      updateCoordinatesByCountry('edit', countryName);
  });
  ```

#### 2. تحسين تجربة المستخدم في اختيار الإحداثيات

- **التحسين**: تم إضافة زر لإعادة تعيين الإحداثيات إلى قيم الدولة الافتراضية
- **الملف**: `countries.php`
- **التنفيذ**:
  ```html
  <!-- إضافة زر إعادة تعيين الإحداثيات في نموذج الإضافة -->
  <div class="mb-3">
      <button type="button" class="btn btn-secondary btn-sm" id="resetAddCoordinates">
          إعادة تعيين الإحداثيات
      </button>
  </div>
  
  <!-- إضافة زر إعادة تعيين الإحداثيات في نموذج التعديل -->
  <div class="mb-3">
      <button type="button" class="btn btn-secondary btn-sm" id="resetEditCoordinates">
          إعادة تعيين الإحداثيات
      </button>
  </div>
  ```
  
  ```javascript
  // معالج حدث زر إعادة تعيين الإحداثيات في نموذج الإضافة
  $('#resetAddCoordinates').on('click', function() {
      const countryName = $('#addCountryName').val();
      updateCoordinatesByCountry('add', countryName);
  });
  
  // معالج حدث زر إعادة تعيين الإحداثيات في نموذج التعديل
  $('#resetEditCoordinates').on('click', function() {
      const countryName = $('#editCountryName').val();
      updateCoordinatesByCountry('edit', countryName);
  });
  ```

### تحسينات مقترحة لتطوير النظام

#### 1. تحسين أداء استرجاع بيانات الدول

- **المشكلة**: يتم استرجاع بيانات الدول بشكل متكرر عند تغيير الدولة في نماذج الإضافة والتعديل
- **الحل المقترح**: استخدام تقنية التخزين المؤقت (Caching) لبيانات الدول
- **التنفيذ المقترح**:
  ```javascript
  // إنشاء كائن للتخزين المؤقت
  const countriesCache = {};
  
  // تعديل دالة تغيير الدولة
  $('#country_id').on('change', function() {
      const countryId = $(this).val();
      if (!countryId) return;
      
      // التحقق من وجود البيانات في التخزين المؤقت
      if (countriesCache[countryId]) {
          updateFormWithCountryData(countriesCache[countryId]);
          return;
      }
      
      // استرجاع البيانات من الخادم وتخزينها مؤقتًا
      $.ajax({
          url: `api/countries.php?cities=true&id=${countryId}`,
          method: 'GET',
          success: function(response) {
              if (response.data) {
                  // تخزين البيانات مؤقتًا
                  countriesCache[countryId] = response.data;
                  // تحديث النموذج
                  updateFormWithCountryData(response.data);
              }
          }
      });
  });
  
  // دالة مساعدة لتحديث النموذج
  function updateFormWithCountryData(data) {
      // تحديث حقل المدينة
      $('#city').val(data.city);
      
      // تحديث الإحداثيات
      const lat = parseFloat(data.latitude);
      const lng = parseFloat(data.longitude);
      $('#latitude').val(lat);
      $('#longitude').val(lng);
      
      // تحديث الخريطة
      if (addMarker) addMap.removeLayer(addMarker);
      addMarker = L.marker([lat, lng]).addTo(addMap);
      addMap.setView([lat, lng], 8);
  }
  ```

#### 2. تحسين تجربة المستخدم في اختيار الإحداثيات

- **المشكلة**: عند اختيار دولة، يتم تحديث الإحداثيات تلقائيًا ولكن قد يرغب المستخدم في تعديلها
- **الحل المقترح**: إضافة زر لإعادة تعيين الإحداثيات إلى قيم الدولة الافتراضية
- **التنفيذ المقترح**:
  ```html
  <!-- إضافة زر إعادة تعيين الإحداثيات في نموذج الإضافة -->
  <div class="mb-3">
      <button type="button" class="btn btn-secondary btn-sm" id="resetCoordinates">
          إعادة تعيين الإحداثيات إلى موقع الدولة
      </button>
  </div>
  ```
  
  ```javascript
  // معالج حدث النقر على زر إعادة التعيين
  $('#resetCoordinates').on('click', function() {
      const countryId = $('#country_id').val();
      if (!countryId) return;
      
      // استرجاع بيانات الدولة وتحديث الإحداثيات
      $.ajax({
          url: `api/countries.php?cities=true&id=${countryId}`,
          method: 'GET',
          success: function(response) {
              if (response.data) {
                  // تحديث الإحداثيات
                  const lat = parseFloat(response.data.latitude);
                  const lng = parseFloat(response.data.longitude);
                  $('#latitude').val(lat);
                  $('#longitude').val(lng);
                  
                  // تحديث الخريطة
                  if (addMarker) addMap.removeLayer(addMarker);
                  addMarker = L.marker([lat, lng]).addTo(addMap);
                  addMap.setView([lat, lng], 8);
              }
          }
      });
  });
  ```

#### 3. تحسين التحقق من صحة البيانات

- **المشكلة**: التحقق من صحة البيانات يتم فقط على جانب الخادم
- **الحل المقترح**: إضافة تحقق من صحة البيانات على جانب العميل قبل إرسالها
- **التنفيذ المقترح**:
  ```javascript
  // دالة للتحقق من صحة نموذج إضافة مكان
  function validatePlaceForm() {
      let isValid = true;
      const errors = {};
      
      // التحقق من اسم المكان
      const name = $('#name').val().trim();
      if (!name) {
          errors.name = 'اسم المكان مطلوب';
          isValid = false;
      }
      
      // التحقق من اختيار الدولة
      const countryId = $('#country_id').val();
      if (!countryId) {
          errors.country_id = 'يرجى اختيار الدولة';
          isValid = false;
      }
      
      // التحقق من المدينة
      const city = $('#city').val().trim();
      if (!city) {
          errors.city = 'المدينة مطلوبة';
          isValid = false;
      }
      
      // التحقق من الإحداثيات
      const lat = parseFloat($('#latitude').val());
      const lng = parseFloat($('#longitude').val());
      if (isNaN(lat) || lat < -90 || lat > 90) {
          errors.latitude = 'خط العرض يجب أن يكون بين -90 و 90';
          isValid = false;
      }
      if (isNaN(lng) || lng < -180 || lng > 180) {
          errors.longitude = 'خط الطول يجب أن يكون بين -180 و 180';
          isValid = false;
      }
      
      // عرض الأخطاء إذا وجدت
      if (!isValid) {
          let errorMessage = 'يرجى تصحيح الأخطاء التالية:';
          for (const field in errors) {
              errorMessage += `\n- ${errors[field]}`;
          }
          
          Swal.fire({
              title: 'خطأ في البيانات',
              text: errorMessage,
              icon: 'error'
          });
      }
      
      return isValid;
  }
  
  // تعديل معالج إرسال النموذج
  $('#addPlaceForm').on('submit', function(e) {
      e.preventDefault();
      
      // التحقق من صحة البيانات
      if (!validatePlaceForm()) {
          return;
      }
      
      // إرسال البيانات إلى الخادم
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
  ```

#### 4. تحسين أمان النظام

- **المشكلة**: عدم وجود حماية كافية ضد هجمات CSRF
- **الحل المقترح**: إضافة رمز CSRF لجميع النماذج
- **التنفيذ المقترح**:
  ```php
  // إضافة في ملف helpers/auth.php
  public function generateCsrfToken() {
      if (!isset($_SESSION['csrf_token'])) {
          $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
      return $_SESSION['csrf_token'];
  }
  
  public function validateCsrfToken($token) {
      return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
  }
  ```
  
  ```html
  <!-- إضافة في نماذج الإضافة والتعديل -->
  <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
  ```
  
  ```php
  // إضافة في ملفات API
  // التحقق من رمز CSRF
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
      $token = $_POST['csrf_token'] ?? '';
      if (empty($token) || !$auth->validateCsrfToken($token)) {
          http_response_code(403);
          echo json_encode(['error' => 'CSRF token validation failed']);
          exit;
      }
  }
  ```

#### 5. تحسين تنظيم الكود

- **المشكلة**: تكرار الكود في معالجات الأحداث لنماذج الإضافة والتعديل
- **الحل المقترح**: استخراج الوظائف المشتركة إلى دوال مستقلة
- **التنفيذ المقترح**:
  ```javascript
  // دالة مشتركة لتحديث حقول النموذج بناءً على الدولة المحددة
  function updateFormFieldsByCountry(countryId, formPrefix = '', mapObj = null, markerObj = null) {
      if (!countryId) return;
      
      // استرجاع بيانات الدولة
      $.ajax({
          url: `api/countries.php?cities=true&id=${countryId}`,
          method: 'GET',
          success: function(response) {
              if (response.data) {
                  // تحديث حقل المدينة
                  $(`#${formPrefix}city`).val(response.data.city);
                  
                  // تحديث الإحداثيات
                  const lat = parseFloat(response.data.latitude);
                  const lng = parseFloat(response.data.longitude);
                  $(`#${formPrefix}latitude`).val(lat);
                  $(`#${formPrefix}longitude`).val(lng);
                  
                  // تحديث الخريطة إذا كانت متوفرة
                  if (mapObj && markerObj) {
                      if (markerObj) mapObj.removeLayer(markerObj);
                      markerObj = L.marker([lat, lng]).addTo(mapObj);
                      mapObj.setView([lat, lng], 8);
                  }
              }
          }
      });
  }
  
  // استخدام الدالة في معالجات الأحداث
  $('#country_id').on('change', function() {
      updateFormFieldsByCountry($(this).val(), '', addMap, addMarker);
  });
  
  $('#editCountryId').on('change', function() {
      updateFormFieldsByCountry($(this).val(), 'edit', editMap, editMarker);
  });
  ```

## الدوال المستخدمة في API

### api/countries.php

#### GET /api/countries.php
- **الوصف**: استرجاع قائمة الدول
- **المدخلات**: لا يوجد
- **المخرجات**: مصفوفة JSON تحتوي على قائمة الدول
- **مثال الاستخدام**:
  ```javascript
  $.get('api/countries.php', function(response) {
      console.log(response.data); // قائمة الدول
  });
  ```

#### GET /api/countries.php?id={id}
- **الوصف**: استرجاع معلومات دولة محددة
- **المدخلات**: معرف الدولة (id)
- **المخرجات**: كائن JSON يحتوي على معلومات الدولة
- **مثال الاستخدام**:
  ```javascript
  $.get('api/countries.php?id=1', function(response) {
      console.log(response.data); // معلومات الدولة
  });
  ```

#### GET /api/countries.php?cities=true&id={id}
- **الوصف**: استرجاع معلومات المدينة والإحداثيات للدولة المحددة
- **المدخلات**: معرف الدولة (id)
- **المخرجات**: كائن JSON يحتوي على المدينة وخط العرض وخط الطول
- **مثال الاستخدام**:
  ```javascript
  $.get('api/countries.php?cities=true&id=1', function(response) {
      console.log(response.data.city); // اسم المدينة
      console.log(response.data.latitude); // خط العرض
      console.log(response.data.longitude); // خط الطول
  });
  ```

### api/places.php

#### GET /api/places.php
- **الوصف**: استرجاع قائمة الأماكن
- **المدخلات**: لا يوجد
- **المخرجات**: مصفوفة JSON تحتوي على قائمة الأماكن
- **مثال الاستخدام**:
  ```javascript
  $.get('api/places.php', function(response) {
      console.log(response.data); // قائمة الأماكن
  });
  ```

#### GET /api/places.php?id={id}
- **الوصف**: استرجاع معلومات مكان محدد
- **المدخلات**: معرف المكان (id)
- **المخرجات**: كائن JSON يحتوي على معلومات المكان
- **مثال الاستخدام**:
  ```javascript
  $.get('api/places.php?id=1', function(response) {
      console.log(response.data); // معلومات المكان
  });
  ```

#### POST /api/places.php
- **الوصف**: إضافة مكان جديد
- **المدخلات**: بيانات المكان (name, country_id, city, type, total, latitude, longitude)
- **المخرجات**: كائن JSON يحتوي على حالة العملية
- **مثال الاستخدام**:
  ```javascript
  $.ajax({
      url: 'api/places.php',
      method: 'POST',
      data: {
          name: 'اسم المكان',
          country_id: 1,
          city: 'اسم المدينة',
          type: 'خاص',
          total: 10,
          latitude: 24.7136,
          longitude: 46.6753
      },
      success: function(response) {
          console.log(response); // حالة العملية
      }
  });
  ```

#### PUT /api/places.php?id={id}
- **الوصف**: تحديث معلومات مكان
- **المدخلات**: معرف المكان (id) وبيانات المكان المحدثة
- **المخرجات**: كائن JSON يحتوي على حالة العملية
- **مثال الاستخدام**:
  ```javascript
  $.ajax({
      url: 'api/places.php?id=1',
      method: 'PUT',
      data: {
          name: 'اسم المكان الجديد',
          country_id: 2,
          city: 'اسم المدينة الجديد',
          type: 'حكومة',
          total: 20,
          latitude: 25.7136,
          longitude: 47.6753
      },
      success: function(response) {
          console.log(response); // حالة العملية
      }
  });
  ```

#### DELETE /api/places.php?id={id}
- **الوصف**: حذف مكان
- **المدخلات**: معرف المكان (id)
- **المخرجات**: كائن JSON يحتوي على حالة العملية
- **مثال الاستخدام**:
  ```javascript
  $.ajax({
      url: 'api/places.php?id=1',
      method: 'DELETE',
      success: function(response) {
          console.log(response); // حالة العملية
      }
  });
  ```

## الدوال المستخدمة في JavaScript

### معالجات الأحداث في places.php

#### $('#country_id').on('change', function())
- **الوصف**: معالج حدث تغيير الدولة في نموذج الإضافة
- **المدخلات**: حدث تغيير القيمة
- **المخرجات**: تحديث حقول المدينة والإحداثيات والخريطة
- **تدفق البيانات**:
  1. الحصول على معرف الدولة المحددة
  2. إرسال طلب AJAX إلى `api/countries.php?cities=true&id={id}`
  3. استرجاع معلومات المدينة والإحداثيات
  4. تحديث حقول النموذج
  5. تحديث الخريطة

#### $('#editCountryId').on('change', function())
- **الوصف**: معالج حدث تغيير الدولة في نموذج التعديل
- **المدخلات**: حدث تغيير القيمة
- **المخرجات**: تحديث حقول المدينة والإحداثيات والخريطة
- **تدفق البيانات**:
  1. الحصول على معرف الدولة المحددة
  2. إرسال طلب AJAX إلى `api/countries.php?cities=true&id={id}`
  3. استرجاع معلومات المدينة والإحداثيات
  4. تحديث حقول النموذج
  5. تحديث الخريطة

#### $('#addPlaceForm').on('submit', function(e))
- **الوصف**: معالج حدث إرسال نموذج إضافة مكان
- **المدخلات**: حدث إرسال النموذج
- **المخرجات**: إضافة مكان جديد وتحديث الجدول
- **تدفق البيانات**:
  1. منع السلوك الافتراضي للنموذج
  2. إرسال بيانات النموذج إلى `api/places.php` بطريقة POST
  3. إغلاق النافذة المنبثقة
  4. تحديث الجدول
  5. عرض رسالة نجاح

#### $('#editPlaceForm').on('submit', function(e))
- **الوصف**: معالج حدث إرسال نموذج تعديل مكان
- **المدخلات**: حدث إرسال النموذج
- **المخرجات**: تحديث معلومات المكان وتحديث الجدول
- **تدفق البيانات**:
  1. منع السلوك الافتراضي للنموذج
  2. الحصول على معرف المكان
  3. إرسال بيانات النموذج إلى `api/places.php?id={id}` بطريقة PUT
  4. إغلاق النافذة المنبثقة
  5. تحديث الجدول
  6. عرض رسالة نجاح

## الدوال المستخدمة في PHP

### helpers/database.php

#### Database::getInstance()
- **الوصف**: الحصول على نسخة وحيدة من كلاس Database
- **المدخلات**: لا يوجد
- **المخرجات**: كائن Database
- **مثال الاستخدام**:
  ```php
  $db = Database::getInstance();
  ```

#### Database::getConnection()
- **الوصف**: الحصول على اتصال PDO
- **المدخلات**: لا يوجد
- **المخرجات**: كائن PDO
- **مثال الاستخدام**:
  ```php
  $pdo = $db->getConnection();
  ```

#### Database::fetchAll($sql, $params = [])
- **الوصف**: جلب جميع الصفوف من قاعدة البيانات
- **المدخلات**: استعلام SQL والمعلمات
- **المخرجات**: مصفوفة من الصفوف
- **مثال الاستخدام**:
  ```php
  $places = $db->fetchAll('SELECT * FROM places WHERE country_id = ?', [$countryId]);
  ```

#### Database::fetchOne($sql, $params = [])
- **الوصف**: جلب صف واحد من قاعدة البيانات
- **المدخلات**: استعلام SQL والمعلمات
- **المخرجات**: كائن يمثل الصف
- **مثال الاستخدام**:
  ```php
  $place = $db->fetchOne('SELECT * FROM places WHERE id = ?', [$id]);
  ```

#### Database::execute($sql, $params = [])
- **الوصف**: تنفيذ استعلام SQL
- **المدخلات**: استعلام SQL والمعلمات
- **المخرجات**: عدد الصفوف المتأثرة
- **مثال الاستخدام**:
  ```php
  $rowCount = $db->execute('UPDATE places SET name = ? WHERE id = ?', [$name, $id]);
  ```

#### Database::insert($sql, $params = [])
- **الوصف**: إدراج صف جديد في قاعدة البيانات
- **المدخلات**: استعلام SQL والمعلمات
- **المخرجات**: معرف الصف المدرج
- **مثال الاستخدام**:
  ```php
  $lastId = $db->insert('INSERT INTO places (name, country_id) VALUES (?, ?)', [$name, $countryId]);
  ```

### helpers/auth.php

#### Auth::getInstance()
- **الوصف**: الحصول على نسخة وحيدة من كلاس Auth
- **المدخلات**: لا يوجد
- **المخرجات**: كائن Auth
- **مثال الاستخدام**:
  ```php
  $auth = Auth::getInstance();
  ```

#### Auth::requireLogin()
- **الوصف**: التحقق من تسجيل الدخول وإعادة التوجيه إذا لم يكن المستخدم مسجل الدخول
- **المدخلات**: لا يوجد
- **المخرجات**: لا يوجد
- **مثال الاستخدام**:
  ```php
  $auth->requireLogin();
  ```

#### Auth::isAdmin()
- **الوصف**: التحقق مما إذا كان المستخدم الحالي مديرًا
- **المدخلات**: لا يوجد
- **المخرجات**: قيمة منطقية (true/false)
- **مثال الاستخدام**:
  ```php
  if ($auth->isAdmin()) {
      // عرض خيارات المدير
  }
  ```