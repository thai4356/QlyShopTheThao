<?php
// **THÊM MỚI: Nạp script JS riêng cho trang chi tiết người dùng**
$page_scripts[] = 'assets/js/admin-user-details.js';
?>

<div class="page-header">
    <h4 class="page-title">Chi Tiết Người Dùng</h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="index.php?page=dashboard"><i class="flaticon-home"></i></a>
        </li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item">
            <a href="index.php?page=users">Người Dùng</a>
        </li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item">
            <a href="#">Chi Tiết Người Dùng</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-profile">
            <div class="card-header" style="background-image: url('https://wallpapercave.com/wp/wp11075337.jpg')">
                <div class="profile-picture">
                    <div class="avatar avatar-xl">
                        <img src="https://www.solidbackgrounds.com/images/1920x1080/1920x1080-amber-orange-solid-color-background.jpg" alt="..." class="avatar-img rounded-circle" />
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="user-profile text-center">
                    <div class="name">
                        <?php echo isset($user['email']) ? htmlspecialchars($user['email']) : 'Không có dữ liệu'; ?>
                    </div>
                    <div class="job">
                        <?php echo isset($user['role']) ? 'Vai trò: ' . htmlspecialchars(ucfirst($user['role'])) : ''; ?>
                    </div>
                    <div class="desc">
                        <?php if (isset($user['is_active']) && $user['is_active'] == 1) : ?>
                            <span class="badge bg-primary">Đang hoạt động</span>
                        <?php else : ?>
                            <span class="badge bg-secondary">Bị vô hiệu hóa</span>
                        <?php endif; ?>

                        <?php if (isset($user['is_verified']) && $user['is_verified'] == 1) : ?>
                            <span class="badge bg-success">Đã xác thực</span>
                        <?php else : ?>
                            <span class="badge bg-warning">Chưa xác thực</span>
                        <?php endif; ?>
                    </div>
                    <div class="view-profile">
                        <a href="index.php?page=users" class="btn btn-secondary btn-block">Quay lại danh sách</a>

                        <?php
                        // **ĐÃ CẬP NHẬT: Thống nhất sử dụng thẻ <a> cho cả hai nút**
                        if (isset($user['id']) && isset($_SESSION['user_id']) && $user['id'] != $_SESSION['user_id']) :

                            // Xác định trạng thái hiện tại
                            $currentStatus = (int)$user['is_active'];
                            $actionText = ($currentStatus === 1) ? 'Vô hiệu hóa người dùng' : 'Kích hoạt lại';
                            $btnClass = ($currentStatus === 1) ? 'btn-warning' : 'btn-success';

                            // **Thay đổi từ <button> thành <a> và thêm href="#"**
                            echo '<a href="#" id="toggle-status-btn" class="btn ' . $btnClass . ' btn-block" 
                            data-user-id="' . $user['id'] . '" 
                            data-current-status="' . $currentStatus . '">
                            ' . $actionText . '
                            </a>';
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Lịch Sử Đặt Hàng</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Ngày Đặt</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Hành Động</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($orders)) : ?>
                            <?php foreach ($orders as $order) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['orderNo']); ?></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo number_format($order['total_price'], 0, ',', '.'); ?> đ</td>
                                    <td>
                                            <span class="badge
                                                <?php
                                            switch ($order['status']) {
                                                case 'đã giao': echo 'bg-success'; break;
                                                case 'đang xử lý': echo 'bg-info'; break;
                                                case 'đã thanh toán': echo 'bg-primary'; break;
                                                case 'hủy': echo 'bg-danger'; break;
                                                default: echo 'bg-secondary';
                                            }
                                            ?>">
                                                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                            </span>
                                    </td>
                                    <td>
                                        <a href="index.php?page=order_details&id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Xem</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center">Người dùng này chưa có đơn hàng nào.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>