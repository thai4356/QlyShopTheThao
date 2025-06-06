<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn #<?php echo $orderDetails['id']; ?></title>
    <style>
        /* Dùng font DejaVu Sans vì nó hỗ trợ tiếng Việt rất tốt trong mpdf */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .details table { width: 100%; margin-bottom: 30px;}
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; }
        .items-table th { background-color: #f2f2f2; text-align: left; }
        .text-right { text-align: right; }
        .total { margin-top: 20px; text-align: right; font-size: 14px; }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="header">
        <h1>HÓA ĐƠN BÁN HÀNG</h1>
        <p>Mã đơn hàng: #<?php echo htmlspecialchars($orderDetails['id']); ?></p>
        <p>Ngày đặt: <?php echo date('d/m/Y', strtotime($orderDetails['created_at'])); ?></p>
    </div>

    <div class="details">
        <table>
            <tr>
                <td style="width: 50%;">
                    <strong>Họ và tên:</strong> <?php echo htmlspecialchars($orderDetails['name']); ?><br>
                    <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($orderDetails['address']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($orderDetails['customer_email']); ?><br>
                    <strong>Điện thoại:</strong> <?php echo htmlspecialchars($orderDetails['phone']); ?>
                </td>
                <td class="text-right" style="vertical-align: top;">
                    <strong>Phương thức thanh toán:</strong><br>
                    <?php echo strtoupper(htmlspecialchars($orderDetails['payment_method'])); ?>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
        <tr>
            <th>Sản phẩm</th>
            <th class="text-right">Số lượng</th>
            <th class="text-right">Đơn giá</th>
            <th class="text-right">Thành tiền</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="text-right"><?php echo $item['quantity']; ?></td>
                <td class="text-right"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?>đ</td>
                <td class="text-right"><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        <h3>Tổng cộng: <?php echo number_format($orderDetails['total_price'], 0, ',', '.'); ?>đ</h3>
    </div>
</div>
</body>
</html>