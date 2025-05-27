<?php

// Function to map status to text and CSS class (moved here for clarity or can be in a helper file)
function getOrderStatusDisplay($status) {
    return match ($status) {
        'pending' => ['text' => 'Đang xử lý', 'class' => 'status-pending'],
        'confirmed' => ['text' => 'Đã xác nhận', 'class' => 'status-confirmed'],
        'shipped' => ['text' => 'Đang giao', 'class' => 'status-shipped'],
        'completed' => ['text' => 'Hoàn tất', 'class' => 'status-completed'],
        'canceled' => ['text' => 'Đã hủy', 'class' => 'status-canceled'],
        default => ['text' => 'Không xác định', 'class' => 'status-default'],
    };
}
?>

<link rel="stylesheet" href="../Public/CSS/order-history.css"> <div class="order-history-container container" style="margin-top: 100px;">
    <h2 class="page-title">Lịch sử đơn hàng</h2>

    <?php if (empty($orders)): ?>
        <div class="no-orders-message">
            <p>Chưa có đơn hàng nào.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table table-hover mt-4">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Ngày đặt</th>
                    <th>Phương thức</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $index => $order):
                    $statusDisplay = getOrderStatusDisplay($order['status']);
                    ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($order['created_at'])) ?></td>
                        <td><?= htmlspecialchars($order['payment_method']) ?></td>
                        <td><?= number_format($order['total_price'], 0, ',', '.') ?>₫</td>
                        <td>
                            <span class="order-status-badge <?= $statusDisplay['class'] ?>">
                                <?= htmlspecialchars($statusDisplay['text']) ?>
                            </span>
                        </td>
                        <td>

                            <a href="?module=orderdetail&id=<?= $order['id'] ?>" class="btn-order-detail">
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>