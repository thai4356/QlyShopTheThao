<?php
require_once 'Connect.php';

class Category {
    private $conn;
    private $table = "category";
    public $id, $name, $description;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT id, name FROM category");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
