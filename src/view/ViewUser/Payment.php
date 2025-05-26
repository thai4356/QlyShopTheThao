<?php

session_start();
$items = isset($_SESSION['checkout_items']) ? $_SESSION['checkout_items'] : [];

if (empty($items)) {
    echo "Không có sản phẩm để thanh toán!";
    exit;
}
?>

<?php
include 'header.php';
?>


<h2>Chọn hình thức thanh toán</h2>

<table border="1" cellpadding="10" cellspacing="0" style="width:100%; margin-bottom:20px;">
    <tr>
        <th>Ảnh</th>
        <th>Tên sản phẩm</th>
        <th>Đơn giá</th>
        <th>Số lượng</th>
        <th>Thành tiền</th>
    </tr>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><img src="../image/Product/<?= $item['image_url'] ?>" width="60"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price']) ?>₫</td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['price'] * $item['quantity']) ?>₫</td>
        </tr>
    <?php endforeach; ?>
</table>

<form method="post" action="?module=order">
    <label><input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng (COD)</label><br>
    <label><input type="radio" name="payment_method" value="bank"> Chuyển khoản ngân hàng</label><br>
    <label><input type="radio" name="payment_method" value="momo"> Ví MoMo</label><br><br>
    <button type="submit">Hoàn tất đơn hàng</button>
</form>

<?php
include 'footer.php';
?>

