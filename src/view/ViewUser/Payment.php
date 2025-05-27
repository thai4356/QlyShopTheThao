<?php
session_start();

require_once "../../controller/checklogin.php";

$items = isset($_SESSION['checkout_items']) ? $_SESSION['checkout_items'] : [];
$total = 0;
$count = count($items);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../Public/CSS/checkout.css">
    <link rel="stylesheet" href="../Public/CSS/vCP.css">
    <style>
        /* Additional styles inline or link external CSS */
        .Content_Table { width: 100%; border-collapse: collapse; }
        .Content_Table th, .Content_Table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    </style>
</head>
<body>
<br>
<div class="col-75">
    <div style="width: 45%; float:left; margin-left: 2%">
        <?php if (empty($items)): ?>
            <h1>No items selected for checkout.</h1>
            <h3><a href="indexUser.php">Go to shopping page</a></h3>
        <?php else: ?>
            <div id="content_cart">
                <div id="right_detail">
                    <h3>Your Selected Products</h3>
                    <table class="Content_Table">
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Image</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $lineTotal = $item['price'] * $item['quantity'];
                            $total += $lineTotal;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= number_format($item['price']) ?>₫</td>
                                <td><img src="ProductImage/<?= $item['image_url'] ?>" width="100" height="100"></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($lineTotal) ?>₫</td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <span class="iphone" style="width: 50%; float:right">
        <form action="../../controller/xulyThanhToan.php" method="post" onsubmit="return kt();">

            <p><span>Name</span><input type="text" name="hoten" id="hoten" required></p>
            <p><span>Address</span><input type="text" name="diachi" id="diachi" required></p>
            <p><span>Phone No</span><input type="text" name="dienthoai" id="dienthoai" required></p>
            <fieldset>
                <legend>Payment Method</legend>
                <div class="form__radios">
                    <div class="form__radio">
                        <label for="cod">COD</label>
                        <input checked id="cod" name="payment_method" type="radio" value="cod" />
                    </div>
                    <div class="form__radio">
                        <label for="bank">Bank Transfer</label>
                        <input id="bank" name="payment_method" type="radio" value="bank" />
                    </div>
                    <div class="form__radio">
                        <label for="momo">MoMo</label>
                        <input id="momo" name="payment_method" type="radio" value="momo" />
                    </div>
                </div>
            </fieldset>
            <br>
            <?php
            $shipping = 30000;
            $discount = $total * 0.10;
            $grandTotal = $total + $shipping - $discount;
            ?>
            <div>
                <h2>Shopping Bill</h2>
                <table>
                    <tbody>
                        <tr><td>Shipping fee</td><td align="right"><?= number_format($shipping) ?>₫</td></tr>
                        <tr><td>Discount 10%</td><td align="right">-<?= number_format($discount) ?>₫</td></tr>
                        <tr><td>Subtotal</td><td align="right"><?= number_format($total) ?>₫</td></tr>
                    </tbody>
                    <tfoot>
                        <tr><td>Total</td><td align="right"><strong><?= number_format($grandTotal) ?>₫</strong></td></tr>
                    </tfoot>
                </table>
            </div>
            <div>
                <button name="dathang" id="dathang" class="button button--full" type="submit">
                    <svg class="icon"><use xlink:href="#icon-shopping-bag" /></svg> Buy Now
                </button>
            </div>
        </form>
    </span>
</div>
<script>
    function kt() {
        var hoten = document.getElementById("hoten");
        var diachi = document.getElementById("diachi");
        var dienthoai = document.getElementById("dienthoai");
        if(hoten.value=="" || diachi.value=="" || dienthoai.value=="") {
            alert("Please fill out all fields.");
            return false;
        }
        return true;
    }
</script>
</body>
</html>


<style>
    @use postcss-preset-env {
        stage: 0;
    }

    :root {
        --color-background: #fae3ea;
        --color-primary: #fc8080;
        --font-family-base: Poppin, sans-serif;
        --font-size-h1: 1.25rem;
        --font-size-h2: 1rem;
    }


    * {
        box-sizing: inherit;
    }

    html {
        box-sizing: border-box;
    }



    address {
        font-style: normal;
    }

    button {
        border: 0;
        color: inherit;
        cursor: pointer;
        font: inherit;
    }

    fieldset {
        border: 0;
        margin: 0;
        padding: 0;
    }

    h1 {
        font-size: var(--font-size-h1);
        line-height: 1.2;
        margin-block: 0 1.5em;
    }

    h2 {
        font-size: var(--font-size-h2);
        line-height: 1.2;
        margin-block: 0 0.5em;
    }

    legend {
        font-weight: 600;
        margin-block-end: 0.5em;
        padding: 0;
    }

    input {
        border: 0;
        color: inherit;
        font: inherit;
    }

    input[type="radio"] {
        accent-color: var(--color-primary);
    }

    table {
        border-collapse: collapse;
        inline-size: 100%;
    }

    tbody {
        color: #b4b4b4;
    }

    td {
        padding-block: 0.125em;
    }

    tfoot {
        border-top: 1px solid #b4b4b4;
        font-weight: 600;
    }

    .align {
        display: grid;
        place-items: center;
    }

    .button {
        align-items: center;
        background-color: var(--color-primary);
        border-radius: 999em;
        color: #fff;
        display: flex;
        gap: 0.5em;
        justify-content: center;
        padding-block: 0.75em;
        padding-inline: 1em;
        transition: 0.3s;
    }

    .button:focus,
    .button:hover {
        background-color: #e96363;
    }

    .button--full {
        inline-size: 100%;
    }

    .card {
        border-radius: 1em;
        background-color: var(--color-primary);
        color: #fff;
        padding: 1em;
    }

    .form {
        display: grid;
        gap: 2em;
    }

    .form__radios {
        display: grid;
        gap: 1em;
    }

    .form__radio {
        align-items: center;
        background-color: #fefdfe;
        border-radius: 1em;
        box-shadow: 0 0 1em rgba(0, 0, 0, 0.0625);
        display: flex;
        padding: 1em;
    }

    .form__radio label {
        align-items: center;
        display: flex;
        flex: 1;
        gap: 1em;
    }

    .header {
        display: flex;
        justify-content: center;
        padding-block: 0.5em;
        padding-inline: 1em;
    }

    .icon {
        block-size: 1em;
        display: inline-block;
        fill: currentColor;
        inline-size: 1em;
        vertical-align: middle;
    }

    .iphone {
        background-color: #fbf6f7;
        background-image: linear-gradient(to bottom, #fbf6f7, #fff);
        border-radius: 2em;
        block-size: 812px;
        box-shadow: 0 0 1em rgba(0, 0, 0, 0.0625);
        inline-size: 375px;
        overflow: auto;
        padding: 2em;
    }


</style>
