<?php
require_once __DIR__ . '/AdminConnect.php';

class AdminUser {
    private $conn;
    private $table_name = "username";

    public function __construct() {
        $db = new AdminConnect();
        $this->conn = $db->getConnection();
    }

    /**
     * Lấy dữ liệu người dùng cho DataTables với phân trang, tìm kiếm, sắp xếp phía server.
     */
    public function getUsersForDataTable($start, $length, $searchValue, $orderColumn, $orderDir) {
        // Cột có thể sắp xếp
        $columns = ['u.id', 'u.email', 'u.is_verified', 'r.name', 'u.is_active'];

        // Truy vấn chính để lấy dữ liệu
        $query = "SELECT u.id, u.email, u.is_verified, u.is_active, r.name as role
                  FROM " . $this->table_name . " u
                  JOIN role r ON u.roleid = r.id";

        // Xử lý tìm kiếm
        if (!empty($searchValue)) {
            $query .= " WHERE u.email LIKE :searchValue OR r.name LIKE :searchValue";
        }

        // Xử lý sắp xếp
        if (isset($orderColumn) && isset($orderDir)) {
            $query .= " ORDER BY " . $columns[$orderColumn] . " " . $orderDir;
        } else {
            $query .= " ORDER BY u.id DESC";
        }

        // Lấy tổng số bản ghi (trước khi lọc)
        $totalRecordsQuery = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmtTotal = $this->conn->prepare($totalRecordsQuery);
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        // Lấy tổng số bản ghi (sau khi lọc)
        $totalFiltered = $totalRecords;
        if (!empty($searchValue)) {
            $totalFilteredQuery = "SELECT COUNT(*) as total
                                   FROM " . $this->table_name . " u
                                   JOIN role r ON u.roleid = r.id
                                   WHERE u.email LIKE :searchValue OR r.name LIKE :searchValue";
            $stmtFiltered = $this->conn->prepare($totalFilteredQuery);
            $stmtFiltered->bindValue(':searchValue', '%' . $searchValue . '%');
            $stmtFiltered->execute();
            $totalFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
        }


        // Xử lý phân trang
        $query .= " LIMIT :start, :length";

        // Chuẩn bị và thực thi truy vấn chính
        $stmt = $this->conn->prepare($query);

        if (!empty($searchValue)) {
            $stmt->bindValue(':searchValue', '%' . $searchValue . '%');
        }
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "recordsTotal" => (int)$totalRecords,
            "recordsFiltered" => (int)$totalFiltered,
            "data" => $users
        ];
    }

    /**
     * Lấy thông tin chi tiết một người dùng bằng ID
     */
    public function getUserById($id) {
        $query = "SELECT u.id, u.email, u.is_verified, u.is_active, u.created_at, r.name as role
                  FROM " . $this->table_name . " u
                  JOIN role r ON u.roleid = r.id
                  WHERE u.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tất cả đơn hàng của một người dùng bằng User ID
     */
    public function getOrdersByUserId($userId) {
        $query = "SELECT id, orderNo, total_price, status, created_at
                  FROM orders
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật trạng thái is_active của người dùng.
     * @param int $userId ID của người dùng
     * @param int $newStatus Trạng thái mới (0 cho vô hiệu hóa, 1 cho kích hoạt)
     * @return bool True nếu cập nhật thành công, False nếu thất bại
     */
    public function toggleUserStatus($userId, $newStatus) {
        // Câu lệnh SQL để cập nhật
        $query = "UPDATE " . $this->table_name . " SET is_active = :new_status WHERE id = :id";

        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);

        // Gán giá trị và làm sạch dữ liệu
        $userId = (int)$userId;
        $newStatus = (int)$newStatus;

        $stmt->bindParam(':new_status', $newStatus, PDO::PARAM_INT);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        // Thực thi câu lệnh và trả về kết quả
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Kiểm tra xem một email đã tồn tại trong CSDL hay chưa.
     * @param string $email Email cần kiểm tra
     * @return bool True nếu email đã tồn tại, False nếu chưa
     */
    public function isEmailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true; // Email đã tồn tại
        }
        return false; // Email chưa tồn tại
    }

    /**
     * Thêm một người dùng mới vào cơ sở dữ liệu.
     * @param string $email
     * @param string $hashedPassword Mật khẩu đã được băm
     * @param int $roleId ID của vai trò
     * @return bool True nếu thêm thành công, False nếu thất bại
     */
    public function addUser($email, $hashedPassword, $roleId) {
        // is_verified và is_active mặc định là 1 (đã xác thực và hoạt động)
        $query = "INSERT INTO " . $this->table_name . " 
              (email, password, roleid, is_verified, is_active) 
              VALUES 
              (:email, :password, :roleid, 1, 1)";

        $stmt = $this->conn->prepare($query);

        // Gán giá trị
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':roleid', $roleId, PDO::PARAM_INT);

        // Thực thi
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

}
?>