<?php
// File: src/model/admin/AdminOrderItem.php

require_once __DIR__ . '/AdminConnect.php';

class AdminOrderItem {
    private $conn;
    private $table = "order_item";

    public function __construct() {
        $this->conn = (new AdminConnect())->getConnection();
    }

    /**
     * Lấy danh sách các sản phẩm (product_id và quantity) của một đơn hàng.
     * Dùng cho việc hoàn kho.
     * @param int $orderId ID của đơn hàng.
     * @return array Danh sách các item.
     */
    public function getItemsByOrderId($orderId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT product_id, quantity 
                FROM " . $this->table . " 
                WHERE order_id = ?
            ");
            $stmt->execute([$orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error in AdminOrderItem->getItemsByOrderId: " . $e->getMessage());
            return [];
        }
    }
}