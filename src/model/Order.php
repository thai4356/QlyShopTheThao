<?php
require_once 'Connect.php';

class Order {
    private $conn;
    private $table = "orders";
    public $id, $user_id, $total_price, $status, $created_at, $updated_at;
    public $payment_method, $name, $address, $phone;


    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }

    public function createOrder($userId, $totalPrice, $paymentMethod, $name, $address, $phone) {
        $stmt = $this->conn->prepare("
        INSERT INTO orders 
        (user_id, total_price, payment_method, name, address, phone, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
    ");
        $stmt->execute(array($userId, $totalPrice, $paymentMethod, $name, $address, $phone));
        return $this->conn->lastInsertId();
    }




}
