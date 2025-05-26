<?php
session_start();

// NGĂN CACHE TRÌNH DUYỆT:
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// NẾU ĐÃ ĐĂNG NHẬP THÌ KHÔNG CHO VÀO TRANG LOGIN
if (isset($_SESSION['username'])) {
    header("Location: ../view/ViewUser/Index.php");
    exit;
}
?>



<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Public/CSS/register.css">
</head>


<!--<form action="cRegistry.php" method="post">-->
<!--    Username<input name="tusername" id="username" type="text">-->
<!--    Password<input name="tpassword" id="password" type="password">-->
<!--    <input type="submit" value="Log in">-->
<!--    chua co tai khoan ?-->
<!--    <a href="DangKi.php">Dang ki </a>-->
<!--</form>-->
<body style="background-color: #3c3f41">
<div class="container" id="container">

    <div class="form-container sign-up-container">
        <form action="../controller/xulyDangKi.php" method="Post" enctype="multipart/form-data" >
            <h1>Create Account</h1>
            <input type="email" name="email" id="email" placeholder="email" class="form-control" required/>
            <span class="form-message"></span>
            <input type="password" name="password" id="password" placeholder="Password" class="form-control" required minlength="5">
            <input type="submit" value="Sign Up" id="b1">
        </form>
    </div>
    <div class="form-container sign-in-container">

    <form action="../controller/xulyDangNhap.php" method="post" enctype="multipart/form-data" class="form">
        <h1>Sign in</h1>
        <span>or use your account</span>

        <input name="email" id="email" type="text" placeholder="Email" class="form-group"
               value="<?php echo isset($_COOKIE['email']) ? $_COOKIE['email'] : ''; ?>">

        <input name="password" id="password" type="password" placeholder="Password"
               value="<?php echo isset($_COOKIE['password']) ? $_COOKIE['password'] : ''; ?>">

        <label class="checkbox-custom">
            <input type="checkbox" id="showPassword">
            <span class="checkmark"></span>
                Show Password
        </label>


        <label class="checkbox-custom">
            <input type="checkbox" name="remember" 
                <?php echo isset($_COOKIE['email']) ? 'checked' : ''; ?>>
            <span class="checkmark"></span>
            Remember Me
        </label>

        <a href="ForgotPassword.php">Forgot your password?</a>
        <input type="submit" id="b1" value="Sign in">
    </form>

    </div>
    <div class="overlay-container">
        <div class="overlay">
            <!--            //di chuyen-->
            <div class="overlay-panel overlay-left">
                <h1>Already have an account ?!</h1>
                <p>Log in now to become healthy</p>
                <button class="ghost" id="signIn">Sign In</button>
            </div>
            <!--            //di chuyen-->
            <div class="overlay-panel overlay-right">
                <h1>Want to become better now ?</h1>
                <p>Enter your personal details and start journey with us</p>
                <button class="ghost" id="signUp">Sign Up</button>
            </div>

        </div>
    </div>
</div>
</body>

</html>
<script src="Public/JS/register.js"></script>

<style>
    #b1:hover{
        color: red;
        border: red;
        border-width:1px ;
        border-style: solid;
    }
    button {
        border-radius: 20px;
        border: 1px solid #FF4B2B;
        background-color: #FF4B2B;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: bold;
        padding: 12px 45px;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: transform 80ms ease-in;
    }

    button:active {
        transform: scale(0.95);
    }

    button:focus {
        outline: none;
    }

    button.ghost {
        background-color: transparent;
        border-color: #FFFFFF;
    }

</style>
<script>

    document.addEventListener('DOMContentLoaded', function () {
        // Mong muốn của chúng ta
        Validator({
            form: '#form-1',
            formGroupSelector: '.form-group',
            errorSelector: '.form-message',
            rules: [
                Validator.isRequired('#fullname', 'Vui lòng nhập tên đầy đủ của bạn'),
                Validator.isEmail('#email'),
                Validator.minLength('#password', 6),
                Validator.isRequired('#password_confirmation'),
                Validator.isConfirmed('#password_confirmation', function () {
                    return document.querySelector('#form-1 #password').value;
                }, 'Mật khẩu nhập lại không chính xác')
            ],
            onSubmit: function (data) {
                // Call API
                console.log(data);
            }
        });

        Validator({
            form: '#form-2',
            formGroupSelector: '.form-group',
            errorSelector: '.form-message',
            rules: [
                Validator.isEmail('#email'),
                Validator.minLength('#password', 6),
            ],
            onSubmit: function (data) {
                // Call API
                console.log(data);
            }
        });

        // Toggle hiển thị mật khẩu
        const passwordInput = document.querySelector('.sign-in-container #password');
        const togglePassword = document.getElementById('showPassword');

        if (togglePassword) {
            togglePassword.addEventListener('change', function () {
                passwordInput.type = this.checked ? 'text' : 'password';
            });
        }

    });

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

</script>



