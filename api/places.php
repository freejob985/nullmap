<?php
/**
 * Places API Endpoint
 * 
 * Handles CRUD operations for places:
 * - GET: List all places
 * - POST: Create new place
 * - PUT: Update place
 * - DELETE: Delete place
 */

require_once __DIR__ . '/../helpers/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validation.php';

// Initialize helpers
$db = Database::getInstance();
$auth = Auth::getInstance();
$validation = Validation::getInstance();

// Set JSON response headers
header('Content-Type: application/json; charset=utf-8');

// Require authentication
$auth->requireLogin();

// Check if user has permission to view places
if (!$auth->hasPermission('view_places') && !$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'ليس لديك صلاحية لعرض الأماكن']);
    exit;
}

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get single place by ID
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid place ID']);
                exit;
            }

            $place = $db->fetchOne('
                SELECT p.*, c.name as country_name 
                FROM places p
                JOIN countries c ON p.country_id = c.id
                WHERE p.id = ?
            ', [$id]);

            if (!$place) {
                http_response_code(404);
                echo json_encode(['error' => 'Place not found']);
                exit;
            }

            echo json_encode(['data' => $place]);
            exit;
        }

        // Get all places with country names
        $places = $db->fetchAll('
            SELECT p.*, c.name as country_name 
            FROM places p
            JOIN countries c ON p.country_id = c.id
            ORDER BY p.id DESC
        ');
        echo json_encode(['data' => $places]);
        break;

    case 'POST':
        // Check if user has permission to add places
        if (!$auth->hasPermission('add_places') && !$auth->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'ليس لديك صلاحية لإضافة الأماكن']);
            exit;
        }

        // Validate input
        $data = $validation->sanitize($_POST);
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'total' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:private,government'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'city' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180']
        ];

        if (!$validation->validate($data, $rules)) {
            http_response_code(422);
            echo json_encode(['error' => 'Validation failed', 'errors' => $validation->getErrors()]);
            exit;
        }

        // Verify country exists
        $country = $db->fetchOne('SELECT id FROM countries WHERE id = ?', [$data['country_id']]);
        if (!$country) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid country ID']);
            exit;
        }

        // Insert place
        try {
            $lastId = $db->insert('
                INSERT INTO places (name, total, type, country_id, city, latitude, longitude)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ', [
                $data['name'],
                $data['total'],
                $data['type'],
                $data['country_id'],
                $data['city'],
                $data['latitude'],
                $data['longitude']
            ]);

            // Get country name for response
            $country = $db->fetchOne('SELECT name FROM countries WHERE id = ?', [$data['country_id']]);

            echo json_encode([
                'message' => 'Place created successfully',
                'data' => [
                    'id' => $lastId,
                    'name' => $data['name'],
                    'total' => $data['total'],
                    'type' => $data['type'],
                    'country_id' => $data['country_id'],
                    'country_name' => $country['name'],
                    'city' => $data['city'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude']
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create place']);
        }
        break;

    case 'PUT':
        // Check if user has permission to edit places
        if (!$auth->hasPermission('edit_places') && !$auth->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'ليس لديك صلاحية لتعديل الأماكن']);
            exit;
        }

        // Check for ID
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Place ID is required']);
            exit;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid place ID']);
            exit;
        }

        // Get PUT data
        parse_str(file_get_contents('php://input'), $putData);
        $data = $validation->sanitize($putData);

        // Validate input
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'total' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:private,government'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'city' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180']
        ];

        if (!$validation->validate($data, $rules)) {
            http_response_code(422);
            echo json_encode(['error' => 'Validation failed', 'errors' => $validation->getErrors()]);
            exit;
        }

        // Verify country exists
        $country = $db->fetchOne('SELECT id FROM countries WHERE id = ?', [$data['country_id']]);
        if (!$country) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid country ID']);
            exit;
        }

        // Update place
        try {
            $rowCount = $db->execute('
                UPDATE places
                SET name = ?, total = ?, type = ?, country_id = ?, city = ?, latitude = ?, longitude = ?
                WHERE id = ?
            ', [
                $data['name'],
                $data['total'],
                $data['type'],
                $data['country_id'],
                $data['city'],
                $data['latitude'],
                $data['longitude'],
                $id
            ]);

            if ($rowCount === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Place not found']);
                exit;
            }

            // Get country name for response
            $country = $db->fetchOne('SELECT name FROM countries WHERE id = ?', [$data['country_id']]);

            echo json_encode([
                'message' => 'Place updated successfully',
                'data' => [
                    'id' => $id,
                    'name' => $data['name'],
                    'total' => $data['total'],
                    'type' => $data['type'],
                    'country_id' => $data['country_id'],
                    'country_name' => $country['name'],
                    'city' => $data['city'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude']
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update place']);
        }
        break;

    case 'DELETE':
        // Check if user has permission to delete places
        if (!$auth->hasPermission('delete_places') && !$auth->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'ليس لديك صلاحية لحذف الأماكن']);
            exit;
        }

        // Check for ID
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Place ID is required']);
            exit;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid place ID']);
            exit;
        }

        // Delete place
        try {
            $rowCount = $db->execute('DELETE FROM places WHERE id = ?', [$id]);

            if ($rowCount === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Place not found']);
                exit;
            }

            echo json_encode(['message' => 'Place deleted successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete place']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}