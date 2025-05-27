<div class="container" style="margin-top: 100px;">
    <h2>Lịch sử đơn hàng</h2>

    <?php if (empty($orders)): ?>
        <p>Chưa có đơn hàng nào.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped mt-4">
            <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Ngày đặt</th>
                <th>Phương thức</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Xem</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $index => $order): ?>
                <tr>
                    <td><?= $order['orderNo'] ?></td>
                    <td><?= $order['created_at'] ?></td>
                    <td><?= htmlspecialchars($order['payment_method']) ?></td>
                    <td><?= number_format($order['total_price']) ?>₫</td>
                    <td>
                        <?= match ($order['status']) {
                            'pending' => 'Đang xử lý',
                            'confirmed' => 'Đã xác nhận',
                            'shipped' => 'Đang giao',
                            'completed' => 'Hoàn tất',
                            'canceled' => 'Đã hủy',
                            default => 'Không xác định'
                        }; ?>
                    </td>
                    <td>
                        <a href="?module=orderdetail&id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                    </td>
                </tr>

            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
