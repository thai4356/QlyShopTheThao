<?php
// view/ViewAdmin/partials/footer_scripts.php
$assets_path = 'assets/'; // Đường dẫn đến thư mục assets
?>
<footer class="footer">
    <div class="container-fluid">
        <nav class="pull-left">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="#"> SportShop Admin
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"> Help </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"> Licenses </a>
                </li>
            </ul>
        </nav>
        <div class="copyright ms-auto">
            <?php echo date('Y'); ?>, made with <i class="fa fa-heart heart text-danger"></i> by
            <a href="#">Your Team/Name</a>
        </div>
    </div>
</footer>
</div> </div> <script src="<?php echo $assets_path; ?>js/core/jquery-3.7.1.min.js"></script>
<script src="<?php echo $assets_path; ?>js/core/popper.min.js"></script>
<script src="<?php echo $assets_path; ?>js/core/bootstrap.min.js"></script>

<script src="<?php echo $assets_path; ?>js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

<script src="<?php echo $assets_path; ?>js/plugin/chart.js/chart.min.js"></script>

<script src="<?php echo $assets_path; ?>js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

<script src="<?php echo $assets_path; ?>js/plugin/chart-circle/circles.min.js"></script>

<script src="https://cdn.datatables.net/2.3.1/js/dataTables.min.js" referrerpolicy="no-referrer"></script>

<script src="<?php echo $assets_path; ?>js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="<?php echo $assets_path; ?>js/kaiadmin.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" referrerpolicy="no-referrer"></script>

<?php
// Nơi để echo các script riêng của từng trang nếu cần
if (isset($page_scripts) && is_array($page_scripts)) {
    foreach ($page_scripts as $script_url) {
        echo '<script src="' . htmlspecialchars($script_url) . '"></script>';
    }
}
?>

<script>
    // Khởi tạo tooltip (có thể nằm trong admin-common.js)
    $(document).ready(function() { // Đảm bảo DOM sẵn sàng
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // **QUAN TRỌNG: Định nghĩa các biến cấu hình toàn cục cho JavaScript ở đây**
    var MyAppAdmin = window.MyAppAdmin || {}; // Đảm bảo namespace tồn tại
    MyAppAdmin.config = MyAppAdmin.config || {};
    // Lấy giá trị $product_image_base_url từ PHP mà AdminProductController đã truyền cho view products.php
    // Biến này sẽ được file products.php (là file view chính) nhận được thông qua extract($view_data)
    // Do đó, footer_scripts.php (được include bởi layout.php, mà layout.php được include bởi index.php sau khi extract)
    // sẽ có thể truy cập được biến $product_image_base_url này.
    MyAppAdmin.config.productImageBaseUrl = "<?php echo isset($product_image_base_url) ? htmlspecialchars($product_image_base_url) : '../../view/ViewUser/ProductImage/'; // Giá trị fallback nếu không có ?>";
    console.log("Admin Config - productImageBaseUrl set to:", MyAppAdmin.config.productImageBaseUrl);
</script>

<script src="<?php echo $assets_path; ?>js/admin-common.js"></script>
<script src="<?php echo $assets_path; ?>js/image-cropper-modal.js"></script>
<script src="<?php echo $assets_path; ?>js/product-modal-add.js"></script>
<script src="<?php echo $assets_path; ?>js/product-modal-edit.js"></script>
<script src="<?php echo $assets_path; ?>js/product-delete-handler.js"></script>
<script src="<?php echo $assets_path; ?>js/product-delete-handler.js"></script>


<script src="<?php echo $assets_path; ?>js/category-modal-handler.js    "></script>
<script>
    
</script>


</body>
</html>
