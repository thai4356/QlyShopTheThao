// QlyShopTheThao/view/Public/JS/login_scripts.js

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
    // Đảm bảo đối tượng Validator đã được định nghĩa (ví dụ từ file register.js)
    if (typeof Validator === 'function' && signUpFormElement) {
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
    if (typeof Validator === 'function' && signInFormElement) {
        Validator({
            form: '#signInForm',
            formGroupSelector: '.form-group',
            errorSelector: '.form-message',
            rules: [
                Validator.isEmail('#email_signin'),
                Validator.minLength('#password_signin', 1) // Hoặc quy tắc phù hợp cho password đăng nhập
            ],
            onSubmit: function (data_form_validator) {
                console.log('Sign In form valid. Showing reCAPTCHA for Sign In.');
                if (recaptchaModal) {
                    recaptchaModal.style.display = 'flex';
                    // For sign-in, reCAPTCHA is auto-rendered by class in HTML. Reset if needed.
                    if (typeof grecaptcha !== 'undefined' && grecaptcha.reset) {
                        // The default reCAPTCHA (the first one on the page) doesn't have a specific widget ID
                        // unless explicitly rendered. If using the class-based auto-render,
                        // resetting might be implicit or you might need to find its ID if there are multiple.
                        // For simplicity, this reset is a general attempt.
                        // For more robust control, render the sign-in reCAPTCHA explicitly too.
                        try {
                            grecaptcha.reset(); // Tries to reset the default widget
                        } catch (e) {
                            console.warn("Could not reset default reCAPTCHA, it might not be rendered yet or might need explicit ID.");
                        }
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

    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Close button for Sign In reCAPTCHA modal
    if (closeRecaptchaModalButton && recaptchaModal) {
        closeRecaptchaModalButton.addEventListener('click', function() {
            recaptchaModal.style.display = 'none';
            // Optionally reset the Sign In reCAPTCHA
            if (typeof grecaptcha !== 'undefined' && grecaptcha.reset) {
                // Consider resetting the widget if needed, though auto-rendered might reset on its own.
            }
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