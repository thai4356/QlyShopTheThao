<?php
require_once 'Database.php';

class OrderItem {
    private $conn;
    private $table = "order_item";
    public $id, $order_id, $product_id, $quantity, $unit_price;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
