<?php
// src/model/admin/AdminOrder.php

require_once __DIR__ . '/AdminConnect.php';

class AdminOrder
{
    private $pdo;

    public function __construct()
    {
        $connectInstance = new AdminConnect();
        $this->pdo = $connectInstance->getConnection();
    }

    /**
     * Lấy tất cả đơn hàng cùng với thông tin email của người dùng.
     * Sắp xếp theo ngày tạo mới nhất.
     * @return array
     */
    public function getAllOrdersWithUserDetails()
    {
        try {
            // Câu lệnh SQL JOIN bảng 'orders' với bảng 'username' để lấy email
            $sql = "SELECT 
                        o.id, 
                        o.name AS customer_name, 
                        u.email AS customer_email, 
                        o.total_price, 
                        o.status, 
                        o.payment_method, 
                        o.created_at
                    FROM orders AS o
                    LEFT JOIN username AS u ON o.user_id = u.id
                    ORDER BY o.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            // Lấy tất cả kết quả dưới dạng mảng kết hợp
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $orders;

        } catch (PDOException $e) {
            // Xử lý lỗi nếu có, có thể ghi log
            error_log("Database Error: " . $e->getMessage());
            return []; // Trả về mảng rỗng nếu có lỗi
        }
    }

    /**
     * Lấy thông tin chi tiết của một đơn hàng bằng ID.
     * @param int $orderId
     * @return array|false
     */
    public function getOrderById($orderId)
    {
        try {
            $sql = "SELECT 
                    o.*, 
                    u.email AS customer_email
                FROM orders AS o
                LEFT JOIN username AS u ON o.user_id = u.id
                WHERE o.id = :orderId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách các sản phẩm trong đơn hàng, kèm theo hình ảnh đại diện.
     * @param int $orderId
     * @return array
     */
    public function getOrderItemsWithImages($orderId)
    {
        try {
            // Query này join 4 bảng: order_item -> product -> product_image
            $sql = "SELECT 
                    oi.quantity, 
                    oi.unit_price, 
                    p.name AS product_name,
                    p.id as product_id,
                    pi.image_url
                FROM order_item AS oi
                JOIN product AS p ON oi.product_id = p.id
                LEFT JOIN product_image AS pi ON p.id = pi.product_id AND pi.is_thumbnail = 1
                WHERE oi.order_id = :orderId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cập nhật trạng thái của một đơn hàng.
     * @param int $orderId ID của đơn hàng.
     * @param string $newStatus Trạng thái mới.
     * @return bool True nếu cập nhật thành công, False nếu thất bại.
     */
    public function updateStatus($orderId, $newStatus)
    {
        // Các trạng thái hợp lệ để tránh lỗi SQL Injection hoặc dữ liệu không mong muốn
        $allowedStatuses = ["đã giao", "hủy", "đã hoàn tiền"];
        if (!in_array($newStatus, $allowedStatuses)) {
            return false;
        }

        try {
            $sql = "UPDATE orders SET status = :newStatus WHERE id = :orderId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':newStatus', $newStatus, PDO::PARAM_STR);
            $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error updating order status: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Lấy danh sách các email khách hàng duy nhất đã từng đặt hàng.
     * @return array Danh sách các email.
     */
    public function getUniqueCustomerEmails()
    {
        try {
            // JOIN 2 bảng và dùng DISTINCT để lấy email duy nhất
            $sql = "SELECT DISTINCT u.email 
                FROM orders AS o
                JOIN username AS u ON o.user_id = u.id
                WHERE u.email IS NOT NULL AND u.email <> ''
                ORDER BY u.email ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            // Trả về một mảng chỉ chứa các giá trị email
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        } catch (PDOException $e) {
            error_log("Database Error getting unique customer emails: " . $e->getMessage());
            return [];
        }
    }



}