<?php
require_once 'Connect.php';

class ShippingAddress {
    private $conn;
    private $table = "shipping_address";
    public $id, $user_id, $full_name, $phone, $address, $city, $country, $postal_code;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }
}
