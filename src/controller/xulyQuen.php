<?php
// PHPMailer dependencies
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$conn = require_once "../model/Connect.php";
$database = new Connect();
$conn = $database->getConnection();
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

//require '../../vendor/autoload.php';  // Autoload PHPMailer via Composer

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if email exists in the database
    $query = "SELECT * FROM username WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Email exists, create a password reset token
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(50));  // Generate a random token for PHP 7.0 and higher
        } else {
            $token = bin2hex(openssl_random_pseudo_bytes(50));  // Generate a random token for older PHP versions
        }

        // Store the token in the database (you may want to store its expiration time as well)
        $update_query = "UPDATE username SET reset_token = :token WHERE email = :email";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':token', $token);
        $update_stmt->bindParam(':email', $email);
        $update_stmt->execute();


        $mail = new PHPMailer(true);
// Send email with the password reset link using PHPMailer
        try {
            //Server settings
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
            $mail->Subject = 'Password Reset Request';

            $mail->Body = 'Click <a href="https://up-summary-honeybee.ngrok-free.app/QlyShopTheThao/src/view/resetpassword.php?token=' . $token . '">here</a> to reset your password.';

            $mail->AltBody = 'Click this link to reset your password: https://up-summary-honeybee.ngrok-free.app/QlyShopTheThao/src/view/resetpassword.php?token=' . $token;


            $mail->send();
            header("Location: ../view/success_reset.php?email=" . urlencode($email));
            exit();
//            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>
