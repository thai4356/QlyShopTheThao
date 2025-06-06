<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản bị vô hiệu hóa</title>

    <link rel="stylesheet" href="Public/CSS/register.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* Thêm một vài style tùy chỉnh cho trang này */
        .banned-container {
            padding: 40px;
            text-align: center;
            max-width: 550px; /* Giới hạn chiều rộng cho đẹp hơn */
            margin: auto;
        }

        .banned-icon {
            font-size: 5rem; /* Icon to hơn */
            color: #FF4B2B; /* Dùng màu nhấn từ register.css */
            margin-bottom: 25px;
        }

        /* Style cho nút bấm giống với trang đăng nhập */
        .banned-container a.button {
            border-radius: 20px;
            border: 1px solid #FF4B2B;
            background-color: #FF4B2B;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .banned-container a.button:hover {
            transform: scale(1.05);
        }

        .banned-container a.button:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body style="background-color: #f6f5f7;">

<div class="container" style="max-width: 600px; min-height: 400px; display: flex; justify-content: center; align-items: center;">

    <div class="banned-container">

        <div class="banned-icon">
            <i class="fas fa-user-lock"></i>
        </div>

        <h1>Tài khoản của bạn đã bị vô hiệu hóa</h1>

        <p style="font-weight: 400; letter-spacing: 0;">
            Tài khoản này đã bị tạm khóa bởi quản trị viên do vi phạm chính sách hoặc vì lý do bảo mật. Vui lòng liên hệ với bộ phận hỗ trợ để được xem xét và kích hoạt lại.
        </p>

        <a href="login.php" class="button">Quay về trang đăng nhập</a>

    </div>

</div>

</body>
</html>