<?php
require_once __DIR__ . '/../model/Review.php';

class ReviewController {
    public function loadProductReviews($productId) {
        $reviewModel = new Review();
        return $reviewModel->getByProductId($productId);
    }

    public function submitReview() {
        session_start();

        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $productId = isset($_POST['product_id']) ? $_POST['product_id'] : null;
        $rating = isset($_POST['rating']) ? $_POST['rating'] : null;
        $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

        if (!$userId || !$productId || !$rating) {
            $_SESSION['message'] = "Vui lòng nhập đầy đủ thông tin.";
        } else {
            $reviewModel = new Review();
            try{
                $result = $reviewModel->addReview($userId, $productId, $rating, $comment);
            }catch(PDOException $e){
                $_SESSION['message'] = "Bạn đã đánh giá sản phẩm này rồi.";
                header("Location: ../view/ViewUser/Index.php?module=chitietsanpham&masp=$productId");
                exit;
            }
                $_SESSION['message'] = "Cảm ơn bạn đã đánh giá!";
        }
        header("Location: ../view/ViewUser/Index.php?module=chitietsanpham&masp=$productId");
        exit;
    }
}

// Router xử lý hành động submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'submitReview') {
    $ctrl = new ReviewController();
    $ctrl->submitReview();
}
