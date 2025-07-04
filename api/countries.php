<?php
/**
 * Countries API Endpoint
 * 
 * Handles CRUD operations for countries:
 * - GET: List all countries
 * - POST: Create new country
 * - PUT: Update country
 * - DELETE: Delete country
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
        // Get single country by ID
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid country ID']);
                exit;
            }

            $stmt = $db->getConnection()->prepare('SELECT * FROM countries WHERE id = ?');
            $stmt->execute([$id]);
            $country = $stmt->fetch();

            if (!$country) {
                http_response_code(404);
                echo json_encode(['error' => 'Country not found']);
                exit;
            }

            echo json_encode(['data' => $country]);
            exit;
        }

        // Get all countries
        $stmt = $db->getConnection()->query('SELECT * FROM countries ORDER BY id DESC');
        $countries = $stmt->fetchAll();
        echo json_encode(['data' => $countries]);
        break;

    case 'POST':
        // Require admin role for creating
        $auth->requireAdmin();

        // Validate input
        $data = $validation->sanitize($_POST);
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180']
        ];

        if (!$validation->validate($data, $rules)) {
            http_response_code(422);
            echo json_encode(['error' => 'Validation failed', 'errors' => $validation->getErrors()]);
            exit;
        }

        // Insert country
        $stmt = $db->getConnection()->prepare('
            INSERT INTO countries (name, city, latitude, longitude)
            VALUES (?, ?, ?, ?)
        ');

        try {
            $stmt->execute([
                $data['name'],
                $data['city'],
                $data['latitude'],
                $data['longitude']
            ]);

            $lastId = $db->getConnection()->lastInsertId();
            echo json_encode([
                'message' => 'Country created successfully',
                'data' => [
                    'id' => $lastId,
                    'name' => $data['name'],
                    'city' => $data['city'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude']
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create country']);
        }
        break;

    case 'PUT':
        // Require admin role for updating
        $auth->requireAdmin();

        // Check for ID
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Country ID is required']);
            exit;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid country ID']);
            exit;
        }

        // Get PUT data
        parse_str(file_get_contents('php://input'), $putData);
        $data = $validation->sanitize($putData);

        // Validate input
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180']
        ];

        if (!$validation->validate($data, $rules)) {
            http_response_code(422);
            echo json_encode(['error' => 'Validation failed', 'errors' => $validation->getErrors()]);
            exit;
        }

        // Update country
        $stmt = $db->getConnection()->prepare('
            UPDATE countries
            SET name = ?, city = ?, latitude = ?, longitude = ?
            WHERE id = ?
        ');

        try {
            $stmt->execute([
                $data['name'],
                $data['city'],
                $data['latitude'],
                $data['longitude'],
                $id
            ]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Country not found']);
                exit;
            }

            echo json_encode([
                'message' => 'Country updated successfully',
                'data' => [
                    'id' => $id,
                    'name' => $data['name'],
                    'city' => $data['city'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude']
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update country']);
        }
        break;

    case 'DELETE':
        // Require admin role for deleting
        $auth->requireAdmin();

        // Check for ID
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Country ID is required']);
            exit;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid country ID']);
            exit;
        }

        // Delete country
        $stmt = $db->getConnection()->prepare('DELETE FROM countries WHERE id = ?');

        try {
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Country not found']);
                exit;
            }

            echo json_encode(['message' => 'Country deleted successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete country']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 