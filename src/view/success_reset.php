<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Thông báo</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">✅ Thành công!</h5>
        </div>
        <div class="modal-body">
            <p>Chúng tôi đã gửi liên kết đặt lại mật khẩu đến <?= htmlspecialchars(isset($_GET['email']) ? $_GET['email'] : '') ?></p>
        </div>
        <div class="modal-footer">
            <a href="login.php" class="btn btn-primary">Quay lại đăng nhập</a>
        </div>
    </div>
</div>
</body>
</html>