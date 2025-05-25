<?php
session_start();
$locations = $productModel->getAllLocations();
$brands = $productModel->getAllBrands();

?>

<div class="container">
    <div class="row">

        <!-- BỘ LỌC 30% -->
        <div class="col-12 col-md-3 filter-sidebar" style="padding-top: 100px">
            <form method="GET" action="">
                <input type="hidden" name="module" value="sanpham">

                <h5>Nơi Bán</h5>
                <?php foreach ($locations as $loc): ?>
                    <label>
                        <input type="checkbox" name="location[]" value="<?= htmlspecialchars($loc) ?>"
                            <?= isset($_GET['location']) && in_array($loc, $_GET['location']) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($loc) ?>
                    </label><br>
                <?php endforeach; ?>

                <h5>Thương hiệu</h5>
                <?php foreach ($brands as $brand): ?>
                    <label>
                        <input type="checkbox" name="brand[]" value="<?= htmlspecialchars($brand) ?>"
                            <?= isset($_GET['brand']) && in_array($brand, $_GET['brand']) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($brand) ?>
                    </label><br>
                <?php endforeach; ?>


                <h5>Giá</h5>
                <input type="number" name="price_min" placeholder="TỪ"
                       value="<?= isset($_GET['price_min']) ? $_GET['price_min'] : '' ?>"> -
                <input type="number" name="price_max" placeholder="ĐẾN"
                       value="<?= isset($_GET['price_max']) ? $_GET['price_max'] : '' ?>"><br><br>

                <button type="submit">Lọc</button>
            </form>
        </div>

        <!-- SẢN PHẨM 70% -->
        <div class="col-12 col-md-9">
            <section class="main-classes-in">
                <div class="row" id="counter">
                    <?php foreach ($products as $product): ?>
                        <?php $image = $product['image_url'];
                        if (empty($image)) $image = 'no-image.png'; ?>
                        <div class="col-lg-4 col-md-6 product-item">
                            <div class="class-box wow fadeInUp" data-wow-delay=".5s">
                                <div class="class-img">
                                    <img src="image/Product/<?= htmlspecialchars($image) ?>" width="440" height="270"
                                         alt="<?= htmlspecialchars($product['name']) ?>" class="img-cover"
                                         style="max-width: 440px; max-height: 270px;" loading="lazy">
                                </div>
                                <div class="class-box-contant">
                                    <div class="class-box-title">
                                        <div class="class-box-icon">
                                            <img src="../Public/Image/class-icon-1.png" alt="icon">
                                        </div>
                                        <a href="?module=chitietsanpham&masp=<?= $product['id'] ?>">
                                            <h3 class="h3-title" style="display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;overflow: hidden;text-overflow: ellipsis;line-height: 1.3em;max-height: 2.6em;">
                                                <?= htmlspecialchars($product['name']) ?>
                                            </h3>

                                        </a>
                                    </div>
                                    <p><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                                    <p>Price: <?= number_format($product['price']) ?>₫</p>
                                    <a href="?module=cart&act=add&masp=<?= $product['id'] ?>"
                                       class="btn-link has-before">Add to cart</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- PAGINATION -->
                <div class="pagination" style="padding-top: 70px;padding-left: 33%">
                    <?php if ($page > 1): ?>
                        <a href="?module=sanpham&page=<?= $page - 1 ?>" class="prev">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?module=sanpham&page=<?= $i ?>"
                           class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?module=sanpham&page=<?= $page + 1 ?>" class="next">Next</a>
                    <?php endif; ?>
                </div>

            </section>
        </div>
    </div>
</div>


<!--Classes End-->
<!--Counter Start-->
<section class="main-counter">
    <div class="container">
        <div class="row counter-bg wow fadeInUp" data-wow-delay=".5s"
             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="counter-box">
                    <div class="counter-content">
                        <h2 class="h2-title counting-data" data-count="874">874</h2>
                        <div class="counter-text">
                            <img src="../Public/Image/happy-client.png" alt="Happy Client">
                            <span>Happy Clients</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="counter-box">
                    <div class="counter-content">
                        <h2 class="h2-title counting-data" data-count="987">987</h2>
                        <div class="counter-text">
                            <img src="../Public/Image/total-clients.png" alt="Total Clients">
                            <span>Total Clients</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="counter-box">
                    <div class="counter-content">
                        <h2 class="h2-title counting-data" data-count="587">587</h2>
                        <div class="counter-text">
                            <img src="../Public/Image/gym-equipment.png" alt="Gym Equipment">
                            <span>Gym Equipment</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="counter-box">
                    <div class="counter-content">
                        <h2 class="h2-title counting-data" data-count="748">748</h2>
                        <div class="counter-text">
                            <img src="../Public/Image/cup-of-coffee.png" alt="Cup Of Coffee">
                            <span>Cup Of Coffee</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Counter End-->

<!--Team Start-->
<section class="main-team">
    <div class="team-overlay-bg animate-this" style="transform: translateX(15.9991px) translateY(-9.99986px);">
        <!--        <img src="assets/images/team-overlay-bg.png" alt="Overlay">-->
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="team-title">
                    <div class="subtitle">
                        <h2 class="h2-subtitle">Best Trainer</h2>
                    </div>
                    <h2 class="h2-title">Clients said</h2>
                </div>
            </div>
        </div>
        <div class="row team-slider slick-initialized slick-slider slick-dotted">
            <div class="slick-list draggable">
                <div class="slick-track" style="opacity: 1; width: 4620px; transform: translate3d(-2310px, 0px, 0px);">
                    <div class="col-lg-3 slick-slide slick-cloned" data-slick-index="-4" id="" aria-hidden="true"
                         tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInDown" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInDown;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="assets/images/trainer2.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">Kate
                                        Johnson</h3></a>
                                <span>Fitness Trainer</span>
                                <div class="team-social">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-facebook"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-instagram"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-twitter"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-cloned" data-slick-index="-3" id="" aria-hidden="true"
                         tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInUp" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="assets/images/trainer3.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">John
                                        Hard</h3></a>
                                <span>Fitness Trainer</span>
                                <div class="team-social">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-facebook"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-instagram"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-twitter"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-cloned" data-slick-index="-2" id="" aria-hidden="true"
                         tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInDown" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInDown;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="assets/images/trainer4.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">Zahra
                                        Sharif</h3></a>
                                <span>Fitness Trainer</span>
                                <div class="team-social">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-facebook"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-instagram"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" tabindex="-1"><i class="fa fa-twitter"
                                                                                           aria-hidden="true"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-cloned" data-slick-index="-1" id="" aria-hidden="true"
                         tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInUp" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="#" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">Ruth
                                        Edwards</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide" data-slick-index="0" aria-hidden="true" tabindex="-1"
                         role="tabpanel" id="slick-slide10" aria-describedby="slick-slide-control10"
                         style="width: 330px;">
                        <div class="team-box wow fadeInUp" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="assets/images/trainer1.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">Desert
                                        Antony</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide" data-slick-index="1" aria-hidden="true" tabindex="-1"
                         role="tabpanel" id="slick-slide11" aria-describedby="slick-slide-control11"
                         style="width: 330px;">
                        <div class="team-box wow fadeInDown" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInDown;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="assets/images/trainer2.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">Kate
                                        Johnson</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide" data-slick-index="2" aria-hidden="true" tabindex="-1"
                         role="tabpanel" id="slick-slide12" aria-describedby="slick-slide-control12"
                         style="width: 330px;">
                        <div class="team-box wow fadeInUp" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="assets/images/trainer3.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">John
                                        Hard</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-current slick-active" data-slick-index="3"
                         aria-hidden="false" tabindex="0" role="tabpanel" id="slick-slide13"
                         aria-describedby="slick-slide-control13" style="width: 330px;">
                        <div class="team-box wow fadeInDown" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInDown;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="../Public/Image/trainer2.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="0"><h3 class="h3-title team-text-color">Zahra
                                        Sharif</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-active" data-slick-index="4" aria-hidden="false" tabindex="0"
                         role="tabpanel" id="slick-slide14" aria-describedby="slick-slide-control14"
                         style="width: 330px;">
                        <div class="team-box wow fadeInUp" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="../Public/Image/trainer3.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="0"><h3 class="h3-title team-text-color">Ruth
                                        Edwards</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-cloned slick-active" data-slick-index="5" id=""
                         aria-hidden="false" tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInUp" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="../Public/Image/trainer1.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="0"><h3 class="h3-title team-text-color">Desert
                                        Antony</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-cloned slick-active" data-slick-index="6" id=""
                         aria-hidden="false" tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInDown" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInDown;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="../Public/Image/trainer4.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="0"><h3 class="h3-title team-text-color">Kate
                                        Johnson</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-cloned" data-slick-index="7" id="" aria-hidden="true"
                         tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInUp" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="../Public/Image/trainer3%20(1).jpg.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">John
                                        Hard</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-cloned" data-slick-index="8" id="" aria-hidden="true"
                         tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInDown" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInDown;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="../Public/Image/trainer3.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">Zahra
                                        Sharif</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 slick-slide slick-cloned" data-slick-index="9" id="" aria-hidden="true"
                         tabindex="-1" style="width: 330px;">
                        <div class="team-box wow fadeInUp" data-wow-delay=".5s"
                             style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
                            <div class="team-img-box team-border-one">
                                <div class="team-img">
                                    <img src="../Public/Image/trainer1.jpg" alt="Trainer">
                                </div>
                            </div>
                            <div class="team-content">
                                <a href="team-detail.html" tabindex="-1"><h3 class="h3-title team-text-color">Ruth
                                        Edwards</h3></a>
                                <span>Fitness Trainer</span>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
    .filter-sidebar {
        padding: 20px;
        background-color: #f8f8f8;
        border-right: 1px solid #ddd;
    }

    .filter-sidebar h5 {
        margin-top: 20px;
        font-weight: bold;
    }

    .filter-sidebar label {
        display: block;
        margin-bottom: 5px;
    }

    .pagination {
        text-align: center;
        margin-top: 20px;
    }

    .pagination a {
        padding: 8px 12px;
        margin: 0 3px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
    }

    .pagination a.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .pagination a:hover {
        background-color: #0056b3;
        color: white;
    }

    .product-item {
        margin-bottom: 30px;
    }

    .class-box {
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease-in-out;
        background-color: #fff;
    }

    .class-box:hover {
        transform: translateY(-5px);
    }

    .class-img {
        margin-bottom: 15px;
        text-align: center;
    }

    .class-img img {
        max-width: 100%;
        height: auto;
        object-fit: cover;
    }
</style>
