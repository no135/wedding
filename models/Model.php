<?php
/**
 * Base Model Class
 */

class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Get all records
     */
    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT * FROM " . $this->table;
        
        if ($limit) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        return $this->db->prepare($query)->execute() ? $this->db->prepare($query)->fetchAll() : [];
    }

    /**
     * Get by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        return $this->db->fetch($query, [$id]);
    }

    /**
     * Get by column
     */
    public function getByColumn($column, $value) {
        $query = "SELECT * FROM " . $this->table . " WHERE " . $column . " = ?";
        return $this->db->fetch($query, [$value]);
    }

    /**
     * Count records
     */
    public function count($where = null, $params = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        
        if ($where) {
            $query .= " WHERE " . $where;
        }

        $result = $this->db->fetch($query, $params);
        return $result['total'];
    }

    /**
     * Insert record
     */
    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO " . $this->table . " (" . $columns . ") VALUES (" . $placeholders . ")";
        
        return $this->db->execute($query, array_values($data));
    }

    /**
     * Update record
     */
    public function update($id, $data) {
        $updates = [];
        $values = [];

        foreach ($data as $key => $value) {
            $updates[] = $key . " = ?";
            $values[] = $value;
        }

        $values[] = $id;
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $updates) . " WHERE id = ?";

        return $this->db->execute($query, $values);
    }

    /**
     * Delete record
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        return $this->db->execute($query, [$id]);
    }

    /**
     * Search
     */
    public function search($search_term, $search_columns) {
        $conditions = [];
        $params = [];

        foreach ($search_columns as $column) {
            $conditions[] = $column . " LIKE ?";
            $params[] = '%' . $search_term . '%';
        }

        $query = "SELECT * FROM " . $this->table . " WHERE " . implode(' OR ', $conditions);
        return $this->db->fetchAll($query, $params);
    }
}
