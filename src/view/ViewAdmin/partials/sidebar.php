<?php
// view/ViewAdmin/partials/sidebar.php
$current_page_param = $_GET['page'] ?? 'dashboard'; // Lấy trang hiện tại từ URL để active menu
$assets_path = 'assets/'; // Đường dẫn đến thư mục assets
?>
<div class="sidebar" data-background-color="dark"> <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark"> <a href="index.php?page=dashboard" class="logo">
                <img src="<?php echo $assets_path; ?>img/logo.png" alt="navbar brand" class="navbar-brand" height="50">
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item <?php echo ($current_page_param === 'dashboard' ? 'active' : ''); ?>">
                    <a href="index.php?page=dashboard">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                    <h4 class="text-section">Quản Lý Cửa Hàng</h4>
                </li>
                <li class="nav-item <?php echo ($current_page_param === 'products' ? 'active' : ''); ?>">
                    <a href="index.php?page=products">
                        <i class="fas fa-cubes"></i>
                        <p>Sản Phẩm</p>
                    </a>
                </li>
                <li class="nav-item <?php echo ($current_page_param === 'categories' ? 'active' : ''); ?>">
                    <a href="index.php?page=categories">
                        <i class="fas fa-tags"></i>
                        <p>Danh Mục</p>
                    </a>
                </li>
                <li class="nav-item <?php echo ($current_page_param === 'orders' ? 'active' : ''); ?>">
                    <a href="index.php?page=orders">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Đơn Hàng</p>
                    </a>
                </li>
                <li class="nav-item <?php echo ($current_page_param === 'users' ? 'active' : ''); ?>">
                    <a href="index.php?page=users">
                        <i class="fas fa-users"></i>
                        <p>Người Dùng</p>
                    </a>
                </li>
                <li class="nav-item <?php echo ($current_page_param === 'reviews' ? 'active' : ''); ?>">
                    <a href="index.php?page=reviews">
                        <i class="fas fa-star"></i>
                        <p>Đánh Giá</p>
                    </a>
                </li>

                <li class="nav-section">
                    <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                    <h4 class="text-section">Tài Khoản</h4>
                </li>
                <li class="nav-item <?php echo ($current_page_param === 'profile' ? 'active' : ''); ?>">
                    <a href="index.php?page=profile">
                        <i class="fas fa-user-circle"></i>
                        <p>Hồ Sơ Admin</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../index.php?ctrl=auth&act=logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <p>Đăng Xuất</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>