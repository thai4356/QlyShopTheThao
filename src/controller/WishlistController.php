<?php
session_start();
require_once '../model/connect.php';
require_once '../model/Wishlist.php';

$database = new Connect();
$conn = $database->getConnection();

class WishlistController {
    public $wishlistModel;

    public function __construct($conn) {
        $this->wishlistModel = new Wishlist($conn);
    }

    public function addToWishlist($user_id, $product_id) {
        if (!$this->wishlistModel->exists($user_id, $product_id)) {
            $this->wishlistModel->add($user_id, $product_id);
            $_SESSION['message'] = "Đã thêm vào danh sách yêu thích.";
        } else {
            $_SESSION['message'] = "Sản phẩm đã có trong danh sách yêu thích.";
        }
    }

    public function removeFromWishlist($user_id, $product_id) {
        if ($this->wishlistModel->exists($user_id, $product_id)) {
            $this->wishlistModel->remove($user_id, $product_id);
            $_SESSION['message'] = "Đã xoá khỏi danh sách yêu thích.";
        } else {
            $_SESSION['message'] = "Sản phẩm không tồn tại trong danh sách yêu thích.";
        }
    }

    public function getUserWishlist($user_id) {
        return $this->wishlistModel->getWishlistByUser($user_id);
    }
}

// --- Xử lý các hành động được gửi từ form ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = "Bạn cần đăng nhập.";
        header("Location: ../../view/login.php");
        exit();
    }

    $ctrl = new WishlistController($conn);
    $productId = intval($_POST['product_id']);

    // Phân biệt action add hay remove
    if (isset($_POST['action']) && $_POST['action'] === 'remove') {
        $ctrl->removeFromWishlist($_SESSION['user_id'], $productId);
    } else {
        $ctrl->addToWishlist($_SESSION['user_id'], $productId);
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
