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
                        <div class="col-md-12">
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
                                    <div class="chart-container">
                                        <canvas id="revenueChart"></canvas>
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
</script>
