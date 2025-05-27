<?php

require_once __DIR__ . '/../model/Cart.php';
require_once __DIR__ . '/../model/CartItem.php';

class CartController {
    private $cartModel;
    private $itemModel;

    public function __construct() {
        $this->cartModel = new Cart();
        $this->itemModel = new CartItem();
    }

    // Hiển thị giỏ hàng
    public function index() {
        $userId = $_SESSION['user_id'];
        $cart = $this->cartModel->getCartByUserId($userId);

        if (!$cart) {
            $cartId = $this->cartModel->createCart($userId);
        } else {
            $cartId = $cart['id'];
        }

        $items = $this->itemModel->getItems($cartId);
        include __DIR__ . '/../view/ViewUser/Cart.php';
    }

    // Thêm vào giỏ
    public function add() {
        $userId = $_SESSION['user_id'];
        $productId = $_GET['masp'];
        $quantity = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;

        $cart = $this->cartModel->getCartByUserId($userId);
        $cartId = $cart ? $cart['id'] : $this->cartModel->createCart($userId);

        $this->itemModel->addItem($cartId, $productId, $quantity);
        header("Location: ?module=cart");
        exit;
    }

    public function remove() {
        $userId = $_SESSION['user_id'];
        $productId = $_GET['masp'];

        $cart = $this->cartModel->getCartByUserId($userId);
        if ($cart) {
            $this->itemModel->removeItem($cart['id'], $productId);
        }

        header("Location: ?module=cart");
        exit;
    }
}
