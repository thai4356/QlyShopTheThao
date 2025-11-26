<?php
require_once __DIR__ . '/../model/Connect.php';
require_once __DIR__ . '/../model/Order.php';
require_once __DIR__ . '/../model/OrderItem.php';
require_once __DIR__ . '/../model/Product.php';
// ĐÃ XÓA DÒNG REQUIRE VNPAY CONFIG
require_once __DIR__ . '/../model/Cart.php';
require_once __DIR__ . '/../model/CartItem.php';

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use PayOS\PayOS;
use Stripe\Stripe;
use Stripe\Checkout\Session;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class OrderController
{
    private $payOS;
    private $ngrok_url = "https://whippet-exotic-specially.ngrok-free.app";

    public function __construct()
    {
        // Initialize PayOS SDK
        if (empty($_ENV['PAYOS_CLIENT_ID']) || empty($_ENV['PAYOS_API_KEY']) || empty($_ENV['PAYOS_CHECKSUM_KEY'])) {
            error_log("PayOS environment variables are not loaded. Check .env file and path.");
        }
        $this->payOS = new PayOS($_ENV['PAYOS_CLIENT_ID'], $_ENV['PAYOS_API_KEY'], $_ENV['PAYOS_CHECKSUM_KEY']);

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
            $product = $productModel->getById($productId);
            $qty = isset($quantities[$productId]) ? (int)$quantities[$productId] : 1;

            if ($qty > $product['stock']) {
                $qty = $product['stock'];
            }

            $actual_selling_price = (!empty($product['discount_price']) && $product['discount_price'] > 0) ? $product['discount_price'] : $product['price'];

            if (isset($cartItems[$productId])) {
                $cartItems[$productId]['quantity'] += $qty;
            } else {
                $cartItems[$productId] = [
                    'product_id' => $productId,
                    'name' => $product['name'],
                    'image_url' => $product['image_url'],
                    'price' => $actual_selling_price,
                    'quantity' => $qty,
                ];
            }
        }

        $_SESSION['checkout_items'] = array_values($cartItems);

        header("Location: ../ViewUser/Payment.php");
        exit;
    }

    public function processPayment()
    {
        // Xử lý cho COD (Thanh toán khi nhận hàng)
        $paymentMethod = $_POST['payment_method'] ?? '';
        $name = $_POST['hoten'] ?? '';
        $address = $_POST['diachi'] ?? '';
        $phone = $_POST['dienthoai'] ?? '';

        if ($paymentMethod == '' || $name == '' || $address == '' || $phone == '') {
            echo "Vui lòng nhập đầy đủ thông tin thanh toán!";
            return;
        }

        $userId = $_SESSION['user_id'] ?? 1;
        $cartItems = $_SESSION['checkout_items'] ?? [];
        $totalPrice = 0;
        $productModel = new Product();

        foreach ($cartItems as &$item) {
            $product = $productModel->getById($item['product_id']);
            // Giữ giá bán đã tính ở bước trước (index) hoặc lấy lại từ DB nếu cần an toàn
            // Ở đây ta tin tưởng session từ index() đã xử lý giá

            if ($product['stock'] == 0) {
                echo "Sản phẩm '{$product['name']}' đã hết hàng.";
                return;
            }

            if ($item['quantity'] > $product['stock']) {
                echo "<div style='margin: 50px auto; width: 80%; padding: 15px; background-color: #ffe6e6; color: red; border: 1px solid red; border-radius: 5px; text-align: center; font-weight: bold;'>
        Sản phẩm '{$product['name']}' chỉ còn {$product['stock']} sản phẩm trong kho do một người dùng khác vừa mới mua sản phẩm.
        <br><br>
        <a href='Index.php?module=home' style='color: blue;'>Quay lại trang chủ</a>
             </div>";
                return;
            }
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // Tính giảm giá và ship cho COD (để khớp với hiển thị Payment.php)
        $shipping = 30000;
        $discount = $totalPrice * 0.10;
        $grandTotal = $totalPrice + $shipping - $discount;

        $orderModel = new Order();
        // Lưu grandTotal vào DB
        $orderId = $orderModel->createOrder($userId, $grandTotal, $paymentMethod, $name, $address, $phone);

        $orderItemModel = new OrderItem();
        foreach ($cartItems as &$item) {
            $orderItemModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
            $productModel->reduceStock($item['product_id'], $item['quantity']);
            $productModel->increseSold($item['product_id'], $item['quantity']);
        }

        $cartModel = new Cart();
        $cart = $cartModel->getCartByUserId($userId);
        if ($cart) {
            $cartId = $cart['id'];
            $cartItemModel = new CartItem();
            foreach ($cartItems as &$item) {
                $cartItemModel->removeItem($cartId, $item['product_id']);
            }
        }

        unset($_SESSION['checkout_items']);
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

        $order = $orderModel->getOrderById($orderId);
        $items = $orderItemModel->getItemsByOrderId($orderId);

        foreach ($items as $index => $item) {
            $product = $productModel->getById($item['product_id']);
            $items[$index]['product_name'] = $product['name'];
            $items[$index]['image_url'] = $product['image_url'];
        }

        include __DIR__ . '/../view/ViewUser/OrderDetail.php';
    }

    // --- CÁC HÀM VNPAY ĐÃ BỊ XÓA TẠI ĐÂY ---

    public function initiatePayOSPayment()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_items']) || empty($_SESSION['checkout_items'])) {
            echo "Lỗi: Thông tin người dùng hoặc giỏ hàng không hợp lệ.";
            exit;
        }

        $userId = $_SESSION['user_id'];
        $cartItems = $_SESSION['checkout_items'];

        $name = isset($_POST['hoten']) ? trim($_POST['hoten']) : '';
        $address = isset($_POST['diachi']) ? trim($_POST['diachi']) : '';
        $phone = isset($_POST['dienthoai']) ? trim($_POST['dienthoai']) : '';

        if (empty($name) || empty($address) || empty($phone)) {
            echo "Vui lòng nhập đầy đủ thông tin giao hàng.";
            exit;
        }

        $subTotal = 0;
        foreach ($cartItems as $item) {
            $subTotal += $item['price'] * $item['quantity'];
        }

        $shippingFee = 30000;
        $discountPercentage = 0.10;
        $discountAmount = $subTotal * $discountPercentage;

        // Tính tổng tiền cần thanh toán
        $finalTotal = $subTotal + $shippingFee - $discountAmount;
        // PayOS yêu cầu tối thiểu 2000 VND (hoặc bạn có thể hardcode test như cũ là 2000)
        // Nếu muốn test: $finalTotal = 2000;
        if ($finalTotal < 2000) $finalTotal = 2000;

        $orderModel = new Order();
        $orderId = $orderModel->createOrder($userId, $finalTotal, 'payos', $name, $address, $phone);

        if (!$orderId) {
            echo "Lỗi khi tạo đơn hàng trong cơ sở dữ liệu.";
            exit;
        }

        // Cập nhật trạng thái ban đầu
        $orderModel->updateOrderStatusAndTxn($orderId, 'đang xử lý');

        $orderItemModel = new OrderItem();
        $payosItems = [];
        // PayOS Items chỉ dùng để hiển thị trên trang thanh toán của PayOS
        // Ta có thể gom đơn giản thành 1 dòng "Thanh toán đơn hàng #ID" để tránh lỗi làm tròn số lẻ
        $payosItems[] = [
            "name" => "Thanh toán đơn hàng #" . $orderId,
            "quantity" => 1,
            "price" => (int)$finalTotal
        ];

        // Lưu item vào DB
        foreach ($cartItems as $cartItem) {
            $orderItemModel->addItem($orderId, $cartItem['product_id'], $cartItem['quantity'], $cartItem['price']);
        }

        $returnUrl = $this->ngrok_url . "/QlyShopTheThao/src/controller/payos_return_handler.php";
        $cancelUrl = $this->ngrok_url . "/QlyShopTheThao/src/controller/payos_cancel_handler.php";

        $paymentData = [
            "orderCode" => (int)$orderId,
            "amount" => (int)$finalTotal,
            "description" => "Don hang #" . $orderId,
            "items" => $payosItems,
            "buyerName" => $name,
            "buyerPhone" => $phone,
            "cancelUrl" => $cancelUrl,
            "returnUrl" => $returnUrl,
        ];

        try {
            $payosResponse = $this->payOS->createPaymentLink($paymentData);

            if (isset($payosResponse['paymentLinkId'])) {
                $orderModel->updateOrderPayOSInfo($orderId, $payosResponse['paymentLinkId']);
            }

            header('Location: ' . $payosResponse['checkoutUrl']);
            exit;
        } catch (\Throwable $e) {
            error_log("PayOS Error: " . $e->getMessage());
            echo "Có lỗi xảy ra trong quá trình tạo liên kết thanh toán PayOS: " . $e->getMessage();
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

        $conn = (new Connect())->getConnection();

        try {
            $paymentLinkInfo = $this->payOS->getPaymentLinkInformation($orderIdFromPayOS);
            $orderModel = new Order();
            $dbOrder = $orderModel->getOrderById($orderIdFromPayOS);

            if (!$dbOrder) {
                echo "Lỗi: Không tìm thấy đơn hàng trong hệ thống.";
                exit;
            }

            if ($dbOrder['status'] == 'đang xử lý') {
                if ($paymentLinkInfo['status'] == 'PAID' || $statusFromQuery == 'PAID') {

                    $conn->beginTransaction();

                    $orderItemModel = new OrderItem();
                    $productModel = new Product();
                    $itemsInOrder = $orderItemModel->getItemsByOrderId($orderIdFromPayOS);
                    $canProcess = true;

                    foreach ($itemsInOrder as $item) {
                        $rowsAffected = $productModel->reduceStock($item['product_id'], $item['quantity']);
                        if ($rowsAffected == 0) {
                            $canProcess = false;
                            error_log("OVERSALE on PayOS Return for Order ID: " . $orderIdFromPayOS);
                            break;
                        }
                    }

                    if ($canProcess) {
                        foreach ($itemsInOrder as $item) {
                            $productModel->increseSold($item['product_id'], $item['quantity']);
                        }

                        $transactionTime = !empty($paymentLinkInfo['transactions']) ? date('Y-m-d H:i:s', strtotime($paymentLinkInfo['transactions'][0]['transactionDateTime'])) : date('Y-m-d H:i:s');
                        $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'đã thanh toán', $paymentLinkInfo['orderCode'], $transactionTime);

                        $userId = $dbOrder['user_id'];
                        $cartModel = new Cart();
                        $userCart = $cartModel->getCartByUserId($userId);
                        if ($userCart) {
                            $cartItemModel = new CartItem();
                            foreach ($itemsInOrder as $orderedItem) {
                                $cartItemModel->removeItem($userCart['id'], $orderedItem['product_id']);
                            }
                        }

                        $conn->commit();
                        unset($_SESSION['checkout_items']);
                        header('Location: ../view/ViewUser/Success.php?order_id=' . $orderIdFromPayOS . '&payment_method=payos&status=success');
                        exit;

                    } else {
                        $conn->rollBack();
                        $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'chờ hoàn tiền');
                        header('Location: ../view/ViewUser/Payment.php?error=oversold_and_refund_pending&order_id=' . $orderIdFromPayOS);
                        exit;
                    }

                } else {
                    $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'hủy');
                    header('Location: ../view/ViewUser/Payment.php?error=payos_failed&order_id=' . $orderIdFromPayOS . '&payos_status=' . $paymentLinkInfo['status']);
                    exit;
                }
            } else {
                header('Location: ../view/ViewUser/Success.php?order_id=' . $orderIdFromPayOS . '&status=already_processed');
                exit;
            }

        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("PayOS Return Error: " . $e->getMessage());
            echo "Có lỗi xảy ra: " . $e->getMessage();
            exit;
        }
    }

    public function handlePayOSCancel()
    {
        $orderIdFromPayOS = isset($_GET['orderCode']) ? (int)$_GET['orderCode'] : null;

        if (!$orderIdFromPayOS) {
            echo "Lỗi: Mã đơn hàng không hợp lệ.";
            exit;
        }

        try {
            $paymentLinkInfo = $this->payOS->getPaymentLinkInformation($orderIdFromPayOS);
            $orderModel = new Order();

            if ($paymentLinkInfo['status'] == 'hủy' || $paymentLinkInfo['status'] == 'CANCELLED') {
                $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'hủy');
            } else {
                $dbOrder = $orderModel->getOrderById($orderIdFromPayOS);
                if ($dbOrder && ($dbOrder['status'] == 'đang xử lý')) {
                    $orderModel->updateOrderPayOSInfo($orderIdFromPayOS, $paymentLinkInfo['id'], 'hủy');
                }
            }

            header('Location: ../view/ViewUser/Payment.php?status=payos_cancelled&order_id=' . $orderIdFromPayOS);
            exit;
        } catch (\Throwable $e) {
            error_log("PayOS Cancel Error: " . $e->getMessage());
            echo "Có lỗi xảy ra khi xử lý hủy thanh toán PayOS.";
            exit;
        }
    }

    public function initiateStripePayment()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_items']) || empty($_SESSION['checkout_items'])) {
            echo "Lỗi: Thông tin người dùng hoặc giỏ hàng không hợp lệ.";
            exit;
        }

        // Cấu hình Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $userId = $_SESSION['user_id'];
        $cartItems = $_SESSION['checkout_items'];
        $name = $_POST['hoten'] ?? '';
        $address = $_POST['diachi'] ?? '';
        $phone = $_POST['dienthoai'] ?? '';

        // Tính toán tổng tiền (như cũ)
        $subTotal = 0;
        foreach ($cartItems as $item) {
            $subTotal += $item['price'] * $item['quantity'];
        }
        $shippingFee = 30000;
        $discount = $subTotal * 0.10;
        $finalTotal = $subTotal + $shippingFee - $discount;

        // Tạo đơn hàng trong DB
        $orderModel = new Order();
        $orderId = $orderModel->createOrder($userId, $finalTotal, 'stripe', $name, $address, $phone);

        if (!$orderId) {
            echo "Lỗi tạo đơn hàng.";
            exit;
        }

        // Lưu chi tiết đơn hàng
        $orderItemModel = new OrderItem();
        foreach ($cartItems as $item) {
            $orderItemModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
        }

        // Tạo URL trả về
        $returnUrl = $this->ngrok_url . "/QlyShopTheThao/src/controller/stripe_return_handler.php?session_id={CHECKOUT_SESSION_ID}&order_id=" . $orderId;
        $cancelUrl = $this->ngrok_url . "/QlyShopTheThao/src/view/ViewUser/Payment.php?error=stripe_cancelled";

        try {
            // Tạo Stripe Checkout Session
            // Để đảm bảo tổng tiền khớp chính xác 100% với PHP, ta gom thành 1 dòng item
            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'vnd',
                        'product_data' => [
                            'name' => 'Thanh toán đơn hàng #' . $orderId,
                            'description' => "Khách hàng: $name - SĐT: $phone",
                        ],
                        'unit_amount' => (int)$finalTotal, // Stripe VND không dùng số thập phân
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ]);

            // Cập nhật session_id vào DB
            $orderModel->updateOrderStripeInfo($orderId, $checkout_session->id, 'đang xử lý');

            // Chuyển hướng sang Stripe
            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
            exit;

        } catch (\Exception $e) {
            error_log("Stripe Error: " . $e->getMessage());
            echo "Lỗi kết nối Stripe: " . $e->getMessage();
            exit;
        }
    }

    public function handleStripeReturn()
    {
        // 1. Lấy dữ liệu từ URL trả về
        $sessionId = $_GET['session_id'] ?? null;
        $orderId = $_GET['order_id'] ?? null;

        if (!$sessionId || !$orderId) {
            echo "Thiếu thông tin xác thực thanh toán.";
            exit;
        }

        // Cấu hình Stripe
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $conn = (new Connect())->getConnection();

        try {
            // 2. Lấy thông tin Session từ Stripe để kiểm tra trạng thái
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            $orderModel = new Order();
            $dbOrder = $orderModel->getOrderById($orderId);

            // Kiểm tra xem đơn hàng có hợp lệ không
            if (!$dbOrder || $dbOrder['status'] != 'đang xử lý') {
                header('Location: ../view/ViewUser/Success.php?order_id=' . $orderId . '&status=already_processed');
                exit;
            }

            // 3. Nếu thanh toán thành công ('paid')
            if ($session->payment_status == 'paid') {

                $conn->beginTransaction(); // Bắt đầu giao dịch DB

                $orderItemModel = new OrderItem();
                $productModel = new Product();

                // Lấy danh sách sản phẩm trong đơn hàng (chính là các sản phẩm đã tick)
                $itemsInOrder = $orderItemModel->getItemsByOrderId($orderId);
                $canProcess = true;

                // --- BƯỚC A: TRỪ KHO ---
                foreach ($itemsInOrder as $item) {
                    $rowsAffected = $productModel->reduceStock($item['product_id'], $item['quantity']);
                    if ($rowsAffected == 0) {
                        $canProcess = false; // Hết hàng
                        break;
                    }
                }

                if ($canProcess) {
                    // Tăng lượt bán
                    foreach ($itemsInOrder as $item) {
                        $productModel->increseSold($item['product_id'], $item['quantity']);
                    }

                    // Cập nhật trạng thái đơn hàng
                    $orderModel->updateOrderStripeInfo($orderId, $sessionId, 'đã thanh toán');

                    // --- BƯỚC B: XÓA SẢN PHẨM KHỎI GIỎ HÀNG (YÊU CẦU CỦA BẠN) ---
                    // 1. Lấy giỏ hàng của user hiện tại
                    $cartModel = new Cart();
                    $userCart = $cartModel->getCartByUserId($dbOrder['user_id']); // $dbOrder['user_id'] lấy từ DB cho chính xác

                    if ($userCart) {
                        $cartItemModel = new CartItem();
                        // 2. Duyệt qua từng sản phẩm trong ĐƠN HÀNG và xóa khỏi GIỎ HÀNG
                        foreach ($itemsInOrder as $orderedItem) {
                            // Gọi hàm removeItem mà bạn đã có trong model CartItem
                            // Chỉ xóa đúng sản phẩm đã mua, giữ lại các sản phẩm chưa tick
                            $cartItemModel->removeItem($userCart['id'], $orderedItem['product_id']);
                        }
                    }
                    // -----------------------------------------------------------

                    $conn->commit(); // Lưu thay đổi vào DB

                    // Xóa session checkout_items cho sạch sẽ
                    if (session_status() == PHP_SESSION_NONE) session_start();
                    unset($_SESSION['checkout_items']);

                    header('Location: ../view/ViewUser/Success.php?order_id=' . $orderId . '&payment_method=stripe&status=success');
                    exit;

                } else {
                    // Trường hợp đã thanh toán tiền nhưng kho hết hàng đột xuất
                    $conn->rollBack();
                    $orderModel->updateOrderStripeInfo($orderId, $sessionId, 'chờ hoàn tiền');
                    header('Location: ../view/ViewUser/Payment.php?error=oversold_stripe&order_id=' . $orderId);
                    exit;
                }

            } else {
                header('Location: ../view/ViewUser/Payment.php?error=stripe_payment_failed');
                exit;
            }

        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Stripe Return Error: " . $e->getMessage());
            echo "Có lỗi xảy ra: " . $e->getMessage();
            exit;
        }
    }

}