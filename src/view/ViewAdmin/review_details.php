<?php
// Lấy các biến từ controller
$review = $view_data['review_details'];
$images = $view_data['product_images'];
$product_image_base_url = '../../view/ViewUser/ProductImage/';

$page_scripts[] = 'assets/js/admin-review-details.js';
?>

<div class="page-header">
    <div class="d-flex align-items-center">
        <h4 class="page-title mb-0"><?= htmlspecialchars($pageTitle) ?></h4>

        <a href="index.php?page=reviews" class="btn btn-secondary btn ms-3">
            <i class="fas fa-arrow-left"></i> &nbsp; Quay lại danh sách
        </a>
    </div>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="index.php?page=dashboard"><i class="flaticon-home"></i></a>
        </li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="index.php?page=reviews">Quản lý Đánh giá</a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a>Chi tiết</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Sản phẩm được đánh giá</h4>
            </div>
            <div class="card-body">
                <h5><?= htmlspecialchars($review['product_name']) ?></h5>
                <?php if (!empty($images)): ?>
                    <div id="productImagesCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($images as $index => $image): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= $product_image_base_url . htmlspecialchars($image['image_url']) ?>" class="d-block w-100" style="max-height: 400px; object-fit: contain;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productImagesCarousel" data-bs-slide="prev" style="background-color: rgba(0,0,0,0.3);">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productImagesCarousel" data-bs-slide="next" style="background-color: rgba(0,0,0,0.3);">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                <?php else: ?>
                    <p>Sản phẩm này không có hình ảnh.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Nội dung đánh giá</h4></div>
            <div class="card-body">
                <p><strong>Người gửi:</strong> <?= htmlspecialchars($review['user_email']) ?></p>
                <p><strong>Ngày gửi:</strong> <?= date('d-m-Y H:i', strtotime($review['created_at'])) ?></p>
                <p><strong>Rating:</strong>
                    <span style="color: #ffc107;">
                        <?php for ($i = 0; $i < 5; $i++) { echo $i < $review['rating'] ? '★' : '☆'; } ?>
                    </span>
                </p>
                <p><strong>Trạng thái:</strong>
                    <span class="badge bg-success"><?= htmlspecialchars($review['status']) ?></span>
                </p>
                <p><strong>Nội dung:</strong></p>
                <div class="p-3 bg-light border rounded">
                    <?= nl2br(htmlspecialchars($review['comment'])) ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Phản hồi của Admin</h4></div>
            <div class="card-body">
                <form id="reply-form" method="POST" action=""> <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                    <div class="form-group">
                        <textarea class="form-control" id="admin_reply" name="admin_reply" rows="5" placeholder="Nhập phản hồi của bạn tại đây..."><?= htmlspecialchars($review['admin_reply'] ?? '') ?></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary mt-2">Lưu Phản Hồi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>