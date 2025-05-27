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

    public function getOrdersByUser($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM  orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

//    public function getItemsByOrderId($orderId) {
//        $stmt = $this->conn->prepare("SELECT *  FROM order_items WHERE order_id = ?");
//        $stmt->execute([$orderId]);
//        return $stmt->fetchAll(PDO::FETCH_ASSOC);
//    }

    public function getOrderById($orderId) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
