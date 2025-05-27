<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="Public/CSS/register.css">
    <link rel="stylesheet" href="Public/CSS/auth_form_styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@400;700;800&family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page-body">

<div class="auth-form-container">
    <h1>Reset Your Password</h1>
    <form method="POST" action="../controller/xulyDatLai.php" id="resetPasswordForm">
        <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
        <div class="form-group">
            <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required>
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
        </div>
        <span id="passwordMatchError" class="form-error-message" style="display:none;"></span>
        <button type="submit">Reset Password</button>
    </form>
</div>

<script src="Public/JS/reset_password_scripts.js"></script>
</body>
</html>