<?php
require_once 'Database.php';

class ProductCategory {
    private $conn;
    private $table = "product_category";
    public $product_id, $category_id;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
