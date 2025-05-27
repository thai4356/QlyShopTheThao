<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$projectRootPath = __DIR__ . '/../..';

// 1. Require Autoloader for Dotenv
require_once $projectRootPath . '/config/autoloader.php';

// 2. Load Environment Variables (.env file in project root)
try {
    $dotenv = Dotenv\Dotenv::createImmutable($projectRootPath);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    error_log("Lỗi khi nạp file .env: " . $e->getMessage());
    die("Không thể nạp cấu hình môi trường. Vui lòng kiểm tra file .env. Chi tiết: " . $e->getMessage());
} catch (Exception $e) { // Catch other general exceptions from Dotenv
    error_log("Lỗi không xác định khi nạp file .env: " . $e->getMessage());
    die("Lỗi không xác định khi nạp cấu hình môi trường. Chi tiết: " . $e->getMessage());
}

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require_once "../model/Connect.php"; // chỉ include, không gán vào $conn

$recaptchaSecretKey = $_ENV['RECAPTCHA_SECRET_KEY'] ?? null;

$database = new Connect();
$conn = $database->getConnection(); // chính xác: đây là object PDO


$email = $_REQUEST["email"];
$pass = $_REQUEST["password"];

function sendVerificationEmail($email, $token) {
    $mail = new PHPMailer(true);
    try {
        // Cấu hình SMTP
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();  //Send using SMTP
        $mail->Host = 'smtp.gmail.com';  //Change this if it's different
        $mail->SMTPAuth = true;  //Enable SMTP authentication
        $mail->Username = 'thaitqth2210015@fpt.edu.vn';  //SMTP username
        $mail->Password = 'wqjo ryit lhgs regq';  //SMTP password
        $mail->SMTPSecure = 'ssl';  // for SMTPS (SSL encryption)
        $mail->Port = 465;  //Port for SMTP (465 for TLS or 587 for STARTTLS)

        //Recipients
        $mail->setFrom('thaitqth2210015@fpt.edu.vn', 'Mailer');
        $mail->addAddress($email);  //Send to user email
        $mail->addReplyTo($email, 'Information');

        //Content
        $mail->isHTML(true);  //Set email format to HTML
        $mail->Subject = 'Xac thuc tai khoan';

        $mail->Body = "Nhấn vào <a href='http://localhost/kiemtra2-2/src/controller/verify.php?email=$email&token=$token'>đây</a> để xác thực tài khoản.";

        $mail->AltBody = 'Bam vao de xac thuc tai khoan: http://localhost/kiemtra2-2/src/view/resetpassword.php?token=' . $token;


        $mail->send();
        header("Location: ../view/success_reset_log.php?email=" . urlencode($email));
        exit();
    } catch (Exception $e) {
        echo "Không thể gửi email xác thực. Lỗi: {$mail->ErrorInfo}";
    }
}

function Add($email, $pass, $conn) {
    try {
        $options = ['cost' => 12];
        $pass = password_hash($pass, PASSWORD_BCRYPT, $options);
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $stmt = $conn->prepare("INSERT INTO username (email, password, is_verified, verify_token ,roleid) VALUES (:email, :password, 0, :token , 2)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $pass);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        sendVerificationEmail($email, $token);
        echo "Tạo tài khoản thành công. Vui lòng kiểm tra email để xác thực.";
    } catch(PDOException $e) {
        echo $e->getMessage();
        echo "Email đã tồn tại nếu bạn chưa xác thực thì hãy kiểm tra gmail nếu quên mật khẩu thì hãy đặt lại: ";
    }
    $conn = null;
}

// --- Main Script Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST["email"] ?? ''; // Use POST, and null coalescing for safety
    $pass = $_POST["password"] ?? '';

    // Validate inputs server-side as well
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Vui lòng cung cấp một địa chỉ email hợp lệ.";
        exit;
    }
    if (empty($pass) || strlen($pass) < 5) { // Match your client-side validation (minlength 5)
        echo "Mật khẩu phải có ít nhất 5 ký tự.";
        exit;
    }

    // 1. Check if reCAPTCHA Secret Key is configured
    if (empty($recaptchaSecretKey)) {
        error_log("RECAPTCHA Secret Key chưa được cấu hình trong file .env.");
        echo "Lỗi cấu hình hệ thống (reCAPTCHA key). Vui lòng thử lại sau.";
        exit;
    }

    // 2. Check if reCAPTCHA response was sent
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        echo "Vui lòng hoàn thành xác minh reCAPTCHA.";
        exit;
    }
    $recaptcha_response_token = $_POST['g-recaptcha-response'];

    // 3. Verify reCAPTCHA token with Google
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret'   => $recaptchaSecretKey,
        'response' => $recaptcha_response_token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] // Optional but recommended
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $recaptcha_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($recaptcha_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set a timeout
    $verify_response_json = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        error_log("reCAPTCHA cURL Error: " . $curl_error);
        echo "Lỗi kết nối đến dịch vụ reCAPTCHA. Vui lòng thử lại sau.";
        exit;
    }

    $responseData = json_decode($verify_response_json);

    // 4. Process based on reCAPTCHA verification result
    if ($responseData && $responseData->success) {
        // reCAPTCHA is valid, proceed with user registration
        Add($email, $pass, $conn); // Pass the PDO connection object
    } else {
        // reCAPTCHA verification failed
        $error_message = "Xác minh reCAPTCHA thất bại. Vui lòng thử lại.";
        if ($responseData && !empty($responseData->{'error-codes'})) {
            $error_message .= " Lỗi: " . implode(', ', $responseData->{'error-codes'});
            error_log("reCAPTCHA error codes: " . implode(', ', $responseData->{'error-codes'}));
        }
        echo $error_message;
        exit;
    }

} else {
    // If not a POST request, redirect to registration page or show an error
    header("Location: ../view/login.php"); // Redirect to the page with the form
    exit;
}

