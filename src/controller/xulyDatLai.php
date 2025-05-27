<?php
require_once "../model/Connect.php"; //
$database = new Connect(); //
$conn = $database->getConnection(); //

// Biến để lưu thông báo và loại thông báo
$alert_message = '';
$alert_type_class = ''; // ví dụ: 'alert-success-custom', 'alert-danger-custom'
$page_title_text = 'Thông báo đặt lại mật khẩu'; // Tiêu đề mặc định
$show_login_link = true; // Mặc định hiển thị link quay lại đăng nhập
$show_try_again_link = false; // Mặc định không hiển thị link thử lại

if ($_SERVER['REQUEST_METHOD'] == 'POST') { //
    $token = $_POST['token']; //
    $new_password = $_POST['new_password']; //

    if (empty($token) || empty($new_password)) { //
        $alert_message = 'Thiếu dữ liệu!'; //
        $alert_type_class = 'alert-danger-custom';
        $page_title_text = 'Lỗi!';
        $show_try_again_link = true;
        // exit; // PHP exit sẽ được xử lý sau khi echo HTML
    } else {
        // Kiểm tra token tồn tại
        $query = "SELECT * FROM username WHERE reset_token = :token"; //
        $stmt = $conn->prepare($query); //
        $stmt->bindParam(':token', $token); //
        $stmt->execute(); //
        $result = $stmt->fetch(PDO::FETCH_ASSOC); //

        if (!$result) { //
            $alert_message = 'Token không hợp lệ hoặc đã hết hạn.'; //
            $alert_type_class = 'alert-danger-custom';
            $page_title_text = 'Lỗi!';
            $show_try_again_link = true;
            // exit;
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]); //
            // Giữ nguyên logic cập nhật của bạn, chỉ xóa reset_token
            $update_query = "UPDATE username SET password = :password, reset_token = NULL WHERE reset_token = :token"; //
            $update_stmt = $conn->prepare($update_query); //
            $update_stmt->bindParam(':password', $hashed_password); //
            $update_stmt->bindParam(':token', $token); //

            if ($update_stmt->execute()) { //
                $alert_message = 'Mật khẩu đã đặt lại thành công!'; //
                $alert_type_class = 'alert-success-custom';
                $page_title_text = '✅ Thành công!';
            } else {
                // Trường hợp execute() thất bại (ít khi xảy ra nếu query đúng và DB hoạt động)
                $alert_message = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
                $alert_type_class = 'alert-danger-custom';
                $page_title_text = 'Lỗi!';
                $show_try_again_link = true;
                error_log("Password update failed for token: " . $token); // Ghi log lỗi
            }
        }
    }
} else {
    $alert_message = 'Yêu cầu không hợp lệ.'; //
    $alert_type_class = 'alert-warning-custom';
    $page_title_text = 'Cảnh báo!';
}
?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="../view/Public/CSS/register.css">
        <title><?php echo htmlspecialchars($page_title_text); ?></title>
        <style>
            /* body đã được style bởi register.css để căn giữa */
            .auth-message-container {
                background-color: #FFFFFF;
                padding: 40px 50px;
                border-radius: 10px;
                box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
                width: 100%;
                max-width: 480px;
                text-align: center;
                margin-top: 30px; /* Đảm bảo không bị che nếu body có margin âm */
            }
            .auth-message-container h1 {
                margin-bottom: 15px;
                font-size: 24px;
                /* Màu sắc cho tiêu đề sẽ được đặt dựa trên loại thông báo */
            }
            .auth-message-container h1.title-success { color: #4CAF50; } /* Xanh lá */
            .auth-message-container h1.title-danger { color: #FF4B2B; } /* Đỏ (màu theme) */
            .auth-message-container h1.title-warning { color: #ffc107; } /* Vàng */

            .auth-message-container .message-text { /* Class cho đoạn text thông báo */
                font-size: 16px;
                color: #333;
                margin-bottom: 25px;
                line-height: 1.6;
                padding: 15px;
                border: 1px solid transparent;
                border-radius: .25rem;
            }
            /* Màu nền và viền cho các loại thông báo (giống alert) */
            .auth-message-container .alert-success-custom { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
            .auth-message-container .alert-danger-custom { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
            .auth-message-container .alert-warning-custom { color: #856404; background-color: #fff3cd; border-color: #ffeeba; }

            .auth-message-container a.button-link {
                display: inline-block;
                border-radius: 20px;
                border: 1px solid #FF4B2B;
                background-color: #FF4B2B;
                color: #FFFFFF !important;
                font-size: 12px;
                font-weight: bold;
                padding: 12px 25px;
                letter-spacing: 1px;
                text-transform: uppercase;
                text-decoration: none;
                transition: transform 80ms ease-in, background-color 0.2s;
                margin: 5px 10px; /* Thêm margin cho các nút */
            }
            .auth-message-container a.button-link:hover {
                background-color: #e04020;
                border-color: #e04020;
                transform: scale(1.05);
            }
        </style>
    </head>
    <body>
    <div class="auth-message-container">
        <?php if (!empty($page_title_text)): ?>
            <h1 class="
                <?php
            if ($alert_type_class === 'alert-success-custom') echo 'title-success';
            elseif ($alert_type_class === 'alert-danger-custom') echo 'title-danger';
            elseif ($alert_type_class === 'alert-warning-custom') echo 'title-warning';
            ?>
            ">
                <?php echo htmlspecialchars($page_title_text); ?>
            </h1>
        <?php endif; ?>

        <?php if (!empty($alert_message)): ?>
            <div class="message-text <?php echo htmlspecialchars($alert_type_class); ?>">
                <?php echo htmlspecialchars($alert_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($show_login_link): ?>
            <a href="../view/login.php" class="button-link">Về trang đăng nhập</a>
        <?php endif; ?>
        <?php if ($show_try_again_link): ?>
            <a href="../view/ForgotPassword.php" class="button-link">Thử lại</a>
        <?php endif; ?>
    </div>
    </body>
    </html>
<?php
// Xử lý exit ở đây nếu cần thiết sau khi đã echo HTML
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($token) || empty($new_password) || !$result && !$show_try_again_link)) {
    exit; // Exit nếu có lỗi nghiêm trọng ban đầu và không muốn hiển thị nút
}
?>