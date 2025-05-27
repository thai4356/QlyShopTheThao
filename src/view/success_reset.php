<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Sent</title>
    <link rel="stylesheet" href="Public/CSS/register.css">
    <link rel="stylesheet" href="Public/CSS/message_page_styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@400;700;800&family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="message-page-body">

<div class="auth-message-container">
    <h1>✅ Request Sent!</h1>
    <p>
        <?php
        $email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'your email address';
        $status = $_GET['status'] ?? '';

        if ($status === 'emailsent_placeholder') {
            echo "If an account with the provided email exists, we have sent a password reset link. Please check your inbox (and spam folder).";
        } else {
            // Sử dụng thẻ strong để làm nổi bật email, CSS sẽ style nó
            echo "We have sent a password reset link to <strong>" . $email . "</strong>. Please check your inbox (and spam folder).";
        }
        ?>
    </p>
    <a href="login.php" class="button-link">Back to Login</a>
</div>

</body>
</html>