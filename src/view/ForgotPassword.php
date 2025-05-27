<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="Public/CSS/register.css"> <style>
        /* body được style bởi register.css để căn giữa nội dung */
        .auth-form-container {
            background-color: #FFFFFF; /* */
            padding: 40px 50px;
            border-radius: 10px; /* */
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22); /* */
            width: 100%;
            max-width: 420px;
            text-align: center; /* */
        }
        .auth-form-container h1 {
            margin-bottom: 20px; /* */
        }
        .auth-form-container p {
            font-size: 14px; /* */
            color: #555;
            margin-bottom: 25px; /* */
        }
        .auth-form-container form {
            padding: 0; /* Ghi đè padding của form từ register.css nếu không cần thiết */
            background-color: transparent; /* Ghi đè background của form từ register.css */
            box-shadow: none;
        }
        .auth-form-container input[type="email"] {
            margin-bottom: 20px; /* */
        }
        .auth-form-container button[type="submit"] {
            width: 100%; /* */
        }
    </style>
</head>
<body>

<div class="auth-form-container">
    <h1>Forgot Password</h1> <p>Enter your email address, and we will send you a link to reset your password.</p>
    <form action="../controller/xulyQuen.php" method="post"> <input type="email" name="email" id="email" placeholder="Enter your email" required> <button type="submit">Send Reset Link</button> </form>
</div>

</body>
</html>