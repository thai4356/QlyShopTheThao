<?php
/* Payment Notify
 * IPN URL: Ghi nhận kết quả thanh toán từ VNPAY
 * Các bước thực hiện:
 * Kiểm tra checksum
 * Tìm giao dịch trong database
 * Kiểm tra số tiền giữa hai hệ thống
 * Kiểm tra tình trạng của giao dịch trước khi cập nhật
 * Cập nhật kết quả vào Database
 * Trả kết quả ghi nhận lại cho VNPAY
 */

require_once __DIR__ . '/../../config/vnpay_config.php'; // <<< THAY ĐỔI: Trỏ đến file config chính
$inputData = array();
$returnData = array();

// Lấy tất cả dữ liệu VNPay gửi sang
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = isset($inputData['vnp_SecureHash']) ? $inputData['vnp_SecureHash'] : null; // <<< LƯU Ý: Kiểm tra isset
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
$vnpTranId = isset($inputData['vnp_TransactionNo']) ? $inputData['vnp_TransactionNo'] : null; //Mã giao dịch tại VNPAY
$vnp_BankCode = isset($inputData['vnp_BankCode']) ? $inputData['vnp_BankCode'] : null; //Ngân hàng thanh toán
$vnp_Amount = isset($inputData['vnp_Amount']) ? $inputData['vnp_Amount'] / 100 : null; // Số tiền thanh toán VNPAY phản hồi

// $Status = 0; // Trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán. Sẽ được logic DB quyết định.
$orderId = isset($inputData['vnp_TxnRef']) ? $inputData['vnp_TxnRef'] : null;

try {
    if ($secureHash == $vnp_SecureHash) {
        // <<< LƯU Ý: PHẦN LOGIC DATABASE CẦN ĐƯỢC THAY THẾ BẰNG CODE THỰC TẾ CỦA BẠN
        // Sử dụng các model (Order.php, Product.php) để:
        // 1. Lấy thông tin đơn hàng ($order) từ DB dựa vào $orderId (vnp_TxnRef)
        // 2. Kiểm tra $order có tồn tại không.
        // 3. Kiểm tra $order['total_price'] (hoặc cột tương ứng) có bằng $vnp_Amount không.
        // 4. Kiểm tra trạng thái hiện tại của $order['status'] để tránh xử lý trùng lặp.

        // === PHẦN GIẢ LẬP LOGIC DATABASE (CẦN THAY THẾ) ===
        // $orderModel = new Order(); // Khởi tạo model Order của bạn
        // $order = $orderModel->getOrderByTxnRef($orderId); // Phương thức này bạn cần tự viết
        $order = null; // Giả lập không tìm thấy đơn hàng hoặc đơn hàng đã được xử lý

        // Ví dụ kiểm tra (bạn cần code thực tế dựa trên model của mình)
        // if ($order !== null) { // Nếu tìm thấy đơn hàng
        //     if ($order['total_price'] == $vnp_Amount) { // Kiểm tra số tiền
        //         if ($order['status'] == 'pending_vnpay') { // Kiểm tra trạng thái chờ thanh toán VNPay
        //             if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
        //                 // Thanh toán thành công
        //                 // $orderModel->updateOrderStatus($orderId, 'completed', $vnpTranId);
        //                 // Gọi ProductModel để giảm số lượng tồn kho
        //                 $Status = 1;
        //             } else {
        //                 // Thanh toán thất bại
        //                 // $orderModel->updateOrderStatus($orderId, 'failed_vnpay', $vnpTranId);
        //                 $Status = 2;
        //             }
        //             $returnData['RspCode'] = '00';
        //             $returnData['Message'] = 'Confirm Success';
        //         } else {
        //             $returnData['RspCode'] = '02'; // Đơn đã được xác nhận trước đó
        //             $returnData['Message'] = 'Order already confirmed';
        //         }
        //     } else {
        //         $returnData['RspCode'] = '04'; // Sai số tiền
        //         $returnData['Message'] = 'Invalid amount';
        //     }
        // } else {
        //     $returnData['RspCode'] = '01'; // Không tìm thấy đơn hàng
        //     $returnData['Message'] = 'Order not found';
        // }
        // === KẾT THÚC PHẦN GIẢ LẬP LOGIC DATABASE ===

        // Giữ lại logic trả về mặc định của code mẫu nếu $order là NULL (chưa tích hợp DB)
        // Phần này cần được thay thế bằng logic kiểm tra $order thực tế từ DB của bạn
        if ($order != NULL) { // Code mẫu gốc, bạn sẽ thay thế điều kiện này
            // ... (Logic kiểm tra Amount, Status như trên) ...
        } else { // Nếu $order là NULL (ví dụ: chưa tìm thấy hoặc đơn hàng không hợp lệ theo logic của bạn)
            // Tạm thời giả định là không tìm thấy đơn hàng để giống code mẫu ban đầu cho phần này
            // Trong thực tế, bạn sẽ không để $order = NULL cố định mà sẽ lấy từ DB
            if (true) { // Khối này sẽ được thực thi nếu $order không được tìm thấy hoặc không hợp lệ
                // Đây là logic bạn cần thay đổi để kiểm tra đơn hàng thực tế
                if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                    // Giả định thành công nếu chưa có logic check DB
                    // TODO: Cập nhật DB tại đây
                    // $orderModel->updateOrderStatus($orderId, 'completed', $vnpTranId);
                    error_log("VNPay IPN Success for orderId: " . $orderId . " - TransactionNo: " . $vnpTranId);
                } else {
                    // Giao dịch thất bại
                    // TODO: Cập nhật DB tại đây
                    // $orderModel->updateOrderStatus($orderId, 'failed_vnpay', $vnpTranId);
                    error_log("VNPay IPN Failed for orderId: " . $orderId . " - ResponseCode: " . $inputData['vnp_ResponseCode']);
                }
                $returnData['RspCode'] = '00'; // Luôn trả về 00 cho VNPay nếu nhận được và xử lý (kể cả thất bại từ phía ngân hàng)
                $returnData['Message'] = 'Confirm Success'; // Message này cho VNPay biết bạn đã nhận và xử lý IPN
            } else { // Logic này sẽ không bao giờ chạy với $order = NULL và if(true) ở trên
                $returnData['RspCode'] = '01';
                $returnData['Message'] = 'Order not found';
            }
        }


    } else {
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid signature';
        error_log("VNPay IPN Invalid Signature for orderId: " . $orderId);
    }
} catch (Exception $e) {
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknown error';
    error_log("VNPay IPN Exception: " . $e->getMessage());
}
//Trả lại VNPAY theo định dạng JSON
echo json_encode($returnData);
die(); // <<< THÊM: Dừng script sau khi trả về JSON
?>