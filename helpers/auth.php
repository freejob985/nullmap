<?php
/**
 * Authentication Helper Functions
 */

class Auth {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

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

            echo "<pre>Debug at line " . __LINE__ . " (one-622): \n";
            print_r($user);
            die();  

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

    public function logout() {
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

    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: /index.php');
            exit;
        }
    }

    public function createUser($data) {
        try {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            return $this->db->insert('users', $data);
        } catch (Exception $e) {
            error_log("User creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function updateUser($id, $data) {
        try {
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            return $this->db->update('users', $data, 'id = ?', [$id]);
        } catch (Exception $e) {
            error_log("User update failed: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($id) {
        try {
            return $this->db->delete('users', 'id = ?', [$id]);
        } catch (Exception $e) {
            error_log("User deletion failed: " . $e->getMessage());
            return false;
        }
    }
} 