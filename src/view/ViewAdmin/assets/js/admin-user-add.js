$(document).ready(function() {
    const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
    const addUserForm = $('#addUserForm');
    let isFormDirty = false; // Cờ để kiểm tra xem form đã bị thay đổi chưa
    let forceClose = false; // Cờ để buộc đóng modal sau khi xác nhận hủy

    // Đánh dấu là form đã thay đổi khi người dùng nhập liệu
    addUserForm.on('input', function() {
        isFormDirty = true;
    });

    // Xử lý khi nhấn nút "Lưu"
    $('#save-user-btn').on('click', function() {
        // --- VALIDATION PHÍA CLIENT ---
        const email = $('#userEmail').val().trim();
        const password = $('#userPassword').val();
        const confirmPassword = $('#userConfirmPassword').val();
        const roleId = $('#userRole').val();

        if (!email || !password || !confirmPassword) {
            Swal.fire('Lỗi!', 'Vui lòng điền đầy đủ thông tin.', 'error');
            return;
        }
        if (password !== confirmPassword) {
            Swal.fire('Lỗi!', 'Mật khẩu xác nhận không khớp.', 'error');
            return;
        }
        // --- KẾT THÚC VALIDATION ---

        Swal.fire({
            title: 'Bạn chắc chắn chứ?',
            text: "Bạn sắp thêm một người dùng mới vào hệ thống.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Vâng, thêm mới!',
            cancelButtonText: 'Xem lại'
        }).then((result) => {
            if (result.isConfirmed) {
                // **THAY THẾ TODO BẰNG AJAX ĐẾN BACKEND**
                $.ajax({
                    url: 'index.php?ctrl=adminuser&act=ajaxAddUser',
                    type: 'POST',
                    data: {
                        email: email,
                        password: password,
                        roleId: roleId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            forceClose = true; // Cho phép đóng modal
                            addUserModal.hide();
                            Swal.fire('Thành công!', response.message, 'success');
                            // Tải lại bảng để hiển thị người dùng mới
                            $('#users-table').DataTable().ajax.reload();
                        } else {
                            // Hiển thị lỗi từ server
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

    // Xử lý sự kiện KHI MODAL BẮT ĐẦU ĐÓNG
    $('#addUserModal').on('hide.bs.modal', function(e) {
        // Nếu form có thay đổi và không phải là do bấm nút LƯU thành công
        if (isFormDirty && !forceClose) {
            e.preventDefault(); // Ngăn modal đóng lại

            Swal.fire({
                title: 'Hủy bỏ thay đổi?',
                text: "Các thông tin bạn đã nhập sẽ không được lưu. Bạn có muốn tiếp tục?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Vâng, hủy bỏ!',
                cancelButtonText: 'Ở lại'
            }).then((result) => {
                if (result.isConfirmed) {
                    forceClose = true; // Đặt cờ để cho phép đóng
                    addUserModal.hide(); // Đóng modal
                }
            });
        }
    });

    // Xử lý sự kiện KHI MODAL ĐÃ ĐƯỢC ĐÓNG HOÀN TOÀN
    $('#addUserModal').on('hidden.bs.modal', function() {
        // Reset lại form và các cờ
        addUserForm[0].reset();
        isFormDirty = false;
        forceClose = false;
        // Xóa các lớp validation (nếu có)
        addUserForm.find('.is-invalid').removeClass('is-invalid');
    });
});