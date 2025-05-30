<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Ensure autoloader is here
require_once 'OrderController.php'; // Make sure the path is correct

if (!isset($_POST['payment_method'])) {
    echo "Vui lòng chọn phương thức thanh toán.";
    // header('Location: ../view/ViewUser/Payment.php?error=nopaymentmethod');
    exit;
}

$paymentMethod = $_POST['payment_method'];
$ctrl = new OrderController(); // Constructor will init PayOS and session

if ($paymentMethod == 'vnpay') {
    $ctrl->initiateVNPayPayment();
} elseif ($paymentMethod == 'payos') {
    $ctrl->initiatePayOSPayment(); // New method for PayOS
} else {
    // For COD, Bank, MoMo
    $ctrl->processPayment();
}
?>