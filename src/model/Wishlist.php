<?php
require_once 'connect.php';

class Wishlist {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Kiểm tra sản phẩm đã tồn tại trong wishlist chưa
    public function exists($user_id, $product_id) {
        $stmt = $this->conn->prepare("SELECT * FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([
            ':user_id' => $user_id,
            ':product_id' => $product_id
        ]);
        return $stmt->rowCount() > 0;
    }

    // Thêm sản phẩm vào wishlist
    public function add($user_id, $product_id) {
        $stmt = $this->conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (:user_id, :product_id)");
        return $stmt->execute([
            ':user_id' => $user_id,
            ':product_id' => $product_id
        ]);
    }

    // Lấy danh sách wishlist của người dùng
    public function getWishlistByUser($user_id) {
        $stmt = $this->conn->prepare("
        SELECT 
            p.*, 
            MIN(pi.image_url) AS image_url
        FROM wishlist w
        JOIN product p ON w.product_id = p.id
        LEFT JOIN product_image pi ON pi.product_id = p.id
        WHERE w.user_id = :user_id
        GROUP BY p.id
    ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // Xóa sản phẩm khỏi wishlist
    public function remove($user_id, $product_id) {
        $stmt = $this->conn->prepare("DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
        return $stmt->execute([
            ':user_id' => $user_id,
            ':product_id' => $product_id
        ]);
    }
}
