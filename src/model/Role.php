<?php
require_once 'Connect.php';

class Role {
    private $conn;
    private $table = "role";
    public $id, $name;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }
}
