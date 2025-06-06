$(document).ready(function () {
    // Khởi tạo DataTable
    var usersTable = $('#users-table').DataTable({
        "processing": true, // Hiển thị thông báo "đang xử lý"
        "serverSide": true, // Bật chế độ xử lý phía server
        "ajax": {
            "url": "index.php?ctrl=adminuser&act=ajaxGetUsersForDataTable", // URL để lấy dữ liệu
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "email" },
            {
                "data": "is_verified",
                "render": function (data, type, row) {
                    // Render huy hiệu cho trạng thái xác thực
                    if (data == 1) {
                        return '<span class="badge bg-success">Đã xác thực</span>';
                    } else {
                        return '<span class="badge bg-warning">Chưa xác thực</span>';
                    }
                }
            },
            {
                "data": "role",
                "render": function (data, type, row) {
                    // Render huy hiệu cho vai trò
                    if (data.toLowerCase() === 'admin') {
                        return '<span class="badge bg-danger">Admin</span>';
                    } else {
                        return '<span class="badge bg-info">User</span>';
                    }
                }
            },
            {
                "data": "is_active",
                "render": function (data, type, row) {
                    // Render huy hiệu cho trạng thái hoạt động
                    if (data == 1) {
                        return '<span class="badge bg-primary">Đang hoạt động</span>';
                    } else {
                        return '<span class="badge bg-secondary">Bị vô hiệu hóa</span>';
                    }
                }
            },
            {
                "data": null,
                "orderable": false,
                "render": function (data, type, row) {
                    let viewButton = `<button class="btn btn-info btn-sm btn-icon" data-bs-toggle="tooltip" title="Xem chi tiết" onclick="viewUser(${row.id})"><i class="fa fa-eye"></i></button>`;

                    // **SỬA Ở ĐÂY: Khởi tạo nút là chuỗi rỗng**
                    let toggleStatusButton = '';

                    // **THÊM MỚI: Chỉ hiển thị nút Vô hiệu hóa/Kích hoạt nếu user này KHÔNG PHẢI là admin đang đăng nhập**
                    if (row.id !== MyAppAdmin.config.currentUserId) {
                        if (row.is_active == 1) {
                            toggleStatusButton = `<button class="btn btn-warning btn-sm btn-icon" data-bs-toggle="tooltip" title="Vô hiệu hóa" onclick="toggleUserStatus(${row.id}, 0)"><i class="fa fa-lock"></i></button>`;
                        } else {
                            toggleStatusButton = `<button class="btn btn-success btn-sm btn-icon" data-bs-toggle="tooltip" title="Kích hoạt" onclick="toggleUserStatus(${row.id}, 1)"><i class="fa fa-unlock"></i></button>`;
                        }
                    }

                    return `<div class="form-button-action">${viewButton} ${toggleStatusButton}</div>`;
                }
            }
        ],
        "language": { // Việt hóa các chuỗi trong DataTable
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json"
        },
        "drawCallback": function(settings) {
            // Khởi tạo lại tooltip sau mỗi lần vẽ lại bảng để các nút mới có tooltip
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    // **THÊM MỚI: Bắt sự kiện click vào một dòng của bảng**
    $('#users-table tbody').on('click', 'tr', function (e) {
        // Chỉ xử lý khi click vào ô (td), không phải nút bấm bên trong
        if ($(e.target).is('td')) {
            var data = usersTable.row(this).data();
            if (data) {
                // Điều hướng đến trang chi tiết
                window.location.href = `index.php?page=user_details&id=${data.id}`;
            }
        }
    });
});

// Các hàm xử lý sự kiện (sẽ được implement ở các bước sau)
function viewUser(userId) {
    // Ngăn sự kiện click của dòng (tr) chạy khi bấm nút này
    event.stopPropagation();
    // Điều hướng đến trang chi tiết người dùng
    window.location.href = `index.php?page=user_details&id=${userId}`;
}

function toggleUserStatus(userId, newStatus) {
    event.stopPropagation();
    const actionText = newStatus === 0 ? 'Vô hiệu hóa' : 'Kích hoạt';

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
                        Swal.fire('Thành công!', response.message, 'success');
                        // Tải lại bảng để cập nhật giao diện
                        $('#users-table').DataTable().ajax.reload(null, false);
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
}