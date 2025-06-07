// view/ViewAdmin/assets/js/orders-datatable-init.js
$(document).ready(function() {
    // Lưu đối tượng DataTable vào một biến để có thể gọi lại các API của nó
    var table = $('#ordersTable').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "index.php?ctrl=adminorder&act=ajaxGetOrdersForDataTable",
            "type": "POST",
            "dataSrc": "data"
        },
        "columns": [
            { "data": "id" },
            { "data": "customer_name" },
            { "data": "customer_email" },
            {
                "data": "total_price",
                "render": function(data, type, row) {
                    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(data);
                }
            },
            {
                "data": "status",
                "render": function(data, type, row) {
                    let badgeClass = '';
                    let statusText = data.charAt(0).toUpperCase() + data.slice(1);

                    switch (data.toLowerCase()) {
                        case 'đã giao': badgeClass = 'badge-success'; break;
                        case 'đang xử lý': badgeClass = 'badge-info'; break;
                        case 'đã thanh toán': badgeClass = 'badge-warning'; break;
                        case 'hủy': badgeClass = 'badge-secondary'; break;
                        case 'thất bại': badgeClass = 'badge-danger'; break;
                        default: badgeClass = 'badge-dark';
                    }
                    return `<span class="badge ${badgeClass}">${statusText}</span>`;
                }
            },
            { "data": "payment_method" },
            {
                "data": "created_at",
                "render": function(data, type, row) {
                    if (!data) return '';
                    let date = new Date(data);
                    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
                }
            },
            {
                "data": "id",
                "orderable": false,
                "render": function(data, type, row) {
                    return `
                        <div class="form-button-action">
                            <a href="index.php?page=order_details&id=${data}" data-toggle="tooltip" title="Xem chi tiết" class="btn btn-link btn-primary btn-lg">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>
                    `;
                }
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Vietnamese.json"
        },
        "pageLength": 10,
    });

    // ===== BẮT ĐẦU: LOGIC LỌC DỮ LIỆU =====

    // Lắng nghe sự kiện 'change' trên combobox trạng thái
    $('#statusFilter').on('change', function() {
        var status = $(this).val();
        // Cột "Trạng Thái" là cột thứ 4 (chỉ số 4)
        // Dùng regex để tìm kiếm chính xác giá trị
        // Nếu giá trị là rỗng (chọn "Tất cả"), nó sẽ xóa bộ lọc
        table.column(4).search(status ? '^' + status + '$' : '', true, false).draw();
    });

    // Lắng nghe sự kiện 'change' trên combobox phương thức thanh toán
    $('#paymentFilter').on('change', function() {
        var paymentMethod = $(this).val();
        // Cột "Thanh Toán" là cột thứ 5 (chỉ số 5)
        table.column(5).search(paymentMethod ? '^' + paymentMethod + '$' : '', true, false).draw();
    });
    // ===== KẾT THÚC: LOGIC LỌC DỮ LIỆU =====

    // THÊM MỚI: Logic click vào dòng để xem chi tiết
    $('#ordersTable tbody').on('click', 'tr', function () {
        // Lấy dữ liệu của dòng được click
        var data = table.row(this).data();
        if (data) {
            // Điều hướng đến trang chi tiết
            window.location.href = `index.php?page=order_details&id=${data.id}`;
        }
    });

    // ===== THÊM MỚI LOGIC LỌC EMAIL TẠI ĐÂY =====
    $('#emailFilter').on('change', function() {
        var email = $(this).val();
        // Cột "Email" là cột thứ 2 (chỉ số 2)
        // Lần này không cần regex vì email là duy nhất, tìm kiếm thường là đủ
        table.column(2).search(email).draw();
    });

});