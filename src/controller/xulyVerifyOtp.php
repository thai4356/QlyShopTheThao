<?php
session_start();
require_once __DIR__ . '/../../config/autoloader.php'; // Nếu cần cho các thư viện khác
require_once __DIR__ . "/../model/Connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['otp_page_error'] = "Yêu cầu không hợp lệ.";
    header("Location: ../view/verify_otp.php");
    exit;
}

// Kiểm tra các biến session cần thiết
if (!isset($_SESSION['otp_pending_user_email'], $_POST['otp_code'], $_SESSION['otp_pending_user_id'])) {
    // Nếu thiếu thông tin, có thể session đã hết hạn hoặc có lỗi logic
    $_SESSION['login_error_msg'] = "Phiên xác thực OTP không hợp lệ. Vui lòng đăng nhập lại.";
    unset($_SESSION['otp_pending_user_email']);
    unset($_SESSION['otp_pending_user_id']);
    unset($_SESSION['otp_remember_me']);
    header("Location: ../view/login.php");
    exit;
}

$user_email = $_SESSION['otp_pending_user_email'];
$user_id = $_SESSION['otp_pending_user_id'];
$submitted_otp = trim($_POST['otp_code']);
$remember_me_preference = $_SESSION['otp_remember_me'] ?? false;


if (empty($submitted_otp) || !ctype_digit($submitted_otp) || strlen($submitted_otp) !== 6) {
    $_SESSION['otp_page_error'] = "Mã OTP không hợp lệ. Vui lòng nhập 6 chữ số.";
    header("Location: ../view/verify_otp.php");
    exit;
}

$database = new Connect();
$conn = $database->getConnection();

try {
    $stmt = $conn->prepare("SELECT id, email, roleid, otp_code, otp_expires_at FROM username WHERE id = :user_id AND email = :email_addr");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':email_addr', $user_email); // Dùng tên biến khác để tránh trùng
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['otp_page_error'] = "Không tìm thấy thông tin người dùng phù hợp.";
        header("Location: ../view/verify_otp.php");
        exit;
    }

    // QUAN TRỌNG: Nếu bạn hash OTP khi lưu, dùng password_verify ở đây:
    // if ($user['otp_code'] && password_verify($submitted_otp, $user['otp_code']) && (new DateTime() < new DateTime($user['otp_expires_at']))) {
    // Vì đang lưu plaintext (demo):
    if ($user['otp_code'] == $submitted_otp && $user['otp_expires_at'] != null && (new DateTime() < new DateTime($user['otp_expires_at']))) {
        // OTP chính xác và chưa hết hạn

        // Xóa OTP khỏi cơ sở dữ liệu để không thể sử dụng lại
        $stmt_clear_otp = $conn->prepare("UPDATE username SET otp_code = NULL, otp_expires_at = NULL, otp_created_at = NULL WHERE id = :user_id_clear");
        $stmt_clear_otp->bindParam(':user_id_clear', $user['id']);
        $stmt_clear_otp->execute();

        // Đăng nhập thành công: Thiết lập các biến session chính
        $_SESSION["logined"] = "OK";
        $_SESSION["username"] = $user["email"]; // Hoặc một trường tên người dùng khác nếu có
        $_SESSION["role"] = $user["roleid"];
        $_SESSION["user_id"] = $user["id"];

        // Xóa các biến session tạm thời của OTP
        unset($_SESSION['otp_pending_user_email']);
        unset($_SESSION['otp_pending_user_id']);
        unset($_SESSION['otp_remember_me']);
        unset($_SESSION['otp_page_error']);
        unset($_SESSION['otp_page_success']);


        // Xử lý "Remember Me"
        if ($remember_me_preference) {
            // Thời gian cookie: 7 ngày
            setcookie('email', $user["email"], time() + (86400 * 7), "/", "", false, true); // Thêm httpOnly
        } else {
            // Xóa cookie nếu người dùng không chọn "Remember me" và cookie tồn tại
            if (isset($_COOKIE['email'])) {
                setcookie('email', '', time() - 3600, "/");
            }
        }

        // Chuyển hướng dựa trên vai trò
        if ($user["roleid"] == 2) { // Giả sử roleid 2 là admin
            header("Location: ../view/adminView.php");
            exit;
        } else {
            header("Location: ../view/ViewUser/Menu.php"); // Trang người dùng thông thường
            exit;
        }

    } else {
        // OTP sai hoặc đã hết hạn
        $_SESSION['otp_page_error'] = "Mã OTP không chính xác hoặc đã hết hạn. Vui lòng thử lại.";
        // Có thể thêm logic đếm số lần nhập sai OTP ở đây
        header("Location: ../view/verify_otp.php");
        exit;
    }

} catch (PDOException $e) {
    error_log("DB Error in xulyVerifyOtp: " . $e->getMessage());
    $_SESSION['otp_page_error'] = "Lỗi hệ thống khi xác thực OTP. Vui lòng thử lại sau.";
    header("Location: ../view/verify_otp.php");
    exit;
} catch (Exception $e) { // Bắt các lỗi chung khác, ví dụ từ DateTime
    error_log("General Error in xulyVerifyOtp: " . $e->getMessage());
    $_SESSION['otp_page_error'] = "Có lỗi xảy ra trong quá trình xác thực. Vui lòng thử lại.";
    header("Location: ../view/verify_otp.php");
    exit;
}
?>
