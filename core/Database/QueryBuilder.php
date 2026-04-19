<?php
namespace Core\Database;

use PDO;

class QueryBuilder {
    protected $db;
    protected $table;

    public function __construct(PDO $db) { $this->db = $db; }

    public function table(string $table) {
        $this->table = $table;
        return $this;
    }

    public function insert(array $data) {
        $keys = implode(', ', array_keys($data));
        $params = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ($keys) VALUES ($params)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public function find(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}