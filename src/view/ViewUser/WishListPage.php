<div class="container" style="margin-top: 200px">

    <?php if (isset($_SESSION['message'])): ?>
        <script>alert('<?= addslashes($_SESSION['message']) ?>');</script>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

<h2  >Danh sách yêu thích</h2>
    <div class="row">
        <?php if (empty($wishlistItems)): ?>
            <p>Bạn chưa có sản phẩm nào trong danh sách yêu thích.</p>
        <?php else: ?>
            <?php foreach ($wishlistItems as $product): ?>
                <div class="col-md-3 mb-4" >
                    <div class="card h-100" >
                        <div class="card-body" >

                            <img src="ProductImage/<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">


                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-danger"><?= number_format($product['price']) ?>₫</p>
                            <a href="?module=chitietsanpham&masp=<?= $product['id'] ?>" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            <form method="POST" action="../../controller/WishlistController.php" style="display:inline-block">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="btn btn-danger btn-sm">Bỏ yêu thích</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
