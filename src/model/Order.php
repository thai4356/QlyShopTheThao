<?php
require_once 'Database.php';

class Order {
    private $conn;
    private $table = "orders";
    public $id, $user_id, $total_price, $status, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
