<?php
include 'header.php';

$module = isset($_GET['module']) ? $_GET['module'] : 'home';

switch ($module) {
    case 'sanpham':
        require_once '../../controller/ProductController.php';
        $controller = new ProductController();
        $controller->index();
        break;

    case 'blog':
        require_once '../../controller/BlogController.php';
        $controller = new BlogController();
        $controller->index();
        break;

    case 'chitietsanpham':
        require_once '../../controller/ProductController.php';
        $controller = new ProductController();
        $id = isset($_GET['masp']) ? (int)$_GET['masp'] : 0;
        $controller->detail($id);
        break;


    case 'home':

    default:
        include '222.php';
        break;

}

include 'footer.php';
