<?php
// src/model/admin/AdminCategory.php
require_once 'AdminConnect.php'; // Đảm bảo file AdminConnect.php đã tồn tại trong cùng thư mục

class AdminCategory {
    private $conn;
    private $table_name = "category";

    public function __construct() {
        $db = new AdminConnect();
        $this->conn = $db->getConnection();
    }

    /**
     * Lấy tất cả các danh mục.
     * Trong database schema bạn cung cấp, bảng 'category' không có cột 'is_active'.
     * Phương thức này sẽ lấy tất cả danh mục.
     * Nếu sau này bạn thêm cột trạng thái (ví dụ: is_active), bạn có thể cập nhật câu query.
     * @return array Mảng các danh mục hoặc mảng rỗng nếu không có.
     */
    public function getAllActiveCategories() {
        $query = "SELECT id, name, description FROM " . $this->table_name . " ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $categories = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories[] = $row;
            }
        }
        return $categories;
    }

    /**
     * Lấy thông tin một danh mục bằng ID.
     * @param int $id ID của danh mục.
     * @return array|null Thông tin danh mục hoặc null nếu không tìm thấy.
     */
    public function getCategoryById($id) {
        $query = "SELECT id, name, description FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Bạn có thể thêm các phương thức khác ở đây sau này nếu cần
    // Ví dụ: createCategory, updateCategory, deleteCategory
}
?>