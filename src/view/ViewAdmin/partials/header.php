<?php
// view/ViewAdmin/partials/header.php

// Biến $pageTitle sẽ được truyền từ layout.php (hoặc đặt mặc định)
$page_title = isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Dashboard';
// Đường dẫn đến thư mục assets, tính từ file index.php trong ViewAdmin
$assets_path = 'assets/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo $page_title; ?> - Shop Thể Thao Admin</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="<?php echo $assets_path; ?>img/kaiadmin/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"  referrerpolicy="no-referrer" />
    <script src="<?php echo $assets_path; ?>js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: {"families":["Be VietNam Pro:300,400,500,600,700"]},
            custom: {"families":["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['<?php echo $assets_path; ?>css/fonts.min.css']},
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>

    <link rel="stylesheet" href="<?php echo $assets_path; ?>css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo $assets_path; ?>css/plugins.min.css" />
    <link rel="stylesheet" href="<?php echo $assets_path; ?>css/kaiadmin.min.css" />

</head>
<body>
<div class="wrapper">
