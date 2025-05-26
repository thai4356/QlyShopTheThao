<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Giỏ hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .cart-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            border-radius: 4px;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        a {
            color: #d9534f;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="cart-container" style="margin-top: 100px ; margin-bottom:  100px">
    <h2 style="margin-top: 0">Giỏ hàng</h2>


        <?php if (empty($items)): ?>
            <p style="margin-top:300px; margin-bottom: 100px; text-align:center; font-size:18px; color:gray;">
                Giỏ hàng trống
            </p>
        <?php else: ?>
            <form method="post" action="?module=order">
                <table border="1" cellpadding="10">
                    <tr>
                        <th></th><th>Ảnh</th><th>Tên</th><th>Giá</th><th>Số lượng</th><th>Tổng</th><th>Xóa</th>
                    </tr>
                    <?php
                    $tong = 0;
                    foreach ($items as $item):
                        $total = $item['price'] * $item['quantity'];
                        $tong += $total;
                        ?>
                        <tr>
                            <td class="checkbox-cell"><input type="checkbox" name="select_item[]" value="<?= $item['product_id'] ?>" onclick="updateTotal()">
                            <td><img src="image/Product/<?= $item['image_url'] ?>" width="60"></td>
                            <td><?= $item['name'] ?></td>
                            <td id="total-<?= $item['product_id'] ?>" data-total="<?= $total ?>">
                                <?= number_format($total) ?>₫
                            </td>
                            <td>
                                <input class="qty-input"
                                       type="number"
                                       name="qty[<?= $item['product_id'] ?>]"
                                       id="qty-<?= $item['product_id'] ?>"
                                       value="<?= $item['quantity'] ?>"
                                       min="1"
                                       onchange="updateItemTotal(<?= $item['product_id'] ?>, <?= $item['price'] ?>)">
                            </td>
                            <input type="hidden" name="qty_hidden[<?= $item['product_id'] ?>]" id="qty-hidden-<?= $item['product_id'] ?>" value="<?= $item['quantity'] ?>">

                            <td data-total="<?= $total ?>"><?= number_format($total) ?>₫</td>
                            <td><a href="?module=cart&act=remove&masp=<?= $item['product_id'] ?>">Xóa</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="6" align="right"><strong>Tổng đã chọn:</strong></td>
                        <td id="selected-total">0₫</td>
                    </tr>
                </table>
                <br>
                <div style="text-align: right">
                    <button type="submit" class="button">Thanh toán sản phẩm đã chọn</button>
                </div>
            </form>
        <?php endif; ?>




</div>
</body>
</html>

<script>
    function updateItemTotal(productId, price) {
        const qtyInput = document.getElementById(`qty-${productId}`);
        const qty = parseInt(qtyInput.value) || 1;

        // Cập nhật ô ẩn
        const hiddenQtyInput = document.getElementById(`qty-hidden-${productId}`);
        if (hiddenQtyInput) {
            hiddenQtyInput.value = qty;
        }

        const total = qty * price;

        const totalCell = document.getElementById(`total-${productId}`);
        totalCell.dataset.total = total;
        totalCell.innerText = total.toLocaleString('vi-VN') + '₫';

        updateTotal();
    }


    function updateTotal() {
        let total = 0;
        const checkboxes = document.querySelectorAll('input[name="select_item[]"]');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const productId = cb.value;
                const totalCell = document.getElementById(`total-${productId}`);
                if (totalCell) {
                    total += parseFloat(totalCell.dataset.total) || 0;
                }
            }
        });
        document.getElementById("selected-total").innerText = total.toLocaleString('vi-VN') + '₫';
    }

</script>

