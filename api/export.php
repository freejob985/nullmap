<?php
/**
 * Export API Endpoint
 * 
 * Handles exporting data to Excel format:
 * - GET: Export places data to Excel
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

// Check if user has permission to export places
if (!$auth->hasPermission('export_places') && !$auth->isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo 'ليس لديك صلاحية لتصدير البيانات';
    exit;
}

// Handle different export types
$type = $_GET['type'] ?? 'places';

switch ($type) {
    case 'places':
        exportPlaces();
        break;
    default:
        header('HTTP/1.1 400 Bad Request');
        echo 'نوع التصدير غير صالح';
        break;
}

/**
 * Export places data to Excel
 * 
 * @return void
 */
function exportPlaces() {
    global $db;
    
    // Get places with country names
    $places = $db->fetchAll(
        'SELECT p.*, c.name as country_name 
         FROM places p
         JOIN countries c ON p.country_id = c.id
         ORDER BY p.id DESC'
    );
    
    // Define column headers in Arabic
    $headers = [
        'id' => 'الرقم',
        'name' => 'الاسم',
        'country_name' => 'الدولة',
        'city' => 'المدينة',
        'type' => 'النوع',
        'total' => 'العدد',
        'latitude' => 'خط العرض',
        'longitude' => 'خط الطول',
        'created_at' => 'تاريخ الإضافة',
        'updated_at' => 'تاريخ التحديث'
    ];
    
    // Translate type values
    $typeTranslations = [
        'private' => 'خاص',
        'government' => 'حكومي'
    ];
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="places_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output Excel file with UTF-8 BOM for proper Arabic display
    echo '\xEF\xBB\xBF'; // UTF-8 BOM
    
    // Start HTML table
    echo '<table border="1">';
    
    // Output headers
    echo '<tr>';
    foreach ($headers as $header) {
        echo '<th>' . $header . '</th>';
    }
    echo '</tr>';
    
    // Output data rows
    foreach ($places as $place) {
        echo '<tr>';
        foreach (array_keys($headers) as $key) {
            echo '<td>';
            
            // Format specific fields
            if ($key === 'type' && isset($typeTranslations[$place[$key]])) {
                echo $typeTranslations[$place[$key]];
            } elseif ($key === 'created_at' || $key === 'updated_at') {
                echo date('Y-m-d H:i', strtotime($place[$key]));
            } else {
                echo $place[$key];
            }
            
            echo '</td>';
        }
        echo '</tr>';
    }
    
    // End HTML table
    echo '</table>';
    exit;
}