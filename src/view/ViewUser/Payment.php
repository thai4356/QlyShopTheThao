<?php
session_start();
require_once "../../controller/checklogin.php";

$items = isset($_SESSION['checkout_items']) ? $_SESSION['checkout_items'] : [];
$total = 0;
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán đơn hàng</title>
    <!-- Link đến file CSS -->
    <link rel="stylesheet" href="../Public/CSS/payment-style.css">

</head>
<body>

<div class="container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <?php if (empty($items)): ?>
        <div style="text-align: center; padding: 50px;">
            <h1 style="color: #888;">Giỏ hàng của bạn đang trống</h1>
            <a href="indexUser.php" class="btn-submit" style="display:inline-block; width:auto; text-decoration:none;">Quay lại mua sắm</a>
        </div>
    <?php else: ?>

        <form action="../../controller/xulyThanhToan.php" method="post" onsubmit="return kt();">
            <div class="checkout-layout">

                <!-- CỘT TRÁI: SẢN PHẨM -->
                <div class="checkout-products">
                    <h3>Sản phẩm đã chọn (<?= count($items) ?>)</h3>
                    <table class="product-table">
                        <thead>
                        <tr>
                            <th style="width: 40%">Sản phẩm</th>
                            <th>Giá</th>
                            <th>Hình ảnh</th>
                            <th>SL</th>
                            <th>Thành tiền</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $lineTotal = $item['price'] * $item['quantity'];
                            $total += $lineTotal;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td class="product-price"><?= number_format($item['price']) ?>₫</td>
                                <td>
                                    <img src="ProductImage/<?= $item['image_url'] ?>" width="60" height="60" alt="Img" style="object-fit:cover; border-radius:5px;">
                                </td>
                                <td>x<?= $item['quantity'] ?></td>
                                <td class="product-price"><?= number_format($lineTotal) ?>₫</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- CỘT PHẢI: THÔNG TIN & THANH TOÁN -->
                <div class="checkout-payment">
                    <h3>Thông tin giao hàng</h3>

                    <div class="form-group">
                        <label for="hoten">Họ và tên</label>
                        <input type="text" name="hoten" id="hoten" placeholder="Nhập họ tên người nhận" required>
                    </div>

                    <div class="form-group">
                        <label for="dienthoai">Số điện thoại</label>
                        <input type="text" name="dienthoai" id="dienthoai" placeholder="Ví dụ: 0912345678" required>
                    </div>

                    <div class="form-group">
                        <label for="diachi">Địa chỉ nhận hàng</label>
                        <input type="text" name="diachi" id="diachi" placeholder="Số nhà, đường, phường/xã..." required>
                    </div>

                    <h3 style="margin-top: 25px;">Phương thức thanh toán</h3>
                    <div class="payment-methods" style="border: 1px solid #eee; border-radius: 8px;">
                        <label for="cod" class="payment-option">
                            <input type="radio" id="cod" name="payment_method" value="cod" checked>
                            <span style="margin-left: 10px;">Thanh toán khi nhận hàng (COD)</span>
                            <img src="https://cdn-icons-png.flaticon.com/512/2331/2331941.png" alt="COD" class="payment-icon">
                        </label>
                        <label for="payos" class="payment-option">
                            <input type="radio" id="payos" name="payment_method" value="payos">
                            <span style="margin-left: 10px;">Thanh toán chuyển khoản (PayOS)</span>
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSn7cwXPUowOI81NE9GEkuks2EUjHwYPsHm2A&s" alt="PayOS" class="payment-icon" style="height: 25px;">
                        </label>
                        <label for="stripe" class="payment-option">
                            <input type="radio" id="stripe" name="payment_method" value="stripe">
                            <span style="margin-left: 10px;">Thanh toán qua thẻ quốc tế (Stripe)</span>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" class="payment-icon" style="height: 25px;">
                        </label>
                    </div>

                    <?php
                    $shipping = 30000;
                    $discount = $total * 0.10; // Giảm 10%
                    $grandTotal = $total + $shipping - $discount;
                    ?>

                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?= number_format($total) ?>₫</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span><?= number_format($shipping) ?>₫</span>
                        </div>
                        <div class="summary-row" style="color: #27ae60;">
                            <span>Giảm giá (10%):</span>
                            <span>-<?= number_format($discount) ?>₫</span>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng thanh toán:</span>
                            <span><?= number_format($grandTotal) ?>₫</span>
                        </div>
                    </div>

                    <button type="submit" name="dathang" id="dathang" class="btn-submit">
                        Đặt hàng ngay
                    </button>
                </div>
            </div>
        </form>

    <?php endif; ?>
</div>

<script>
    function kt() {
        var hoten = document.getElementById("hoten").value.trim();
        var diachi = document.getElementById("diachi").value.trim();
        var dienthoai = document.getElementById("dienthoai").value.trim();

        if (hoten === "" || diachi === "" || dienthoai === "") {
            alert("Vui lòng điền đầy đủ thông tin giao hàng!");
            return false;
        }
        if(isNaN(dienthoai) || dienthoai.length < 9) {
            alert("Số điện thoại không hợp lệ!");
            return false;
        }
        return true;
    }
</script>
</body>
</html>