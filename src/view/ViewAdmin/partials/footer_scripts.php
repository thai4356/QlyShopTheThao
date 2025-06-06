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
            <a href="#">Nhóm 1</a>
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

//Nạp scripts cho việc khởi tạo datatable cho các trang

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
    MyAppAdmin.config.productImageBaseUrl = "<?php echo isset($product_image_base_url) ? htmlspecialchars($product_image_base_url) : '../../view/ViewUser/ProductImage/'; // Giá trị fallback nếu không có ?>";
    console.log("Admin Config - productImageBaseUrl set to:", MyAppAdmin.config.productImageBaseUrl);

    // **THÊM DÒNG MỚI NÀY**
    // Lấy ID của admin đang đăng nhập từ session để JS sử dụng
    MyAppAdmin.config.currentUserId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>;

    console.log("Admin Config - productImageBaseUrl set to:", MyAppAdmin.config.productImageBaseUrl);
    console.log("Admin Config - currentUserId set to:", MyAppAdmin.config.currentUserId);
</script>

<script src="<?php echo $assets_path; ?>js/admin-common.js"></script>
<script src="<?php echo $assets_path; ?>js/image-cropper-modal.js"></script>
<!--product-->
<script src="<?php echo $assets_path; ?>js/product-modal-add.js"></script>
<script src="<?php echo $assets_path; ?>js/product-modal-edit.js"></script>
<script src="<?php echo $assets_path; ?>js/product-delete-handler.js"></script>

<!--category-->
<script src="<?php echo $assets_path; ?>js/category-modal-handler.js"></script>

<!--order-->



</body>
</html>
