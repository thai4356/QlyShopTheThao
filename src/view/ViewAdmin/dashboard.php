<?php
// $pageTitle và $page đã được đặt trong controller và truyền vào layout.php
// Nội dung HTML cho dashboard
?>
<div class="page-header">
    <h4 class="page-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="index.php?ctrl=admin&act=dashboard">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Dashboard</a>
        </li>
    </ul>
</div>

<div class="page-category">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Xin chào Admin!</div>
                </div>
                <div class="card-body">
                    <p>Đây là trang quản trị cửa hàng dụng cụ thể thao. Bạn có thể quản lý sản phẩm, đơn hàng, người dùng và nhiều hơn nữa từ đây.</p>

                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-success card-round">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="icon-big text-center">
                                                <i class="fa-solid fa-sack-dollar"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-stats">
                                            <div class="numbers">
                                                <p class="card-category">Doanh thu tháng</p>
                                                <h4 class="card-title"><?= number_format($stats['total_revenue_month'] ?? 0, 0, ',', '.') ?> đ</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-info card-round">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="icon-big text-center">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-stats">
                                            <div class="numbers">
                                                <p class="card-category">Đơn hàng mới (Tháng)</p>
                                                <h4 class="card-title"><?= number_format($stats['new_orders_month'] ?? 0) ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-primary card-round">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="icon-big text-center">
                                                <i class="fa-solid fa-users"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-stats">
                                            <div class="numbers">
                                                <p class="card-category">Tổng người dùng</p>
                                                <h4 class="card-title"><?= number_format($stats['total_users'] ?? 0) ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-warning card-round">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="icon-big text-center">
                                                <i class="fa-solid fa-comment-dots"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-stats">
                                            <div class="numbers">
                                                <p class="card-category">Đánh giá chờ duyệt</p>
                                                <h4 class="card-title"><?= number_format($stats['pending_reviews'] ?? 0) ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0">Doanh thu</h4>
                                        <select id="revenue-chart-filter" class="form-select" style="width: auto;">
                                            <option value="last7days" selected>7 ngày qua</option>
                                            <option value="today">Hôm nay</option>
                                            <option value="yesterday">Hôm qua</option>
                                            <option value="this_month">Tháng này</option>
                                            <option value="this_quarter">Quý này</option>
                                            <option value="this_year">Năm nay</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 350px">
                                        <canvas id="revenueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Phân bổ Sản phẩm</h4>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 350px">
                                        <canvas id="categoryChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Đơn Hàng Mới Nhất</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th>Mã ĐH</th>
                                                <th>Khách Hàng</th>
                                                <th>Tổng Tiền</th>
                                                <th>Trạng Thái</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (empty($recent_orders)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Không có đơn hàng nào gần đây.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recent_orders as $order): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="index.php?page=order_details&id=<?= $order['id'] ?>">#<?= htmlspecialchars($order['id']) ?></a>
                                                        </td>
                                                        <td><?= htmlspecialchars($order['name']) ?></td>
                                                        <td><?= number_format($order['total_price'], 0, ',', '.') ?> đ</td>
                                                        <td>
                                                            <?php
                                                            $status_class = 'badge-secondary';
                                                            if ($order['status'] === 'đang xử lý') $status_class = 'badge-warning';
                                                            if ($order['status'] === 'đang giao') $status_class = 'badge-info';
                                                            if ($order['status'] === 'đã giao') $status_class = 'badge-success';
                                                            if ($order['status'] === 'đã hủy') $status_class = 'badge-danger';
                                                            ?>
                                                            <span class="badge <?= $status_class ?>"><?= htmlspecialchars($order['status']) ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Sản Phẩm Sắp Hết Hàng (< 10)</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th>Sản Phẩm</th>
                                                <th class="text-end">Tồn Kho</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (empty($low_stock_products)): ?>
                                                <tr>
                                                    <td colspan="2" class="text-center">Không có sản phẩm nào sắp hết hàng.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($low_stock_products as $product): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php
                                                                // Kiểm tra nếu có image_url thì dùng, không thì dùng ảnh mặc định
                                                                $image_path = !empty($product['image_url'])
                                                                    ? '../../view/ViewUser/ProductImage/' . htmlspecialchars($product['image_url'])
                                                                    : 'assets/img/kaiadmin/default_product.png'; // Đường dẫn đến ảnh mặc định
                                                                ?>
                                                                <img src="<?= $image_path ?>" alt="Product" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                                                <a href="index.php?page=products#edit-<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                                                            </div>
                                                        </td>
                                                        <td class="text-end text-danger fw-bold"><?= $product['stock'] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Người Dùng Mới Đăng Ký</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Email</th>
                                                <th>Ngày Đăng Ký</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (empty($recent_users)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">Không có người dùng nào đăng ký gần đây.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recent_users as $user): ?>
                                                    <tr>
                                                        <td><?= $user['id'] ?></td>
                                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var revenueChartData = <?php echo json_encode($revenue_chart_data ?? ['labels' => [], 'data' => []]); ?>;
    var categoryChartData = <?php echo json_encode($category_chart_data ?? ['labels' => [], 'data' => []]); ?>;
</script>
