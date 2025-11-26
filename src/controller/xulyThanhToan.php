<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'OrderController.php';

if (!isset($_POST['payment_method'])) {
    echo "Vui lòng chọn phương thức thanh toán.";
    exit;
}

$paymentMethod = $_POST['payment_method'];
$ctrl = new OrderController();

if ($paymentMethod == 'payos') {
    $ctrl->initiatePayOSPayment();
} elseif ($paymentMethod == 'stripe') {
    $ctrl->initiateStripePayment(); // <--- THÊM DÒNG NÀY
} else {
    // COD
    $ctrl->processPayment();
}
?>