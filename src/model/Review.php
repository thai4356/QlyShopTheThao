<?php
require_once 'Database.php';

class Review {
    private $conn;
    private $table = "review";
    public $id, $user_id, $product_id, $rating, $comment, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
