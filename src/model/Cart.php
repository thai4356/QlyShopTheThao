<?php
require_once 'Connect.php';

class Cart {
    private $conn;
    private $table = "cart";
    public $id, $user_id, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }
}
