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
        $stmt = $this->conn->prepare("
        SELECT r.*, u.email 
        FROM $this->table r 
        INNER JOIN username u ON r.user_id = u.id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function addReview($userId, $productId, $rating, $comment) {
        $stmt = $this->conn->prepare("INSERT INTO $this->table (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $productId, $rating, $comment]);
    }

}
