<?php
require_once './AdminConnect.php';

class AdminUser {
    private $conn;
    private $table = "username";

    public function __construct() {
        $this->conn = (new AdminConnect())->getConnection();
    }

    public function countAll() {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    public function getAll() {
        $stmt = $this->conn->query("
            SELECT u.id, u.email, r.name AS role, u.is_verified, u.created_at 
            FROM {$this->table} u 
            LEFT JOIN role r ON u.roleid = r.id
            ORDER BY u.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $id = intval($id);
        if ($id <= 0) return false;

        try {
            $this->conn->beginTransaction();

            $this->conn->prepare("DELETE FROM cart_item WHERE cart_id IN (SELECT id FROM cart WHERE user_id = ?)")->execute([$id]);
            $this->conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM order_item WHERE order_id IN (SELECT id FROM orders WHERE user_id = ?)")->execute([$id]);
            $this->conn->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM wishlist WHERE user_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM review WHERE user_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM shipping_address WHERE user_id = ?")->execute([$id]);

            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Xóa user thất bại: " . $e->getMessage());
            return false;
        }
    }
}
