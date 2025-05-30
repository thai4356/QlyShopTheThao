<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

/**
 * @author CTT VNPAY
 */
require_once __DIR__ . '/../../config/vnpay_config.php'; // <<< GIỮ NGUYÊN, ĐÃ ĐÚNG HƯỚNG

// Mã giao dịch thanh toán tham chiếu của merchant
// <<< LƯU Ý: $vnp_TxnRef NÊN được tạo dựa trên mã đơn hàng thực tế từ hệ thống của bạn để đảm bảo tính duy nhất và dễ đối soát.
// Ví dụ: $vnp_TxnRef = $_POST['order_id'] . '_' . time(); // Nếu bạn truyền order_id từ form
// Hiện tại đang là random, chỉ phù hợp cho test ban đầu.
$vnp_TxnRef = rand(1,10000) . '_' . time(); // Thêm time() để tăng tính duy nhất cho testing

$vnp_Amount = isset($_POST['amount']) ? $_POST['amount'] : 0; // Số tiền thanh toán
$vnp_Locale = isset($_POST['language']) ? $_POST['language'] : 'vn'; //Ngôn ngữ chuyển hướng thanh toán (mặc định là vn)
$vnp_BankCode = isset($_POST['bankCode']) ? $_POST['bankCode'] : ''; //Mã phương thức thanh toán
$vnp_IpAddr = $_SERVER['REMOTE_ADDR']; //IP Khách hàng thanh toán

// <<< LƯU Ý: Tính toán vnp_ExpireDate (Thời gian hết hạn thanh toán)
// Nên tạo trực tiếp ở đây thay vì dựa vào biến $expire từ config.
$vnp_CreateDate = date('YmdHis');
$vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', strtotime($vnp_CreateDate)));

$inputData = array(
    "vnp_Version" => VNP_VERSION, // <<< THAY ĐỔI: Sử dụng hằng số
    "vnp_TmnCode" => VNP_TMN_CODE, // <<< THAY ĐỔI: Sử dụng hằng số
    "vnp_Amount" => $vnp_Amount * 100, // VNPay yêu cầu số tiền * 100
    "vnp_Command" => "pay",
    "vnp_CreateDate" => $vnp_CreateDate, // <<< THAY ĐỔI: Sử dụng biến vừa tạo ở trên
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef, // <<< THAY ĐỔI: Dùng dấu "." để nối chuỗi
    "vnp_OrderType" => "other", // Có thể thay đổi tùy theo loại hàng hóa/dịch vụ
    "vnp_ReturnUrl" => VNP_RETURN_URL, // <<< THAY ĐỔI: Sử dụng hằng số
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $vnp_ExpireDate // <<< THAY ĐỔI: Sử dụng biến vừa tạo ở trên
);

if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnpayUrl = VNP_URL . "?" . $query; // <<< THAY ĐỔI: Sử dụng hằng số VNP_URL
if (defined('VNP_HASH_SECRET')) { // <<< THAY ĐỔI: Kiểm tra hằng số và sử dụng hằng số
    $vnpSecureHash =   hash_hmac('sha512', $hashdata, VNP_HASH_SECRET);
    $vnpayUrl .= 'vnp_SecureHash=' . $vnpSecureHash;
}
header('Location: ' . $vnpayUrl);
die();
?>