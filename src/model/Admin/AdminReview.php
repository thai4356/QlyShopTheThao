<?php
require_once __DIR__ . '/AdminConnect.php';

class AdminReview {
    private $conn;

    public function __construct() {
        $db = new AdminConnect();
        $this->conn = $db->getConnection();
    }

    // HÀM MỚI: Lấy tất cả sản phẩm có đánh giá
    public function getProductsWithReviews() {
        $query = "SELECT DISTINCT p.id, p.name 
                  FROM product p 
                  JOIN review r ON p.id = r.product_id 
                  ORDER BY p.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // HÀM MỚI: Lấy tất cả người dùng đã gửi đánh giá
    public function getUsersWithReviews() {
        $query = "SELECT DISTINCT u.id, u.email 
                  FROM username u 
                  JOIN review r ON u.id = r.user_id 
                  ORDER BY u.email ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy dữ liệu đánh giá cho DataTables với phân trang, tìm kiếm, sắp xếp.
     */
    public function getReviewsForDataTable($start, $length, $searchValue, $orderColumn, $orderDir, $filterProductId, $filterUserId, $filterRating, $filterStatus) {
        $columns = [
            'r.id',
            'p.name',
            'u.email',
            'r.rating',
            'r.comment',
            'r.status',
            'r.created_at'
        ];

        // Bắt đầu câu truy vấn
        $query = "SELECT r.id, p.name as product_name, u.email as user_email, r.rating, r.comment, r.status, r.created_at
                  FROM review r
                  JOIN product p ON r.product_id = p.id
                  JOIN username u ON r.user_id = u.id
                  WHERE 1=1"; // Thêm WHERE 1=1 để dễ dàng nối các điều kiện AND

        $countQuery = "SELECT COUNT(r.id) as total
                       FROM review r
                       JOIN product p ON r.product_id = p.id
                       JOIN username u ON r.user_id = u.id
                       WHERE 1=1";

        // Thêm điều kiện lọc
        $params = [];
        if (!empty($filterProductId)) {
            $query .= " AND r.product_id = :productId";
            $countQuery .= " AND r.product_id = :productId";
            $params[':productId'] = $filterProductId;
        }
        if (!empty($filterUserId)) {
            $query .= " AND r.user_id = :userId";
            $countQuery .= " AND r.user_id = :userId";
            $params[':userId'] = $filterUserId;
        }
        if (!empty($filterRating)) {
            $query .= " AND r.rating = :rating";
            $countQuery .= " AND r.rating = :rating";
            $params[':rating'] = $filterRating;
        }
        if (!empty($filterStatus)) {
            $query .= " AND r.status = :status";
            $countQuery .= " AND r.status = :status";
            $params[':status'] = $filterStatus;
        }

        // Xử lý tìm kiếm chung
        if (!empty($searchValue)) {
            $query .= " AND (p.name LIKE :searchValue OR u.email LIKE :searchValue OR r.comment LIKE :searchValue)";
            $countQuery .= " AND (p.name LIKE :searchValue OR u.email LIKE :searchValue OR r.comment LIKE :searchValue)";
            $params[':searchValue'] = '%' . $searchValue . '%';
        }

        // Lấy tổng số bản ghi (sau khi lọc)
        $stmtFiltered = $this->conn->prepare($countQuery);
        $stmtFiltered->execute($params);
        $totalFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

        // Lấy tổng số bản ghi (trước khi lọc)
        $totalRecordsQuery = "SELECT COUNT(*) as total FROM review";
        $stmtTotal = $this->conn->prepare($totalRecordsQuery);
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        // Sắp xếp và phân trang
        $query .= " ORDER BY " . $columns[$orderColumn] . " " . $orderDir . " LIMIT :start, :length";

        $stmt = $this->conn->prepare($query);

        // Gán các tham số
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);

        if (!empty($filterProductId)) $stmt->bindValue(':productId', $filterProductId);
        if (!empty($filterUserId)) $stmt->bindValue(':userId', $filterUserId);
        if (!empty($filterRating)) $stmt->bindValue(':rating', $filterRating);
        if (!empty($filterStatus)) $stmt->bindValue(':status', $filterStatus);
        if (!empty($searchValue)) $stmt->bindValue(':searchValue', '%' . $searchValue . '%');

        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "recordsTotal" => (int)$totalRecords,
            "recordsFiltered" => (int)$totalFiltered,
            "data" => $reviews
        ];
    }

    // HÀM MỚI: Lấy chi tiết một đánh giá bằng ID
    public function getReviewById($reviewId) {
        $query = "SELECT 
                r.id, r.rating, r.comment, r.status, r.created_at, r.admin_reply, r.replied_at,
                u.email as user_email,
                p.id as product_id, p.name as product_name
              FROM review r
              JOIN username u ON r.user_id = u.id
              JOIN product p ON r.product_id = p.id
              WHERE r.id = :reviewId
              LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // HÀM MỚI: Lấy tất cả ảnh của một sản phẩm
    public function getProductImages($productId) {
        $query = "SELECT image_url FROM product_image WHERE product_id = :productId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // HÀM MỚI: Lưu phản hồi của admin cho một review
    public function saveAdminReply($reviewId, $replyText) {
        // Cập nhật cột admin_reply và thời gian trả lời
        $query = "UPDATE review 
              SET admin_reply = :replyText, replied_at = NOW() 
              WHERE id = :reviewId";

        $stmt = $this->conn->prepare($query);

        // Gán giá trị
        $stmt->bindParam(':replyText', $replyText);
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);

        // Thực thi và trả về kết quả
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // HÀM MỚI: Cập nhật trạng thái của một đánh giá
    public function updateReviewStatus($reviewId, $newStatus) {
        // Chỉ cho phép các giá trị trạng thái hợp lệ
        $allowed_statuses = ['approved', 'hidden', 'pending'];
        if (!in_array($newStatus, $allowed_statuses)) {
            return false; // Trả về false nếu trạng thái không hợp lệ
        }

        $query = "UPDATE review SET status = :newStatus WHERE id = :reviewId";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':newStatus', $newStatus);
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // HÀM MỚI: Xóa vĩnh viễn một đánh giá bằng ID
    public function deleteReviewById($reviewId) {
        $query = "DELETE FROM review WHERE id = :reviewId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);

        // Thực thi và trả về kết quả
        return $stmt->execute();
    }

}
?>
