<?php
/**
 * Template API Endpoint
 * 
 * Provides downloadable templates for data import:
 * - GET: Download places import template
 */

require_once __DIR__ . '/../helpers/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validation.php';

// Initialize helpers
$db = Database::getInstance();
$auth = Auth::getInstance();
$validation = Validation::getInstance();

// Require authentication
$auth->requireLogin();

// Check if user has permission to import places
if (!$auth->hasPermission('import_places') && !$auth->isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo 'ليس لديك صلاحية لتنزيل قوالب الاستيراد';
    exit;
}

// Check if example parameter is set
$showExamples = isset($_GET['example']) && $_GET['example'] == '1';

// Handle different template types
$type = $_GET['type'] ?? 'places';

switch ($type) {
    case 'places':
        downloadPlacesTemplate();
        break;
    default:
        header('HTTP/1.1 400 Bad Request');
        echo 'نوع القالب غير صالح';
        break;
}

/**
 * Download places import template
 * 
 * @return void
 */
function downloadPlacesTemplate() {
    global $db, $showExamples;
    
    // Get countries for reference
    $countries = $db->fetchAll('SELECT id, name FROM countries ORDER BY name');
    
    // Prepare countries list for display
    $countriesList = '';
    foreach ($countries as $country) {
        $countriesList .= $country['name'] . ', ';
    }
    $countriesList = rtrim($countriesList, ', ');
    
    // Define column headers in Arabic with descriptions
    $headers = [
        'الاسم' => 'اسم المكان (مطلوب)',
        'الدولة' => 'اسم الدولة كما هو مسجل في النظام (مطلوب)',
        'المدينة' => 'اسم المدينة (مطلوب)',
        'النوع' => 'خاص أو حكومي (مطلوب)',
        'العدد' => 'قيمة رقمية (اختياري)',
        'خط العرض' => 'قيمة رقمية (اختياري)',
        'خط الطول' => 'قيمة رقمية (اختياري)'
    ];
    
    // Set headers for Excel download
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');

// Set filename based on whether examples are included
$filename = $showExamples ? 'places_template_with_examples.xls' : 'places_template.xls';
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output UTF-8 BOM for proper Arabic display
echo chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM
    
    // Start HTML document with proper encoding and styling
    echo '<!DOCTYPE html>\n<html dir="rtl">\n<head>\n<meta charset="UTF-8">\n<style>\n    body { font-family: Arial, Tahoma, sans-serif; margin: 20px; background-color: #f9f9f9; }\n    table { border-collapse: collapse; width: 100%; direction: rtl; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }\n    th { background-color: #4CAF50; color: white; text-align: center; padding: 12px 8px; font-size: 14px; }\n    td { padding: 8px 6px; text-align: right; border: 1px solid #ddd; }\n    tr:hover { background-color: #f5f5f5; }\n    .note { font-size: 12px; color: #666; }\n    .required { color: #e53935; font-weight: bold; }\n    .example-row { background-color: #e3f2fd; }\n    .input-row { background-color: #fffbea; }\n    h1 { text-shadow: 1px 1px 2px rgba(0,0,0,0.1); }\n    .info-box { margin: 10px 0; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; text-align: right; }\n    .help-section { margin-top: 20px; padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; border-radius: 4px; }\n    .help-section h3 { color: #4CAF50; margin-top: 0; }\n    ol { padding-right: 20px; }\n    li { margin-bottom: 5px; }\n</style>\n</head>\n<body>\n<h1 style="text-align: center; color: #4CAF50;">نموذج استيراد الأماكن</h1>\n<p style="text-align: center; font-weight: bold;">يرجى تعبئة البيانات حسب التعليمات أدناه. الحقول المشار إليها بعلامة (*) هي حقول مطلوبة يجب تعبئتها.</p>\n<div class="info-box">\n<strong>ملاحظة هامة:</strong> تأكد من كتابة اسم الدولة بالضبط كما هو مكتوب في قائمة الدول المتاحة في أسفل الصفحة.\n</div>\n<div style="text-align: center; margin: 15px 0;">\n<a href="api/template.php?type=places" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; margin-left: 10px;">تنزيل نموذج فارغ</a>\n<a href="api/template.php?type=places&example=1" style="display: inline-block; padding: 10px 20px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">تنزيل نموذج مع أمثلة متعددة</a>\n</div>\n<table border="1">';
    
    // Output headers with tooltips for better understanding
    echo '<tr>';
    foreach ($headers as $header => $description) {
        // Add asterisk to required fields
        $isRequired = strpos($description, '(مطلوب)') !== false;
        $headerText = $header . ($isRequired ? ' *' : '');
        echo '<th title="' . $description . '" style="position: relative;">' . $headerText . '</th>';
    }
    echo '</tr>';
    
    // Output description row for better guidance
    echo '<tr style="background-color: #e8f5e9;">';
    foreach ($headers as $description) {
        echo '<td style="font-size: 12px; color: #333; padding: 5px;">' . $description . '</td>';
    }
    echo '</tr>';
    
    // Output example row with real data
    echo '<tr class="example-row">';
    echo '<td>مركز الملك عبدالعزيز التاريخي</td>'; // الاسم
    echo '<td>' . ($countries[0]['name'] ?? 'المملكة العربية السعودية') . '</td>'; // الدولة
    echo '<td>الرياض</td>'; // المدينة
    echo '<td>حكومي</td>'; // النوع (خاص أو حكومي)
    echo '<td>100</td>'; // العدد
    echo '<td>24.6408</td>'; // خط العرض
    echo '<td>46.7127</td>'; // خط الطول
    echo '</tr>';
    
    // Output second example row
    echo '<tr class="example-row">';
    echo '<td>برج المملكة</td>'; // الاسم
    echo '<td>' . ($countries[0]['name'] ?? 'المملكة العربية السعودية') . '</td>'; // الدولة
    echo '<td>الرياض</td>'; // المدينة
    echo '<td>خاص</td>'; // النوع (خاص أو حكومي)
    echo '<td>50</td>'; // العدد
    echo '<td>24.7136</td>'; // خط العرض
    echo '<td>46.6753</td>'; // خط الطول
    echo '</tr>';
    
    // Add more examples if example parameter is set
    if ($showExamples) {
        // Example 3 - Jeddah
        echo '<tr class="example-row">';
        echo '<td>برج جدة</td>'; // الاسم
        echo '<td>' . ($countries[0]['name'] ?? 'المملكة العربية السعودية') . '</td>'; // الدولة
        echo '<td>جدة</td>'; // المدينة
        echo '<td>حكومي</td>'; // النوع
        echo '<td>1</td>'; // العدد
        echo '<td>21.5433</td>'; // خط العرض
        echo '<td>39.1728</td>'; // خط الطول
        echo '</tr>';
        
        // Example 4 - Dammam
        echo '<tr class="example-row">';
        echo '<td>كورنيش الدمام</td>'; // الاسم
        echo '<td>' . ($countries[0]['name'] ?? 'المملكة العربية السعودية') . '</td>'; // الدولة
        echo '<td>الدمام</td>'; // المدينة
        echo '<td>حكومي</td>'; // النوع
        echo '<td>1</td>'; // العدد
        echo '<td>26.4207</td>'; // خط العرض
        echo '<td>50.0888</td>'; // خط الطول
        echo '</tr>';
        
        // Example 5 - Makkah
        echo '<tr class="example-row">';
        echo '<td>المسجد الحرام</td>'; // الاسم
        echo '<td>' . ($countries[0]['name'] ?? 'المملكة العربية السعودية') . '</td>'; // الدولة
        echo '<td>مكة المكرمة</td>'; // المدينة
        echo '<td>حكومي</td>'; // النوع
        echo '<td>1</td>'; // العدد
        echo '<td>21.4225</td>'; // خط العرض
        echo '<td>39.8262</td>'; // خط الطول
        echo '</tr>';
    }
    
    // Add empty row for user to fill with light styling
    echo '<tr class="input-row">';
    foreach ($headers as $header => $description) {
        echo '<td style="height: 25px;"></td>';
    }
    echo '</tr>';
    
    // Add note about adding more rows
    echo '<tr style="background-color: #f9f9f9;">';
    echo '<td colspan="' . count($headers) . '" class="note" style="text-align: center; font-style: italic;">';
    echo 'يمكنك إضافة المزيد من الصفوف حسب الحاجة. تأكد من تعبئة الحقول المطلوبة <span class="required">*</span> لكل صف.';
    echo '</td>';
    echo '</tr>';
    
    // End HTML table
    echo '</table>';
    
    // Add countries reference section
    echo '<div class="help-section">';
    echo '<h3>الدول المتاحة في النظام</h3>';
    echo '<p>يجب استخدام أسماء الدول كما هي مكتوبة أدناه تماماً:</p>';
    echo '<p style="direction: rtl; text-align: right; line-height: 1.6;">' . $countriesList . '</p>';
    echo '</div>';
    
    // Add help section
    echo '<div class="help-section">';
    echo '<h3>تعليمات الاستخدام</h3>';
    echo '<ol>';
    echo '<li>قم بتعبئة البيانات في الصف الفارغ ذو اللون الأصفر الفاتح.</li>';
    echo '<li>يمكنك إضافة المزيد من الصفوف حسب الحاجة.</li>';
    echo '<li>تأكد من كتابة اسم الدولة بالضبط كما هو مكتوب في قائمة الدول المتاحة.</li>';
    echo '<li>قيمة النوع يجب أن تكون "خاص" أو "حكومي" فقط.</li>';
    echo '<li>قيم الإحداثيات (خط العرض وخط الطول) يجب أن تكون أرقاماً.</li>';
    echo '<li>احفظ الملف بتنسيق .xlsx أو .xls قبل رفعه.</li>';
    echo '</ol>';
    echo '</div>';
    
    // Add common errors section
    echo '<div class="help-section">';
    echo '<h3>الأخطاء الشائعة</h3>';
    echo '<ul style="color: #d32f2f;">';
    echo '<li>عدم كتابة اسم الدولة بشكل صحيح - تأكد من نسخ الاسم بالضبط من قائمة الدول المتاحة.</li>';
    echo '<li>استخدام قيم غير صحيحة للنوع - يجب أن تكون "خاص" أو "حكومي" فقط.</li>';
    echo '<li>إدخال إحداثيات بتنسيق غير صحيح - يجب أن تكون أرقاماً (مثال: 24.7136).</li>';
    echo '<li>ترك الحقول المطلوبة فارغة - تأكد من تعبئة جميع الحقول المشار إليها بعلامة (*).</li>';
    echo '<li>مشاكل في عرض الحروف العربية - تأكد من حفظ الملف بتنسيق Excel يدعم UTF-8.</li>';
    echo '</ul>';
    echo '</div>';
    
    // Add Arabic display fix section
    echo '<div class="help-section" style="background-color: #fff8e1; border-color: #ffc107;">';
    echo '<h3>حل مشكلة ظهور الأحرف العربية بشكل غير صحيح</h3>';
    echo '<p>إذا ظهرت الأحرف العربية بشكل غير صحيح في الملف الذي قمت بتنزيله، جرب الخطوات التالية:</p>';
    echo '<ol>';
    echo '<li>افتح برنامج Excel وانقر على <strong>ملف (File)</strong> ثم <strong>فتح (Open)</strong>.</li>';
    echo '<li>حدد <strong>كافة الملفات (*.*)</strong> من قائمة أنواع الملفات.</li>';
    echo '<li>اختر ملف النموذج الذي قمت بتنزيله.</li>';
    echo '<li>في نافذة <strong>معالج استيراد النص (Text Import Wizard)</strong>، اختر <strong>Delimited</strong> ثم انقر <strong>التالي (Next)</strong>.</li>';
    echo '<li>حدد <strong>Tab</strong> كفاصل، ثم انقر <strong>التالي (Next)</strong>.</li>';
    echo '<li>حدد <strong>عام (General)</strong> كتنسيق للبيانات، ثم انقر <strong>إنهاء (Finish)</strong>.</li>';
    echo '<li>بعد فتح الملف، احفظه بتنسيق <strong>Excel Workbook (*.xlsx)</strong> مع التأكد من اختيار ترميز <strong>Unicode (UTF-8)</strong>.</li>';
    echo '</ol>';
    echo '<p><strong>ملاحظة:</strong> يمكنك أيضاً استخدام برامج أخرى مثل LibreOffice Calc أو Google Sheets التي قد تتعامل بشكل أفضل مع النصوص العربية.</p>';
    echo '</div>';
    
    // Add Excel saving instructions
    echo '<div class="help-section">';
    echo '<h3>كيفية حفظ الملف بشكل صحيح</h3>';
    echo '<ol>';
    echo '<li>بعد تعبئة البيانات، انقر على <strong>ملف (File)</strong> ثم <strong>حفظ باسم (Save As)</strong>.</li>';
    echo '<li>اختر نوع الملف <strong>Excel Workbook (*.xlsx)</strong>.</li>';
    echo '<li>تأكد من اختيار <strong>Tools > Web Options</strong> وتحديد ترميز <strong>Unicode (UTF-8)</strong> في علامة التبويب <strong>Encoding</strong>.</li>';
    echo '<li>انقر على <strong>حفظ (Save)</strong>.</li>';
    echo '</ol>';
    echo '<p style="margin-top: 10px;"><strong>ملاحظة:</strong> إذا استمرت مشكلة ظهور الحروف العربية بشكل غير صحيح، جرب حفظ الملف بتنسيق <strong>Excel 97-2003 Workbook (*.xls)</strong>.</p>';
    echo '</div>';
    
    // Add coordinates help section
    echo '<div class="help-section">';
    echo '<h3>كيفية الحصول على إحداثيات صحيحة</h3>';
    echo '<ol>';
    echo '<li>يمكنك استخدام <strong>خرائط جوجل (Google Maps)</strong> للحصول على إحداثيات دقيقة.</li>';
    echo '<li>انتقل إلى موقع المكان على الخريطة.</li>';
    echo '<li>انقر بزر الماوس الأيمن على الموقع المحدد واختر <strong>ما هذا المكان؟ (What\'s here?)</strong>.</li>';
    echo '<li>ستظهر الإحداثيات في مربع البحث بالأعلى بتنسيق (خط العرض، خط الطول).</li>';
    echo '<li>انسخ هذه القيم إلى الأعمدة المناسبة في ملف الإكسل.</li>';
    echo '</ol>';
    echo '<p style="margin-top: 10px;"><strong>مثال:</strong> إذا كانت الإحداثيات في خرائط جوجل هي <strong>24.7136, 46.6753</strong>، فإن خط العرض هو <strong>24.7136</strong> وخط الطول هو <strong>46.6753</strong>.</p>';
    echo '</div>';
    
    // Add technical support section
    echo '<div class="help-section" style="background-color: #e8f5e9; border-color: #4CAF50;">';
    echo '<h3>الدعم الفني</h3>';
    echo '<p>إذا واجهتك أي مشكلة في استخدام هذا النموذج أو في عملية استيراد البيانات، يرجى التواصل مع فريق الدعم الفني:</p>';
    echo '<ul>';
    echo '<li><strong>البريد الإلكتروني:</strong> support@example.com</li>';
    echo '<li><strong>رقم الهاتف:</strong> +966 XX XXX XXXX</li>';
    echo '</ul>';
    echo '<p>يرجى تضمين المعلومات التالية عند التواصل:</p>';
    echo '<ol>';
    echo '<li>وصف المشكلة بالتفصيل</li>';
    echo '<li>لقطة شاشة للخطأ (إن وجد)</li>';
    echo '<li>نسخة من ملف الإكسل الذي تحاول استيراده</li>';
    echo '</ol>';
    echo '</div>';
    
    // End HTML document
    echo '</body>\n</html>';
    exit;
}