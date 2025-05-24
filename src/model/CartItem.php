<?php
require_once 'Database.php';

class CartItem {
    private $conn;
    private $table = "cart_item";
    public $id, $cart_id, $product_id, $quantity;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
