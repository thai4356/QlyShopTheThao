<?php
$locations = $productModel->getAllLocations();
$brands = $productModel->getAllBrands();
require_once '../../model/category.php';
$categoryModel = new Category();
$categories = $categoryModel->getAll()
?>

<div class="container">
    <div class="row">

        <!-- BỘ LỌC 30% -->
        <div class="col-12 col-md-3 filter-sidebar" style="padding-top: 100px; position: sticky">
            <form method="GET" action="">
                <input type="hidden" name="module" value="sanpham">

                <!-- 🔍 Tìm kiếm sản phẩm -->
                <div class="mb-3">
                    <label for="search" class="form-label">Tìm kiếm sản phẩm</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Nhập tên sản phẩm..."
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>

                <!-- 📁 Danh mục sản phẩm -->
                <div class="mb-3">
                    <h5>Danh mục</h5>
                    <?php foreach ($categories as $cat): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="category_id[]" value="<?= $cat['id'] ?>"
                                <?= isset($_GET['category_id']) && in_array($cat['id'], $_GET['category_id']) ? 'checked' : '' ?>>
                            <label class="form-check-label"><?= htmlspecialchars($cat['name']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 🔃 Sắp xếp -->
                <div class="mb-3" style="margin-top: 30px">
                    <label for="sort" class="form-label">Sắp xếp</label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="">Mặc định</option>
                        <option value="asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'asc') ? 'selected' : '' ?>>Giá tăng dần</option>
                        <option value="desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'desc') ? 'selected' : '' ?>>Giá giảm dần</option>
                    </select>
                </div>

                <!-- 📍 Nơi Bán -->
                <h5>Nơi Bán</h5>
                <?php foreach ($locations as $loc): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="location[]" value="<?= htmlspecialchars($loc) ?>"
                            <?= isset($_GET['location']) && in_array($loc, $_GET['location']) ? 'checked' : '' ?>>
                        <label class="form-check-label">
                            <?= htmlspecialchars($loc) ?>
                        </label>
                    </div>
                <?php endforeach; ?>

                <!-- 🏷 Thương hiệu -->
                <h5 class="mt-3">Thương hiệu</h5>
                <?php foreach ($brands as $brand): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="brand[]" value="<?= htmlspecialchars($brand) ?>"
                            <?= isset($_GET['brand']) && in_array($brand, $_GET['brand']) ? 'checked' : '' ?>>
                        <label class="form-check-label">
                            <?= htmlspecialchars($brand) ?>
                        </label>
                    </div>
                <?php endforeach; ?>

                <!-- 💵 Khoảng giá -->
                <h5 class="mt-3">Khoảng giá</h5>
                <div style="display: flex; gap: 10px;">
                    <input type="number" name="price_min" class="form-control" placeholder="Từ"
                           value="<?= isset($_GET['price_min']) ? $_GET['price_min'] : '' ?>">
                    <input type="number" name="price_max" class="form-control" placeholder="Đến"
                           value="<?= isset($_GET['price_max']) ? $_GET['price_max'] : '' ?>">
                </div>

                <!-- ✅ Nút lọc -->
                <div class="mt-3">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fa fa-filter"></i> Lọc
                    </button>
                </div>
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

                                    <img src="ProductImage/<?= htmlspecialchars($image) ?>"
                                         style="max-width: 440px; max-height: 270px;" loading="lazy">
                                </div>
                                <div class="class-box-contant">
                                    <div class="class-box-title">
                                        <div class="class-box-icon">
                                            <img src="../Public/Image/class-icon-1.png" alt="icon">
                                        </div>
                                        <a href="?module=chitietsanpham&masp=<?= $product['id'] ?>">
                                            <h3 class="h3-title" style="display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;overflow: hidden;text-overflow: ellipsis;line-height: 1.3em;max-height: 2.6em;font-family: 'Be Vietnam Pro', sans-serif !important;">
                                                <?= $product['name'] ?>
                                            </h3>

                                        </a>
                                    </div>
                                    <p class="truncate-2-lines"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?></p>
                                    <p style="color: orangered">Giá bán: <?= number_format($product['price']) ?>₫</p>
                                    <a href="?module=cart&act=add&masp=<?= $product['id'] ?>"
                                       class="btn btn-warning d-inline-flex align-items-center gap-2">
                                        <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                                    </a>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- PAGINATION -->
                <div class="pagination" style="padding-top: 70px;padding-left: 33%">
                    <?php if ($page > 1): ?>
                        <a href="?<?= $queryStr ?>page=<?= $page - 1 ?>" class="prev">Trang trước</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?<?= $queryStr ?>page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= $queryStr ?>page=<?= $page + 1 ?>" class="next">Trang sau</a>
                    <?php endif; ?>
                </div>


            </section>
        </div>
    </div>
</div>




<style>
    .filter-sidebar h5 {
        margin-top: 20px;
        font-weight: 600;
    }
    .filter-sidebar input[type="checkbox"] {
        margin-right: 5px;
    }

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
        background-color: #f86902;
        color: white;
        border-color: #fc4c08;
    }

    .pagination a:hover {
        background-color: #df1111;
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

    .truncate-2-lines {
        display: -webkit-box;
        -webkit-line-clamp: 2;       /* Hiển thị 2 dòng */
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

</style>
