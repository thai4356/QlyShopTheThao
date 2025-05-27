<?php
require_once 'Connect.php';

class ProductCategory {
    private $conn;
    private $table = "product_category";
    public $product_id, $category_id;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }
}
