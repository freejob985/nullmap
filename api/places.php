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

            $stmt = $db->prepare('
                SELECT p.*, c.name as country_name 
                FROM places p
                JOIN countries c ON p.country_id = c.id
                WHERE p.id = ?
            ');
            $stmt->execute([$id]);
            $place = $stmt->fetch();

            if (!$place) {
                http_response_code(404);
                echo json_encode(['error' => 'Place not found']);
                exit;
            }

            echo json_encode(['data' => $place]);
            exit;
        }

        // Get all places with country names
        $stmt = $db->query('
            SELECT p.*, c.name as country_name 
            FROM places p
            JOIN countries c ON p.country_id = c.id
            ORDER BY p.id DESC
        ');
        $places = $stmt->fetchAll();
        echo json_encode(['data' => $places]);
        break;

    case 'POST':
        // Require admin role for creating
        $auth->requireAdmin();

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
        $stmt = $db->prepare('SELECT id FROM countries WHERE id = ?');
        $stmt->execute([$data['country_id']]);
        if (!$stmt->fetch()) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid country ID']);
            exit;
        }

        // Insert place
        $stmt = $db->prepare('
            INSERT INTO places (name, total, type, country_id, city, latitude, longitude)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        try {
            $stmt->execute([
                $data['name'],
                $data['total'],
                $data['type'],
                $data['country_id'],
                $data['city'],
                $data['latitude'],
                $data['longitude']
            ]);

            // Get country name for response
            $stmt = $db->prepare('SELECT name FROM countries WHERE id = ?');
            $stmt->execute([$data['country_id']]);
            $country = $stmt->fetch();

            echo json_encode([
                'message' => 'Place created successfully',
                'data' => [
                    'id' => $db->lastInsertId(),
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
        // Require admin role for updating
        $auth->requireAdmin();

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
        $stmt = $db->prepare('SELECT id FROM countries WHERE id = ?');
        $stmt->execute([$data['country_id']]);
        if (!$stmt->fetch()) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid country ID']);
            exit;
        }

        // Update place
        $stmt = $db->prepare('
            UPDATE places
            SET name = ?, total = ?, type = ?, country_id = ?, city = ?, latitude = ?, longitude = ?
            WHERE id = ?
        ');

        try {
            $stmt->execute([
                $data['name'],
                $data['total'],
                $data['type'],
                $data['country_id'],
                $data['city'],
                $data['latitude'],
                $data['longitude'],
                $id
            ]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Place not found']);
                exit;
            }

            // Get country name for response
            $stmt = $db->prepare('SELECT name FROM countries WHERE id = ?');
            $stmt->execute([$data['country_id']]);
            $country = $stmt->fetch();

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
        // Require admin role for deleting
        $auth->requireAdmin();

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
        $stmt = $db->prepare('DELETE FROM places WHERE id = ?');

        try {
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
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