<?php
// view/ViewAdmin/index.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

 $is_admin_logged_in = (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 1); // Ví dụ

if (!$is_admin_logged_in) {
    echo "<h1>Truy cập bị từ chối</h1><p>Bạn cần đăng nhập với tư cách quản trị viên để xem trang này.</p>";
    // echo "<p><a href='../../index.php?ctrl=auth&act=loginForm'>Đến trang đăng nhập</a></p>"; // Điều hướng nếu cần
    exit;
}

// Xác định controller và action từ URL, hoặc page truyền thống
$ctrl_param = $_GET['ctrl'] ?? null;
$act_param = $_GET['act'] ?? null;
$page_param = $_GET['page'] ?? 'dashboard'; // Trang mặc định nếu không có ctrl/act

$pageTitle = ucfirst(str_replace('_', ' ', $page_param)); // Tiêu đề trang mặc định
$view_data = []; // Dữ liệu mặc định truyền cho view

// Định tuyến cơ bản
if ($ctrl_param === 'adminproduct' && $act_param === 'listProducts') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminProductController.php';
    $controller = new AdminProductController();
    $view_data = $controller->listProducts(); // Lấy dữ liệu từ controller
    $page_param = $view_data['page_name']; // Đảm bảo page_param đúng với trang controller xử lý
    $pageTitle = $view_data['pageTitle'];  // Cập nhật pageTitle từ controller
}

else if ($ctrl_param === 'adminproduct' && $act_param === 'ajaxUpdateProduct') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminProductController.php';
    $controller = new AdminProductController();
    $controller->ajaxUpdateProduct(); // Gọi action và nó sẽ tự exit sau khi echo JSON
    // Không cần load layout cho các request AJAX
    exit; // Đảm bảo dừng ở đây cho request AJAX
}

// THÊM ROUTE MỚI CHO SOFT DELETE
else if ($ctrl_param === 'adminproduct' && $act_param === 'ajaxSoftDeleteProduct') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminProductController.php';
    $controller = new AdminProductController();
    $controller->ajaxSoftDeleteProduct(); // Gọi action và nó sẽ tự exit
    exit; // Dừng ở đây cho request AJAX
}

else if ($ctrl_param === 'adminproduct' && $act_param === 'ajaxAddProduct') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminProductController.php';
    $controller = new AdminProductController();
    $controller->ajaxAddProduct(); // Gọi action, nó sẽ tự exit
    exit;
}

else if ($ctrl_param === 'adminproduct' && $act_param === 'ajaxGetProductDetailsForEdit') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminProductController.php';
    $controller = new AdminProductController();
    $controller->ajaxGetProductDetailsForEdit();
    exit;
}

else if ($ctrl_param === 'adminproduct' && $act_param === 'ajaxGetProductsForDataTable') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminProductController.php';
    $controller = new AdminProductController();
    $controller->ajaxGetProductsForDataTable();
    exit;
}

else if (($ctrl_param === 'admincategory' && $act_param === 'listCategories') || $page_param === 'categories' && $ctrl_param === null) {
    require_once __DIR__ . '/../../../src/controller/admin/AdminCategoryController.php';
    $controller = new AdminCategoryController();
    $view_data = $controller->listCategories();
    $page_param = $view_data['page_name'];
    $pageTitle = $view_data['pageTitle'];
}

else if ($ctrl_param === 'admincategory' && $act_param === 'ajaxUpdateCategory') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminCategoryController.php';
    $controller = new AdminCategoryController();
    $controller->ajaxUpdateCategory(); // Action này sẽ tự echo JSON và exit
    // Không cần load layout cho AJAX
    exit;
}

else if ($ctrl_param === 'admincategory' && $act_param === 'ajaxGetCategoriesForDataTable') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminCategoryController.php';
    $controller = new AdminCategoryController();
    $controller->ajaxGetCategoriesForDataTable(); // Action này sẽ tự echo JSON và exit
    exit;
}

// THÊM ROUTE CHO AJAX SOFT DELETE CATEGORY
else if ($ctrl_param === 'admincategory' && $act_param === 'ajaxSoftDeleteCategory') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminCategoryController.php';
    $controller = new AdminCategoryController();
    $controller->ajaxSoftDeleteCategory(); // Action này sẽ tự echo JSON và exit
    exit;
}

else if ($ctrl_param === 'admincategory' && $act_param === 'ajaxAddCategory') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminCategoryController.php';
    $controller = new AdminCategoryController();
    $controller->ajaxAddCategory(); // Action này sẽ tự echo JSON và exit
    exit;
}

else if ($ctrl_param === 'adminuser' && $act_param === 'list') {
    require_once __DIR__ . '/../../../src/controller/admin/AdminUserController.php';
    // Gọi trực tiếp UserController.php như bạn đang dùng
    include __DIR__ . '/../../../src/controller/admin/AdminUserController.php';
    exit;
}


else {
    // Xử lý các trang không qua controller MVC (nếu có) hoặc trang mặc định
    $allowed_static_pages = ['dashboard', 'categories', 'orders', 'users', 'reviews', 'profile', 'test'];
    if (in_array($page_param, $allowed_static_pages)) {
        // Không có controller cụ thể, $page_param sẽ được dùng để load file content trực tiếp
        // $pageTitle đã được đặt ở trên
    } else if ($page_param === 'products' && $ctrl_param === null) { // Xử lý ngầm cho ?page=products
        require_once __DIR__ . '/../../../src/controller/admin/AdminProductController.php';
        $controller = new AdminProductController();
        $view_data = $controller->listProducts();
        $page_param = $view_data['page_name'];
        $pageTitle = $view_data['pageTitle'];
    }

    else {
        // Nếu trang không hợp lệ, mặc định về dashboard
        $page_param = 'dashboard';
        $pageTitle = ucfirst($page_param);
    }
}

// Truyền dữ liệu từ controller (nếu có) vào phạm vi của layout và content view
if (!empty($view_data)) {
    extract($view_data); // Biến các key của mảng $view_data thành các biến riêng lẻ (VD: $products, $pageTitle)
}

// Nạp layout chính, layout này sẽ include file content dựa trên $page_param
// Các biến như $products, $product_image_base_url, $pageTitle giờ đã có sẵn cho layout và content view
require_once __DIR__ . '/layout.php';
?>