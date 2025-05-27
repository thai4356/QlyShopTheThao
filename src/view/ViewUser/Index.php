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



