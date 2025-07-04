<?php
/**
 * Users API Endpoint
 * 
 * Handles CRUD operations for users:
 * - GET: List all users
 * - POST: Create new user
 * - PUT: Update user
 * - DELETE: Delete user
 * 
 * Also handles user permissions management:
 * - GET ?permissions=true&id={id}: Get user permissions
 * - PUT ?permissions=true&id={id}: Update user permissions
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

// Handle permissions request
if (isset($_GET['permissions'])) {
    // Check if user has permission to manage permissions
    if (!$auth->hasPermission('manage_permissions') && !$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden: You do not have permission to manage user permissions']);
        exit;
    }
    
    // Get user ID
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID is required']);
        exit;
    }
    
    // Check if user exists
    $user = $db->fetchOne('SELECT id, name, email, role FROM users WHERE id = ?', [$userId]);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Handle GET request - Get user permissions
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userPermissions = $auth->getUserPermissions($userId);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'user' => $user,
                'permissions' => $userPermissions
            ]
        ]);
        exit;
    }
    
    // Handle PUT request - Update user permissions
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Get request body
        $requestBody = file_get_contents('php://input');
        $data = json_decode($requestBody, true);
        
        if (!isset($data['permissions']) || !is_array($data['permissions'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Permissions array is required']);
            exit;
        }
        
        // Update user permissions
        $result = $auth->assignPermissionsToUser($userId, $data['permissions']);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'User permissions updated successfully',
                'data' => [
                    'user_id' => $userId,
                    'permissions' => $data['permissions']
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update user permissions']);
        }
        exit;
    }
    
    // Method not allowed
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Regular users API
// Require admin role or manage_users permission
if (!$auth->hasPermission('manage_users') && !$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: You do not have permission to manage users']);
    exit;
}

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get single user by ID
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid user ID']);
                exit;
            }

            $stmt = $db->getConnection()->prepare('SELECT id, name, email, role, is_active FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit;
            }

            echo json_encode(['data' => $user]);
            exit;
        }

        // Get all users
        $stmt = $db->getConnection()->query('SELECT id, name, email, role, is_active FROM users ORDER BY id DESC');
        $users = $stmt->fetchAll();
        echo json_encode(['data' => $users]);
        break;

    case 'POST':
        // Validate input
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
            'role' => ['required', 'in:admin,user'],
            'is_active' => ['required', 'in:0,1']
        ];

        if (!$validation->validate($data, $rules)) {
            http_response_code(422);
            echo json_encode(['error' => 'Validation failed', 'errors' => $validation->getErrors()]);
            exit;
        }

        // Check if email already exists
        $stmt = $db->getConnection()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            http_response_code(422);
            echo json_encode(['error' => 'Email already exists']);
            exit;
        }

        // Insert user
        $stmt = $db->getConnection()->prepare('
            INSERT INTO users (name, email, password, role, is_active)
            VALUES (?, ?, ?, ?, ?)
        ');

        try {
            $stmt->execute([
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $data['is_active']
            ]);

            $lastId = $db->getConnection()->lastInsertId();
            echo json_encode([
                'message' => 'User created successfully',
                'data' => [
                    'id' => $lastId,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                    'is_active' => $data['is_active']
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user']);
            error_log("Failed to create user: " . $e->getMessage());
        }
        break;

    case 'PUT':
        // Check for ID
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID is required']);
            exit;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid user ID']);
            exit;
        }

        // Get PUT data
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        // Validate input
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'role' => ['required', 'in:admin,user'],
            'is_active' => ['required', 'in:0,1']
        ];

        if (isset($data['password'])) {
            $rules['password'] = ['required', 'min:6'];
        }

        if (!$validation->validate($data, $rules)) {
            http_response_code(422);
            echo json_encode(['error' => 'Validation failed', 'errors' => $validation->getErrors()]);
            exit;
        }

        // Check if email exists for other users
        $stmt = $db->getConnection()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$data['email'], $id]);
        if ($stmt->fetch()) {
            http_response_code(422);
            echo json_encode(['error' => 'Email already exists']);
            exit;
        }

        // Update user
        $sql = 'UPDATE users SET name = ?, email = ?, role = ?, is_active = ?';
        $params = [$data['name'], $data['email'], $data['role'], $data['is_active']];

        if (isset($data['password'])) {
            $sql .= ', password = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = ?';
        $params[] = $id;

        $stmt = $db->getConnection()->prepare($sql);

        try {
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit;
            }

            echo json_encode([
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                    'is_active' => $data['is_active']
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update user']);
            error_log("Failed to update user: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        // Check for ID
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID is required']);
            exit;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid user ID']);
            exit;
        }

        // Prevent deleting self
        if ($id == $_SESSION['user']['id']) {
            http_response_code(422);
            echo json_encode(['error' => 'Cannot delete your own account']);
            exit;
        }

        // Delete user
        $stmt = $db->getConnection()->prepare('DELETE FROM users WHERE id = ?');

        try {
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit;
            }

            echo json_encode(['message' => 'User deleted successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete user']);
            error_log("Failed to delete user: " . $e->getMessage());
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}