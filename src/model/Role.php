<?php
require_once 'Database.php';

class Role {
    private $conn;
    private $table = "role";
    public $id, $name;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }
}
