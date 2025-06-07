<?php
require_once __DIR__ . '/../../../src/model/admin/AdminReview.php';

class AdminReviewController {

    /**
     * Cung cấp dữ liệu cho DataTables dưới dạng JSON
     */
    public function ajaxGetReviewsForDataTable() {
        $draw = $_POST['draw'] ?? 0;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = $_POST['search']['value'] ?? '';
        $orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
        $orderDir = $_POST['order'][0]['dir'] ?? 'desc';

        // LẤY THÊM CÁC THAM SỐ FILTER
        $filterProductId = $_POST['product_id'] ?? null;
        $filterUserId = $_POST['user_id'] ?? null;
        $filterRating = $_POST['rating'] ?? null;
        $filterStatus = $_POST['status'] ?? null;


        $adminReviewModel = new AdminReview();
        $result = $adminReviewModel->getReviewsForDataTable($start, $length, $searchValue, $orderColumnIndex, $orderDir, $filterProductId, $filterUserId, $filterRating, $filterStatus);

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $result['recordsTotal'],
            "recordsFiltered" => $result['recordsFiltered'],
            "data" => $result['data']
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    /**
     * Hiển thị trang quản lý đánh giá
     */
    public function listReviews() {
        $adminReviewModel = new AdminReview();
        // LẤY DỮ LIỆU CHO CÁC COMBOBOX
        $all_products = $adminReviewModel->getProductsWithReviews();
        $all_users = $adminReviewModel->getUsersWithReviews();

        $view_data = [
            'page_name' => 'reviews',
            'pageTitle' => 'Quản Lý Đánh Giá',
            'page_scripts' => ['assets/js/admin-reviews.js'],
            'all_products' => $all_products, // Dữ liệu sản phẩm
            'all_users' => $all_users       // Dữ liệu người dùng
        ];
        return $view_data;
    }

    // HÀM MỚI: Hiển thị trang chi tiết đánh giá
    public function showReviewDetails() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php?page=reviews&error=invalid_id');
            exit();
        }
        $reviewId = (int)$_GET['id'];
        $adminReviewModel = new AdminReview();

        // Lấy thông tin chi tiết của review
        $review_details = $adminReviewModel->getReviewById($reviewId);

        if (!$review_details) {
            header('Location: index.php?page=reviews&error=not_found');
            exit();
        }

        // Lấy tất cả ảnh của sản phẩm được review
        $product_images = $adminReviewModel->getProductImages($review_details['product_id']);

        $view_data = [
            'page_name' => 'review_details',
            'pageTitle' => 'Chi tiết Đánh giá',
            'review_details' => $review_details,
            'product_images' => $product_images
        ];
        return $view_data;
    }

    public function ajaxSubmitReply() {
        $response = ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.'];

        // Kiểm tra dữ liệu đầu vào
        if (isset($_POST['review_id']) && is_numeric($_POST['review_id']) && isset($_POST['admin_reply'])) {
            $reviewId = (int)$_POST['review_id'];
            // trim() để xóa các khoảng trắng thừa ở đầu và cuối
            $replyText = trim($_POST['admin_reply']);

            // Khởi tạo model và gọi phương thức cập nhật
            $adminReviewModel = new AdminReview();
            if ($adminReviewModel->saveAdminReply($reviewId, $replyText)) {
                $response = ['status' => 'success', 'message' => 'Đã lưu phản hồi thành công!'];
            } else {
                $response['message'] = 'Lỗi khi lưu phản hồi vào cơ sở dữ liệu.';
            }
        }

        // Trả về kết quả dạng JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // HÀM MỚI: Xử lý yêu cầu AJAX để ẩn/hiện một đánh giá
    public function ajaxToggleReviewStatus() {
        $response = ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.'];

        if (isset($_POST['review_id']) && is_numeric($_POST['review_id']) && isset($_POST['new_status'])) {
            $reviewId = (int)$_POST['review_id'];
            $newStatus = $_POST['new_status'];

            $adminReviewModel = new AdminReview();
            if ($adminReviewModel->updateReviewStatus($reviewId, $newStatus)) {
                $response = ['status' => 'success', 'message' => 'Cập nhật trạng thái thành công!'];
            } else {
                $response['message'] = 'Cập nhật thất bại hoặc trạng thái không hợp lệ.';
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // HÀM MỚI: Xử lý yêu cầu AJAX để xóa vĩnh viễn một đánh giá
    public function ajaxDeleteReview() {
        $response = ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.'];

        if (isset($_POST['review_id']) && is_numeric($_POST['review_id'])) {
            $reviewId = (int)$_POST['review_id'];

            $adminReviewModel = new AdminReview();
            if ($adminReviewModel->deleteReviewById($reviewId)) {
                $response = ['status' => 'success', 'message' => 'Đã xóa đánh giá thành công!'];
            } else {
                $response['message'] = 'Xóa đánh giá trong cơ sở dữ liệu thất bại.';
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

}
?>
