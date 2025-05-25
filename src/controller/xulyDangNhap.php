<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


session_start();
require_once __DIR__ . '/../../config/autoloader.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require_once "../model/Connect.php"; // chỉ include, không gán vào $conn
$database = new Connect();
$conn = $database->getConnection();

$projectRootPath = __DIR__ . '/../..'; // Thư mục gốc của project

try {
    $dotenv = Dotenv\Dotenv::createImmutable($projectRootPath);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    error_log("Lỗi khi nạp file .env: " . $e->getMessage());
    die("Không thể nạp cấu hình môi trường. Vui lòng kiểm tra file .env. Chi tiết: " . $e->getMessage());
} catch (Exception $e) { // Bắt các lỗi chung khác
    error_log("Lỗi không xác định khi nạp file .env: " . $e->getMessage());
    die("Lỗi không xác định khi nạp cấu hình môi trường. Chi tiết: " . $e->getMessage());
}

$recaptchaSecretKey = $_ENV['RECAPTCHA_SECRET_KEY'] ?? null;

// Hàm gửi email OTP (tương tự như đã thảo luận)
function sendOtpEmail($userEmail, $otp, $userName = '') {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // DEBUG_SERVER để debug
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME']; // Từ file .env
        $mail->Password = $_ENV['SMTP_PASSWORD']; // Từ file .env
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $_ENV['SMTP_PORT'] ?? 465;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@example.com', $_ENV['SMTP_FROM_NAME'] ?? 'QlyShopTheThao');
        $mail->addAddress($userEmail, $userName);

        $mail->isHTML(true);
        $mail->Subject = 'Mã OTP xác thực đăng nhập tài khoản';
        $mail->Body    = "Chào " . ($userName ?: htmlspecialchars($userEmail)) . ",<br><br>Mã OTP của bạn để đăng nhập là: <h2><b>$otp</b></h2><br>Mã này sẽ hết hạn sau 5 phút.<br><br>Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.<br><br>Trân trọng,<br>Đội ngũ QlyShopTheThao.";
        $mail->AltBody = "Mã OTP của bạn để đăng nhập là: $otp. Mã này sẽ hết hạn sau 5 phút.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer OTP Error for $userEmail: {$mail->ErrorInfo}");
        return false;
    }
}

// Hàm KiemTraTaiKhoan của bạn (đã có sẵn và kiểm tra is_verified)
function KiemTraTaiKhoan($email, $pass, $conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM username WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (password_verify($pass, $row['password'])) {
                if ($row['is_verified'] == 1) {
                    return $row; // Trả về thông tin user nếu hợp lệ và đã xác thực
                } else {
                    // Thay vì echo, lưu vào session để hiển thị trên trang login
                    $_SESSION['login_error_msg'] = "Tài khoản chưa xác thực. Vui lòng kiểm tra email để xác thực tài khoản.";
                    return false;
                }
            } else {
                $_SESSION['login_error_msg'] = "Mật khẩu không đúng.";
                return false;
            }
        } else {
            $_SESSION['login_error_msg'] = "Email không tồn tại.";
            return false;
        }
    } catch (PDOException $e) {
        error_log("DB Error in KiemTraTaiKhoan: " . $e->getMessage());
        $_SESSION['login_error_msg'] = "Lỗi xử lý dữ liệu. Vui lòng thử lại sau.";
        return false;
    }
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($recaptchaSecretKey)) {
        $_SESSION['login_error_msg'] = "Lỗi cấu hình hệ thống (reCAPTCHA).";
        header("Location: ../view/login.php");
        exit;
    }

    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        $_SESSION['login_error_msg'] = "Vui lòng hoàn thành xác minh reCAPTCHA.";
        header("Location: ../view/login.php");
        exit;
    }

    $recaptcha_response = $_POST['g-recaptcha-response'];
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret'   => $recaptchaSecretKey,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $recaptcha_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($recaptcha_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $verify_response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        error_log("reCAPTCHA cURL Error: " . $curl_error);
        $_SESSION['login_error_msg'] = "Lỗi kết nối dịch vụ reCAPTCHA.";
        header("Location: ../view/login.php");
        exit;
    }
    $responseData = json_decode($verify_response);

    if ($responseData && $responseData->success) {
        // reCAPTCHA hợp lệ
        $database = new Connect();
        $conn = $database->getConnection();

        $email = $_POST["email"] ?? '';
        $pass = $_POST["password"] ?? '';

        if (empty($email) || empty($pass)) {
            $_SESSION['login_error_msg'] = "Vui lòng nhập email và mật khẩu.";
            header("Location: ../view/login.php");
            exit;
        }

        $userData = KiemTraTaiKhoan($email, $pass, $conn);

        if ($userData) {
            // Email, password, và tài khoản đã xác thực (is_verified == 1) là OK.
            // Bây giờ tạo và gửi OTP.
            $otp = rand(100000, 999999); // Tạo OTP 6 chữ số
            $otp_expires_at = date('Y-m-d H:i:s', time() + (5 * 60)); // OTP hết hạn sau 5 phút
            $otp_created_at = date('Y-m-d H:i:s');

            try {
                $stmt_otp = $conn->prepare("UPDATE username SET otp_code = :otp_code, otp_expires_at = :otp_expires_at, otp_created_at = :otp_created_at WHERE id = :user_id");
                // QUAN TRỌNG: Trong môi trường production, bạn NÊN hash mã OTP trước khi lưu:
                // $hashed_otp = password_hash((string)$otp, PASSWORD_DEFAULT);
                // $stmt_otp->bindParam(':otp_code', $hashed_otp);
                // Vì mục đích demo, tạm lưu plaintext:
                $stmt_otp->bindParam(':otp_code', $otp);
                $stmt_otp->bindParam(':otp_expires_at', $otp_expires_at);
                $stmt_otp->bindParam(':otp_created_at', $otp_created_at);
                $stmt_otp->bindParam(':user_id', $userData['id']);

                if ($stmt_otp->execute()) {
                    if (sendOtpEmail($userData['email'], $otp, $userData['email'] /* Hoặc tên người dùng nếu có */)) {
                        $_SESSION['otp_pending_user_email'] = $userData['email']; // Lưu email để dùng ở trang xác thực OTP
                        $_SESSION['otp_pending_user_id'] = $userData['id'];      // Lưu id để dùng ở trang xác thực OTP
                        $_SESSION['otp_remember_me'] = isset($_POST['remember']); // Lưu trạng thái "remember me"

                        // Chuyển hướng đến trang nhập OTP
                        header("Location: ../view/verify_otp.php");
                        exit;
                    } else {
                        $_SESSION['login_error_msg'] = "Không thể gửi mã OTP qua email. Vui lòng thử lại.";
                        header("Location: ../view/login.php");
                        exit;
                    }
                } else {
                    $_SESSION['login_error_msg'] = "Lỗi hệ thống khi lưu OTP. Vui lòng thử lại.";
                    error_log("DB Error storing OTP (execute failed) for user ID: " . $userData['id']);
                    header("Location: ../view/login.php");
                    exit;
                }
            } catch (PDOException $e) {
                error_log("DB Error storing OTP: " . $e->getMessage());
                $_SESSION['login_error_msg'] = "Lỗi hệ thống khi tạo OTP. Vui lòng thử lại.";
                header("Location: ../view/login.php");
                exit;
            }
        } else {
            // Hàm KiemTraTaiKhoan đã set $_SESSION['login_error_msg']
            header("Location: ../view/login.php");
            exit;
        }
    } else {
        // Xác minh reCAPTCHA thất bại
        $errorMessage = "Xác minh reCAPTCHA thất bại. Vui lòng thử lại.";
        if ($responseData && !empty($responseData->{'error-codes'})) {
            $errorMessage .= " Lỗi: " . implode(', ', $responseData->{'error-codes'});
            error_log("reCAPTCHA error codes: " . implode(', ', $responseData->{'error-codes'}));
        }
        $_SESSION['login_error_msg'] = $errorMessage;
        header("Location: ../view/login.php");
        exit;
    }
} else {
    // Không phải POST request
    header("Location: ../view/login.php");
    exit;
}
?>
