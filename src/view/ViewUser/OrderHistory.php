<?php
// File: view/ViewUser/OrderHistory.php

// ===== BƯỚC 1: CẬP NHẬT LẠI HÀM NÀY =====
// Cập nhật các case để khớp với trạng thái trong CSDL của bạn
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
?>

<style>
    /* File: public/css/order-history.css hoặc đặt trực tiếp ở đây */
    body {
        background-color: #f8f9fa;
    }
    .order-history-container {
        max-width: 1140px;
        margin: 100px auto;
        padding: 2rem;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .page-title {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: #333;
    }
    .table-wrapper {
        overflow-x: auto;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    .table thead th {
        background-color: #f1f3f5;
        text-align: left;
        padding: 12px 15px;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 13px;
        border-bottom: 2px solid #dee2e6;
    }
    .table tbody td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        color: #495057;
    }
    .table tbody tr:last-child td {
        border-bottom: none;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* CSS cho các trạng thái đơn hàng */
    .order-status-badge {
        display: inline-block;
        padding: 0.4em 0.8em;
        font-size: 0.85em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 20px; /* Bo tròn hơn */
        color: #fff;
    }
    .status-processing { background-color: #007bff; } /* Xanh dương */
    .status-paid { background-color: #ffc107; color: #212529; } /* Vàng */
    .status-delivered { background-color: #28a745; } /* Xanh lá */
    .status-canceled { background-color: #6c757d; } /* Xám */
    .status-failed { background-color: #dc3545; } /* Đỏ */
    .status-refund-pending { background-color: #fd7e14; } /* Cam */
    .status-default { background-color: #343a40; } /* Đen */

    .btn-order-detail {
        padding: 6px 12px;
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
        transition: background-color 0.2s;
    }
    .btn-order-detail:hover {
        background-color: #5a6268;
    }
    .no-orders-message {
        text-align: center;
        padding: 50px;
        font-size: 18px;
        color: #6c757d;
    }
</style>

<div class="order-history-container container">
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
                    <th>#Mã đơn hàng</th>
                    <th>Ngày đặt</th>
                    <th>Thanh toán</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order):
                    // Gọi hàm đã được cập nhật
                    $statusDisplay = getOrderStatusDisplay($order['status']);
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['orderNo']); ?></strong></td>
                        <td><?php echo date("d/m/Y H:i", strtotime($order['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars(strtoupper($order['payment_method'])); ?></td>
                        <td><?php echo number_format($order['total_price'], 0, ',', '.'); ?>₫</td>
                        <td>
                            <span class="order-status-badge <?php echo $statusDisplay['class']; ?>">
                                <?php echo htmlspecialchars($statusDisplay['text']); ?>
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