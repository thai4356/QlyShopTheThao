<?php
// src/model/admin/AdminCategory.php
require_once 'AdminConnect.php'; // Đảm bảo file AdminConnect.php đã tồn tại trong cùng thư mục

class AdminCategory {
    private $conn;
    private $table_name = "category";
    private $product_table_name = "product";

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
     * Lấy tất cả các danh mục bao gồm cả trạng thái.
     * @return array Mảng các danh mục hoặc mảng rỗng nếu không có.
     */
    public function getAllCategories() {
        // Câu truy vấn đã được cập nhật để lấy cả cột is_active
        $query = "SELECT id, name, description, is_active FROM " . $this->table_name . " ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $categories = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Chuyển đổi giá trị is_active từ database (có thể là byte) thành boolean
                $row['is_active'] = (bool)$row['is_active'];
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
        // Câu truy vấn đã được cập nhật để lấy cả cột is_active
        $query = "SELECT id, name, description, is_active FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $row['is_active'] = (bool)$row['is_active'];
            return $row;
        }
        return null;
    }

    /**
     * Cập nhật thông tin một danh mục và trạng thái của các sản phẩm liên quan.
     * @param int $id ID của danh mục.
     * @param string $name Tên mới của danh mục.
     * @param string $description Mô tả mới.
     * @param bool $isActive Trạng thái mới (true: hoạt động, false: không hoạt động).
     * @return bool True nếu cập nhật thành công, false nếu thất bại.
     */
    public function updateCategory($id, $name, $description, $isActive) {
        // Chuyển boolean thành giá trị 0 hoặc 1 cho CSDL
        $isActiveDbValue = $isActive ? 1 : 0;

        $this->conn->beginTransaction(); // Bắt đầu transaction

        try {
            // 1. Cập nhật bản thân danh mục
            $queryCategory = "UPDATE " . $this->table_name . "
                              SET name = :name, description = :description, is_active = :is_active_category
                              WHERE id = :id";
            $stmtCategory = $this->conn->prepare($queryCategory);

            // Làm sạch dữ liệu (chỉ cho các giá trị sẽ được chèn/cập nhật trực tiếp vào SQL)
            $cleanName = htmlspecialchars(strip_tags($name));
            $cleanDescription = htmlspecialchars(strip_tags($description)); // Giữ lại ký tự xuống dòng nếu cần
            // strip_tags có thể loại bỏ <br> nếu bạn muốn nl2br xử lý sau

            $stmtCategory->bindParam(':name', $cleanName);
            $stmtCategory->bindParam(':description', $cleanDescription); // Lưu description thô, nl2br khi hiển thị
            $stmtCategory->bindParam(':is_active_category', $isActiveDbValue, PDO::PARAM_INT);
            $stmtCategory->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmtCategory->execute()) {
                $this->conn->rollBack();
                error_log("Lỗi khi cập nhật danh mục ID: " . $id);
                return false;
            }

            // 2. Cập nhật trạng thái is_active của các sản phẩm liên quan
            $queryProducts = "UPDATE " . $this->product_table_name . "
                              SET is_active = :is_active_product
                              WHERE category_id = :category_id";
            $stmtProducts = $this->conn->prepare($queryProducts);
            $stmtProducts->bindParam(':is_active_product', $isActiveDbValue, PDO::PARAM_INT);
            $stmtProducts->bindParam(':category_id', $id, PDO::PARAM_INT);

            if (!$stmtProducts->execute()) {
                $this->conn->rollBack();
                error_log("Lỗi khi cập nhật sản phẩm cho danh mục ID: " . $id . " sang trạng thái: " . $isActiveDbValue);
                return false;
            }

            $this->conn->commit(); // Hoàn tất transaction nếu mọi thứ thành công
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack(); // Hoàn tác nếu có lỗi
            error_log("Transaction thất bại khi cập nhật danh mục ID " . $id . " và sản phẩm liên quan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa mềm một danh mục và ẩn tất cả các sản phẩm liên quan.
     * @param int $categoryId ID của danh mục cần xóa mềm.
     * @return bool True nếu thành công, false nếu thất bại.
     */
    public function softDeleteCategoryAndRelatedProducts($categoryId) {
        $this->conn->beginTransaction();

        try {
            // 1. Cập nhật trạng thái is_active = 0 cho danh mục
            $queryCategory = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE id = :id";
            $stmtCategory = $this->conn->prepare($queryCategory);
            $stmtCategory->bindParam(':id', $categoryId, PDO::PARAM_INT);
            if (!$stmtCategory->execute()) {
                $this->conn->rollBack();
                error_log("Failed to soft delete category ID: " . $categoryId);
                return false;
            }

            // 2. Cập nhật trạng thái is_active = 0 cho các sản phẩm thuộc danh mục đó
            // (Chỉ thực hiện nếu bạn chắc chắn rằng bảng product có cột category_id và is_active)
            $queryProducts = "UPDATE " . $this->product_table_name . " SET is_active = 0 WHERE category_id = :category_id";
            $stmtProducts = $this->conn->prepare($queryProducts);
            $stmtProducts->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            if (!$stmtProducts->execute()) {
                $this->conn->rollBack();
                error_log("Failed to hide products for category ID: " . $categoryId);
                return false;
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Transaction failed for soft deleting category ID " . $categoryId . ": " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thêm một danh mục mới vào cơ sở dữ liệu.
     * @param string $name Tên của danh mục.
     * @param string $description Mô tả của danh mục.
     * @param bool $isActive Trạng thái hoạt động (true/false).
     * @return string|false ID của danh mục mới được chèn nếu thành công, ngược lại là false.
     */
    public function addCategory($name, $description, $isActive) {
        $query = "INSERT INTO " . $this->table_name . " (name, description, is_active) 
                  VALUES (:name, :description, :is_active)";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $cleanName = htmlspecialchars(strip_tags($name));
        $cleanDescription = htmlspecialchars(strip_tags($description)); // Lưu description thô
        $isActiveDbValue = $isActive ? 1 : 0;

        // Gán tham số
        $stmt->bindParam(':name', $cleanName);
        $stmt->bindParam(':description', $cleanDescription);
        $stmt->bindParam(':is_active', $isActiveDbValue, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId(); // Trả về ID của bản ghi mới
        }
        error_log("Lỗi khi thêm danh mục: " . implode(":", $stmt->errorInfo()));
        return false;
    }

}
?>