<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<main class="container" style="margin-top: 100px">
    <?php
    require_once '../../controller/ReviewController.php';

    $productId = $id;

    $reviewCtrl = new ReviewController();
    $reviews = $reviewCtrl->loadProductReviews($productId);
    $totalReviews = count($reviews);
    $averageRating = 0;

    if ($totalReviews > 0) {
        $sumRating = array_sum(array_column($reviews, 'rating'));
        $averageRating = round($sumRating / $totalReviews, 1); // Làm tròn 1 chữ số thập phân
    }

    ?>

    <div class="container" style="padding-top: 80px;">
        <div class="row">
            <div class="col-md-5">
                <div id="productCarousel" class="carousel slide mb-3" data-bs-ride="carousel" style="max-height: 300px;max-width: 300px;margin-top: -30px">
                    <div class="carousel-inner">
                        <?php foreach ($product['images'] as $index => $img): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="ProductImage/<?= $img ?>" class="d-block w-100 main-image" alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" style="background-color: rgba(0,0,0,0.3);"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" style="background-color: rgba(0,0,0,0.3);"></span>
                    </button>
                </div>

                <!-- thumbnail dưới -->
                <div class="d-flex gap-2 justify-content-center thumbnail-wrapper">
                    <?php foreach ($product['images'] as $index => $img): ?>
                        <img src="ProductImage/<?= $img ?>" class="thumbnail-img" data-bs-target="#productCarousel" data-bs-slide-to="<?= $index ?>" <?= $index === 0 ? 'class="active"' : '' ?>>
                    <?php endforeach; ?>
                </div>
            </div>




            <div class="col-md-7">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <h4 style="color:red"><?= number_format($product['price']) ?>₫</h4>
                <?php if ($totalReviews > 0): ?>
                    <p>
                        <strong>Đánh giá:</strong>
                        <span style="color: #ffc107; font-size: 1.2rem;">
            <?= str_repeat('★', floor($averageRating)) ?>
            <?= ($averageRating - floor($averageRating)) >= 0.5 ? '½' : '' ?>
            <?= str_repeat('☆', 5 - ceil($averageRating)) ?>
        </span>
                        (<?= $averageRating ?>/5, <?= $totalReviews ?> lượt đánh giá)
                    </p>
                <?php else: ?>
                    <p><strong>Đánh giá:</strong> Chưa có đánh giá</p>
                <?php endif; ?>

                <p><strong>Còn lại:</strong> <?= $product['stock'] ?></p>
                <p><strong>Đã bán:</strong> <?= $product['sold_quantity'] ?></p>
                <a href="?module=cart&act=add&masp=<?= $product['id'] ?>" class="btn btn-success">Thêm vào giỏ</a>
            </div>
        </div>

</main>



<?php if (isset($_SESSION['message'])): ?>
    <script>alert('<?= addslashes($_SESSION['message']) ?>');</script>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<section style="max-width: 1200px; margin: 50px auto; padding: 20px; border-top: 2px solid #eee;">
    <h2>Đánh giá sản phẩm</h2>

    <?php if (count($reviews) === 0): ?>
        <p>Chưa có đánh giá nào cho sản phẩm này.</p>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
            <div style="border-bottom: 1px solid #ddd; padding: 10px 0;">
                <strong>⭐ <?= htmlspecialchars($r['rating']) ?>/5</strong><br>
                <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                <small>Đánh giá lúc <?= $r['created_at'] ?></small><br>
                <small>Bởi <?= strstr($r['email'], '@', true) ?></small>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr style="margin: 30px 0;">

    <h3>Gửi đánh giá của bạn</h3>
    <form method="POST" action="../../controller/ReviewController.php?action=submitReview" style="margin-top: 15px;">
        <input type="hidden" name="product_id" value="<?= $productId ?>">

        <label>Đánh giá:</label><br>
        <div class="star-rating">
            <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
        </div>
        <br><br>

        <label for="comment">Bình luận:</label><br>
        <textarea name="comment" id="comment" rows="4" style="width: 100%;" required></textarea><br><br>

        <button type="submit" style="background-color: #fc4c08; color: white; padding: 10px 20px; border: none; border-radius: 6px;">Gửi đánh giá</button>
    </form>
</section>



<style>
    .star-rating {
        direction: rtl;
        font-size: 2rem;
        unicode-bidi: bidi-override;
        display: inline-flex;
        gap: 5px;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        color: #ccc;
        cursor: pointer;
    }

    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: gold;
    }

    #productCarousel {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    #productCarousel .carousel-inner,
    #productCarousel .carousel-item {
        height: 100%;
    }
    #productCarousel img {
        height: 100%;
        width: auto;
        object-fit: contain;
    }

    /* Thumbnail dưới */
    .thumbnail-wrapper {
        margin-top: 10px;
    }
    .thumbnail-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border: 2px solid transparent;
        cursor: pointer;
        transition: border 0.3s ease;
        border-radius: 4px;
    }
    .thumbnail-img.active,
    .thumbnail-img:hover {
        border: 2px solid #fc4c08;
    }

</style>

<style>
    html, body {
        height: 100%;
        width: 100%;
        margin: 0;
        font-family: 'Roboto', sans-serif;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 15px;
        display: flex;
    }

    .left-column {
        width: 65%;
        position: relative;
    }

    .right-column {
        width: 35%;
        margin-top: 60px;
    }

    .left-column img {
        width: 100%;
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .left-column img.active {
        opacity: 1;
    }

    .product-description {
        border-bottom: 1px solid #E1E8EE;
        margin-bottom: 20px;
    }
    .product-description span {
        font-size: 12px;
        color: #358ED7;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-decoration: none;
    }
    .product-description h1 {
        font-weight: 300;
        font-size: 52px;
        color: #43484D;
        letter-spacing: -2px;
    }
    .product-description p {
        font-size: 16px;
        font-weight: 300;
        color: #86939E;
        line-height: 24px;
    }

    .product-color {
        margin-bottom: 30px;
    }

    .color-choose div {
        display: inline-block;
    }

    .color-choose input[type="radio"] {
        display: none;
    }

    .color-choose input[type="radio"] + label span {
        display: inline-block;
        width: 40px;
        height: 40px;
        margin: -1px 4px 0 0;
        vertical-align: middle;
        cursor: pointer;
        border-radius: 50%;
        border: 2px solid #FFFFFF;
        box-shadow: 0 1px 3px 0 rgba(0,0,0,0.33);
    }

    .color-choose input[type="radio"]#red + label span {
        background-color: #C91524;
    }
    .color-choose input[type="radio"]#blue + label span {
        background-color: #314780;
    }
    .color-choose input[type="radio"]#black + label span {
        background-color: #323232;
    }

    .color-choose input[type="radio"]:checked + label span {
        background-repeat: no-repeat;
        background-position: center;
    }

    .cable-choose {
        margin-bottom: 20px;
    }

    .cable-choose button {
        border: 2px solid #E1E8EE;
        border-radius: 6px;
        padding: 13px 20px;
        font-size: 14px;
        color: #5E6977;
        background-color: #fff;
        cursor: pointer;
        transition: all .5s;
    }

    .cable-choose button:hover,
    .cable-choose button:active,
    .cable-choose button:focus {
        border: 2px solid #86939E;
        outline: none;
    }

    .cable-config {
        border-bottom: 1px solid #E1E8EE;
        margin-bottom: 20px;
    }

    .cable-config a {
        color: #358ED7;
        text-decoration: none;
        font-size: 12px;
        position: relative;
        margin: 10px 0;
        display: inline-block;
    }

    .cable-config a:before {
        content: "?";
        height: 15px;
        width: 15px;
        border-radius: 50%;
        border: 2px solid rgba(53, 142, 215, 0.5);
        display: inline-block;
        text-align: center;
        line-height: 16px;
        opacity: 0.5;
        margin-right: 5px;
    }

    .product-price {
        display: flex;
        align-items: center;
    }

    .product-price span {
        font-size: 26px;
        font-weight: 300;
        color: #43474D;
        margin-right: 20px;
    }

    .cart-btn {
        display: inline-block;
        background-color: #7DC855;
        border-radius: 6px;
        font-size: 16px;
        color: #FFFFFF;
        text-decoration: none;
        padding: 12px 30px;
        transition: all .5s;
    }
    .cart-btn:hover {
        background-color: #64af3d;
    }

    @media (max-width: 940px) {
        .container {
            flex-direction: column;
            margin-top: 60px;
        }

        .left-column,
        .right-column {
            width: 100%;
        }

        .left-column img {
            width: 300px;
            right: 0;
            top: -65px;
            left: initial;
        }
    }

    @media (max-width: 535px) {
        .left-column img {
            width: 220px;
            top: -85px;
        }
    }
</style>

<script>
    const thumbnails = document.querySelectorAll('.thumbnail-img');
    const carousel = document.querySelector('#productCarousel');

    thumbnails.forEach((thumb, index) => {
        thumb.addEventListener('click', () => {
            // Remove active class khỏi tất cả
            thumbnails.forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        });

        carousel.addEventListener('slid.bs.carousel', (e) => {
            thumbnails.forEach(t => t.classList.remove('active'));
            thumbnails[e.to].classList.add('active');
        });
    });
</script>

