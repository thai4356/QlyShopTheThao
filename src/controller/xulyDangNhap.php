<?php
session_start();
require_once __DIR__ . '/../../config/autoloader.php';
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

$recaptchaSecretKey = isset($_ENV['RECAPTCHA_SECRET_KEY']) ? $_ENV['RECAPTCHA_SECRET_KEY'] : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['g-recaptcha-response'])) {
        $recaptcha_response = $_POST['g-recaptcha-response'];

        if (empty($recaptchaSecretKey)) {
            error_log("RECAPTCHA Secret Key chưa được cấu hình trong file .env."); // Log vẫn có thể giữ nguyên "V2" nếu bạn muốn
            echo "Lỗi cấu hình hệ thống (reCAPTCHA key). Vui lòng thử lại sau.";
            exit;
        }

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
            echo "Lỗi kết nối đến dịch vụ reCAPTCHA: " . htmlspecialchars($curl_error);
            exit;
        }

        $responseData = json_decode($verify_response);

        // THAY ĐỔI LOGIC KIỂM TRA CHO RECAPTCHA V2
        if ($responseData && $responseData->success) {
            // reCAPTCHA v2 hợp lệ
            $database = new Connect();
            $conn = $database->getConnection();

            $email = isset($_POST["email"]) ? $_POST["email"] : '';
            $pass = isset($_POST["password"]) ? $_POST["password"] : '';

            if (empty($email) || empty($pass)) {
                echo "Vui lòng nhập email và mật khẩu.";
                exit;
            }

            $row = KiemTraTaiKhoan($email, $pass, $conn); // Hàm KiemTraTaiKhoan giữ nguyên

            if ($row) {
                $_SESSION["logined"] = "OK";
                $_SESSION["username"] = $row["email"];
                $_SESSION["role"] = $row["roleid"];
                $_SESSION["user_id"] = $row["id"];

                if (isset($_POST['remember'])) {
                    setcookie('email', $email, time() + (86400 * 7), "/");
                } else {
                    if (isset($_COOKIE['email'])) {
                        setcookie('email', '', time() - 3600, "/");
                    }
                }

                if ($row["roleid"] == 2) {
                    header("Location: ../view/adminView.php");
                    exit;
                } else {
                    header("Location: ../view/ViewUser/Menu.php");
                    exit;
                }
            } else {
                // KiemTraTaiKhoan đã echo lỗi
            }

        } else {
            // Xác minh reCAPTCHA v2 thất bại
            $errorMessage = "Xác minh reCAPTCHA thất bại. Vui lòng thử lại.";
            if ($responseData && !empty($responseData->{'error-codes'})) {
                $errorMessage .= " Lỗi: " . implode(', ', $responseData->{'error-codes'});
                error_log("reCAPTCHA v2 error codes: " . implode(', ', $responseData->{'error-codes'}));
            }
            echo $errorMessage;
            exit;
        }
    } else {
        echo "Vui lòng hoàn thành xác minh reCAPTCHA.";
        exit;
    }
} else {
    header("Location: ../view/login.php");
    exit;
}


function KiemTraTaiKhoan($email, $pass, $conn)
{
    try {
        /** @var PDO $conn */
        $stmt = $conn->prepare("SELECT * FROM username WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (password_verify($pass, $row['password'])) {
                if ($row['is_verified'] == 1) {
                    return $row;
                } else {
                    echo "Tài khoản chưa xác thực. Vui lòng kiểm tra email để xác thực tài khoản.";
                    return false;
                }
            } else {
                echo "Mật khẩu không đúng.<br>";
            }
        } else {
            echo "Email không tồn tại.<br>";
        }
    } catch (PDOException $e) {
        error_log("DB Error in KiemTraTaiKhoan: " . $e->getMessage());
        echo "Đã có lỗi xảy ra trong quá trình xử lý dữ liệu, vui lòng thử lại sau.";
    }
    return false;
}


$row = KiemTraTaiKhoan($email, $pass,$conn);

if ($row) {
    $_SESSION["logined"] = "OK";
    $_SESSION["username"] = $row["email"];
    $_SESSION["role"] = $row["roleid"];
    $_SESSION["user_id"] = $row["id"];

    if (isset($_POST['remember'])) {
        setcookie('email', $email, time() + (86400 * 7), "/"); // 7 ngày
        setcookie('password', $pass, time() + (86400 * 7), "/");
    } else {
        setcookie('email', '', time() - 3600, "/");
        setcookie('password', '', time() - 3600, "/");
    }

    if ($row["roleid"] == 1) {
        header("Location: ../view/adminView.php");
        exit;
    } else {
        header("Location: ../view/ViewUser/Index.php");
        exit;
    }
}
=======

?>
