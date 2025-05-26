<?php
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
?>
<h2>Đặt hàng thành công!</h2>
<p>Mã đơn hàng của bạn là: <strong>#<?php echo $orderId; ?></strong></p>
