<?php
session_start();

// NGĂN CACHE TRÌNH DUYỆT:
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// NẾU ĐÃ ĐĂNG NHẬP THÌ KHÔNG CHO VÀO TRANG LOGIN
if (isset($_SESSION['username'])) {
    header("Location: ../view/ViewUser/Index.php"); // Giả sử đây là trang chủ cho user đã đăng nhập
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

    <title>Login/Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Public/CSS/register.css">
    <link rel="stylesheet" href="Public/CSS/login_styles.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body style="background-color: #3c3f41">
<div class="container" id="container">

    <div id="recaptchaModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; flex-direction:column;">
        <div style="background-color:white; padding:20px 40px; border-radius:8px; text-align:center; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
            <h4 style="margin-top:0; margin-bottom:15px; font-family: 'Montserrat', sans-serif; font-size:18px;">Verify you are human</h4>
            <div class="g-recaptcha"
                 data-sitekey="6Ldfv0srAAAAANlyHXc6UYBC2YJ0JWwIbrgHpIBW"
                 data-callback="onRecaptchaSuccess"
                 style="margin: 15px auto; transform:scale(0.9);transform-origin:50% 50%;">
            </div>
            <button type="button" id="closeRecaptchaModal" style="margin-top:10px; padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; background-color: #f0f0f0; cursor:pointer;">Close</button>
        </div>
    </div>

    <div id="recaptchaSignUpModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1001; justify-content:center; align-items:center; flex-direction:column;">
        <div style="background-color:white; padding:20px 40px; border-radius:8px; text-align:center; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
            <h4 style="margin-top:0; margin-bottom:15px; font-family: 'Montserrat', sans-serif; font-size:18px;">Verify you are human</h4>
            <div id="recaptchaWidgetSignUpContainer" style="margin: 15px auto; transform:scale(0.9);transform-origin:50% 50%;"></div>
            <button type="button" id="closeRecaptchaSignUpModal" style="margin-top:10px; padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; background-color: #f0f0f0; cursor:pointer;">Close</button>
        </div>
    </div>

    <div class="form-container sign-up-container">
        <form action="../controller/xulyDangKi.php" method="Post" enctype="multipart/form-data" id="form-1">
            <h1>Create Account</h1>
            <div class="form-group"> <input type="email" name="email" id="email" placeholder="Email" class="form-control" required/>
                <span class="form-message"></span>
            </div>
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Password" class="form-control" required minlength="5">
                <span class="form-message"></span>
            </div>
            <input type="submit" value="Sign Up" id="b1_signup">
        </form>
    </div>
    <div class="form-container sign-in-container">
        <form action="../controller/xulyDangNhap.php" method="post" enctype="multipart/form-data" class="form" id="signInForm">
            <h1>Sign in</h1>
            <span>or use your account</span>

            <div class="form-group">
                <input name="email" id="email_signin" type="text" placeholder="Email" class="form-control"
                       value="<?php echo isset($_COOKIE['email']) ? htmlspecialchars($_COOKIE['email']) : ''; ?>">
                <span class="form-message"></span>
            </div>
            <div class="form-group">
                <input name="password" id="password_signin" type="password" placeholder="Password" class="form-control">
                <span class="form-message"></span>
            </div>

            <label class="checkbox-custom">
                <input type="checkbox" id="showPassword"> <span class="checkmark"></span> Show Password
            </label>

            <label class="checkbox-custom">
                <input type="checkbox" name="remember"
                    <?php echo isset($_COOKIE['email']) ? 'checked' : ''; ?>> <span class="checkmark"></span> Remember Me
            </label>

            <a href="ForgotPassword.php">Forgot your password?</a>
            <input type="submit" id="b1" value="Sign in">
        </form>
    </div>
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Already have an account ?!</h1>
                <p>Log in now to become healthy</p>
                <button class="ghost" id="signIn">Sign In</button>
            </div>
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
<script src="Public/JS/login_scripts.js"></script>