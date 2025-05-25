

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
        /* Giữ lại hiệu ứng hover cơ bản cho nút, nhưng điều chỉnh để nhất quán hơn */
        background-color: #e04020; /* Một màu tối hơn của #FF4B2B */
        border-color: #d03010;   /* Một màu tối hơn nữa cho border khi hover */
        color: #FFFFFF;
    }
    button,input[type="submit"]#b1_signup, input[type="submit"]#b1 {
        border-radius: 20px;
        border: 1px solid #FF4B2B;
        background-color: #FF4B2B;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: bold;
        padding: 12px 45px;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: transform 80ms ease-in, background-color 0.2s, border-color 0.2s;
        cursor: pointer;
        margin: 8px 0;
    }

    input[type="submit"]#b1_signup, input[type="submit"]#b1 {
        padding: 12px 15px; /* Override padding for full-width buttons if needed, or keep 12px 45px */
        width: 100%; /* Make submit buttons full width like text inputs */
        box-sizing: border-box;
    }

    button:active,input[type="submit"]#b1_signup:active, input[type="submit"]#b1:active {
        transform: scale(0.95);
    }

    button:focus input[type="submit"]#b1_signup:focus, input[type="submit"]#b1:focus{
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

    .form-container .form-group input[type="text"],
    .form-container .form-group input[type="password"],
    .form-container .form-group input[type="email"] { /* Bao gồm cả input email nếu có */
        background-color: #eee; /* Từ register.css */
        border: 1px solid #ddd;  /* Thêm border mảnh, nhất quán */
        padding: 12px 15px;    /* Từ register.css */
        margin: 8px 0;         /* Từ register.css */
        width: 100%;           /* Từ register.css */
        box-sizing: border-box;/* Từ register.css */
        font-family: 'Montserrat', sans-serif; /* Áp dụng font Roboto */
        font-size: 14px;       /* Điều chỉnh kích thước font cho dễ đọc */
        border-radius: 4px;    /* Bo góc nhẹ cho textbox (tùy chọn) */
    }

    /* Style cho nút Sign In (id="b1") */
    #b1 {
        border-radius: 20px;
        border: 1px solid #FF4B2B; /* Border nhất quán với trạng thái thường */
        background-color: #FF4B2B;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: bold;
        padding: 12px 15px;    /* Giữ padding này để nội dung không quá sát khi width 100% */
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: transform 80ms ease-in, background-color 0.2s, border-color 0.2s;
        width: 100%;           /* Đảm bảo width 100% */
        box-sizing: border-box;/* Đảm bảo box-sizing */
        margin: 8px 0;         /* Giống các input khác */
        font-family: 'Montserrat', sans-serif; /* Giữ font Montserrat cho nút, hoặc đổi thành Roboto nếu muốn */
        cursor: pointer;
    }

</style>
<script>
    // Global reCAPTCHA Site Key (v2 Checkbox)
    const RECAPTCHA_V2_SITE_KEY = "6LcU8UcrAAAAAMqwXwSK3F45YFjaaiBlMtd2CWtT"; // Replace with your actual Site Key

    // Callback for Sign In reCAPTCHA
    function onRecaptchaSuccess(token) {
        const signInFormElement = document.getElementById('signInForm');
        console.log('Sign In reCAPTCHA success. Token:', token);
        let recaptchaInput = signInFormElement.querySelector('input[name="g-recaptcha-response"]');
        if (!recaptchaInput) {
            recaptchaInput = document.createElement('input');
            recaptchaInput.setAttribute('type', 'hidden');
            recaptchaInput.setAttribute('name', 'g-recaptcha-response');
            signInFormElement.appendChild(recaptchaInput);
        }
        recaptchaInput.setAttribute('value', token);
        const recaptchaModal = document.getElementById('recaptchaModal');
        if (recaptchaModal) recaptchaModal.style.display = 'none';
        signInFormElement.submit();
    }

    // NEW Callback for Sign Up reCAPTCHA
    function onRecaptchaSignUpSuccess(token) {
        const signUpFormElement = document.getElementById('form-1'); // Ensure this is the correct ID of your sign-up form
        console.log('Sign Up reCAPTCHA success. Token:', token);
        let recaptchaInput = signUpFormElement.querySelector('input[name="g-recaptcha-response"]');
        if (!recaptchaInput) {
            recaptchaInput = document.createElement('input');
            recaptchaInput.setAttribute('type', 'hidden');
            recaptchaInput.setAttribute('name', 'g-recaptcha-response');
            signUpFormElement.appendChild(recaptchaInput);
        }
        recaptchaInput.setAttribute('value', token);
        const recaptchaSignUpModal = document.getElementById('recaptchaSignUpModal');
        if (recaptchaSignUpModal) recaptchaSignUpModal.style.display = 'none';
        signUpFormElement.submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const signInFormElement = document.getElementById('signInForm');
        const recaptchaModal = document.getElementById('recaptchaModal');
        const closeRecaptchaModalButton = document.getElementById('closeRecaptchaModal');

        const signUpFormElement = document.getElementById('form-1'); // For sign-up form
        const recaptchaSignUpModal = document.getElementById('recaptchaSignUpModal');
        const closeRecaptchaSignUpModalButton = document.getElementById('closeRecaptchaSignUpModal');
        const recaptchaWidgetSignUpContainer = document.getElementById('recaptchaWidgetSignUpContainer');
        let signUpRecaptchaWidgetId = null; // To store widget ID for sign-up reCAPTCHA

        // Validator for Sign Up form (ID: form-1)
        if (signUpFormElement) {
            Validator({
                form: '#form-1', // Targets the sign-up form
                formGroupSelector: '.form-group',
                errorSelector: '.form-message',
                rules: [
                    Validator.isEmail('#email'), // ID of email input in sign-up form
                    Validator.minLength('#password', 5), // ID of password input in sign-up form
                ],
                onSubmit: function (data) { // Called when sign-up email/password are valid
                    console.log('Sign Up form valid. Showing reCAPTCHA for Sign Up.');
                    if (recaptchaSignUpModal && recaptchaWidgetSignUpContainer) {
                        recaptchaSignUpModal.style.display = 'flex';
                        // Render reCAPTCHA for sign-up if not already rendered or reset if needed
                        if (signUpRecaptchaWidgetId === null && typeof grecaptcha !== 'undefined' && grecaptcha.render) {
                            try {
                                signUpRecaptchaWidgetId = grecaptcha.render(recaptchaWidgetSignUpContainer, {
                                    'sitekey': RECAPTCHA_V2_SITE_KEY,
                                    'callback': 'onRecaptchaSignUpSuccess'
                                });
                            } catch (e) {
                                console.error("Error rendering sign-up reCAPTCHA: ", e);
                                alert("Could not load reCAPTCHA. Please try again later.");
                                recaptchaSignUpModal.style.display = 'none';
                            }
                        } else if (typeof grecaptcha !== 'undefined' && grecaptcha.reset && signUpRecaptchaWidgetId !== null) {
                            grecaptcha.reset(signUpRecaptchaWidgetId);
                        }
                    }
                }
            });
        }

        // Validator for Sign In form (signInForm)
        if (signInFormElement) {
            Validator({
                form: '#signInForm',
                formGroupSelector: '.form-group',
                errorSelector: '.form-message',
                rules: [
                    Validator.isEmail('#email_signin'),
                    Validator.minLength('#password_signin', 1)
                ],
                onSubmit: function (data_form_validator) {
                    console.log('Sign In form valid. Showing reCAPTCHA for Sign In.');
                    if (recaptchaModal) {
                        recaptchaModal.style.display = 'flex';
                        // For sign-in, reCAPTCHA is auto-rendered by class. Reset if needed.
                        if (typeof grecaptcha !== 'undefined' && grecaptcha.reset) {
                            // Assuming the first widget (index 0) or find specific widget ID if necessary.
                            // For the auto-rendered one, managing widget ID can be tricky without explicit render.
                            // For now, we assume it resets or provides a fresh challenge.
                            // If problems, explicitly render the sign-in one too.
                        }
                    }
                }
            });
        }

        // Toggle password visibility
        const passwordSigninInput = document.getElementById('password_signin');
        const togglePasswordButton = document.getElementById('showPassword');
        if (passwordSigninInput && togglePasswordButton) {
            togglePasswordButton.addEventListener('change', function () {
                passwordSigninInput.type = this.checked ? 'text' : 'password';
            });
        }

        // Panel switching
        const signUpButtonJs = document.getElementById('signUp');
        const signInButtonJs = document.getElementById('signIn');
        const containerJs = document.getElementById('container');
        if (signUpButtonJs && signInButtonJs && containerJs) {
            signUpButtonJs.addEventListener('click', () => containerJs.classList.add("right-panel-active"));
            signInButtonJs.addEventListener('click', () => containerJs.classList.remove("right-panel-active"));
        }

        // Close button for Sign In reCAPTCHA modal
        if (closeRecaptchaModalButton && recaptchaModal) {
            closeRecaptchaModalButton.addEventListener('click', function() {
                recaptchaModal.style.display = 'none';
                // Optionally reset the Sign In reCAPTCHA
            });
        }

        // Close button for Sign Up reCAPTCHA modal
        if (closeRecaptchaSignUpModalButton && recaptchaSignUpModal) {
            closeRecaptchaSignUpModalButton.addEventListener('click', function() {
                recaptchaSignUpModal.style.display = 'none';
                if (typeof grecaptcha !== 'undefined' && grecaptcha.reset && signUpRecaptchaWidgetId !== null) {
                    grecaptcha.reset(signUpRecaptchaWidgetId);
                }
            });
        }
    });
</script>
