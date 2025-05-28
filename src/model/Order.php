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
        // Tạo kết nối
        $conn = $this->conn;

        // Vòng lặp tạo orderNo không trùng
        do {
            $orderNo = 'ORD-' . strtoupper(bin2hex(random_bytes(4))); // VD: ORD-8FA4C7E1

            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM orders WHERE orderNo = ?");
            $stmtCheck->execute([$orderNo]);
            $exists = $stmtCheck->fetchColumn();

        } while ($exists > 0); // Nếu đã tồn tại thì tạo lại

        // Tạo câu lệnh INSERT
        $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, total_price, payment_method, name, address, phone, status, created_at, updated_at, orderNo)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW(), ?)
    ");

        // Thực thi câu lệnh
        $stmt->execute([
            $userId,
            $totalPrice,
            $paymentMethod,
            $name,
            $address,
            $phone,
            $orderNo
        ]);

        return $conn->lastInsertId(); // Trả lại ID đơn hàng vừa tạo
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

    // Trong class Order (QlyShopTheThao/src/model/Order.php)
    public function updateOrderStatusAndTxn($orderId, $status, $vnpTransactionNo = null, $vnpTxnRefRaw = null) {
        // Giả sử bạn đã thêm các cột vnp_transaction_no và vnp_txn_ref_ipn vào bảng orders
        $sql = "UPDATE " . $this->table . "
            SET status = ?,
                updated_at = NOW()";
        $params = [$status];

        if ($vnpTransactionNo !== null) {
            $sql .= ", vnp_transaction_no = ?"; // Giả sử tên cột là vnp_transaction_no
            $params[] = $vnpTransactionNo;
        }
        if ($vnpTxnRefRaw !== null) {
            $sql .= ", vnp_txn_ref_ipn = ?"; // Giả sử tên cột là vnp_txn_ref_ipn
            $params[] = $vnpTxnRefRaw;
        }
        $sql .= " WHERE id = ?";
        $params[] = $orderId;

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database Error in updateOrderStatusAndTxn: " . $e->getMessage());
            return false;
        }
    }

}
