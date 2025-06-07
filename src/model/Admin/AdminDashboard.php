<?php
require_once __DIR__ . '/AdminConnect.php';

class AdminDashboard {
    private $conn;

    public function __construct() {
        $db = new AdminConnect();
        $this->conn = $db->getConnection();
    }

    // Đếm tổng số người dùng
    public function getTotalUsers() {
        $query = "SELECT COUNT(id) as total FROM username";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Tính tổng doanh thu trong tháng hiện tại (chỉ tính đơn hàng đã giao hoặc đã thanh toán)
    public function getTotalRevenueThisMonth() {
        $query = "SELECT SUM(total_price) as total 
                  FROM orders 
                  WHERE (status = 'đã giao' OR status = 'đã thanh toán') 
                  AND MONTH(created_at) = MONTH(NOW()) 
                  AND YEAR(created_at) = YEAR(NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // Trả về 0 nếu không có doanh thu
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    // Đếm tổng số đơn hàng mới trong tháng hiện tại
    public function getNewOrdersThisMonth() {
        $query = "SELECT COUNT(id) as total 
                  FROM orders 
                  WHERE MONTH(created_at) = MONTH(NOW()) 
                  AND YEAR(created_at) = YEAR(NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Đếm số lượng đánh giá đang chờ duyệt
    public function getPendingReviewsCount() {
        $query = "SELECT COUNT(id) as total FROM review WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // HÀM MỚI: Lấy dữ liệu doanh thu cho biểu đồ (7 ngày gần nhất)
    public function getRevenueDataForChart() {
        // Lấy doanh thu thực tế từ CSDL
        $query = "SELECT DATE(created_at) as sale_date, SUM(total_price) as daily_revenue 
                  FROM orders 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                    AND (status = 'đã giao' OR status = 'đã thanh toán') 
                  GROUP BY DATE(created_at) 
                  ORDER BY sale_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn bị dữ liệu cho 7 ngày, kể cả ngày không có doanh thu
        $salesData = [];
        // Chuyển kết quả từ CSDL vào một mảng dễ tra cứu
        foreach ($results as $row) {
            $salesData[$row['sale_date']] = (int)$row['daily_revenue'];
        }

        $labels = [];
        $data = [];
        // Lặp qua 7 ngày từ quá khứ đến hiện tại
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d/m', strtotime($date)); // Nhãn là ngày/tháng
            $data[] = $salesData[$date] ?? 0; // Lấy doanh thu của ngày, nếu không có thì là 0
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Lấy dữ liệu doanh thu linh hoạt theo khoảng thời gian và cách nhóm.
     * @param string $startDate Ngày bắt đầu (Y-m-d H:i:s)
     * @param string $endDate Ngày kết thúc (Y-m-d H:i:s)
     * @param string $groupBy 'day' hoặc 'month'
     * @return array Mảng chứa 'labels' và 'data'
     */
    public function getRevenueDataByRange($startDate, $endDate, $groupBy = 'day') {
        $mysqlDateFormat = ($groupBy === 'month') ? '%Y-%m' : '%Y-%m-%d';
        $phpLabelFormat = ($groupBy === 'month') ? 'm/Y' : 'd/m';

        $query = "SELECT DATE_FORMAT(created_at, :mysqlDateFormat) as sale_period, SUM(total_price) as period_revenue 
              FROM orders 
              WHERE created_at BETWEEN :startDate AND :endDate
                AND (status = 'đã giao' OR status = 'đã thanh toán') 
              GROUP BY sale_period 
              ORDER BY sale_period ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mysqlDateFormat', $mysqlDateFormat);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn bị dữ liệu
        $salesData = [];
        foreach ($results as $row) {
            $salesData[$row['sale_period']] = (int)$row['period_revenue'];
        }

        $labels = [];
        $data = [];
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);

        while ($current <= $end) {
            $periodKey = $current->format(($groupBy === 'month') ? 'Y-m' : 'Y-m-d');
            $labels[] = $current->format($phpLabelFormat);
            $data[] = $salesData[$periodKey] ?? 0;

            if ($groupBy === 'month') {
                $current->modify('first day of next month');
            } else {
                $current->modify('+1 day');
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    // HÀM MỚI: Lấy số lượng sản phẩm theo từng danh mục
    public function getProductCountByCategory() {
        $query = "SELECT c.name as category_name, COUNT(p.id) as product_count 
              FROM category c 
              LEFT JOIN product p ON c.id = p.category_id 
              WHERE c.is_active = 1
              GROUP BY c.id, c.name 
              ORDER BY product_count DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn bị dữ liệu cho Chart.js
        $labels = [];
        $data = [];
        foreach ($results as $row) {
            $labels[] = $row['category_name'];
            $data[] = (int)$row['product_count'];
        }

        return ['labels' => $labels, 'data' => $data];
    }

    // HÀM MỚI: Lấy 5 đơn hàng gần đây nhất
    public function getRecentOrders($limit = 5) {
        $query = "SELECT id, name, total_price, status, created_at 
              FROM orders 
              ORDER BY created_at DESC 
              LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // HÀM MỚI: Lấy 5 người dùng đăng ký gần đây nhất
    public function getRecentUsers($limit = 5) {
        $query = "SELECT id, email, created_at 
              FROM username 
              ORDER BY created_at DESC 
              LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // HÀM MỚI: Lấy các sản phẩm sắp hết hàng (tồn kho < 10)
    public function getLowStockProducts($limit = 5, $threshold = 10) {
        // Sửa lại câu lệnh SQL để JOIN với bảng product_image
        // và chỉ lấy ảnh là thumbnail (is_thumbnail = 1)
        $query = "SELECT p.id, p.name, p.stock, pi.image_url
              FROM product p
              LEFT JOIN product_image pi ON p.id = pi.product_id AND pi.is_thumbnail = 1
              WHERE p.stock < :threshold AND p.is_active = 1
              ORDER BY p.stock ASC 
              LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>