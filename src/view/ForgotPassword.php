<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
    <link rel="stylesheet" href="Public/CSS/register.css">
    <style>
        /* body được style bởi register.css để căn giữa nội dung */
        .auth-form-container {
            background-color: #FFFFFF;
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .auth-form-container h1 {
            margin-bottom: 20px;
        }
        .auth-form-container p {
            font-size: 14px;
            color: #555;
            margin-bottom: 25px;
        }
        .auth-form-container form {
            padding: 0;
            background-color: transparent;
            box-shadow: none;
        }
        .auth-form-container input[type="email"] {
            margin-bottom: 20px;
        }
        .auth-form-container button[type="submit"] {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="auth-form-container">
    <h1>Quên Mật Khẩu</h1>
    <p>Nhập địa chỉ email của bạn, chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.</p>
    <form action="../controller/xulyQuen.php" method="post" style="padding-bottom: 200px">
        <input type="email" name="email" id="email" placeholder="Nhập email của bạn" required>
        <button type="submit">Gửi liên kết đặt lại</button>
    </form>
</div>

</body>
</html>
