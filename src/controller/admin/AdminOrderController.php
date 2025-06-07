<?php
// src/controller/admin/AdminOrderController.php

require_once __DIR__ . '/../../../src/model/admin/AdminOrder.php';
require_once __DIR__ . '/../../model/admin/AdminOrderItem.php';
require_once __DIR__ . '/../../model/admin/AdminProduct.php';
use Mpdf\Mpdf;

require_once __DIR__ . '/../../../vendor/autoload.php'; // Composer autoloader

class AdminOrderController
{
    /**
     * Hiển thị trang quản lý đơn hàng.
     * @return array Dữ liệu cần thiết cho view
     */
    public function listOrders()
    {
        // Thêm dòng này để tạo instance của model
        $adminOrderModel = new AdminOrder();

        // Lấy danh sách email
        $customer_emails = $adminOrderModel->getUniqueCustomerEmails();

        // Các script JS cần thiết cho trang này
        $page_scripts = [
            'assets/js/orders-datatable-init.js'
        ];

        return [
            'page_name' => 'orders',
            'pageTitle' => 'Quản Lý Đơn Hàng',
            'page_scripts' => $page_scripts, // Truyền danh sách script cho view
            'customer_emails' => $customer_emails
        ];
    }

    /**
     * Cung cấp dữ liệu đơn hàng cho DataTables thông qua AJAX.
     * Sẽ được gọi từ file JS.
     */
    public function ajaxGetOrdersForDataTable()
    {
        header('Content-Type: application/json');

        $adminOrderModel = new AdminOrder();
        $orders = $adminOrderModel->getAllOrdersWithUserDetails();

        // Trả về dữ liệu dưới định dạng mà DataTables yêu cầu
        echo json_encode(['data' => $orders]);
        exit();
    }

    /**
     * Hiển thị trang chi tiết một đơn hàng.
     * @return array
     */
    public function showOrderDetails()
    {
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$orderId) {
            // Nếu ID không hợp lệ, có thể chuyển hướng về trang danh sách
            header('Location: index.php?page=orders');
            exit();
        }

        $page_scripts = [
            'assets/js/order-details-modal.js',
            'assets/js/order-status-handler.js'
        ];

        $adminOrderModel = new AdminOrder();
        $orderDetails = $adminOrderModel->getOrderById($orderId);
        $orderItems = $adminOrderModel->getOrderItemsWithImages($orderId);

        // Nếu không tìm thấy đơn hàng, hiển thị trang lỗi hoặc quay về danh sách
        if (!$orderDetails) {
            // Bạn có thể tạo một trang lỗi 404 riêng
            echo "<h1>Lỗi 404: Đơn hàng không tồn tại.</h1>";
            exit();
        }

        // Trả về dữ liệu cho view
        return [
            'page_name' => 'order_details',
            'pageTitle' => 'Chi tiết Đơn hàng #' . $orderId,
            'orderDetails' => $orderDetails,
            'orderItems' => $orderItems,
            'page_scripts' => $page_scripts
        ];
    }

    /**
     * Xử lý yêu cầu AJAX để cập nhật trạng thái đơn hàng.
     */
    public function ajaxUpdateOrderStatus()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $newStatus = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

        if (!$orderId || !$newStatus) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            exit;
        }

        $orderModel = new AdminOrder();

        // Bạn có thể thêm logic kiểm tra ở đây:
        // Ví dụ: chỉ cho phép chuyển sang "đã giao" nếu trạng thái hiện tại là "đang xử lý" hoặc "đã thanh toán"
        // $currentOrder = $orderModel->getOrderById($orderId);
        // if ($newStatus === 'đã giao' && !in_array($currentOrder['status'], ['đang xử lý', 'đã thanh toán'])) { ... }

        $result = $orderModel->updateStatus($orderId, $newStatus);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, không thể cập nhật trạng thái.']);
        }
        exit;
    }

    public function printInvoice()
    {
        // 1. Lấy ID đơn hàng từ URL
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId) {
            die("ID đơn hàng không hợp lệ.");
        }

        // 2. Lấy dữ liệu chi tiết đơn hàng và sản phẩm từ Model
        $orderModel = new AdminOrder();
        $orderDetails = $orderModel->getOrderById($orderId);
        $orderItems = $orderModel->getOrderItemsWithImages($orderId);

        if (!$orderDetails) {
            die("Không tìm thấy đơn hàng.");
        }

        // 3. Dùng output buffering để lấy nội dung HTML từ một file template
        ob_start();
        // Truyền biến vào file template
        include __DIR__ . '/../../../src/view/ViewAdmin/templates/invoice_template.php';
        $html = ob_get_clean();

        // 4. Khởi tạo và cấu hình mpdf
        try {
            // Tạo thư mục tạm nếu chưa có
            $tempDir = __DIR__ . '/../../../logs/mpdf_temp';
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'tempDir' => $tempDir
            ]);

            // Ghi nội dung HTML vào PDF
            $mpdf->WriteHTML($html);

            // 5. Xuất file PDF ra trình duyệt
            $mpdf->Output('hoa-don-' . $orderId . '.pdf', 'I'); // 'I' để mở ngay trên trình duyệt

        } catch (\Mpdf\MpdfException $e) {
            echo "Lỗi khi tạo PDF: " . $e->getMessage();
        }
        exit;
    }

    /**
     * Xử lý AJAX để hủy đơn hàng đã thanh toán và hoàn trả sản phẩm về kho.
     */
    public function ajaxCancelAndRestockOrder()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ.']);
            exit;
        }

        // Lấy connection để dùng transaction
        $conn = (new AdminConnect())->getConnection();

        // Khởi tạo các model cần thiết
        $orderModel = new AdminOrder();
        $orderItemModel = new AdminOrderItem(); // Giả sử bạn có AdminOrderItem.php với getItemsByOrderId
        $productModel = new AdminProduct();

        try {
            $conn->beginTransaction();

            // 1. Lấy tất cả sản phẩm trong đơn hàng
            $items = $orderItemModel->getItemsByOrderId($orderId);
            if (empty($items)) {
                throw new Exception('Không tìm thấy sản phẩm nào trong đơn hàng.');
            }

            // 2. Lặp qua từng sản phẩm để hoàn kho và giảm số lượng đã bán
            foreach ($items as $item) {
                $productModel->restockProduct($item['product_id'], $item['quantity']);
                $productModel->decreaseSoldCount($item['product_id'], $item['quantity']);
            }

            // 3. Cập nhật trạng thái đơn hàng thành "hủy"
            $orderModel->updateStatus($orderId, 'hủy');

            // 4. Nếu tất cả thành công, commit transaction
            $conn->commit();

            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng và hoàn trả sản phẩm về kho thành công!']);

        } catch (Exception $e) {
            // Nếu có lỗi, rollback tất cả thay đổi
            $conn->rollBack();
            error_log("Failed to cancel and restock order ID $orderId: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
        exit;
    }



}