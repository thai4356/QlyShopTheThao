<?php
require_once __DIR__ . '/../model/Product.php';


class ProductController {
    private $productModel;

    public function index() {
        $productModel = new Product();

        $limit = 6;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Gộp các filter – thay ?? bằng isset(...) ? ... : ...
        $filters = [
            'location' => isset($_GET['location']) ? $_GET['location'] : [],
            'brand' => isset($_GET['brand']) ? $_GET['brand'] : [],
            'price_min' => isset($_GET['price_min']) ? $_GET['price_min'] : null,
            'price_max' => isset($_GET['price_max']) ? $_GET['price_max'] : null
        ];

        $products = $productModel->getFiltered($limit, $offset, $filters);
        $total = $productModel->countFiltered($filters);
        $totalPages = ceil($total / $limit);

        include __DIR__ . '/../view/ViewUser/product.php';
    }




    public function __construct() {
        $this->productModel = new Product();
    }

    // Hàm xem chi tiết sản phẩm (tùy chọn)
//    public function detail($id) {
//        $product = $this->productModel->getById($id);
//        include __DIR__ . '/../view/ViewUser/detail.php';
//    }
}
