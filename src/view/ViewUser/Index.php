<?php
session_start();



$module = isset($_GET['module']) ? $_GET['module'] : 'home';


if ($module === 'cart' && isset($_GET['act']) && in_array($_GET['act'], ['add', 'remove'])) {
    require_once '../../controller/CartController.php';
    $cartCtrl = new CartController();

    if ($_GET['act'] === 'add') {
        $cartCtrl->add();
    } elseif ($_GET['act'] === 'remove') {
        $cartCtrl->remove();
    }

    exit;
}

if (!in_array($module, ['home', 'sanpham']) && !isset($_SESSION['username'])) {
    echo '<script>alert("Dang nhap de su dung cac tinh nang khac"); window.location.href="?module=home";</script>';
    exit;
}


switch ($module) {
    case 'order':
        require_once '../../controller/OrderController.php';
        $ctrl = new OrderController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
            $ctrl->processPayment();
        } else {

            $ctrl->index();
        }
        exit;

    case 'cart':
        require_once '../../controller/CartController.php';
        $controller = new CartController();
        break;

    case 'sanpham':
        ;

        require_once '../../controller/ProductController.php';
        $controller = new ProductController();
        break;

    case 'chitietsanpham':
        require_once '../../controller/ProductController.php';
        $controller = new ProductController();
        $id = isset($_GET['masp']) ? (int)$_GET['masp'] : 0;
        include 'header.php';
        $controller->detail($id);
        include 'footer.php';
        exit;

    case 'orderhistory':
        require_once '../../controller/OrderController.php';
        $controller = new OrderController();
        include 'header.php';
        $controller->viewOrderHistory();
        include 'footer.php';
        exit;

    case 'orderdetail':
        require_once '../../controller/OrderController.php';
        $controller = new OrderController();
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        include 'header.php';
        $controller->viewOrderDetail($orderId);
        include 'footer.php';
        exit;

    case 'wishlist':
        require_once '../../model/connect.php';
        require_once '../../model/Wishlist.php';
        $conn = (new Connect())->getConnection();

        if (!isset($_SESSION['user_id'])) {
            echo '<script>alert("Vui lòng đăng nhập để xem danh sách yêu thích"); window.location.href="?module=home";</script>';
            exit;
        }

        $wishlist = new Wishlist($conn);
        $wishlistItems = $wishlist->getWishlistByUser($_SESSION['user_id']);

        include 'header.php';
        include '../ViewUser/WishlistPage.php';
        include 'footer.php';
        exit;



    case 'home':
    default:
        include 'header.php';
        include 'Menu.php';
        include 'footer.php';
        exit;
}

include 'header.php';
$controller->index();
include 'footer.php';
?>

<script>
    window.history.replaceState(null, null, window.location.href);
</script>



