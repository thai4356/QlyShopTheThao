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

<script src="<?php echo $assets_path; ?>js/plugin/datatables/datatables.min.js"></script>

<script src="<?php echo $assets_path; ?>js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

<script src="<?php echo $assets_path; ?>js/plugin/sweetalert/sweetalert.min.js"></script>

<script src="<?php echo $assets_path; ?>js/kaiadmin.min.js"></script>

<?php
// Nơi để echo các script riêng của từng trang nếu cần
if (isset($page_scripts) && is_array($page_scripts)) {
    foreach ($page_scripts as $script_url) {
        echo '<script src="' . htmlspecialchars($script_url) . '"></script>';
    }
}
?>
<script>
    // Khởi tạo tooltip (nếu dùng Bootstrap 5)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
</body>
</html>
