<?php
// QlyShopTheThao/config/vnpay_config.php

date_default_timezone_set('Asia/Ho_Chi_Minh');

/*
 * Thông tin cấu hình kết nối VNPay Sandbox
 * Đây là file cấu hình chính cho VNPay trong dự án của bạn.
 * Các file thư viện VNPay trong thư mục /vnpay_php/ sau khi điều chỉnh sẽ include file này.
 */

// Thông tin tài khoản VNPay Sandbox của bạn
define('VNP_TMN_CODE', '4Y0CRRH1'); // Mã website tại VNPAY (Terminal Id) - Lấy từ thông tin bạn cung cấp
define('VNP_HASH_SECRET', '8ABUYCIMXJXE5HSKOO4VRZ1H6EN9CZV8'); // Chuỗi bí mật (Secret key) - Lấy từ thông tin bạn cung cấp

// URL của cổng thanh toán VNPay Sandbox
define('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');

// URL API của VNPay Sandbox (ít dùng trực tiếp trong luồng thanh toán cơ bản, chủ yếu cho query/refund)
define('VNP_API_URL_MERCHANT_PORTAL', 'http://sandbox.vnpayment.vn/merchant_webapi/merchant.html'); // URL trang quản lý merchant của VNPay (tham khảo)
define('VNP_API_URL_TRANSACTION_QUERY', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'); // URL API để truy vấn, hoàn trả

// URL Callback (Return URL và IPN URL) - RẤT QUAN TRỌNG: Phải sử dụng URL ngrok của bạn
// THAY THẾ '' BẰNG URL NGrok HIỆN TẠI CỦA BẠN NẾU NÓ THAY ĐỔI!
$ngrokBaseUrl = "https://up-summary-honeybee.ngrok-free.app"; // Đây là URL ngrok bạn đã cung cấp.
$projectRootPathForUrl = "/QlyShopTheThao"; // Đường dẫn gốc của dự án trên webserver của bạn.

define('VNP_RETURN_URL', $ngrokBaseUrl . $projectRootPathForUrl . '/src/controller/vnpay_return_handler.php');
define('VNP_IPN_URL', $ngrokBaseUrl . $projectRootPathForUrl . '/src/controller/vnpay_ipn_handler.php');

// Phiên bản API VNPay
define('VNP_VERSION', '2.1.0'); // Thông thường là "2.1.0"



 $startTime = date("YmdHis");
 $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));


?>