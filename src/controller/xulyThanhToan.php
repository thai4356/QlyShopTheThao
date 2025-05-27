<?php
require_once 'OrderController.php';

// <<< THAY ĐỔI BẮT ĐẦU TỪ ĐÂY >>>
if (!isset($_POST['payment_method'])) {
    // Xử lý trường hợp không có phương thức thanh toán được chọn (ví dụ: chuyển hướng về trang thanh toán với thông báo lỗi)
    echo "Vui lòng chọn phương thức thanh toán.";
    // header('Location: ../view/ViewUser/Payment.php?error=nopaymentmethod');
    exit;
}

$paymentMethod = $_POST['payment_method'];
$ctrl = new OrderController();

if ($paymentMethod == 'vnpay') {
    $ctrl->initiateVNPayPayment(); // Gọi phương thức mới để xử lý VNPay
} else {
    // Giữ lại logic xử lý cho các phương thức thanh toán khác đã có (COD, Bank, MoMo)
    // Giả sử phương thức processPayment() hiện tại của bạn xử lý các trường hợp này
    $ctrl->processPayment();
}
// <<< KẾT THÚC THAY ĐỔI >>>
?>