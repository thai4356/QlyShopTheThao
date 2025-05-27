<?php
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CDN nếu cần -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Be Vietnam Pro', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .success-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }
        .success-container h2 {
            color: #28a745;
            margin-bottom: 20px;
        }
        .order-id {
            font-size: 20px;
            color: #333;
            margin-bottom: 30px;
        }
        .order-id strong {
            color: #dc3545;
        }
        .btn-home {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .btn-home:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="success-container">
    <h2>🎉 Đặt hàng thành công! Hãy theo dõi tình trạng đơn hàng của bạn trong phần lịch sử nhé</h2>
    <p class="order-id">Mã đơn hàng của bạn là: <strong>#<?php echo $orderId; ?></strong></p>
    <a href="Index.php" class="btn-home">⬅️ Về trang chủ</a>
</div>

</body>
</html>
