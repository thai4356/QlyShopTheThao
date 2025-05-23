<?php
$conn = require_once "../model/Connect.php";

$stmt = $conn->query("SELECT * FROM product");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <form action="../../controller/xulyLogout.php" method="post">
        <button type="submit">Logout</button>
    </form>


    <title>Danh sách sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4 text-center"> Danh sách sản phẩm</h2>
    <table class="table table-bordered table-hover bg-white shadow-sm">
        <thead class="table-dark">
        <tr>
            <th>Tên</th>
            <th>Giá (VNĐ)</th>
            <th>Số lượng</th>
            <th>Mô tả</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= number_format($p['price'], 0, ',', '.') ?></td>
                <td><?= $p['quantity'] ?></td>
                <td><?= htmlspecialchars($p['description']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

