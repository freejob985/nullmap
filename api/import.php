<?php
/**
 * Import API Endpoint
 * 
 * Handles importing data from Excel format:
 * - POST: Import places data from Excel
 */

require_once __DIR__ . '/../helpers/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validation.php';

// Check if PhpSpreadsheet is installed
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    header('Content-Type: application/json');
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'مكتبة PhpSpreadsheet غير مثبتة. يرجى تثبيت المكتبة باستخدام Composer.']);
    exit;
}

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\IOFactory;

// Initialize helpers
$db = Database::getInstance();
$auth = Auth::getInstance();
$validation = Validation::getInstance();

// Require authentication
$auth->requireLogin();

// Handle different import types
$type = $_GET['type'] ?? 'places';

switch ($type) {
    case 'places':
        // Check if user has permission to import places
        if (!$auth->hasPermission('import_places') && !$auth->isAdmin()) {
            header('Content-Type: application/json');
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'ليس لديك صلاحية لاستيراد البيانات']);
            exit;
        }
        importPlaces();
        break;
    default:
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'نوع الاستيراد غير صالح']);
        break;
}

/**
 * Import places data from Excel
 * 
 * @return void
 */
function importPlaces() {
    global $db, $validation;
    
    // Check if file was uploaded
    if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'لم يتم تحميل الملف بشكل صحيح']);
        exit;
    }
    
    // Check file extension
    $fileExtension = strtolower(pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ['xlsx', 'xls'])) {
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'يجب أن يكون الملف بتنسيق Excel (.xlsx أو .xls)']);
        exit;
    }
    
    try {
        // Load the spreadsheet
        $spreadsheet = IOFactory::load($_FILES['excelFile']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Get all rows as array
        $rows = $worksheet->toArray();
        
        // Remove header row
        $headers = array_shift($rows);
        
        // Map Arabic column names to English
        $columnMap = [
            'الاسم' => 'name',
            'الدولة' => 'country',
            'المدينة' => 'city',
            'النوع' => 'type',
            'العدد' => 'total',
            'خط العرض' => 'latitude',
            'خط الطول' => 'longitude'
        ];
        
        // Map column indices
        $columnIndices = [];
        foreach ($headers as $index => $header) {
            $header = trim($header);
            if (isset($columnMap[$header])) {
                $columnIndices[$columnMap[$header]] = $index;
            }
        }
        
        // Check if required columns exist
        $requiredColumns = ['name', 'country', 'city', 'type'];
        foreach ($requiredColumns as $column) {
            if (!isset($columnIndices[$column])) {
                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'الملف لا يحتوي على العمود المطلوب: ' . array_search($column, $columnMap)]);
                exit;
            }
        }
        
        // Start transaction
        $db->beginTransaction();
        
        // Get all countries for mapping
        $countries = $db->fetchAll('SELECT id, name FROM countries');
        $countryMap = [];
        foreach ($countries as $country) {
            $countryMap[strtolower($country['name'])] = $country['id'];
        }
        
        // Type mapping
        $typeMap = [
            'خاص' => 'private',
            'حكومي' => 'government',
            'private' => 'private',
            'government' => 'government'
        ];
        
        // Process rows
        $imported = 0;
        $errors = [];
        
        foreach ($rows as $rowIndex => $row) {
            // Skip empty rows
            if (empty($row[$columnIndices['name']])) {
                continue;
            }
            
            // Extract data
            $name = trim($row[$columnIndices['name']]);
            $countryName = trim($row[$columnIndices['country']]);
            $city = trim($row[$columnIndices['city']]);
            $typeRaw = trim($row[$columnIndices['type']]);
            $total = isset($columnIndices['total']) ? intval($row[$columnIndices['total']]) : 0;
            $latitude = isset($columnIndices['latitude']) ? floatval($row[$columnIndices['latitude']]) : 0;
            $longitude = isset($columnIndices['longitude']) ? floatval($row[$columnIndices['longitude']]) : 0;
            
            // Validate data
            if (empty($name) || strlen($name) > 255) {
                $errors[] = "الصف " . ($rowIndex + 2) . ": اسم المكان غير صالح";
                continue;
            }
            
            // Map country name to ID
            $countryId = null;
            $countryKey = strtolower($countryName);
            if (isset($countryMap[$countryKey])) {
                $countryId = $countryMap[$countryKey];
            } else {
                $errors[] = "الصف " . ($rowIndex + 2) . ": الدولة '$countryName' غير موجودة في قاعدة البيانات";
                continue;
            }
            
            // Map type
            $type = null;
            $typeKey = strtolower($typeRaw);
            foreach ($typeMap as $key => $value) {
                if (strtolower($key) === $typeKey) {
                    $type = $value;
                    break;
                }
            }
            
            if ($type === null) {
                $errors[] = "الصف " . ($rowIndex + 2) . ": نوع المكان '$typeRaw' غير صالح. يجب أن يكون 'خاص' أو 'حكومي'";
                continue;
            }
            
            // Insert place
            $placeData = [
                'name' => $name,
                'country_id' => $countryId,
                'city' => $city,
                'type' => $type,
                'total' => $total,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('places', $placeData);
            $imported++;
        }
        
        // Commit transaction if no errors, otherwise rollback
        if (empty($errors)) {
            $db->commit();
            
            // Return success response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'imported' => $imported,
                'message' => "تم استيراد $imported مكان بنجاح"
            ]);
        } else {
            $db->rollback();
            
            // Return error response
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad Request');
            echo json_encode([
                'error' => 'حدثت أخطاء أثناء استيراد البيانات',
                'details' => $errors
            ]);
        }
        
    } catch (Exception $e) {
        // Return error response
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'حدث خطأ أثناء معالجة الملف: ' . $e->getMessage()]);
    }
}