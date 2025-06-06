<?php
if (!function_exists('getOrderStatusDisplay')) {
    function getOrderStatusDisplay($status) {
        $normalizedStatus = strtolower(trim((string)$status)); // Vẫn giữ để đề phòng

        switch ($normalizedStatus) {
            case 'đang xử lý':
                return ['text' => 'Đang xử lý', 'class' => 'status-pending'];
            case 'đã thanh toán':
                return ['text' => 'Đã thanh toán', 'class' => 'status-completed']; // hoặc status-paid
            case 'đã giao':
                return ['text' => 'Đã giao', 'class' => 'status-shipped']; // hoặc status-delivered
            case 'hủy':
                return ['text' => 'Đã hủy', 'class' => 'status-canceled'];
            case 'thất bại':
                return ['text' => 'Thất bại', 'class' => 'status-failed'];
            default:
                // Có thể thêm các trường hợp cho các trạng thái trung gian bạn vẫn muốn hiển thị khác biệt
                // nếu bạn quyết định không hoàn toàn chỉ lưu 5 trạng thái trên.
                // Ví dụ:
                // case 'pending_payos':
                //     return ['text' => 'Chờ thanh toán PayOS', 'class' => 'status-pending-payment'];
                return ['text' => ucfirst($status), 'class' => 'status-default'];
        }
    }
}
?>