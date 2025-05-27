<?php
require_once 'Connect.php';

class Cart {
    private $conn;
    private $table = "cart";
    public $id, $user_id, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }

    // Lấy cart theo user_id
    public function getCartByUserId($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo giỏ hàng mới nếu chưa có
    public function createCart($userId) {
        $stmt = $this->conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        return $this->conn->lastInsertId();
    }
}

