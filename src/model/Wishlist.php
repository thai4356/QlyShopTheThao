<?php
require_once 'Database.php';

class Wishlist {
    private $conn;
    private $table = "wishlist";
    public $id, $user_id, $product_id, $created_at;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
