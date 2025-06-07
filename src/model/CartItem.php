<?php
require_once 'Connect.php';

class CartItem {
    private $conn;

    public $id, $cart_id, $product_id, $quantity;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }

    // Thêm sản phẩm vào cart (nếu có thì tăng số lượng)
    public function addItem($cartId, $productId, $quantity = 1) {
        $check = $this->conn->prepare("SELECT * FROM cart_item WHERE cart_id = ? AND product_id = ?");
        $check->execute([$cartId, $productId]);
        $exists = $check->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $update = $this->conn->prepare("UPDATE cart_item SET quantity = quantity + ? WHERE cart_id = ? AND product_id = ?");
            return $update->execute([$quantity, $cartId, $productId]);
        } else {
            $insert = $this->conn->prepare("INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
            return $insert->execute([$cartId, $productId, $quantity]);
        }
    }

    // Lấy tất cả item trong cart kèm thông tin sản phẩm
    public function getItems($cartId) {
        $sql = "
            SELECT 
            ci.*, 
            p.name, 
            p.price,
            p.stock,
            pi.image_url
        FROM cart_item ci
        INNER JOIN product p ON ci.product_id = p.id
        INNER JOIN (
            SELECT product_id, MIN(image_url) AS image_url
            FROM product_image
            WHERE is_thumbnail = 1
            GROUP BY product_id
        ) pi ON p.id = pi.product_id
        WHERE ci.cart_id = ?
        
        ";


        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$cartId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Xóa 1 sản phẩm trong giỏ
    public function removeItem($cartId, $productId) {
        $stmt = $this->conn->prepare("DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?");
        return $stmt->execute([$cartId, $productId]);
    }

    // Cập nhật số lượng
    public function updateQuantity($cartId, $productId, $quantity) {
        $stmt = $this->conn->prepare("UPDATE cart_item SET quantity = ? WHERE cart_id = ? AND product_id = ?");
        return $stmt->execute([$quantity, $cartId, $productId]);
    }
}

