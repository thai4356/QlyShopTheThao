<?php
require_once 'Database.php';

class Cart {
    private $conn;
    private $table = "cart";
    public $id, $user_id, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
