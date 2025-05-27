<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu đã được gửi</title>
    <link rel="stylesheet" href="Public/CSS/register.css">
    <link rel="stylesheet" href="Public/CSS/message_page_styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@400;700;800&family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="message-page-body">

<div class="auth-message-container">
    <h1>✅ Yêu cầu đã được gửi!</h1>
    <p>
        <?php
        $email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'địa chỉ email của bạn';
        $status = $_GET['status'] ?? '';

        if ($status === 'emailsent_placeholder') {
            echo "Nếu có tài khoản tương ứng với email đã cung cấp, chúng tôi đã gửi một liên kết đặt lại mật khẩu. Vui lòng kiểm tra hộp thư đến (và cả thư mục spam).";
        } else {
            echo "Chúng tôi đã gửi một liên kết đặt lại mật khẩu tới <strong>" . $email . "</strong>. Vui lòng kiểm tra hộp thư đến (và cả thư mục spam).";
        }
        ?>
    </p>
    <a href="login.php" class="button-link">Quay lại đăng nhập</a>
</div>

</body>
</html>
