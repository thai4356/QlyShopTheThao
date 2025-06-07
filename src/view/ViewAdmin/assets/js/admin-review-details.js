$(document).ready(function() {
    // Bắt sự kiện submit của form phản hồi
    $('#reply-form').on('submit', function(event) {
        // Ngăn chặn hành vi mặc định của form (tải lại trang)
        event.preventDefault();

        // Lấy dữ liệu từ form
        var formData = $(this).serialize();
        var submitButton = $(this).find('button[type="submit"]');

        // Vô hiệu hóa nút bấm để tránh double-click
        submitButton.prop('disabled', true).text('Đang lưu...');

        // Gửi yêu cầu AJAX
        $.ajax({
            url: 'index.php?ctrl=adminreview&act=ajaxSubmitReply',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message,
                        timer: 2000, // Tự động đóng sau 2 giây
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối',
                    text: 'Không thể gửi yêu cầu đến máy chủ.'
                });
            },
            complete: function() {
                // Kích hoạt lại nút bấm sau khi hoàn tất
                submitButton.prop('disabled', false).text('Lưu Phản Hồi');
            }
        });
    });
});