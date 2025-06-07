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
        $query = "SELECT 
                r.id, r.user_id, r.product_id, r.rating, r.comment, r.created_at,
                r.admin_reply, r.replied_at, -- Lấy thêm dữ liệu trả lời của admin
                u.email 
              FROM $this->table r 
              INNER JOIN username u ON r.user_id = u.id 
              WHERE r.product_id = ? 
              AND r.status = 'approved'
              ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function addReview($userId, $productId, $rating, $comment) {
        $stmt = $this->conn->prepare("INSERT INTO $this->table (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $productId, $rating, $comment]);
    }

}
