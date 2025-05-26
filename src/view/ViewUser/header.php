<?php
$activeModule = isset($_GET['module']) ? $_GET['module'] : 'home';
?>

<header class="site-header sticky-header">

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
    <!--Google Fonts CSS-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@100;200;300;400;500;600;700;800;900&amp;family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">


    <!--Navbar Start  -->
    <div class="header-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2">
                    <!-- Sit Logo Start -->
                    <div class="site-branding">
                        <a href="Menu.php" title="Fithub">
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
                                        <a href="?module=home" title="Home">Home</a>
                                    </li>
                                    <!--                                    <li><a href="indexUserProduct.php">Our products</a></li>-->
                                    <li>
                                        <a href="?module=sanpham" class="navbar-link" data-nav-link>Product</a>


                                    </li>
                                    <li>
                                        <a href="?module=blog" class="navbar-link" title="Blog" data-nav-link>Blog</a>
                                    </li>

                                    <li>
                                        <a href="?module=blog" class="navbar-link" title="Favourite" data-nav-link>Favourite</a>
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
