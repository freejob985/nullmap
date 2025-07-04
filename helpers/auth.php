<?php
/**
 * Authentication Helper Functions
 * 
 * This class provides authentication and authorization functionality including:
 * - User login and logout
 * - Session management
 * - Role-based access control
 * - Permission-based access control
 * - User management (CRUD operations)
 */

class Auth {
    private static $instance = null;
    private $db;
    private $userPermissions = [];

    private function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Load user permissions if logged in
        if ($this->isLoggedIn()) {
            $this->loadUserPermissions();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Authenticate a user with email and password
     * 
     * @param string $email User email
     * @param string $password User password (plain text)
     * @return bool True if login successful, false otherwise
     */
    public function login($email, $password) {
        try {
            // Log login attempt
            error_log("Login attempt for email: " . $email);

            // Validate input
            if (empty($email) || empty($password)) {
                error_log("Login failed: Empty email or password");
                return false;
            }

            // Get user from database
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE email = ?",
                [$email]
            );

            // Log user query result
            error_log("User query result: " . ($user ? "User found" : "User not found"));

            // Check if user exists
            if (!$user) {
                error_log("Login failed: User not found");
                return false;
            }

            // Check if user is active
            if (!$user['is_active']) {
                error_log("Login failed: User is not active");
                return false;
            }

            // Verify password
            $passwordValid = password_verify($password, $user['password']);
            error_log("Password verification result: " . ($passwordValid ? "Valid" : "Invalid"));

            if ($passwordValid) {
                // Set session data
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                
                // Load user permissions
                $this->loadUserPermissions();
                
                error_log("Login successful for user: " . $user['email']);
                return true;
            }

            error_log("Login failed: Invalid password");
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log out the current user
     * 
     * @return bool True if logout successful
     */
    public function logout() {
        // Clear user permissions cache
        $this->userPermissions = [];
        
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    public function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return $_SESSION['user']['role'] === $role;
    }

    public function isAdmin() {
        return $this->hasRole('admin');
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }

    /**
     * Require admin role to access a page
     * Redirects to index if not admin
     */
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: /index.php');
            exit;
        }
    }
    
    /**
     * Check if user has a specific permission
     * 
     * @param string $permissionName The permission name to check
     * @return bool True if user has permission, false otherwise
     */
    public function hasPermission($permissionName) {
        // Admins have all permissions
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if permission exists in user's permissions
        return in_array($permissionName, $this->userPermissions);
    }
    
    /**
     * Require a specific permission to access a page
     * Redirects to index if permission not granted
     * 
     * @param string $permissionName The required permission
     */
    public function requirePermission($permissionName) {
        if (!$this->hasPermission($permissionName)) {
            header('Location: /index.php');
            exit;
        }
    }
    
    /**
     * Load user permissions from database
     */
    private function loadUserPermissions() {
        if (!$this->isLoggedIn()) {
            return;
        }
        
        $userId = $_SESSION['user']['id'];
        
        try {
            // Get user permissions from database
            $permissions = $this->db->fetchAll(
                "SELECT p.name 
                 FROM permissions p 
                 JOIN user_permissions up ON p.id = up.permission_id 
                 WHERE up.user_id = ?",
                [$userId]
            );
            
            // Extract permission names
            $this->userPermissions = array_column($permissions, 'name');
        } catch (Exception $e) {
            error_log("Error loading user permissions: " . $e->getMessage());
            $this->userPermissions = [];
        }
    }

    /**
     * Create a new user
     * 
     * @param array $data User data (name, email, password, role, is_active)
     * @param array $permissions Array of permission names to assign to user
     * @return int|bool User ID if successful, false otherwise
     */
    public function createUser($data, $permissions = []) {
        try {
            $this->db->beginTransaction();
            
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $userId = $this->db->insert(
                'INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)',
                [
                    $data['name'],
                    $data['email'],
                    $data['password'],
                    $data['role'] ?? 'user',
                    $data['is_active'] ?? 1
                ]
            );
            
            // Assign permissions if provided
            if (!empty($permissions)) {
                $this->assignPermissionsToUser($userId, $permissions);
            }
            
            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("User creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing user
     * 
     * @param int $id User ID
     * @param array $data User data to update
     * @param array $permissions Array of permission names to assign to user (optional)
     * @return bool True if successful, false otherwise
     */
    public function updateUser($id, $data, $permissions = null) {
        try {
            $this->db->beginTransaction();
            
            // Build update query parts
            $updateFields = [];
            $updateParams = [];
            
            // Handle name
            if (isset($data['name'])) {
                $updateFields[] = 'name = ?';
                $updateParams[] = $data['name'];
            }
            
            // Handle email
            if (isset($data['email'])) {
                $updateFields[] = 'email = ?';
                $updateParams[] = $data['email'];
            }
            
            // Handle password
            if (isset($data['password']) && !empty($data['password'])) {
                $updateFields[] = 'password = ?';
                $updateParams[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            // Handle role
            if (isset($data['role'])) {
                $updateFields[] = 'role = ?';
                $updateParams[] = $data['role'];
            }
            
            // Handle active status
            if (isset($data['is_active'])) {
                $updateFields[] = 'is_active = ?';
                $updateParams[] = $data['is_active'];
            }
            
            // Add user ID to params
            $updateParams[] = $id;
            
            // Execute update if there are fields to update
            if (!empty($updateFields)) {
                $sql = 'UPDATE users SET ' . implode(', ', $updateFields) . ' WHERE id = ?';
                $this->db->execute($sql, $updateParams);
            }
            
            // Update permissions if provided
            if ($permissions !== null) {
                // First remove all existing permissions
                $this->db->execute('DELETE FROM user_permissions WHERE user_id = ?', [$id]);
                
                // Then assign new permissions
                if (!empty($permissions)) {
                    $this->assignPermissionsToUser($id, $permissions);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("User update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a user
     * 
     * @param int $id User ID
     * @return bool True if successful, false otherwise
     */
    public function deleteUser($id) {
        try {
            $this->db->beginTransaction();
            
            // Delete user permissions first (should cascade, but just to be safe)
            $this->db->execute('DELETE FROM user_permissions WHERE user_id = ?', [$id]);
            
            // Delete user
            $result = $this->db->execute('DELETE FROM users WHERE id = ?', [$id]);
            
            $this->db->commit();
            return $result > 0;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("User deletion failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all available permissions
     * 
     * @return array List of permissions
     */
    public function getAllPermissions() {
        try {
            return $this->db->fetchAll('SELECT * FROM permissions ORDER BY name');
        } catch (Exception $e) {
            error_log("Error fetching permissions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get permissions for a specific user
     * 
     * @param int $userId User ID
     * @return array List of permission IDs
     */
    public function getUserPermissions($userId) {
        try {
            $permissions = $this->db->fetchAll(
                'SELECT permission_id FROM user_permissions WHERE user_id = ?',
                [$userId]
            );
            return array_column($permissions, 'permission_id');
        } catch (Exception $e) {
            error_log("Error fetching user permissions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Assign permissions to a user
     * 
     * @param int $userId User ID
     * @param array $permissionNames Array of permission names
     * @return bool True if successful, false otherwise
     */
    public function assignPermissionsToUser($userId, $permissionNames) {
        try {
            // Get permission IDs from names
            $placeholders = implode(',', array_fill(0, count($permissionNames), '?'));
            $permissions = $this->db->fetchAll(
                "SELECT id FROM permissions WHERE name IN ($placeholders)",
                $permissionNames
            );
            
            $permissionIds = array_column($permissions, 'id');
            
            // Insert permissions for user
            foreach ($permissionIds as $permissionId) {
                $this->db->execute(
                    'INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES (?, ?)',
                    [$userId, $permissionId]
                );
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error assigning permissions: " . $e->getMessage());
            return false;
        }
    }
}