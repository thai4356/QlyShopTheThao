<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký thành công</title>
    <link rel="stylesheet" href="Public/CSS/register.css">
    <link rel="stylesheet" href="Public/CSS/message_page_styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@400;700;800&family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="message-page-body">

<div class="auth-message-container">
    <h1>✅ Đăng ký đã được khởi tạo!</h1>
    <p>
        Cảm ơn bạn đã đăng ký! Chúng tôi đã gửi một liên kết xác minh tài khoản đến
        <strong><?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'địa chỉ email của bạn'; ?></strong>.
        Vui lòng kiểm tra hộp thư đến (và cả thư rác) để hoàn tất việc đăng ký.
    </p>
    <a href="login.php" class="button-link">Quay lại trang đăng nhập</a>
</div>

</body>
</html>
