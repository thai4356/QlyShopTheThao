<?php
$conn = require_once "../model/Connect.php";
$database = new Connect();
$conn = $database->getConnection();
$email = $_REQUEST["email"];
$password= $_REQUEST["password"];
function Update($u,$p,$conn)
{
    try {
        $p = md5($p);
        $sql = "UPDATE user SET password = '$p' WHERE email = '$u'";
        // Prepare statement
        /** @var PDO $conn */
        $stmt = $conn->prepare($sql);
        // execute the query
        $stmt->execute();
        // echo a message to say the UPDATE succeeded
        if($stmt->rowCount()==0){
            echo"Failed";
        }
        else{
            echo"success";
        }
        echo "<a href='../view/login.php'>Dang nhap lai ngay</a>";
    } catch (PDOException $e) {
//        echo "Change password failed" . "<a href='../no%20need/ForgotPassword.php'>CLick</a>";
        echo $e->getMessage();

    }
}
$conn = null;

Update($email,$password,$conn);

