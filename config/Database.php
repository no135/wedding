<?php
/**
 * Database Connection Class
 */

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $user = DB_USER;
    private $password = DB_PASS;
    private $port = DB_PORT;
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db_name;
            
            $this->conn = new PDO($dsn, $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            return $this->conn;
        } catch (PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }
    }

    public function getConnection() {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

    // Prepared statement helper
    public function prepare($sql) {
        return $this->getConnection()->prepare($sql);
    }

    // Execute query
    public function execute($sql, $params = []) {
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }

    // Fetch all results
    public function fetchAll($sql, $params = []) {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Fetch single result
    public function fetch($sql, $params = []) {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Get last insert ID
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }

    // Begin transaction
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    // Commit transaction
    public function commit() {
        return $this->getConnection()->commit();
    }

    // Rollback transaction
    public function rollback() {
        return $this->getConnection()->rollback();
    }
}
