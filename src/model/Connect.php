<?php
class Connect {

    private $host = "gondola.proxy.rlwy.net:37729";
    private $db_name = "user_database";
    private $username = "root";
    private $password = "NvsggkIBRDcJpFNKVKOPYAofrTUsFhor";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username, $this->password
            );
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
