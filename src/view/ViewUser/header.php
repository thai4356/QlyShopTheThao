<?php
$activeModule = isset($_GET['module']) ? $_GET['module'] : 'home';
?>

<header class="site-header sticky-header">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/index2.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/style.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/slick.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/magnific-popup.min.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/animate.min.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/css2.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/styles.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/style2.css">
    <link rel="stylesheet" type="text/css" href="../Public/CSS/slick-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!--Google Fonts CSS-->

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <!--Navbar Start  -->
    <div class="header-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2">
                    <!-- Sit Logo Start -->
                    <div class="site-branding">
                        <a href="?module=home" title="Fithub">
                            <img src="../Public/Image/logo.png" alt="Logo">
                            <img src="../Public/Image/logo_stickey.png" class="sticky-logo" alt="Logo">

                        </a>
                    </div>
                    <!-- Sit Logo End -->
                </div>
                <div class="col-lg-10">
                    <div class="header-menu">
                        <nav class="main-navigation one">
                            <button class="toggle-button">
                                <span></span>
                                <span class="toggle-width"></span>
                                <span></span>
                            </button>
                            <div class="mobile-menu-box">
                                <i class="menu-background top"></i>
                                <i class="menu-background middle"></i>
                                <i class="menu-background bottom"></i>
                                <ul class="menu">
                                    <li >
                                        <a href="?module=home" title="Home">Trang Chủ</a>
                                    </li>
                                    <!--                                    <li><a href="indexUserProduct.php">Our products</a></li>-->
                                    <li>
                                        <a href="?module=sanpham" class="navbar-link" data-nav-link>Sản Phẩm</a>


                                    </li>
                                    <li>
                                        <a href="?module=cart" class="navbar-link" title="Blog" data-nav-link>Giỏ hàng</a>
                                    </li>

                                    <li>
                                        <a href="?module=orderhistory" class="navbar-link" title="Favourite" data-nav-link>Lịch sử mua hàng</a>
                                    </li>

                                    <li>
                                        <a href="?module=wishlist" class="navbar-link" title="Favourite" data-nav-link><i class="fas fa-heart" style="color: red"> Thích </i></a>
                                    </li>
                                </ul>
                            </div>
                        </nav>

                        <div class="black-shadow"></div>

                        <div class="header-btn">
                            <?php if (isset($_SESSION['username'])): ?>
                                <a href="../../controller/logout.php" class="sec-btn">Log out</a>
                            <?php else: ?>
                                <a href="../login.php" class="sec-btn">Log in</a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Navbar End  -->
</header>

<style>
    body, html {
        font-family: 'Be Vietnam Pro', sans-serif !important;
    }
</style>
