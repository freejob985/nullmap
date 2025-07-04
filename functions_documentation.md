# توثيق الدوال المستخدمة في نظام إدارة المواقع الجغرافية

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