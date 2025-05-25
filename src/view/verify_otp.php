
<?php
session_start();
// Nếu không có email đang chờ OTP, chuyển về trang đăng nhập
if (!isset($_SESSION['otp_pending_user_email'])) {
    header("Location: login.php");
    exit;
}
$user_email_for_otp = htmlspecialchars($_SESSION['otp_pending_user_email']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP</title>
    <link rel="stylesheet" href="Public/CSS/register.css">
    <style>
        /* body được style bởi register.css để căn giữa nội dung */
        .auth-form-container {
            background-color: #FFFFFF;
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            width: 100%;
            max-width: 450px; /* Tăng độ rộng một chút */
            text-align: center;
            margin-top: 30px;
        }
        .auth-form-container h1 {
            margin-bottom: 15px;
        }
        .auth-form-container p.instruction {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
        .auth-form-container form {
            padding: 0;
            background-color: transparent;
            box-shadow: none;
        }
        /* Style cho input OTP */
        .auth-form-container input[name="otp_code"] {
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.5em; /* To hơn cho dễ nhìn */
            letter-spacing: 3px; /* Khoảng cách giữa các chữ số */
            border: 1px solid #ddd; /* Thêm border cho rõ */
            border-radius: 4px;
        }
        .auth-form-container button[type="submit"] {
            width: 100%;
        }
        .error-message, .success-message { /* Style cho thông báo lỗi/thành công từ session */
            font-size: 14px;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: left;
        }
        .error-message { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .success-message { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; }

        .resend-otp-container {
            margin-top: 20px;
            font-size: 13px;
        }
        .resend-otp-container a {
            color: #FF4B2B; /* Màu chủ đạo của theme */
            text-decoration: none; /* Bỏ gạch chân mặc định */
            font-weight: bold;
        }
        .resend-otp-container a:hover {
            text-decoration: underline;
        }
        #resendOtpLink.disabled {
            color: #aaa;
            pointer-events: none;
            text-decoration: none;
        }
        #otpCooldownTimer { display:none; margin-left: 5px; color: #555; }
    </style>
</head>
<body>

<div class="auth-form-container">
    <h1>Xác Thực OTP</h1>
    <p class="instruction">Một mã OTP gồm 6 chữ số đã được gửi đến email <strong><?php echo $user_email_for_otp; ?></strong>. Vui lòng nhập mã vào ô bên dưới để hoàn tất đăng nhập. Mã có hiệu lực trong 5 phút.</p>

    <?php if (isset($_SESSION['otp_page_error'])): ?>
        <p class="error-message"><?php echo htmlspecialchars($_SESSION['otp_page_error']); unset($_SESSION['otp_page_error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['otp_page_success'])): ?>
        <p class="success-message"><?php echo htmlspecialchars($_SESSION['otp_page_success']); unset($_SESSION['otp_page_success']); ?></p>
    <?php endif; ?>

    <form action="../controller/xulyVerifyOtp.php" method="post">
        <input type="text" name="otp_code" id="otp_code" placeholder="Nhập OTP" maxlength="6" pattern="\d{6}" title="Mã OTP phải là 6 chữ số." required autocomplete="off">
        <button type="submit">Xác Nhận OTP</button>
    </form>

    <div class="resend-otp-container">
        Chưa nhận được mã?
        <a href="../controller/xulyResendOtp.php" id="resendOtpLink">Gửi lại OTP</a>
        <span id="otpCooldownTimer">(Thử lại sau <span id="cooldownTimeValue">60</span> giây)</span>
    </div>
</div>

<script>
    const resendLink = document.getElementById('resendOtpLink');
    const cooldownTimerDisplay = document.getElementById('otpCooldownTimer');
    const cooldownTimeValueSpan = document.getElementById('cooldownTimeValue');
    let initialCooldown = 60; // Thời gian chờ ban đầu (giây)

    function applyCooldown(secondsRemaining) {
        resendLink.classList.add('disabled');
        cooldownTimerDisplay.style.display = 'inline';
        cooldownTimeValueSpan.textContent = secondsRemaining;

        const interval = setInterval(() => {
            secondsRemaining--;
            cooldownTimeValueSpan.textContent = secondsRemaining;
            if (secondsRemaining <= 0) {
                clearInterval(interval);
                resendLink.classList.remove('disabled');
                cooldownTimerDisplay.style.display = 'none';
                sessionStorage.removeItem('otpCooldownEnd'); // Xóa thời gian kết thúc cooldown
            }
        }, 1000);
    }

    // Kiểm tra xem có cần tiếp tục cooldown từ sessionStorage không
    const cooldownEndTime = sessionStorage.getItem('otpCooldownEnd');
    if (cooldownEndTime) {
        const now = Math.floor(Date.now() / 1000);
        const remaining = parseInt(cooldownEndTime) - now;
        if (remaining > 0) {
            applyCooldown(remaining);
        } else {
            sessionStorage.removeItem('otpCooldownEnd');
        }
    }

    resendLink.addEventListener('click', function(event) {
        if (this.classList.contains('disabled')) {
            event.preventDefault();
            return;
        }
        // Lưu thời điểm kết thúc cooldown vào sessionStorage
        const now = Math.floor(Date.now() / 1000);
        sessionStorage.setItem('otpCooldownEnd', now + initialCooldown);
        applyCooldown(initialCooldown);
        // Cho phép link hoạt động để gửi request
    });
</script>

</body>
</html>