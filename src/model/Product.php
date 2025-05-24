<?php
require_once 'Database.php';

class Product {
    private $conn;
    private $table = "product";

    public $id, $name, $description, $price, $stock, $image_url, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
