<?php
session_start();
$conn = require_once "../model/Connect.php";
$email = $_REQUEST["email"];
$pass = $_REQUEST["password"];

function KiemTraTaiKhoan($email, $pass,$conn)
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
        echo "DB Error: " . $e->getMessage();
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

    if ($row["roleid"] == 2) {
        header("Location: ../view/adminView.php");
        exit;
    } else {
        header("Location: ../view/UserView.php");
        exit;
    }
}
?>
