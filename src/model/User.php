<?php
require_once 'Database.php';

class User {
    private $conn;
    private $table = "username";

    public $id, $email, $password, $is_verified, $verify_token, $reset_token, $roleid, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
