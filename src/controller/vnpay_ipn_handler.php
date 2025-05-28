<?php
// File: QlyShopTheThao/src/controller/vnpay_ipn_handler.php
require_once __DIR__ . '/OrderController.php'; // OrderController.php nằm cùng thư mục

if (class_exists('OrderController')) {
    $orderController = new OrderController();
    $orderController->vnpayIpn(); // Gọi phương thức vnpayIpn đã có
} else {
    // Ghi log lỗi nếu không tìm thấy OrderController
    error_log("FATAL ERROR: vnpay_ipn_handler.php - OrderController class not found.");
    // Phản hồi lỗi cho VNPay
    // Quan trọng: IPN phải luôn trả về JSON cho VNPay
    header('Content-Type: application/json');
    echo json_encode(["RspCode" => "99", "Message" => "Error: System configuration issue. (ERR_VNIH_OCNF)"]);
}
?>