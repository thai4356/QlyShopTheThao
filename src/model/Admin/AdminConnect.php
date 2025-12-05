<?php
// src/model/admin/AdminConnect.php
class AdminConnect {
    private $host = "j3egkd.h.filess.io:3306";
    private $db_name = "user_database_biggestzoo";
    private $username = "user_database_biggestzoo";
    private $password = "8200c17fb8ab66b3f73f8a0b4dc95ee2da14de7e";

    // SỬA ĐỔI 1: Khai báo biến tĩnh (static) để lưu kết nối dùng chung
    private static $db_connection = null;

    public function getConnection() {
        // SỬA ĐỔI 2: Kiểm tra xem đã có kết nối chưa
        if (self::$db_connection === null) {
            try {
                // Nếu chưa có (null) thì mới khởi tạo kết nối mới
                self::$db_connection = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );

                // Cấu hình báo lỗi để dễ debug
                self::$db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$db_connection->exec("set names utf8mb4");

            } catch(PDOException $exception) {
                echo "Connection error: " . $exception->getMessage();
                return null;
            }
        }

        // Trả về kết nối đã có (hoặc vừa tạo)
        return self::$db_connection;
    }
}
?>
