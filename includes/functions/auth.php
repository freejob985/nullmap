<?php
/**
 * Authentication Helper Functions
 * 
 * This file contains functions for user authentication and authorization.
 */

/**
 * Authenticate user
 * 
 * @param string $email User email
 * @param string $password Plain text password
 * @return array|false User data or false if authentication fails
 */
function authenticate($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND is_active = 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            return $user;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Authentication Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Register new user
 * 
 * @param array $userData User data (name, email, password)
 * @return int|false User ID or false if registration fails
 */
function register($userData) {
    global $pdo;
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $userData['email']]);
        if ($stmt->fetch()) {
            return false; // Email already exists
        }
        
        // Hash password
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        unset($userData['password']);
        
        // Set default role if not provided
        if (!isset($userData['role'])) {
            $userData['role'] = 'user';
        }
        
        return insert('users', $userData);
    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user password
 * 
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return bool Success status
 */
function updatePassword($userId, $currentPassword, $newPassword) {
    global $pdo;
    
    try {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return false;
        }
        
        // Update password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return update('users', $userId, ['password_hash' => $newHash]);
    } catch (PDOException $e) {
        error_log("Password Update Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Logout user
 * 
 * @return void
 */
function logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user has required role
 * 
 * @param string|array $requiredRoles Required role(s)
 * @return bool Whether user has required role
 */
function hasRole($requiredRoles) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    if (is_array($requiredRoles)) {
        return in_array($_SESSION['user_role'], $requiredRoles);
    }
    
    return $_SESSION['user_role'] === $requiredRoles;
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return getById('users', $_SESSION['user_id']);
} 