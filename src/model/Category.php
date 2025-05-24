<?php
require_once 'Database.php';

class Category {
    private $conn;
    private $table = "category";
    public $id, $name, $description;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
