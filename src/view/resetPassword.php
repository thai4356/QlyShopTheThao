<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
            margin-bottom: 25px; /* */
        }
        .auth-form-container form {
            padding: 0;
            background-color: transparent;
            box-shadow: none;
        }
        .auth-form-container input[type="password"] {
            margin-bottom: 15px; /* */
        }
        .auth-form-container input[name="confirm_password"] { /* Thêm khoảng cách cho input confirm */
            margin-bottom: 20px; /* */
        }
        .auth-form-container button[type="submit"] {
            width: 100%; /* */
        }
        .form-error-message { /* Style cho thông báo lỗi JavaScript (nếu có) */
            color: red;
            font-size: 13px;
            margin-top: -10px;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
<body>

<div class="auth-form-container">
    <h1>Reset Your Password</h1>
    <form method="POST" action="../controller/xulyDatLai.php" id="resetPasswordForm">
        <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
        <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
        <span id="passwordMatchError" class="form-error-message" style="display:none;">Passwords do not match!</span>
        <button type="submit">Reset Password</button>
    </form>
</div>

<script>
    const resetForm = document.getElementById('resetPasswordForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorMessageElement = document.getElementById('passwordMatchError');

            if (newPassword.length < 5) { // Ví dụ kiểm tra độ dài tối thiểu
                if(errorMessageElement) {
                    errorMessageElement.textContent = 'Password must be at least 5 characters long.';
                    errorMessageElement.style.display = 'block';
                }
                event.preventDefault();
                return;
            }

            if (newPassword !== confirmPassword) {
                if(errorMessageElement) {
                    errorMessageElement.textContent = 'Passwords do not match. Please try again.';
                    errorMessageElement.style.display = 'block';
                }
                event.preventDefault(); // Ngăn form submit
            } else {
                if(errorMessageElement) {
                    errorMessageElement.style.display = 'none';
                }
            }
        });
    }
</script>
</body>
</html>