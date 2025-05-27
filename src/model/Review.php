<?php
require_once 'Connect.php';

class Review {
    private $conn;
    private $table = "review";
    public $id, $user_id, $product_id, $rating, $comment, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }

    public function getByProductId($productId) {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE product_id = ? ORDER BY created_at DESC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addReview($userId, $productId, $rating, $comment) {
        $stmt = $this->conn->prepare("INSERT INTO $this->table (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $productId, $rating, $comment]);
    }

}
