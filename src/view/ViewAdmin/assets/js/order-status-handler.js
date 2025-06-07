$(document).ready(function() {

    // Hàm chung để gửi yêu cầu cập nhật
    function updateOrderStatus(orderId, newStatus, confirmConfig, successConfig) {
        Swal.fire({
            title: confirmConfig.title,
            text: confirmConfig.text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Vâng, xác nhận!',
            cancelButtonText: 'Không, hủy bỏ'
        }).then((result) => {
            if (result.isConfirmed) {
                // Gửi yêu cầu AJAX
                $.ajax({
                    url: 'index.php?ctrl=adminorder&act=ajaxUpdateOrderStatus',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        order_id: orderId,
                        new_status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                successConfig.title,
                                successConfig.text,
                                'success'
                            ).then(() => {
                                // Cập nhật giao diện mà không cần tải lại trang
                                const statusBadge = $('#orderStatusBadge span');
                                statusBadge.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));

                                // Đổi màu badge
                                statusBadge.removeClass('badge-info badge-warning').addClass(newStatus === 'đã giao' ? 'badge-success' : 'badge-danger');

                                // Ẩn các nút hành động đi
                                $('#processOrderBtn').hide();
                                $('#cancelOrderBtn').hide();
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
    }

    // Bắt sự kiện click cho nút "Xử lý (Giao hàng)"
    $('#processOrderBtn').on('click', function() {
        const orderId = $(this).data('order-id');
        const confirmConfig = {
            title: 'Xác nhận giao hàng?',
            text: "Bạn có chắc muốn cập nhật trạng thái đơn hàng này thành 'Đã giao' không?"
        };
        const successConfig = {
            title: 'Thành công!',
            text: 'Đơn hàng đã được cập nhật thành công.'
        };
        updateOrderStatus(orderId, 'đã giao', confirmConfig, successConfig);
    });

    // Bắt sự kiện click cho nút "Hủy đơn hàng"
    $('#cancelOrderBtn').on('click', function() {
        const orderId = $(this).data('order-id');
        const confirmConfig = {
            title: 'Xác nhận hủy đơn?',
            text: "Hành động này không thể hoàn tác. Bạn có chắc muốn hủy đơn hàng này không?"
        };
        const successConfig = {
            title: 'Đã hủy!',
            text: 'Đơn hàng đã được hủy thành công.'
        };
        updateOrderStatus(orderId, 'hủy', confirmConfig, successConfig);
    });

    // ===== THÊM MỚI SỰ KIỆN CHO NÚT HOÀN TIỀN =====
    $('#refundConfirmedBtn').on('click', function() {
        const orderId = $(this).data('order-id');
        const confirmConfig = {
            title: 'Xác nhận đã hoàn tiền?',
            text: "Hành động này chỉ nên thực hiện sau khi bạn đã hoàn tiền thành công cho khách hàng qua cổng thanh toán. Tiếp tục?"
        };
        const successConfig = {
            title: 'Đã cập nhật!',
            text: 'Đơn hàng đã được ghi nhận là đã hoàn tiền.'
        };
        // Gọi hàm chung để cập nhật, trạng thái mới là "đã hoàn tiền"
        updateOrderStatus(orderId, 'đã hoàn tiền', confirmConfig, successConfig);
    });

    // ===== THÊM MỚI SỰ KIỆN CHO NÚT HỦY ĐƠN HÀNG ĐÃ THANH TOÁN =====
    $('#cancelPaidOrderBtn').on('click', function() {
        const orderId = $(this).data('order-id');

        Swal.fire({
            title: 'Hủy và Hoàn kho?',
            html: "Hành động này chỉ nên thực hiện sau khi bạn đã <b>hoàn tiền</b> cho khách hàng qua cổng thanh toán.<br><br>Hệ thống sẽ cập nhật trạng thái đơn hàng thành 'Hủy' và <b>tự động cộng lại số lượng sản phẩm vào kho</b>.<br><br>Bạn chắc chắn muốn tiếp tục?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Vâng, xác nhận hủy!',
            cancelButtonText: 'Không'
        }).then((result) => {
            if (result.isConfirmed) {
                // Gửi yêu cầu AJAX đến action mới
                $.ajax({
                    url: 'index.php?ctrl=adminorder&act=ajaxCancelAndRestockOrder',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        order_id: orderId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Thành công!',
                                'Đơn hàng đã được hủy và sản phẩm đã được hoàn kho.',
                                'success'
                            ).then(() => {
                                // Cập nhật giao diện
                                const statusBadge = $('#orderStatusBadge span');
                                statusBadge.text('Hủy');
                                statusBadge.removeClass('badge-warning').addClass('badge-secondary');

                                // Ẩn tất cả các nút hành động
                                $('#processOrderBtn, #cancelOrderBtn, #cancelPaidOrderBtn').hide();
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