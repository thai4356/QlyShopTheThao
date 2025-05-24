

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Public/CSS/register.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            <input type="submit" value="Sign Up" id="b1_signup"> </form>
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
                <input name="password" id="password_signin" type="password" placeholder="Password" class="form-control"
                       value="<?php // echo isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : ''; // Không nên lưu pass vào cookie ?>">
                <span class="form-message"></span>
            </div>

            <div class="g-recaptcha" data-sitekey="6LcU8UcrAAAAAMqwXwSK3F45YFjaaiBlMtd2CWtT" style="margin-bottom: 15px; transform:scale(0.9);transform-origin:0 0"></div>
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
        const v2SiteKey = "6LcU8UcrAAAAAMqwXwSK3F45YFjaaiBlMtd2CWtT"; // Site Key v2 bạn đang dùng cho widget

        // Validator cho form đăng ký (#form-1)
        // Đảm bảo các ID input bên trong form đăng ký là duy nhất và Validator nhắm đúng
        if (document.getElementById('form-1')) {
            Validator({
                form: '#form-1',
                formGroupSelector: '.form-group', // Đảm bảo HTML của bạn có cấu trúc này
                errorSelector: '.form-message',   // Đảm bảo HTML của bạn có cấu trúc này
                rules: [
                    // Ví dụ: Giả sử input tên đầy đủ có id="fullname_signup"
                    // Validator.isRequired('#fullname_signup', 'Vui lòng nhập tên đầy đủ của bạn'),
                    Validator.isEmail('#email'), // ID của input email trong form đăng ký
                    Validator.minLength('#password', 6), // ID của input password trong form đăng ký (đảm bảo là password, không phải password_signup)
                    // Validator.isRequired('#password_confirmation'), // Nếu có
                    // Validator.isConfirmed('#password_confirmation', function () {
                    //     return document.querySelector('#form-1 #password').value;
                    // }, 'Mật khẩu nhập lại không chính xác')
                ],
                onSubmit: function (data) {
                    console.log('Dữ liệu form đăng ký:', data);
                    // Nếu muốn reCAPTCHA cho đăng ký, bạn cũng cần thêm widget và kiểm tra tương tự
                    document.getElementById('form-1').submit(); // Hoặc xử lý AJAX
                }
            });
        }

        // Validator cho form đăng nhập (signInForm)
        const signInFormElement = document.getElementById('signInForm');
        if (signInFormElement) {
            Validator({
                form: '#signInForm', // Sử dụng đúng ID của form đăng nhập
                formGroupSelector: '.form-group', // Các input cần được bọc bởi div.form-group
                errorSelector: '.form-message',   // Cần có span.form-message sau mỗi input
                rules: [
                    Validator.isEmail('#email_signin'),       // Đúng ID input email đăng nhập
                    Validator.minLength('#password_signin', 1) // Đúng ID input password đăng nhập (min length tùy bạn)
                ],
                onSubmit: function (data_form_validator) { // Hàm này được gọi khi các rule của Validator hợp lệ
                    console.log('Form đăng nhập hợp lệ theo Validator. Kiểm tra reCAPTCHA v2...');

                    const recaptchaResponse = grecaptcha.getResponse(); // Lấy token từ widget reCAPTCHA v2

                    if (recaptchaResponse.length === 0) {
                        // Nếu người dùng chưa check reCAPTCHA
                        alert('Vui lòng xác minh bạn không phải là người máy.');
                        // Quan trọng: Ngăn chặn form submit nếu Validator của bạn không tự làm điều đó
                        // Nếu Validator của bạn vẫn submit form nếu hàm này không return false,
                        // bạn cần một cách khác để dừng nó, ví dụ như thêm một lỗi vào Validator.
                        // Hoặc, nếu Validator không tự submit, thì bạn không cần làm gì thêm ở đây.
                        return false; // Thường thì return false sẽ ngăn submit trong nhiều thư viện Validator
                    }

                    // Nếu reCAPTCHA đã được giải quyết, tiến hành submit form
                    // Nếu Validator của bạn tự động submit khi không có lỗi và hàm này không return false,
                    // thì không cần dòng submit() dưới đây.
                    // Ngược lại, nếu Validator chỉ gọi hàm này và bạn phải tự submit, thì dùng dòng dưới.
                    console.log('reCAPTCHA v2 đã được giải quyết. Đang gửi form...');
                    signInFormElement.submit();
                }
            });
        }

        // Toggle hiển thị mật khẩu cho form đăng nhập
        const passwordSigninInput = document.getElementById('password_signin'); // Đúng ID
        const togglePasswordButton = document.getElementById('showPassword');

        if (passwordSigninInput && togglePasswordButton) {
            togglePasswordButton.addEventListener('change', function () {
                passwordSigninInput.type = this.checked ? 'text' : 'password';
            });
        }

        // Xử lý chuyển đổi giữa Sign In và Sign Up (từ file của bạn)
        const signUpButtonJs = document.getElementById('signUp');
        const signInButtonJs = document.getElementById('signIn');
        const containerJs = document.getElementById('container');

        if (signUpButtonJs && signInButtonJs && containerJs) {
            signUpButtonJs.addEventListener('click', () => {
                containerJs.classList.add("right-panel-active");
            });

            signInButtonJs.addEventListener('click', () => {
                containerJs.classList.remove("right-panel-active");
            });
        }
    });
</script>
