<?php
// view/ViewAdmin/partials/navbar.php
$assets_path = 'assets/'; // Đường dẫn đến thư mục assets

// Thông tin admin giả lập cho giao diện
$admin_name_display = $_SESSION['username_admin'] ?? 'Admin Kai'; // Lấy từ session nếu có, hoặc dùng tên mặc định
$admin_email_display = $_SESSION['email_admin'] ?? 'admin@example.com';
$admin_avatar_display = isset($_SESSION['avatar_admin']) ? $assets_path.'img/avatars/'.$_SESSION['avatar_admin'] : $assets_path.'img/profile.jpg';
?>
<div class="main-panel">
    <div class="main-header">
        <div class="main-header-logo">
            <div class="logo-header" data-background-color="dark">
                <a href="index.php?page=dashboard" class="logo">
                    <img src="<?php echo $assets_path; ?>img/logo.png" alt="navbar brand" class="navbar-brand" height="50" />
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
        <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
            <div class="container-fluid">
                <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
                </nav>

                <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">

                    <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">

                    </li>


                    <li class="nav-item topbar-user dropdown hidden-caret">
                        <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                            <div class="avatar-sm">
                                <img src="<?php echo $admin_avatar_display; ?>" alt="..." class="avatar-img rounded-circle"/>
                            </div>
                            <span class="profile-username">
                                <span class="op-7">Hi,</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($admin_name_display); ?></span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-user animated fadeIn">
                            <div class="dropdown-user-scroll scrollbar-outer">
                                <li>
                                    <div class="user-box">
                                        <div class="avatar-lg">
                                            <img src="<?php echo $admin_avatar_display; ?>" alt="image profile" class="avatar-img rounded"/>
                                        </div>
                                        <div class="u-text">
                                            <h4><?php echo htmlspecialchars($admin_name_display); ?></h4>
                                            <p class="text-muted"><?php echo htmlspecialchars($admin_email_display); ?></p>
                                            <a href="index.php?page=profile" class="btn btn-xs btn-secondary btn-sm">Xem hồ sơ</a>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="index.php?page=profile">Hồ sơ của tôi</a>
                                    <a class="dropdown-item" href="#">Cài đặt tài khoản</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="../../index.php?ctrl=auth&act=logout">Đăng xuất</a>
                                </li>
                            </div>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
