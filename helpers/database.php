<?php
/**
 * Database Helper Class
 * 
 * This class provides a singleton pattern for database connections
 * and common database operations using PDO.
 */

class Database {
    /**
     * @var PDO The database connection
     */
    private static $connection = null;

    /**
     * @var Database The singleton instance
     */
    private static $instance = null;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';

        try {
            // Build DSN
            $dsn = sprintf(
                '%s:host=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['database'],
                $config['charset']
            );

            // Create PDO instance
            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            // Log error and throw exception
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new Exception('خطأ في الاتصال بقاعدة البيانات');
        }
    }

    /**
     * Get singleton instance
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get database connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return self::$connection;
    }

    /**
     * Execute a query and return all results
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Query results
     */
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = self::$connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Database Query Error: ' . $e->getMessage());
            throw new Exception('خطأ في تنفيذ الاستعلام');
        }
    }

    /**
     * Execute a query and return one result
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false Query result or false if not found
     */
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = self::$connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Database Query Error: ' . $e->getMessage());
            throw new Exception('خطأ في تنفيذ الاستعلام');
        }
    }

    /**
     * Execute a query and return the last insert ID
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return int Last insert ID
     */
    public function insert($sql, $params = []) {
        try {
            $stmt = self::$connection->prepare($sql);
            $stmt->execute($params);
            return self::$connection->lastInsertId();
        } catch (PDOException $e) {
            error_log('Database Insert Error: ' . $e->getMessage());
            throw new Exception('خطأ في إضافة البيانات');
        }
    }

    /**
     * Execute a query and return affected rows
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return int Number of affected rows
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = self::$connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Database Execute Error: ' . $e->getMessage());
            throw new Exception('خطأ في تنفيذ العملية');
        }
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        self::$connection->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        self::$connection->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback() {
        self::$connection->rollBack();
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {

    }
} 