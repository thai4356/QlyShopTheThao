<?php
require_once 'Connect.php';

class OrderItem {
    private $conn;
    private $table = "order_item";
    public $id, $order_id, $product_id, $quantity, $unit_price;

    public function __construct()
    {
        $this->conn = (new Connect())->getConnection();
    }

    public function addItem($orderId, $productId, $quantity, $unitPrice) {
        $stmt = $this->conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmt->execute(array($orderId, $productId, $quantity, $unitPrice));
    }

    public function getItemsByOrderId($orderId) {
        $stmt = $this->conn->prepare("
        SELECT DISTINCT oi.*, p.name AS product_name, p.stock
        FROM order_item oi
        JOIN product p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
        $stmt->execute([$orderId]);

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $items;
    }




}
