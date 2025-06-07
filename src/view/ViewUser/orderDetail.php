<?php
function getOrderStatusDisplay($status) {
    // Chuyển trạng thái về chữ thường để so sánh cho chắc chắn
    $status = mb_strtolower($status, 'UTF-8');

    return match ($status) {
        'đang xử lý' => ['text' => 'Đang xử lý', 'class' => 'status-processing'],
        'đã thanh toán' => ['text' => 'Đã thanh toán', 'class' => 'status-paid'],
        'đã giao' => ['text' => 'Đã giao', 'class' => 'status-delivered'],
        'hủy' => ['text' => 'Đã hủy', 'class' => 'status-canceled'],
        'thất bại' => ['text' => 'Thất bại', 'class' => 'status-failed'],
        'chờ hoàn tiền' => ['text' => 'Chờ hoàn tiền', 'class' => 'status-refund-pending'],
        default => ['text' => 'Không xác định', 'class' => 'status-default'],
    };
}
$statusDisplay = getOrderStatusDisplay($order['status']);
?>

<link rel="stylesheet" href="../Public/CSS/order-detail.css">

<div class="order-detail-container container" style="margin-top: 100px;">
    <h2 class="page-title">Chi tiết đơn hàng #<?= htmlspecialchars($order['orderNo']) ?></h2>

    <div class="order-info-card">
        <div class="info-section">
            <h3 class="info-header">Thông tin chung</h3>
            <p><strong>Ngày đặt:</strong> <?= htmlspecialchars(date("d/m/Y H:i", strtotime($order['created_at']))) ?></p>
            <p><strong>Phương thức thanh toán:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            <p><strong>Trạng thái:</strong>
                <span class="order-status-detail <?= htmlspecialchars($statusDisplay['class']) ?>">
                    <?= htmlspecialchars($statusDisplay['text']) ?>
                </span>
            </p>
        </div>

        <div class="info-section">
            <h3 class="info-header">Thông tin người nhận</h3>
            <p><strong>Người nhận:</strong> <?= htmlspecialchars($order['name']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
        </div>
    </div>

    <h3 class="info-header" style="text-align: left; margin-bottom:15px; font-family: var(--ff-catamaran, 'Catamaran', sans-serif); font-size: var(--fs-4, 1.9rem); color: var(--rich-black-fogra-29-1, #1C2331);">Sản phẩm đã đặt</h3>
    <div class="order-items-table-wrapper">
        <table class="table order-items-table">
            <thead>
            <tr>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= number_format($item['unit_price'], 0, ',', '.') ?>₫</td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') ?>₫</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Không có sản phẩm nào trong đơn hàng này.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="order-total-summary">
        <span class="total-title">Tổng thanh toán:</span>
        <span class="total-amount"><?= number_format($order['total_price'], 0, ',', '.') ?>₫</span>
    </div>
</div>