$(document).ready(function() {
    $('#toggle-status-btn').on('click', function() {
        const userId = $(this).data('user-id');
        const currentStatus = $(this).data('current-status');
        const isActivating = currentStatus === 0;
        const actionText = isActivating ? 'Kích hoạt' : 'Vô hiệu hóa';
        const newStatus = isActivating ? 1 : 0;

        Swal.fire({
            title: `Bạn chắc chắn muốn ${actionText.toLowerCase()}?`,
            text: `Bạn sắp ${actionText.toLowerCase()} tài khoản người dùng này.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Vâng, ${actionText.toLowerCase()}!`,
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                // **THAY THẾ TODO BẰNG AJAX**
                $.ajax({
                    url: 'index.php?ctrl=adminuser&act=toggleUserStatus',
                    type: 'POST',
                    data: {
                        id: userId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Thành công!', response.message, 'success')
                                .then(() => {
                                    // Tải lại trang để cập nhật trạng thái nút bấm
                                    location.reload();
                                });
                        } else {
                            Swal.fire('Lỗi!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Lỗi!', 'Không thể kết nối đến máy chủ.', 'error');
                    }
                });
            }
        });
    });
});