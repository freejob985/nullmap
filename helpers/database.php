<?php
/**
 * Database Helper Functions
 */

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $config = require_once __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }

    public function insert($table, $data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_values($data));
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            throw new Exception("Database insert failed");
        }
    }

    public function update($table, $data, $where, $whereParams = []) {
        $fields = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$fields} WHERE {$where}";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_merge(array_values($data), $whereParams));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Update failed: " . $e->getMessage());
            throw new Exception("Database update failed");
        }
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Delete failed: " . $e->getMessage());
            throw new Exception("Database delete failed");
        }
    }

    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("FetchAll failed: " . $e->getMessage());
            throw new Exception("Database fetch failed");
        }
    }

    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("FetchOne failed: " . $e->getMessage());
            throw new Exception("Database fetch failed");
        }
    }
} 