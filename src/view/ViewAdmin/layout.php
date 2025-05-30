<?php
// view/ViewAdmin/layout.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cho mục đích front-end, tạm thời bỏ qua kiểm tra đăng nhập nghiêm ngặt
// $isAdminLoggedIn = (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 1);
// if (!$isAdminLoggedIn) {
//     echo "Bạn cần đăng nhập với tư cách quản trị viên. <a href='../../index.php?ctrl=auth&act=loginForm'>Đăng nhập</a>";
//     exit;
// }

$page_param = $_GET['page'] ?? 'dashboard'; // Lấy tên trang từ URL, mặc định là dashboard
$pageTitle = ucfirst(str_replace('_', ' ', $page_param)); // Tạo tiêu đề trang cơ bản

// Nạp header
require_once __DIR__ . '/partials/header.php';

// Nạp sidebar
require_once __DIR__ . '/partials/sidebar.php';

// Nạp navbar (đã bao gồm thẻ mở <div class="main-panel">)
require_once __DIR__ . '/partials/navbar.php';
?>

<div class="container">
    <div class="page-inner">
        <?php
        // Nạp file nội dung tương ứng với biến $page_param
        $contentFile = __DIR__ . '/' . $page_param . '.php';

        if (file_exists($contentFile)) {
            include $contentFile;
        } else {
            // Nếu file không tồn tại, hiển thị trang dashboard mặc định hoặc thông báo lỗi
            $pageTitle = "Lỗi 404"; // Cập nhật lại pageTitle cho header
            include __DIR__ . '/partials/header.php'; // Load lại header với title mới
            // Nạp lại sidebar và navbar vì header đã đóng thẻ body và wrapper
            include __DIR__ . '/partials/sidebar.php';
            include __DIR__ . '/partials/navbar.php';
            echo '<div class="container"><div class="page-inner">'; // Mở lại container và page-inner
            echo "<div class='page-header'><h4 class='page-title'>Trang không tìm thấy</h4></div>";
            echo "<div class='page-category'>Rất tiếc, nội dung bạn yêu cầu không tồn tại (<code>" . htmlspecialchars($page_param) . ".php</code>). <a href='index.php?page=dashboard'>Quay về Dashboard</a>.</div>";
            echo '</div></div>'; // Đóng page-inner và container
        }
        ?>
    </div>
</div>

<?php
// Nạp footer và scripts
require_once __DIR__ . '/partials/footer_scripts.php';
?>
