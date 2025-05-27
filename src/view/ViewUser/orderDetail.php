<div class="container" style="margin-top: 100px;">
    <h2>Chi tiết đơn hàng #<?= $order['id'] ?></h2>
    <p><strong>Ngày đặt:</strong> <?= $order['created_at'] ?></p>
    <p><strong>Người nhận:</strong> <?= $order['name'] ?> | <strong>Địa chỉ:</strong> <?= $order['address'] ?> | <strong>SĐT:</strong> <?= $order['phone'] ?></p>
    <p><strong>Phương thức:</strong> <?= $order['payment_method'] ?> | <strong>Trạng thái:</strong> <?= $order['status'] ?></p>

    <table class="table table-bordered mt-4">
        <thead class="table-light">
        <tr>

            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= number_format($item['unit_price']) ?>₫</td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['unit_price'] * $item['quantity']) ?>₫</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h5 class="text-end">Tổng thanh toán: <span style="color:red"><?= number_format($order['total_price']) ?>₫</span></h5>
</div>
