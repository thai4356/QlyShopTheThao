<?php
// view/ViewAdmin/orders.php
?>
<div class="page-header">
    <h4 class="page-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Đơn hàng'; ?></h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="index.php?page=dashboard"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="index.php?page=orders">Quản Lý Cửa Hàng</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="index.php?page=orders">Đơn Hàng</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Danh sách Đơn hàng</h4>
            </div>
            <div class="card-body">
                <div class="row form-group">
                    <div class="col-md-3">
                        <label for="statusFilter">Lọc theo trạng thái:</label>
                        <select id="statusFilter" class="form-control">
                            <option value="">Tất cả trạng thái</option>
                            <option value="đang xử lý">Đang xử lý</option>
                            <option value="đã thanh toán">Đã thanh toán</option>
                            <option value="đã giao">Đã giao</option>
                            <option value="hủy">Hủy</option>
                            <option value="thất bại">Thất bại</option>
                            <option value="chờ hoàn tiền" style="color: red; font-weight: bold;">Chờ hoàn tiền</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="paymentFilter">Lọc theo thanh toán:</label>
                        <select id="paymentFilter" class="form-control">
                            <option value="">Tất cả phương thức</option>
                            <option value="cod">COD</option>
                            <option value="vnpay">VNPAY</option>
                            <option value="payos">PayOS</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="emailFilter">Lọc theo email khách hàng:</label>
                        <select id="emailFilter" class="form-control">
                            <option value="">Tất cả email</option>
                            <?php if (isset($customer_emails) && !empty($customer_emails)): ?>
                                <?php foreach ($customer_emails as $email): ?>
                                    <option value="<?php echo htmlspecialchars($email); ?>">
                                        <?php echo htmlspecialchars($email); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="ordersTable" class="display table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Khách Hàng</th>
                            <th>Email</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Thanh Toán</th>
                            <th>Ngày Đặt</th>
                            <th>Hành Động</th>
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