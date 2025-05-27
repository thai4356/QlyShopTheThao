<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="Public/CSS/register.css"> <style>
        /* body được style bởi register.css để căn giữa nội dung */
        .auth-message-container {
            background-color: #FFFFFF; /* */
            padding: 40px 50px;
            border-radius: 10px; /* */
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22); /* */
            width: 100%;
            max-width: 480px;
            text-align: center; /* */
        }
        .auth-message-container h1 {
            margin-bottom: 15px; /* */
            font-size: 24px; /* */
            color: #4CAF50; /* Màu xanh cho thành công */
        }
        .auth-message-container p {
            font-size: 16px; /* */
            color: #333;
            margin-bottom: 25px; /* */
            line-height: 1.6;
        }
        .auth-message-container a.button-link {
            display: inline-block;
            border-radius: 20px; /* */
            border: 1px solid #FF4B2B; /* */
            background-color: #FF4B2B; /* */
            color: #FFFFFF !important; /* */
            font-size: 12px; /* */
            font-weight: bold; /* */
            padding: 12px 25px; /* */
            letter-spacing: 1px; /* */
            text-transform: uppercase; /* */
            text-decoration: none;
            transition: transform 80ms ease-in, background-color 0.2s; /* */
        }
        .auth-message-container a.button-link:hover {
            background-color: #e04020;
            border-color: #e04020;
            transform: scale(1.05); /* */
        }
    </style>
</head>
<body>

<div class="auth-message-container">
    <h1>✅ Registration Initiated!</h1>
    <p>
        Thank you for registering! We have sent an account verification link to
        <strong><?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'your email address'; ?></strong>.
        Please check your inbox (and spam folder) to complete your registration.
    </p>
    <a href="login.php" class="button-link">Back to Login</a>
</div>

</body>
</html>

