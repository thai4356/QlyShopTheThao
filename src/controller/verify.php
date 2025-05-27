<?php
require_once "../model/Connect.php";

// Gọi hàm getConnection() để lấy PDO
$connection = new Connect();
$conn = $connection->getConnection();

$database = new Connect();
$conn = $database->getConnection();

$email = $_GET['email'];
$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM username WHERE email = :email AND verify_token = :token");
$stmt->bindParam(':email', $email);
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $update = $conn->prepare("UPDATE username SET is_verified = 1, verify_token = NULL WHERE email = :email");
    $update->bindParam(':email', $email);
    $update->execute();
    echo "Xác thực thành công!";
} else {
    echo "Liên kết xác thực không hợp lệ!";
}

