<?php
require_once __DIR__ . '/../model/Connect.php';
require_once __DIR__ . '/../model/Order.php';
require_once __DIR__ . '/../model/OrderItem.php';
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../../config/vnpay_config.php';
require_once __DIR__ . '/../model/Cart.php';
require_once __DIR__ . '/../model/CartItem.php';

require_once __DIR__ . '/../../vendor/autoload.php'; // Composer autoloader

use Dotenv\Dotenv;
use PayOS\PayOS;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../'); // Assuming .env is in the project root
$dotenv->load();

class OrderController
{
    private $payOS;
    private $ngrok_url = "https://up-summary-honeybee.ngrok-free.app";

    public function __construct()
    {
        // Initialize PayOS SDK
        // Check if ENVs are loaded
        if (empty($_ENV['PAYOS_CLIENT_ID']) || empty($_ENV['PAYOS_API_KEY']) || empty($_ENV['PAYOS_CHECKSUM_KEY'])) {
            // Handle error: .env variables not loaded
            // You might want to log this or throw an exception
            error_log("PayOS environment variables are not loaded. Check .env file and path.");
        }
        $this->payOS = new PayOS($_ENV['PAYOS_CLIENT_ID'], $_ENV['PAYOS_API_KEY'], $_ENV['PAYOS_CHECKSUM_KEY']);

        // Ensure session is started for checkout_items and user_id
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $selectedItems = isset($_POST['select_item']) ? array_unique($_POST['select_item']) : [];
        $quantities = isset($_POST['qty_hidden']) ? $_POST['qty_hidden'] : [];

        if (empty($selectedItems)) {
            echo "<p style='text-align:center; color:red;'>Bạn chưa chọn sản phẩm nào để thanh toán!</p>";
            return;
        }

        $productModel = new Product();
        $cartItems = [];

        foreach ($selectedItems as $productId) {
            $product = $productModel->getById($productId); // Giả sử getById trả về cả price và discount_price
            $qty = isset($quantities[$productId]) ? (int)$quantities[$productId] : 1;

            if ($qty > $product['stock']) {
                $qty = $product['stock'];
            }

            // <<< THAY ĐỔI Ở ĐÂY: Lấy giá bán thực tế >>>
            $actual_selling_price = (!empty($product['discount_price']) && $product['discount_price'] > 0) ? $product['discount_price'] : $product['price'];

            if (isset($cartItems[$productId])) { // Kiểm tra nếu đã tồn tại sản phẩm này trong $cartItems
                $cartItems[$productId]['quantity'] += $qty;
            } else {
                $cartItems[$productId] = [
                    'product_id' => $productId,
                    'name' => $product['name'],
                    'image_url' => $product['image_url'], // Đảm bảo getById trả về image_url thumbnail
                    'price' => $actual_selling_price, // <<< SỬ DỤNG GIÁ BÁN THỰC TẾ
                    'quantity' => $qty,
                ];
            }
        }

        // Reset lại chỉ mục mảng (từ dạng map về array tuần tự nếu bạn đã dùng $productId làm key)
        $_SESSION['checkout_items'] = array_values($cartItems);


        header("Location: ../ViewUser/Payment.php");
        exit;
    }

    public function processPayment()
    {
        // Lấy dữ liệu từ form
        $paymentMethod = $_POST['payment_method'] ?? '';
        $name = $_POST['hoten'] ?? '';
        $address = $_POST['diachi'] ?? '';
        $phone = $_POST['dienthoai'] ?? '';

        // Kiểm tra dữ liệu bắt buộc
        if ($paymentMethod == '' || $name == '' || $address == '' || $phone == '') {
            echo "Vui lòng nhập đầy đủ thông tin thanh toán!";
            return;
        }

        // Lấy user ID từ session
        $userId = $_SESSION['user_id'] ?? 1;
        $cartItems = $_SESSION['checkout_items'] ?? [];
        $totalPrice = 0;
        $productModel = new Product();

        // ✅ Kiểm tra tồn kho trước khi tiếp tục
        foreach ($cartItems as &$item) {
            $product = $productModel->getById($item['product_id']);
            $item['price'] = $product['price'];

            if ($product['stock'] == 0) {
                echo "Sản phẩm '{$product['name']}' đã hết hàng.";
                return;
            }

            if ($item['quantity'] > $product['stock']) {
                echo "<div style='margin: 50px auto; width: 80%; padding: 15px; background-color: #ffe6e6; color: red; border: 1px solid red; border-radius: 5px; text-align: center; font-weight: bold;'>
        Sản phẩm '{$product['name']}' chỉ còn {$product['stock']} sản phẩm trong kho do một người dùng khác vừa mới mua sản phẩm.
        <br><br>
        <a href='https://up-summary-honeybee.ngrok-free.app/QlyShopTheThao/src/view/ViewUser/Index.php?module=home' style='color: blue;'>Quay lại trang chủ</a>
             </div>";
                return;
            }
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // ✅ Tạo đơn hàng
        $orderModel = new Order();
        $orderId = $orderModel->createOrder($userId, $totalPrice, $paymentMethod, $name, $address, $phone);

        // ✅ Ghi chi tiết đơn hàng và cập nhật stock
        $orderItemModel = new OrderItem();
        foreach ($cartItems as &$item) {
            $orderItemModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
            $productModel->reduceStock($item['product_id'], $item['quantity']);
            $productModel->increseSold($item['product_id'], $item['quantity']);
        }

        // ✅ Xóa khỏi giỏ hàng
        $cartModel = new Cart();
        $cart = $cartModel->getCartByUserId($userId);
        $cartId = $cart['id'];

        $cartItemModel = new CartItem();
        foreach ($cartItems as &$item) {
            $cartItemModel->removeItem($cartId, $item['product_id']);
        }

        // ✅ Xóa session
        unset($_SESSION['checkout_items']);

        // ✅ Chuyển hướng
        header("Location: ../view/ViewUser/success.php?order_id=" . $orderId);
        exit;
    }


    public function viewOrderHistory()
    {
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

        if ($userId == 0) {
            echo "<p style='text-align:center; color:red;'>Bạn cần đăng nhập để xem lịch sử đơn hàng!</p>";
            return;
        }

        $orderModel = new Order();
        $orders = $orderModel->getOrdersByUser($userId);

        include __DIR__ . '/../view/ViewUser/OrderHistory.php';

    }

    public function viewOrderDetail($orderId)
    {
        $orderModel = new Order();
        $orderItemModel = new OrderItem();
        $productModel = new Product();

        // Lấy thông tin đơn hàng
        $order = $orderModel->getOrderById($orderId);

        // Lấy danh sách sản phẩm trong đơn hàng
        $items = $orderItemModel->getItemsByOrderId($orderId);

        // Bổ sung tên và ảnh sản phẩm cho từng dòng
        foreach ($items as $index => $item) {
            $product = $productModel->getById($item['product_id']);
            $items[$index]['product_name'] = $product['name'];
            $items[$index]['image_url'] = $product['image_url']; // nếu bạn muốn dùng ảnh
        }

        // Gửi dữ liệu ra view
        include __DIR__ . '/../view/ViewUser/OrderDetail.php';
    }

    public function initiateVNPayPayment()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Kiểm tra thông tin người dùng và giỏ hàng (giữ nguyên)
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_items']) || empty($_SESSION['checkout_items'])) {
            echo "Lỗi: Thông tin người dùng hoặc giỏ hàng không hợp lệ.";
            exit;
        }

        $userId = $_SESSION['user_id'];
        $cartItems = $_SESSION['checkout_items']; // Giờ đây $cartItems đã chứa giá bán thực tế

        // Lấy thông tin khách hàng từ POST (giữ nguyên)
        $name = isset($_POST['hoten']) ? trim($_POST['hoten']) : '';
        $address = isset($_POST['diachi']) ? trim($_POST['diachi']) : '';
        $phone = isset($_POST['dienthoai']) ? trim($_POST['dienthoai']) : '';

        if (empty($name) || empty($address) || empty($phone)) {
            echo "Vui lòng nhập đầy đủ thông tin giao hàng.";
            exit;
        }

        // 2. Tính toán tổng số tiền (Tạm tính - subTotal)
        $subTotal = 0;
        // $productModel = new Product(); // Không cần khởi tạo lại nếu $cartItems đã có giá đúng
        foreach ($cartItems as $item) {
            // $item['price'] bây giờ đã là giá bán thực tế từ bước 1
            $subTotal += $item['price'] * $item['quantity'];
        }

        if ($subTotal <= 0) {
            echo "Tổng tiền sản phẩm không hợp lệ.";
            exit;
        }

        // <<< THAY ĐỔI Ở ĐÂY: Áp dụng phí vận chuyển và giảm giá >>>
        $shippingFee = 30000; // Giống trong Payment.php
        $discountPercentage = 0.10; // 10%
        $discountAmount = $subTotal * $discountPercentage; // Giảm giá trên Tạm tính

        $finalTotal = $subTotal + $shippingFee - $discountAmount; // Tổng tiền cuối cùng để thanh toán

        if ($finalTotal <= 0) {
            echo "Tổng tiền cuối cùng không hợp lệ sau khi áp dụng phí và giảm giá.";
            exit;
        }

        // 3. Tạo đơn hàng trong cơ sở dữ liệu với trạng thái 'pending_vnpay'
        $orderModel = new Order();
        // <<< THAY ĐỔI Ở ĐÂY: Lưu finalTotal vào DB >>>
        $orderId = $orderModel->createOrder($userId, $finalTotal, 'vnpay', $name, $address, $phone);

        if (!$orderId) {
            echo "Lỗi khi tạo đơn hàng.";
            exit;
        }

        // Lưu chi tiết đơn hàng (order items) - giá từng item là giá đã dùng để tính subTotal
        $orderItemModel = new OrderItem();
        foreach ($cartItems as $item) {
            // $item['price'] đã là giá bán thực tế
            $orderItemModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
        }

        // 4. Chuẩn bị dữ liệu và chuyển hướng sang VNPay
        $vnp_TxnRef = $orderId . '_' . time();
        // <<< THAY ĐỔI Ở ĐÂY: Sử dụng finalTotal cho vnp_Amount >>>
        $vnp_Amount = $finalTotal * 100; // Số tiền thanh toán (VNPay yêu cầu đơn vị là đồng * 100)
        $vnp_Locale = 'vn';
        $vnp_BankCode = isset($_POST['vnp_BankCode']) ? $_POST['vnp_BankCode'] : '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $vnp_IpAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        } else {
            $vnp_IpAddr = "127.0.0.1"; // Giá trị mặc định nếu không thể lấy được IP
        }
        // Nếu HTTP_X_FORWARDED_FOR có thể chứa nhiều IP (ví dụ: "client_ip, proxy1_ip, proxy2_ip")
        // bạn nên lấy IP đầu tiên trong danh sách, đó thường là IP của client gốc.
        $ipAddresses = explode(',', $vnp_IpAddr);
        $vnp_IpAddr = trim($ipAddresses[0]);

        // Đảm bảo rằng $vnp_IpAddr không phải là ::1 nếu có thể
        if ($vnp_IpAddr == '::1' && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Trường hợp này không nên xảy ra nếu logic trên đúng, nhưng để chắc chắn
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $vnp_IpAddr = trim($ips[0]);
        } elseif ($vnp_IpAddr == '::1') {
            $vnp_IpAddr = "183.81.10.210";
        }
        $vnp_CreateDate = date('YmdHis');
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', strtotime($vnp_CreateDate)));

        $inputData = array(
            "vnp_Version" => VNP_VERSION,
            "vnp_TmnCode" => VNP_TMN_CODE,
            "vnp_Amount" => $vnp_Amount, // <<< ĐÃ SỬ DỤNG finalTotal
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => "Thanh toan don hang " . $orderId . " Tong: " . number_format($finalTotal) . "VND",
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => VNP_RETURN_URL,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
        );

        // ... (phần còn lại của việc tạo hash và chuyển hướng giữ nguyên) ...

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashdata .= urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
            $query .= urlencode((string)$key) . "=" . urlencode((string)$value) . '&';
        }

        $vnpayUrl = VNP_URL . "?" . $query;
        if (defined('VNP_HASH_SECRET')) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, VNP_HASH_SECRET);
            $vnpayUrl .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // GHI LOG NGAY TẠI ĐÂY
        $log_content = "==== VNPay Request ====\n";
        $log_content .= "Timestamp: " . date("Y-m-d H:i:s") . "\n";
        $log_content .= "Input Data for Hashing:\n" . print_r($inputData, true) . "\n"; // Các field trước khi sắp xếp và thêm hash
        $log_content .= "HashData String: " . $hashdata . "\n";
        $log_content .= "Generated SecureHash: " . $vnpSecureHash . "\n";
        $log_content .= "VNPay URL: " . $vnpayUrl . "\n";
        $log_content .= "=======================\n\n";
        file_put_contents(__DIR__ . '/../../logs/vnpay_debug.log', $log_content, FILE_APPEND); // Tạo thư mục 'logs' ở gốc dự án nếu chưa có

        // Cân nhắc việc xóa session checkout_items ở đây hoặc sau khi IPN xác nhận thành công
        // unset($_SESSION['checkout_items']);

        header('Location: ' . $vnpayUrl);
        die();
    }

    // <<< THÊM HAI PHƯƠNG THỨC DƯỚI ĐÂY ĐỂ XỬ LÝ CALLBACK TỪ VNPAY >>>
    public function vnpayReturn()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Logic xử lý khi VNPay trả về (từ file vnpay_return.php đã sửa)
        // 1. Require file config (đã require ở đầu class)
        // 2. Lấy dữ liệu từ $_GET
        // 3. Xác thực vnp_SecureHash
        // 4. Lấy thông tin đơn hàng từ DB dựa vào vnp_TxnRef (chứa order_id)
        // 5. Hiển thị thông báo cho người dùng dựa trên kết quả (vnp_ResponseCode và trạng thái đơn hàng trong DB)
        //    Chuyển hướng người dùng đến trang Success.php hoặc Failure.php với các tham số cần thiết.

        // === BẮT ĐẦU LOGIC ADAPT TỪ vnpay_return.php ===
        $vnp_SecureHash_received = isset($_GET['vnp_SecureHash']) ? $_GET['vnp_SecureHash'] : '';
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
                $hashData = $hashData . '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashData = urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
        }
        $secureHash_calculated = hash_hmac('sha512', $hashData, VNP_HASH_SECRET);

        $vnp_TxnRef = isset($_GET['vnp_TxnRef']) ? $_GET['vnp_TxnRef'] : '';
        $orderId_parts = explode('_', $vnp_TxnRef); // Giả sử $vnp_TxnRef = $orderId . '_' . time();
        $orderId = $orderId_parts[0];
        $vnp_ResponseCode = isset($_GET['vnp_ResponseCode']) ? $_GET['vnp_ResponseCode'] : '99'; // Mặc định là lỗi nếu không có

        // Chuyển hướng đến trang Success.php (hoặc một trang kết quả riêng)
        // Trang Success.php sẽ tự kiểm tra lại trạng thái đơn hàng trong DB nếu cần
        if ($secureHash_calculated == $vnp_SecureHash_received) {
            if ($vnp_ResponseCode == '00') {
                // Thanh toán thành công từ phía VNPay (nhưng vẫn cần IPN để xác nhận cuối cùng)
                header('Location: ../view/ViewUser/success.php?order_id=' . urlencode($orderId) . '&vnp_response_code=00&payment_method=vnpay&source=vnp_return');
            } else {
                // Thanh toán thất bại hoặc bị hủy từ phía VNPay
                header('Location: ../view/ViewUser/success.php?order_id=' . urlencode($orderId) . '&vnp_response_code=' . urlencode($vnp_ResponseCode) . '&payment_method=vnpay&source=vnp_return&error=payment_failed');
            }
        } else {
            // Sai chữ ký -> có thể giao dịch đã bị thay đổi
            header('Location: ../view/ViewUser/success.php?order_id=' . urlencode($orderId) . '&payment_method=vnpay&source=vnp_return&error=invalid_signature');
        }
        exit;
        // === KẾT THÚC LOGIC ADAPT TỪ vnpay_return.php ===
    }

    public function vnpayIpn()
    {
        // Logic xử lý IPN từ VNPay (từ file vnpay_ipn.php đã sửa)
        // 1. Require file config (đã require ở đầu class)
        // 2. Lấy dữ liệu từ $_GET
        // 3. Xác thực vnp_SecureHash
        // 4. Kiểm tra thông tin đơn hàng trong DB (orderId, amount, status)
        // 5. Cập nhật trạng thái đơn hàng, trừ kho sản phẩm (nếu thành công)
        // 6. Trả về JSON cho VNPay (RspCode, Message)

        // === BẮT ĐẦU LOGIC ADAPT TỪ vnpay_ipn.php ===
        $inputData = array();
        $returnData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash_received = isset($inputData['vnp_SecureHash']) ? $inputData['vnp_SecureHash'] : null;
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashData = $hashData . urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
        }
        $secureHash_calculated = hash_hmac('sha512', $hashData, VNP_HASH_SECRET);

        $conn = (new Connect())->getConnection(); // Lấy connection cho transaction
        $returnData = [];

        try {
            if ($secureHash_calculated == $vnp_SecureHash_received) {
                $orderId_raw = $inputData['vnp_TxnRef'] ?? null;
                $orderId_parts = explode('_', $orderId_raw);
                $orderId = (int)$orderId_parts[0];

                $orderModel = new Order();
                $order = $orderModel->getOrderById($orderId);

                if ($order) {
                    // Chỉ xử lý nếu đơn hàng đang ở trạng thái chờ
                    if ($order['status'] == 'đang xử lý') {
                        if (isset($inputData['vnp_ResponseCode']) && $inputData['vnp_ResponseCode'] == '00') {
                            // Bắt đầu transaction
                            $conn->beginTransaction();

                            $orderItemModel = new OrderItem();
                            $productModel = new Product();
                            $itemsInOrder = $orderItemModel->getItemsByOrderId($orderId);
                            $canProcess = true;

                            // Vòng lặp kiểm tra và trừ kho
                            foreach ($itemsInOrder as $item) {
                                $rowsAffected = $productModel->reduceStock($item['product_id'], $item['quantity']);
                                if ($rowsAffected == 0) {
                                    // Nếu trừ kho thất bại (hết hàng), đánh dấu và dừng lại
                                    $canProcess = false;
                                    break;
                                }
                            }

                            if ($canProcess) {
                                // Nếu trừ kho tất cả sản phẩm thành công
                                foreach ($itemsInOrder as $item) {
                                    $productModel->increseSold($item['product_id'], $item['quantity']);
                                }

                                // Cập nhật trạng thái đơn hàng
                                $orderModel->updateOrderStatusAndTxn($orderId, 'đã thanh toán', $inputData['vnp_TransactionNo'], $orderId_raw);

                                // Xóa giỏ hàng
                                $userId = $order['user_id'];
                                $cartModel = new Cart();
                                $userCart = $cartModel->getCartByUserId($userId);
                                if ($userCart) {
                                    $cartItemModel = new CartItem();
                                    foreach ($itemsInOrder as $orderedItem) {
                                        $cartItemModel->removeItem($userCart['id'], $orderedItem['product_id']);
                                    }
                                }

                                $conn->commit(); // Hoàn tất giao dịch
                                $returnData['RspCode'] = '00';
                                $returnData['Message'] = 'Confirm Success';

                            } else {
                                // Nếu có sản phẩm không đủ hàng
                                $conn->rollBack(); // Hoàn tác tất cả
                                $orderModel->updateOrderStatusAndTxn($orderId, 'chờ hoàn tiền', $inputData['vnp_TransactionNo'], $orderId_raw);
                                error_log("OVERSALE on VNPay IPN for Order ID: " . $orderId);
                                $returnData['RspCode'] = '00'; // Vẫn trả về success cho VNPay, nhưng hệ thống của bạn biết đây là lỗi
                                $returnData['Message'] = 'Confirm Success';
                            }
                        } else {
                            // Giao dịch thất bại từ VNPay
                            $orderModel->updateOrderStatusAndTxn($orderId, 'thất bại', $inputData['vnp_TransactionNo'], $orderId_raw);
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                        }
                    } else {
                        $returnData['RspCode'] = '02'; // Đơn hàng đã được xác nhận trước đó
                        $returnData['Message'] = 'Order already confirmed';
                    }
                } else {
                    $returnData['RspCode'] = '01'; // Không tìm thấy đơn hàng
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97'; // Sai chữ ký
                $returnData['Message'] = 'Invalid signature';
            }
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknown error';
            error_log("VNPay IPN Exception: " . $e->getMessage());
        }

        echo json_encode($returnData);
        exit();
    }

    public function initiatePayOSPayment()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_items']) || empty($_SESSION['checkout_items'])) {
            echo "Lỗi: Thông tin người dùng hoặc giỏ hàng không hợp lệ.";
            // Consider redirecting back to cart with an error
            exit;
        }

        $userId = $_SESSION['user_id'];
        $cartItems = $_SESSION['checkout_items']; // Already contains correct prices

        $name = isset($_POST['hoten']) ? trim($_POST['hoten']) : '';
        $address = isset($_POST['diachi']) ? trim($_POST['diachi']) : '';
        $phone = isset($_POST['dienthoai']) ? trim($_POST['dienthoai']) : '';

        if (empty($name) || empty($address) || empty($phone)) {
            echo "Vui lòng nhập đầy đủ thông tin giao hàng.";
            // Redirect back with error
            exit;
        }

        // Calculate total amount (similar to your VNPay logic or processPayment)
        $subTotal = 0;
        foreach ($cartItems as $item) {
            $subTotal += $item['price'] * $item['quantity'];
        }

        $shippingFee = 30000; // From Payment.php
        $discountPercentage = 0.10; // 10% from Payment.php
        $discountAmount = $subTotal * $discountPercentage;
        $finalTotal = 2000; //

        if ($finalTotal <= 0) {
            echo "Tổng tiền cuối cùng không hợp lệ.";
            exit;
        }

        // Create order in database with 'pending_payos' status
        $orderModel = new Order();
        // Note: createOrder in your Order.php model creates an orderNo.
        // PayOS orderCode needs to be an INT. We'll use the database order ID.
        $orderId = $orderModel->createOrder($userId, $finalTotal, 'payos', $name, $address, $phone); // This returns lastInsertId which is orders.id

        if (!$orderId) {
            echo "Lỗi khi tạo đơn hàng trong cơ sở dữ liệu.";
            // Log error
            exit;
        }
        // Update status to 'pending_payos' if createOrder sets a default like 'pending'
        // Or modify createOrder to accept status
        $orderModel->updateOrderStatusAndTxn($orderId, 'đang xử lý');


        // Save order items
        $orderItemModel = new OrderItem();
        $payosItems = [];
        foreach ($cartItems as $cartItem) {
            $orderItemModel->addItem($orderId, $cartItem['product_id'], $cartItem['quantity'], $cartItem['price']);
            $payosItems[] = [
                "name" => $cartItem['name'],
                "quantity" => (int)$cartItem['quantity'],
                "price" => (int)$cartItem['price'] // PayOS expects integer price for VND
            ];
        }

        // Define Return and Cancel URLs
        // Using handler files for consistency with your potential VNPay setup
        $returnUrl = $this->ngrok_url . "/QlyShopTheThao/src/controller/payos_return_handler.php";
        $cancelUrl = $this->ngrok_url . "/QlyShopTheThao/src/controller/payos_cancel_handler.php";

        $paymentData = [
            "orderCode" => (int)$orderId, // This MUST be an integer
            "amount" => (int)$finalTotal,
            "description" => "Thanh toan don hang #" . $orderId,
            "items" => $payosItems,
            "buyerName" => $name,
            "buyerPhone" => $phone,
            // "buyerEmail" => $_SESSION['user_email'], // If you have user email in session
            "cancelUrl" => $cancelUrl,
            "returnUrl" => $returnUrl,
            // "expiredAt" => time() + (20 * 60) // Optional: Link expires in 20 minutes
        ];

        try {
            $payosResponse = $this->payOS->createPaymentLink($paymentData);
            // Log the request and response for debugging
            // file_put_contents(__DIR__ . '/../../logs/payos_debug.log', "Request: " . json_encode($paymentData) . "\nResponse: " . json_encode($payosResponse) . "\n", FILE_APPEND);

            // Store payos_payment_link_id (which is $payosResponse['paymentLinkId'])
            // The `orders` table has payos_payment_link_id
            if (isset($payosResponse['paymentLinkId'])) {
                $orderModel->updateOrderPayOSInfo($orderId, $payosResponse['paymentLinkId']);
            }


            header('Location: ' . $payosResponse['checkoutUrl']);
            exit;
        } catch (\Throwable $e) {
            error_log("PayOS Error: " . $e->getMessage());
            echo "Có lỗi xảy ra trong quá trình tạo liên kết thanh toán PayOS. Vui lòng thử lại.";
            // Redirect to an error page or cart
            exit;
        }
    }

    public function handlePayOSReturn()
    {
        $orderIdFromPayOS = isset($_GET['orderCode']) ? (int)$_GET['orderCode'] : null;
        $statusFromQuery = isset($_GET['status']) ? $_GET['status'] : null;

        if (!$orderIdFromPayOS) {
            echo "Lỗi: Mã đơn hàng không hợp lệ từ PayOS.";
            exit;
        }

        // Lấy connection để sử dụng transaction
        $conn = (new Connect())->getConnection();

        try {
            $paymentLinkInfo = $this->payOS->getPaymentLinkInformation($orderIdFromPayOS);
            $orderModel = new Order();
            $dbOrder = $orderModel->getOrderById($orderIdFromPayOS);

            if (!$dbOrder) {
                echo "Lỗi: Không tìm thấy đơn hàng trong hệ thống.";
                exit;
            }

            // Chỉ xử lý nếu trạng thái đơn hàng là 'đang xử lý'
            if ($dbOrder['status'] == 'đang xử lý') {
                if ($paymentLinkInfo['status'] == 'PAID' || $statusFromQuery == 'PAID') {

                    // Bắt đầu transaction
                    $conn->beginTransaction();

                    $orderItemModel = new OrderItem();
                    $productModel = new Product();
                    $itemsInOrder = $orderItemModel->getItemsByOrderId($orderIdFromPayOS);
                    $canProcess = true;

                    // Vòng lặp kiểm tra và trừ kho cho từng sản phẩm
                    foreach ($itemsInOrder as $item) {
                        $rowsAffected = $productModel->reduceStock($item['product_id'], $item['quantity']);
                        if ($rowsAffected == 0) {
                            // Nếu trừ kho thất bại (hết hàng), đánh dấu và dừng lại
                            $canProcess = false;
                            error_log("OVERSALE on PayOS Return for Order ID: " . $orderIdFromPayOS . ", Product ID: " . $item['product_id']);
                            break;
                        }
                    }

                    if ($canProcess) {
                        // Nếu trừ kho thành công cho tất cả sản phẩm
                        foreach ($itemsInOrder as $item) {
                            $productModel->increseSold($item['product_id'], $item['quantity']);
                        }

                        // Cập nhật trạng thái và thông tin giao dịch
                        $transactionTime = !empty($paymentLinkInfo['transactions']) ? date('Y-m-d H:i:s', strtotime($paymentLinkInfo['transactions'][0]['transactionDateTime'])) : date('Y-m-d H:i:s');
                        $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'đã thanh toán', $paymentLinkInfo['orderCode'], $transactionTime);

                        // Xóa các sản phẩm đã đặt khỏi giỏ hàng
                        $userId = $dbOrder['user_id'];
                        $cartModel = new Cart();
                        $userCart = $cartModel->getCartByUserId($userId);
                        if ($userCart) {
                            $cartItemModel = new CartItem();
                            foreach ($itemsInOrder as $orderedItem) {
                                $cartItemModel->removeItem($userCart['id'], $orderedItem['product_id']);
                            }
                        }

                        $conn->commit(); // Hoàn tất giao dịch

                        // Chuyển hướng đến trang thành công
                        unset($_SESSION['checkout_items']);
                        header('Location: ../view/ViewUser/Success.php?order_id=' . $orderIdFromPayOS . '&payment_method=payos&status=success');
                        exit;

                    } else {
                        // Nếu có sản phẩm đã hết hàng
                        $conn->rollBack(); // Hoàn tác tất cả các thay đổi

                        // Cập nhật trạng thái đơn hàng thành "chờ hoàn tiền"
                        $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'chờ hoàn tiền');

                        // Chuyển hướng đến trang lỗi (hoặc thành công nhưng với thông báo đặc biệt)
                        header('Location: ../view/ViewUser/Payment.php?error=oversold_and_refund_pending&order_id=' . $orderIdFromPayOS);
                        exit;
                    }

                } else { // Trạng thái từ PayOS là CANCELLED, FAILED, EXPIRED...
                    $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'hủy'); // Hoặc 'thất bại'
                    header('Location: ../view/ViewUser/Payment.php?error=payos_failed&order_id=' . $orderIdFromPayOS . '&payos_status=' . $paymentLinkInfo['status']);
                    exit;
                }
            } else {
                // Đơn hàng đã được xử lý trước đó, chỉ cần chuyển hướng
                header('Location: ../view/ViewUser/Success.php?order_id=' . $orderIdFromPayOS . '&status=already_processed');
                exit;
            }

        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("PayOS Return/Cancel Error for orderCode " . $orderIdFromPayOS . ": " . $e->getMessage());
            echo "Có lỗi xảy ra khi xử lý phản hồi từ PayOS. " . $e->getMessage();
            exit;
        }
    }

    // Inside OrderController class
    public function handlePayOSCancel()
    {
        $orderIdFromPayOS = isset($_GET['orderCode']) ? (int)$_GET['orderCode'] : null;

        if (!$orderIdFromPayOS) {
            echo "Lỗi: Mã đơn hàng không hợp lệ từ PayOS.";
            exit;
        }

        try {
            $paymentLinkInfo = $this->payOS->getPaymentLinkInformation($orderIdFromPayOS);
            $orderModel = new Order();

            // Update order status to 'cancelled_payos' or based on $paymentLinkInfo['status']
            // The getPaymentLinkInformation might show "CANCELLED"
            if ($paymentLinkInfo['status'] == 'hủy') {
                $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'hủy');
            } else {
                // If status is not explicitly CANCELLED, might just be a general failure or user navigated away
                // Keep current status or update to a general failed state if not already 'pending_payos'
                $dbOrder = $orderModel->getOrderById($orderIdFromPayOS);
                if ($dbOrder && ($dbOrder['status'] == 'đang xử lý' || $dbOrder['status'] == 'pending')) {
                    $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'hủy'); // Or a more generic status
                }
            }

            header('Location: ../view/ViewUser/Payment.php?status=payos_cancelled&order_id=' . $orderIdFromPayOS);
            exit;
        } catch (\Throwable $e) {
            error_log("PayOS Cancel Error for orderCode " . $orderIdFromPayOS . ": " . $e->getMessage());
            echo "Có lỗi xảy ra khi xử lý hủy thanh toán PayOS.";
            exit;
        }
    }



}

