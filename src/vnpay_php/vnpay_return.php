<?php
require_once __DIR__ . '/../../config/vnpay_config.php'; // <<< THAY ĐỔI: Trỏ đến file config chính
$vnp_SecureHash = isset($_GET['vnp_SecureHash']) ? $_GET['vnp_SecureHash'] : ''; // <<< LƯU Ý: Kiểm tra isset
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, VNP_HASH_SECRET); // <<< THAY ĐỔI: Sử dụng hằng số
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>VNPAY RESPONSE</title>
    <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet"/>
    <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
</head>
<body>
<div class="container">
    <div class="header clearfix">
        <h3 class="text-muted">KẾT QUẢ THANH TOÁN VNPAY</h3>
    </div>
    <div class="table-responsive">
        <div class="form-group">
            <label >Mã đơn hàng:</label>
            <label><?php echo htmlspecialchars(isset($_GET['vnp_TxnRef']) ? $_GET['vnp_TxnRef'] : ''); ?></label>
        </div>
        <div class="form-group">
            <label >Số tiền:</label>
            <label><?php echo htmlspecialchars(isset($_GET['vnp_Amount']) ? number_format($_GET['vnp_Amount']/100) . ' VND' : ''); ?></label>
        </div>
        <div class="form-group">
            <label >Nội dung thanh toán:</label>
            <label><?php echo htmlspecialchars(isset($_GET['vnp_OrderInfo']) ? $_GET['vnp_OrderInfo'] : ''); ?></label>
        </div>
        <div class="form-group">
            <label >Mã phản hồi (vnp_ResponseCode):</label>
            <label><?php echo htmlspecialchars(isset($_GET['vnp_ResponseCode']) ? $_GET['vnp_ResponseCode'] : ''); ?></label>
        </div>
        <div class="form-group">
            <label >Mã GD Tại VNPAY:</label>
            <label><?php echo htmlspecialchars(isset($_GET['vnp_TransactionNo']) ? $_GET['vnp_TransactionNo'] : ''); ?></label>
        </div>
        <div class="form-group">
            <label >Mã Ngân hàng:</label>
            <label><?php echo htmlspecialchars(isset($_GET['vnp_BankCode']) ? $_GET['vnp_BankCode'] : ''); ?></label>
        </div>
        <div class="form-group">
            <label >Thời gian thanh toán:</label>
            <label>
                <?php
                if (isset($_GET['vnp_PayDate'])) {
                    $payDate = $_GET['vnp_PayDate'];
                    echo htmlspecialchars(substr($payDate, 6, 2) . '/' . substr($payDate, 4, 2) . '/' . substr($payDate, 0, 4) . ' ' . substr($payDate, 8, 2) . ':' . substr($payDate, 10, 2) . ':' . substr($payDate, 12, 2));
                }
                ?>
            </label>
        </div>
        <div class="form-group">
            <label >Kết quả:</label>
            <label>
                <?php
                if ($secureHash == $vnp_SecureHash) {
                    if (isset($_GET['vnp_ResponseCode']) && $_GET['vnp_ResponseCode'] == '00') {
                        echo "<span style='color:blue'>Giao dịch thành công</span>";
                    } else {
                        $errorCode = isset($_GET['vnp_ResponseCode']) ? $_GET['vnp_ResponseCode'] : 'Unknown';
                        echo "<span style='color:red'>Giao dịch không thành công (Mã lỗi: " . htmlspecialchars($errorCode) . ")</span>";
                    }
                } else {
                    echo "<span style='color:red'>Chữ ký không hợp lệ</span>";
                }
                ?>
            </label>
        </div>
    </div>
    <p>
        <a href="<?php echo defined('VNP_SHOP_RETURN_URL_AFTER_PAYMENT') ? VNP_SHOP_RETURN_URL_AFTER_PAYMENT : '/QlyShopTheThao/index.php'; ?>">Quay lại cửa hàng</a>
    </p>
    <footer class="footer">
        <p>&copy; VNPAY <?php echo date('Y')?></p>
    </footer>
</div>
</body>
</html>