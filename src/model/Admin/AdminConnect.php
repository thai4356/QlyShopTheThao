<?php
// src/model/admin/AdminConnect.php
class AdminConnect {
    private $host = "j3egkd.h.filess.io:3306"; //
    private $db_name = "user_database_biggestzoo"; //
    private $username = "user_database_biggestzoo"; //
    private $password = "8200c17fb8ab66b3f73f8a0b4dc95ee2da14de7e"; //
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username, $this->password
            ); //
            $this->conn->exec("set names utf8mb4"); //
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
