<?php
// File: QlyShopTheThao/src/controller/vnpay_return_handler.php
require_once __DIR__ . '/OrderController.php'; // OrderController.php nằm cùng thư mục

if (class_exists('OrderController')) {
    $orderController = new OrderController();
    $orderController->vnpayReturn(); // Gọi phương thức vnpayReturn đã có
} else {
    // Ghi log lỗi nếu không tìm thấy OrderController
    error_log("FATAL ERROR: vnpay_return_handler.php - OrderController class not found.");
    // Hiển thị thông báo lỗi chung cho người dùng
    echo "Đã có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại sau. (ERR_VNRH_OCNF)";
}
?>
