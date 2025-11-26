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
        $conn = $this->conn;
        do {
            $orderNo = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM orders WHERE orderNo = ?");
            $stmtCheck->execute([$orderNo]);
            $exists = $stmtCheck->fetchColumn();
        } while ($exists > 0);

        $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, total_price, payment_method, name, address, phone, status, created_at, updated_at, orderNo)
        VALUES (?, ?, ?, ?, ?, ?, 'đang xử lý', NOW(), NOW(), ?)
        ");

        $stmt->execute([
            $userId,
            $totalPrice,
            $paymentMethod,
            $name,
            $address,
            $phone,
            $orderNo
        ]);

        return $conn->lastInsertId();
    }

    public function getOrdersByUser($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM  orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderById($orderId) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Đã chỉnh sửa: Chỉ cập nhật trạng thái đơn hàng (Dùng chung)
    public function updateOrderStatusAndTxn($orderId, $status) {
        $sql = "UPDATE " . $this->table . " SET status = ?, updated_at = NOW() WHERE id = ?";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$status, $orderId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database Error in updateOrderStatusAndTxn: " . $e->getMessage());
            return false;
        }
    }

    public function updateOrderPayOSInfo($orderId, $payosPaymentLinkId, $status = null, $payosReference = null, $payosTransactionDatetime = null) {
        $fieldsToUpdate = [];
        $params = [];

        if ($status !== null) {
            $fieldsToUpdate[] = "status = ?";
            $params[] = $status;
        }
        if ($payosPaymentLinkId !== null) {
            $fieldsToUpdate[] = "payos_payment_link_id = ?";
            $params[] = $payosPaymentLinkId;
        }
        if ($payosReference !== null) {
            $fieldsToUpdate[] = "payos_reference = ?";
            $params[] = $payosReference;
        }
        if ($payosTransactionDatetime !== null) {
            $fieldsToUpdate[] = "payos_transaction_datetime = ?";
            $params[] = $payosTransactionDatetime;
        }

        if (empty($fieldsToUpdate)) {
            return false;
        }

        $fieldsToUpdate[] = "updated_at = NOW()";

        $sql = "UPDATE " . $this->table . " SET " . implode(", ", $fieldsToUpdate) . " WHERE id = ?";
        $params[] = $orderId;

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database Error in updateOrderPayOSInfo: " . $e->getMessage());
            return false;
        }
    }

    public function updateOrderStripeInfo($orderId, $stripeSessionId, $status = null) {
        $fieldsToUpdate = [];
        $params = [];

        if ($status !== null) {
            $fieldsToUpdate[] = "status = ?";
            $params[] = $status;
        }
        if ($stripeSessionId !== null) {
            $fieldsToUpdate[] = "stripe_session_id = ?";
            $params[] = $stripeSessionId;
        }

        if (empty($fieldsToUpdate)) {
            return false;
        }

        $fieldsToUpdate[] = "updated_at = NOW()";

        $sql = "UPDATE " . $this->table . " SET " . implode(", ", $fieldsToUpdate) . " WHERE id = ?";
        $params[] = $orderId;

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database Error in updateOrderStripeInfo: " . $e->getMessage());
            return false;
        }
    }

}