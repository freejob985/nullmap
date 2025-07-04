<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection settings for the NullMap application.
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'nullmap');
define('DB_USER', 'root');  // Change in production
define('DB_PASS', '');      // Change in production
define('DB_CHARSET', 'utf8mb4');

// Database connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);

return [
    'host' => DB_HOST,
    'dbname' => DB_NAME,
    'username' => DB_USER,
    'password' => DB_PASS,
    'charset' => DB_CHARSET,
    'options' => DB_OPTIONS
]; 