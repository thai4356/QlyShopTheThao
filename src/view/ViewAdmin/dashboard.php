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
                            <div class="card card-stats card-primary card-round">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="icon-big text-center">
                                                <i class="flaticon-users"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-stats">
                                            <div class="numbers">
                                                <p class="card-category">Người dùng</p>
                                                <h4 class="card-title">1,294</h4> </div>
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
</div>
