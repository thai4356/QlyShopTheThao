<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Document</title>
    <style>
        .form-container {
            max-width: 400px;
            margin: auto;
            margin-top: 50px; /* Adjust as needed */
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<?php
$conn = require_once "../model/Connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];

    if (empty($token) || empty($new_password)) {
        echo '<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="form-container">
                    <div class="alert alert-danger text-center" role="alert">Thiếu dữ liệu!</div>
                </div>
            </div>';
        exit;
    }

    // Kiểm tra token tồn tại
    $query = "SELECT * FROM username WHERE reset_token = :token";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo '<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="form-container">
                    <div class="alert alert-danger text-center" role="alert">Token không hợp lệ hoặc đã hết hạn.</div>
                </div>
            </div>';
        exit;
    }

    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
    $update_query = "UPDATE username SET password = :password, reset_token = NULL WHERE reset_token = :token";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':password', $hashed_password);
    $update_stmt->bindParam(':token', $token);
    $update_stmt->execute();

    echo '<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
            <div class="form-container">
                <div class="alert alert-success text-center" role="alert">Mật khẩu đã đặt lại thành công!</div>
            </div>
        </div>';
} else {
    echo '<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
            <div class="form-container">
                <div class="alert alert-warning text-center" role="alert">Yêu cầu không hợp lệ.</div>
            </div>
        </div>';
}
?>
</body>
</html>

