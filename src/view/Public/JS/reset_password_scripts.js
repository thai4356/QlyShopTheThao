// QlyShopTheThao/view/Public/JS/reset_password_scripts.js
document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.getElementById('resetPasswordForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(event) {
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const errorMessageElement = document.getElementById('passwordMatchError');

            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Xóa thông báo lỗi cũ (nếu có)
            if (errorMessageElement) {
                errorMessageElement.style.display = 'none';
                errorMessageElement.textContent = ''; // Xóa nội dung lỗi cũ
            }

            if (newPassword.length < 5) {
                if(errorMessageElement) {
                    errorMessageElement.textContent = 'Mật khẩu phải có ít nhất 5 ký tự.';
                    errorMessageElement.style.display = 'block';
                }
                event.preventDefault(); // Ngăn form submit
                newPasswordInput.focus(); // Focus vào trường mật khẩu mới
                return;
            }

            if (newPassword !== confirmPassword) {
                if(errorMessageElement) {
                    errorMessageElement.textContent = 'Mật khẩu không khớp. Vui lòng thử lại.';
                    errorMessageElement.style.display = 'block';
                }
                event.preventDefault(); // Ngăn form submit
                confirmPasswordInput.focus(); // Focus vào trường xác nhận mật khẩu
            } else {
                // Mật khẩu khớp, không làm gì thêm, form sẽ được submit
            }
        });
    }
});