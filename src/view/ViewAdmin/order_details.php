<?php
// view/ViewAdmin/order_details.php

// Biến $orderDetails và $orderItems đã được controller truyền sang
// Nhớ lại biến cấu hình base URL cho ảnh sản phẩm từ footer
global $product_image_base_url;
if (!isset($product_image_base_url)) {
    // Giá trị fallback nếu không được thiết lập
    $product_image_base_url = '../../view/ViewUser/ProductImage/';
}
$currentStatus = $orderDetails['status'];

?>

<div class="page-header">
    <h4 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h4>
    <div class="ms-auto">
        <a href="index.php?page=orders" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Quay về danh sách
        </a>
        <a href="index.php?ctrl=adminorder&act=printInvoice&id=<?php echo $orderDetails['id']; ?>" target="_blank" class="btn btn-default">
            <i class="fa fa-print"></i> In Hóa đơn
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Thông tin Đơn hàng</h4></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Mã đơn hàng: <span><strong>#<?php echo htmlspecialchars($orderDetails['id']); ?></strong></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Ngày đặt:
                        <span><?php echo date('d/m/Y H:i:s', strtotime($orderDetails['created_at'])); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Trạng thái:
                        <span id="orderStatusBadge">
                        <?php
                        $status = $orderDetails['status'];
                        $badgeClass = 'badge-dark'; // Mặc định
                        if ($status === 'đã giao') $badgeClass = 'badge-success';
                        else if ($status === 'đang xử lý') $badgeClass = 'badge-info';
                        else if ($status === 'đã thanh toán') $badgeClass = 'badge-warning';

                        // THÊM MỚI Ở ĐÂY
                        else if ($status === 'chờ hoàn tiền') $badgeClass = 'badge-danger';

                        else if ($status === 'hủy' || $status === 'thất bại') $badgeClass = 'badge-danger';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Thanh toán:
                        <span><?php echo htmlspecialchars(strtoupper($orderDetails['payment_method'])); ?></span>
                    </li>
                </ul>
            </div>
            <div class="card-footer text-center">

                <?php if ($currentStatus === 'chờ hoàn tiền'): ?>
                <?php endif; ?>

                <?php if ($currentStatus === 'đang xử lý' || $currentStatus === 'đã thanh toán'): ?>
                    <button id="processOrderBtn" class="btn btn-success" data-order-id="<?php echo $orderDetails['id']; ?>">
                        <i class="fa fa-check"></i> Xử lý (Giao hàng)
                    </button>
                <?php endif; ?>

                <?php if ($currentStatus === 'đang xử lý'): ?>
                    <button id="cancelOrderBtn" class="btn btn-danger" data-order-id="<?php echo $orderDetails['id']; ?>">
                        <i class="fa fa-times"></i> Hủy đơn hàng
                    </button>
                <?php endif; ?>

                <?php if ($currentStatus === 'đã thanh toán'): ?>
                    <button id="cancelPaidOrderBtn" class="btn btn-danger" data-order-id="<?php echo $orderDetails['id']; ?>">
                        <i class="fa fa-undo"></i> Hủy đơn & Hoàn tiền
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4 class="card-title">Thông tin Khách hàng</h4></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Tên:</strong> <?php echo htmlspecialchars($orderDetails['name']); ?></li>
                    <li class="list-group-item">
                        <strong>Email:</strong> <?php echo htmlspecialchars($orderDetails['customer_email']); ?></li>
                    <li class="list-group-item"><strong>Số điện
                            thoại:</strong> <?php echo htmlspecialchars($orderDetails['phone']); ?></li>
                    <li class="list-group-item"><strong>Địa chỉ giao
                            hàng:</strong><br><?php echo nl2br(htmlspecialchars($orderDetails['address'])); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Danh sách Sản phẩm</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col" class="text-center">Đơn giá</th>
                            <th scope="col" class="text-center">Số lượng</th>
                            <th scope="col" class="text-right">Tạm tính</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr class="product-row-clickable" data-product-id="<?php echo $item['product_id']; ?>"
                                style="cursor: pointer;">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $product_image_base_url . (!empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'default.png'); ?>"
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                             class="img-thumbnail"
                                             style="width: 60px; height: 60px; object-fit: cover; margin-right: 15px;">
                                        <div>
                                            <a href="#" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                               data-bs-html="true"
                                               data-bs-title="<b>Click để xem chi tiết sản phẩm</b>"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                            <br>
                                            <small>ID: <?php echo $item['product_id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?>
                                    đ
                                </td>
                                <td class="text-center align-middle"><?php echo $item['quantity']; ?></td>
                                <td class="text-right align-middle"><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', '.'); ?>
                                    đ
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Tổng tiền hàng</strong></td>
                            <td class="text-right"><?php echo number_format($orderDetails['total_price'], 0, ',', '.'); ?>
                                đ
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Phí vận chuyển</strong></td>
                            <td class="text-right">0đ</td>
                        </tr>
                        <tr class="font-weight-bold">
                            <td colspan="3" class="text-right"><h4>Thành tiền</h4></td>
                            <td class="text-right">
                                <h4><?php echo number_format($orderDetails['total_price'], 0, ',', '.'); ?>đ</h4></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productDetailModal" tabindex="-1" role="dialog" aria-labelledby="productDetailModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductName">Chi tiết Sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-loader" style="display: none; text-align: center;">
                    <p>Đang tải dữ liệu...</p>
                </div>
                <div id="modal-content-container">
                    <div class="row">
                        <div class="col-md-6">
                            <div id="modalProductImagesCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner" id="carousel-inner-container">
                                </div>
                                <a class="carousel-control-prev" href="#" role="button"
                                   data-bs-target="#modalProductImagesCarousel" data-bs-slide="prev"
                                   style="background-color: rgba(0,0,0,0.3);">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <a class="carousel-control-next" href="#" role="button"
                                   data-bs-target="#modalProductImagesCarousel" data-bs-slide="next"
                                   style="background-color: rgba(0,0,0,0.3);">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>Thông tin cơ bản</h4>
                            <p><strong>Thương hiệu:</strong> <span id="modalProductBrand"></span></p>
                            <p><strong>Giá gốc:</strong> <span id="modalProductPrice"></span></p>
                            <p><strong>Giá khuyến mãi:</strong> <span id="modalProductDiscountPrice"></span></p>
                            <p><strong>Tồn kho:</strong> <span id="modalProductStock"></span></p>
                            <hr>
                            <h4>Mô tả sản phẩm</h4>
                            <div id="modalProductDescription"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>