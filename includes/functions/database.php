<?php
/**
 * Database Helper Functions
 * 
 * This file contains functions for common database operations.
 */

/**
 * Get all records from a table
 * 
 * @param string $table Table name
 * @param array $conditions Optional WHERE conditions
 * @return array Array of records
 */
function getAll($table, $conditions = []) {
    global $pdo;
    
    $sql = "SELECT * FROM {$table}";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', array_map(fn($key) => "$key = :$key", array_keys($conditions)));
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database Error in getAll: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single record by ID
 * 
 * @param string $table Table name
 * @param int $id Record ID
 * @return array|null Record data or null if not found
 */
function getById($table, $id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log("Database Error in getById: " . $e->getMessage());
        return null;
    }
}

/**
 * Insert a new record
 * 
 * @param string $table Table name
 * @param array $data Record data
 * @return int|false The last insert ID or false on failure
 */
function insert($table, $data) {
    global $pdo;
    
    $fields = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_map(fn($field) => ":$field", array_keys($data)));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");
        $stmt->execute($data);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Database Error in insert: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a record
 * 
 * @param string $table Table name
 * @param int $id Record ID
 * @param array $data Update data
 * @return bool Success status
 */
function update($table, $id, $data) {
    global $pdo;
    
    $set = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($data)));
    
    try {
        $stmt = $pdo->prepare("UPDATE {$table} SET {$set} WHERE id = :id");
        return $stmt->execute(array_merge($data, ['id' => $id]));
    } catch (PDOException $e) {
        error_log("Database Error in update: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a record
 * 
 * @param string $table Table name
 * @param int $id Record ID
 * @return bool Success status
 */
function delete($table, $id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        error_log("Database Error in delete: " . $e->getMessage());
        return false;
    }
}

/**
 * Search records
 * 
 * @param string $table Table name
 * @param array $fields Fields to search in
 * @param string $query Search query
 * @return array Search results
 */
function search($table, $fields, $query) {
    global $pdo;
    
    $conditions = implode(' OR ', array_map(fn($field) => "$field LIKE :query", $fields));
    $sql = "SELECT * FROM {$table} WHERE {$conditions}";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['query' => "%{$query}%"]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database Error in search: " . $e->getMessage());
        return [];
    }
} 