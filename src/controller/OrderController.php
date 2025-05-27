<?php
require_once __DIR__ . '/../model/Order.php';
require_once __DIR__ . '/../model/OrderItem.php';
require_once __DIR__ . '/../model/Product.php';

class OrderController {
    public function index() {


        $selectedItems = isset($_POST['select_item']) ? $_POST['select_item'] : [];
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

            // Giới hạn số lượng theo tồn kho
            if ($qty > $product['stock']) {
                $qty = $product['stock'];
            }

            $cartItems[] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'image_url' => $product['image_url'],
                'price' => $product['price'],
                'quantity' => $qty,
            ];
        }

        $_SESSION['checkout_items'] = $cartItems;

        header("Location: ../ViewUser/Payment.php");
        exit;
    }

    public function processPayment() {
        session_start();



        // Lấy dữ liệu từ form thanh toán
        $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
        $name = isset($_POST['hoten']) ? $_POST['hoten'] : '';
        $address = isset($_POST['diachi']) ? $_POST['diachi'] : '';
        $phone = isset($_POST['dienthoai']) ? $_POST['dienthoai'] : '';

        // Kiểm tra dữ liệu bắt buộc
        if ($paymentMethod == '' || $name == '' || $address == '' || $phone == '') {
            echo "Vui lòng nhập đầy đủ thông tin thanh toán!";
            return;
        }

        // Lấy user_id từ session (mặc định là 1 nếu chưa có)
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

        $cartItems = $_SESSION['checkout_items'];
        $totalPrice = 0;
        $productModel = new Product();

        // Tính tổng và kiểm tra tồn kho
        foreach ($cartItems as &$item) {
            $product = $productModel->getById($item['product_id']);
            $item['price'] = $product['price'];

            if ($item['quantity'] > $product['stock']) {
                $item['quantity'] = $product['stock'];
            }

            $totalPrice += $item['price'] * $item['quantity'];
        }

        $orderModel = new Order();
        $orderId = $orderModel->createOrder($userId, $totalPrice, $paymentMethod, $name, $address, $phone);

        // Lưu chi tiết từng sản phẩm
        $orderItemModel = new OrderItem();
        foreach ($cartItems as $item) {
            $orderItemModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
            $productModel->reduceStock($item['product_id'], $item['quantity']);
        }

        // Xóa session sau khi thanh toán
        unset($_SESSION['checkout_items']);

        // Chuyển hướng sang trang thành công
        header("Location: ../view/ViewUser/success.php?order_id=" . $orderId);
        exit;
    }

}

