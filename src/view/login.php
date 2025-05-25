

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

<body style="background-color: #3c3f41">
<div class="container" id="container">

    <div id="recaptchaModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; flex-direction:column;">
        <div style="background-color:white; padding:20px 40px; border-radius:8px; text-align:center; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
            <h4 style="margin-top:0; margin-bottom:15px; font-family: 'Montserrat', sans-serif; font-size:18px;">Verify you are human</h4>
            <div class="g-recaptcha"
                 data-sitekey="6LcU8UcrAAAAAMqwXwSK3F45YFjaaiBlMtd2CWtT"
                 data-callback="onRecaptchaSuccess"
                 style="margin: 15px auto; transform:scale(0.9);transform-origin:50% 50%;">
            </div>
            <button type="button" id="closeRecaptchaModal" style="margin-top:10px; padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; background-color: #f0f0f0; cursor:pointer;">Close</button>
        </div>
    </div>

    <div class="form-container sign-up-container">
        <form action="../controller/xulyDangKi.php" method="Post" enctype="multipart/form-data" >
            <h1>Create Account</h1>
            <input type="email" name="email" id="email" placeholder="email" class="form-control" required/>
            <span class="form-message"></span>
            <input type="password" name="password" id="password" placeholder="Password" class="form-control" required minlength="5">
            <input type="submit" value="Sign Up" id="b1_signup"> </form>
    </div>
    <div class="form-container sign-in-container">
        <form action="../controller/xulyDangNhap.php" method="post" enctype="multipart/form-data" class="form" id="signInForm"> <h1>Sign in</h1>
            <span>or use your account</span>

            <div class="form-group">
                <input name="email" id="email_signin" type="text" placeholder="Email" class="form-control"
                       value="<?php echo isset($_COOKIE['email']) ? htmlspecialchars($_COOKIE['email']) : ''; ?>"> <span class="form-message"></span> </div>
            <div class="form-group">
                <input name="password" id="password_signin" type="password" placeholder="Password" class="form-control"> <span class="form-message"></span> </div>

            <label class="checkbox-custom">
                <input type="checkbox" id="showPassword"> <span class="checkmark"></span> Show Password
            </label>

            <label class="checkbox-custom">
                <input type="checkbox" name="remember"
                    <?php echo isset($_COOKIE['email']) ? 'checked' : ''; ?>> <span class="checkmark"></span> Remember Me
            </label>

            <a href="ForgotPassword.php">Forgot your password?</a> <input type="submit" id="b1" value="Sign in"> </form>
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

    /* Custom styles for modal close button if needed */
    #closeRecaptchaModal {
        background-color: #6c757d; /* A neutral color */
        border-color: #6c757d;
        padding: 10px 20px; /* Adjust padding */
    }
    #closeRecaptchaModal:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

</style>
<script>
    // Global function for reCAPTCHA success callback
    function onRecaptchaSuccess(token) {
        const signInFormElement = document.getElementById('signInForm');
        console.log('reCAPTCHA success. Token:', token);

        // Add the token as a hidden input to the form
        let recaptchaInput = signInFormElement.querySelector('input[name="g-recaptcha-response"]');
        if (!recaptchaInput) {
            recaptchaInput = document.createElement('input');
            recaptchaInput.setAttribute('type', 'hidden');
            recaptchaInput.setAttribute('name', 'g-recaptcha-response');
            signInFormElement.appendChild(recaptchaInput);
        }
        recaptchaInput.setAttribute('value', token);

        const recaptchaModal = document.getElementById('recaptchaModal');
        if (recaptchaModal) {
            recaptchaModal.style.display = 'none';
        }
        // Submit the form (Validator.js already prevented default submission)
        signInFormElement.submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // const v2SiteKey = "6LcU8UcrAAAAAMqwXwSK3F45YFjaaiBlMtd2CWtT"; // Site key is in the div's data-sitekey
        const signInFormElement = document.getElementById('signInForm');
        const recaptchaModal = document.getElementById('recaptchaModal');
        const closeRecaptchaModalButton = document.getElementById('closeRecaptchaModal');

        // Validator cho form đăng ký (#form-1) - Giữ nguyên từ code của bạn
        if (document.getElementById('form-1')) {
            Validator({
                form: '#form-1',
                formGroupSelector: '.form-group',
                errorSelector: '.form-message',
                rules: [
                    Validator.isEmail('#email'),
                    Validator.minLength('#password', 6),
                ],
                onSubmit: function (data) {
                    console.log('Dữ liệu form đăng ký:', data);
                    document.getElementById('form-1').submit();
                }
            });
        }

        // SỬA ĐỔI Validator cho form đăng nhập (signInForm)
        if (signInFormElement) {
            Validator({
                form: '#signInForm',
                formGroupSelector: '.form-group', //
                errorSelector: '.form-message',   //
                rules: [
                    Validator.isEmail('#email_signin'),       //
                    Validator.minLength('#password_signin', 1) //
                ],
                onSubmit: function (data_form_validator) {
                    // Hàm này được gọi khi các trường email/password hợp lệ (theo Validator.js)
                    // Validator.js đã gọi e.preventDefault() trên sự kiện submit của form.
                    console.log('Email/Password hợp lệ. Hiển thị reCAPTCHA modal.');
                    if (recaptchaModal) {
                        recaptchaModal.style.display = 'flex'; // Hiển thị modal
                        // reCAPTCHA sẽ tự render vì nó đã được phân tích bởi api.js khi trang tải
                        // và giờ đây nó đã hiển thị.
                        // Nếu reCAPTCHA đã được giải và modal bị ẩn/hiện lại, có thể cần reset.
                        // Tuy nhiên, callback `onRecaptchaSuccess` sẽ xử lý việc submit.
                        // Nếu người dùng đóng modal và thử lại, reCAPTCHA sẽ sẵn sàng cho lần thử mới.
                        if (typeof grecaptcha !== 'undefined' && grecaptcha.reset) {
                            // Cân nhắc reset reCAPTCHA ở đây nếu cần thiết mỗi khi modal mở
                            // grecaptcha.reset();
                        }
                    }
                }
            });
        }

        // Toggle hiển thị mật khẩu cho form đăng nhập - Giữ nguyên từ code của bạn
        const passwordSigninInput = document.getElementById('password_signin');
        const togglePasswordButton = document.getElementById('showPassword');
        if (passwordSigninInput && togglePasswordButton) {
            togglePasswordButton.addEventListener('change', function () {
                passwordSigninInput.type = this.checked ? 'text' : 'password';
            });
        }

        // Xử lý chuyển đổi giữa Sign In và Sign Up - Giữ nguyên từ code của bạn
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

        // Event listener cho nút đóng modal
        if (closeRecaptchaModalButton && recaptchaModal) {
            closeRecaptchaModalButton.addEventListener('click', function() {
                recaptchaModal.style.display = 'none';
                // Khi đóng thủ công, reset reCAPTCHA để người dùng phải giải lại nếu mở lại.
                if (typeof grecaptcha !== 'undefined' && grecaptcha.reset) {
                    try {
                        grecaptcha.reset();
                    } catch (e) {
                        console.error("Error resetting reCAPTCHA: ", e);
                    }
                }
            });
        }
    });
</script>
