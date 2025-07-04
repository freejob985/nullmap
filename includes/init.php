<?php
/**
 * Application Initialization
 * 
 * This file initializes the application by:
 * 1. Starting the session
 * 2. Setting up error reporting
 * 3. Loading configuration
 * 4. Establishing database connection
 * 5. Including helper functions
 */

// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error_log.txt');

// Load configuration
$config = require_once __DIR__ . '/../config/database.php';

// Database connection
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Could not connect to the database. Please check your configuration.");
}

// Include helper functions
require_once __DIR__ . '/functions/database.php';
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/functions/validation.php';

// Set default timezone
date_default_timezone_set('Asia/Riyadh');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Redirect if not logged in
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit;
} 