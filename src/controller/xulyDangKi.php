<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$conn = require_once "../model/Connect.php";
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

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
        $mail->Body = "Nhấn vào <a href='http://localhost/kiemtra2-1/src/controller/verify.php?email=$email&token=$token'>đây</a> để xác thực tài khoản.";
        $mail->AltBody = 'Bam vao de xac thuc tai khoan: http://localhost/kiemtra2-1/src/view/resetpassword.php?token=' . $token;


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

        $stmt = $conn->prepare("INSERT INTO username (email, password, is_verified, verify_token) VALUES (:email, :password, 0, :token)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $pass);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        sendVerificationEmail($email, $token);
        echo "Tạo tài khoản thành công. Vui lòng kiểm tra email để xác thực.";
    } catch(PDOException $e) {

        echo "Email đã tồn tại nếu bạn chưa xác thực thì hãy kiểm tra gmail nếu quên mật khẩu thì hãy đặt lại: ";

    }
    $conn = null;
}

if (empty($email) || empty($pass)) {
    echo "Vui lòng điền đầy đủ thông tin.";
} else {
    Add($email, $pass, $conn);
}
