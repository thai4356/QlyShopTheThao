<div class="page-header">
    <h4 class="page-title">Quản Lý Đánh Giá</h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="index.php?page=dashboard"><i class="flaticon-home"></i></a>
        </li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">Quản Lý Cửa Hàng</a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="index.php?page=reviews">Đánh Giá</a></li>
    </ul>
</div>




<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Bộ lọc đánh giá</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="product-filter" class="form-label">Lọc theo sản phẩm</label>
                        <select id="product-filter" class="form-select review-filter">
                            <option value="">Tất cả sản phẩm</option>
                            <?php foreach ($all_products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="user-filter" class="form-label">Lọc theo người gửi</label>
                        <select id="user-filter" class="form-select review-filter">
                            <option value="">Tất cả người gửi</option>
                            <?php foreach ($all_users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="rating-filter" class="form-label">Lọc theo rating</label>
                        <select id="rating-filter" class="form-select review-filter">
                            <option value="">Tất cả rating</option>
                            <option value="5">5 ★</option>
                            <option value="4">4 ★</option>
                            <option value="3">3 ★</option>
                            <option value="2">2 ★</option>
                            <option value="1">1 ★</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status-filter" class="form-label">Lọc theo trạng thái</label>
                        <select id="status-filter" class="form-select review-filter">
                            <option value="">Tất cả trạng thái</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="pending">Chờ duyệt</option>
                            <option value="hidden">Đã ẩn</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="reviews-table" class="display table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sản phẩm</th>
                            <th>Người gửi</th>
                            <th>Rating</th>
                            <th>Nội dung</th>
                            <th>Trạng thái</th>
                            <th>Ngày gửi</th>
                            <th style="width: 10%">Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>