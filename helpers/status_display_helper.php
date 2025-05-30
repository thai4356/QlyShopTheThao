<?php
if (!function_exists('getOrderStatusDisplay')) {
    function getOrderStatusDisplay($status) {
        // Chuẩn hóa trạng thái về chữ thường và loại bỏ khoảng trắng thừa
        $normalizedStatus = strtolower(trim((string)$status));

        switch ($normalizedStatus) {
            case 'pending': // Trạng thái chờ xử lý chung (ví dụ: COD chờ xác nhận)
                return ['text' => 'Đang xử lý', 'class' => 'status-pending'];
            case 'pending_payos': // Trạng thái tùy chỉnh: Đơn hàng đã tạo, chờ thanh toán PayOS
                return ['text' => 'Chờ thanh toán PayOS', 'class' => 'status-pending-payment']; // Class CSS mới

            case 'confirmed': // Đã xác nhận (ví dụ: COD đã xác nhận, chuyển khoản đã nhận)
                return ['text' => 'Đã xác nhận', 'class' => 'status-confirmed'];
            case 'shipped': // Đang giao hàng
                return ['text' => 'Đang giao', 'class' => 'status-shipped'];

            case 'completed': // Trạng thái tùy chỉnh: Thanh toán thành công và đã xác minh (cho mọi cổng, bao gồm PayOS 'PAID')
            case 'paid':      // Trạng thái 'PAID' trực tiếp từ PayOS nếu bạn lưu trữ nó
                return ['text' => 'Hoàn thành', 'class' => 'status-completed'];

            case 'canceled':      // Trạng thái hủy chung (do admin, hoặc người dùng hủy trước khi xử lý)
            case 'cancelled':     // Trạng thái 'CANCELLED' trực tiếp từ PayOS (người dùng nhấn hủy trên cổng PayOS)
                // case 'cancelled_payos': // Trạng thái tùy chỉnh nếu bạn dùng (có thể gộp vào 'cancelled')
                return ['text' => 'Đã hủy', 'class' => 'status-canceled'];

            case 'failed':        // Trạng thái 'FAILED' trực tiếp từ PayOS, hoặc trạng thái tùy chỉnh 'failed_payos'
                // case 'failed_payos': // Trạng thái tùy chỉnh (có thể gộp vào 'failed')
                return ['text' => 'Thanh toán thất bại', 'class' => 'status-failed']; // Class CSS mới

            case 'expired':       // Trạng thái 'EXPIRED' trực tiếp từ PayOS nếu link thanh toán hết hạn
                // case 'expired_payos': // Trạng thái tùy chỉnh (có thể gộp vào 'expired')
                return ['text' => 'Đã hết hạn (PayOS)', 'class' => 'status-expired']; // Class CSS mới

            // Xử lý các trạng thái cụ thể của VNPay nếu có
            case 'failed_vnpay':
                return ['text' => 'Thanh toán thất bại (VNPay)', 'class' => 'status-failed'];
            case 'cancelled_vnpay': // Giả sử bạn có trạng thái này
                return ['text' => 'Đã hủy (VNPay)', 'class' => 'status-canceled'];

            default:
                return ['text' => ucfirst($normalizedStatus), 'class' => 'status-default']; // Trạng thái mặc định
        }
    }
}
?>