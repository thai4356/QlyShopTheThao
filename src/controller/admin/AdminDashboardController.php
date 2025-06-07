<?php
require_once __DIR__ . '/../../../src/model/admin/AdminDashboard.php';

class AdminDashboardController {

    public function showDashboard() {
        $dashboardModel = new AdminDashboard();

        $assets_path_for_js = 'assets/';
        // Lấy các chỉ số KPI
        $stats = [
            'total_users' => $dashboardModel->getTotalUsers(),
            'total_revenue_month' => $dashboardModel->getTotalRevenueThisMonth(),
            'new_orders_month' => $dashboardModel->getNewOrdersThisMonth(),
            'pending_reviews' => $dashboardModel->getPendingReviewsCount()
        ];

        $startDate = date('Y-m-d 00:00:00', strtotime('-6 days'));
        $endDate = date('Y-m-d 23:59:59');
        $revenue_chart_data = $dashboardModel->getRevenueDataByRange($startDate, $endDate);
        $category_chart_data = $dashboardModel->getProductCountByCategory();
        $recent_orders = $dashboardModel->getRecentOrders();
        $recent_users = $dashboardModel->getRecentUsers();
        $low_stock_products = $dashboardModel->getLowStockProducts();

        // Chuẩn bị MỌI DỮ LIỆU để truyền cho view
        $view_data = [
            'page_name' => 'dashboard',
            'pageTitle' => 'Dashboard',
            'stats' => $stats,
            'revenue_chart_data' => $revenue_chart_data, // Phải có dòng này
            'category_chart_data' => $category_chart_data,
            'recent_orders' => $recent_orders,
            'recent_users' => $recent_users,
            'low_stock_products' => $low_stock_products,
            'page_scripts' => [$assets_path_for_js . 'js/admin-dashboard-charts.js'] // Phải có dòng này để nạp file JS
        ];

        return $view_data;
    }

    // HÀM MỚI: Xử lý yêu cầu AJAX để lấy dữ liệu biểu đồ theo khoảng thời gian
    public function ajaxGetChartData() {
        $range = $_POST['range'] ?? 'last7days';
        $groupBy = 'day';

        switch ($range) {
            case 'today':
                $startDate = date('Y-m-d 00:00:00');
                $endDate = date('Y-m-d 23:59:59');
                break;
            case 'yesterday':
                $startDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $endDate = date('Y-m-d 23:59:59', strtotime('-1 day'));
                break;
            case 'this_month':
                $startDate = date('Y-m-01 00:00:00');
                $endDate = date('Y-m-t 23:59:59'); // 't' lấy ngày cuối cùng của tháng
                break;
            case 'this_quarter':
                $current_quarter = ceil(date('n') / 3);
                $startDate = date('Y-m-d 00:00:00', mktime(0, 0, 0, ($current_quarter - 1) * 3 + 1, 1, date('Y')));
                $endDate = date('Y-m-t 23:59:59', mktime(0, 0, 0, $current_quarter * 3, 1, date('Y')));
                $groupBy = 'month';
                break;
            case 'this_year':
                $startDate = date('Y-01-01 00:00:00');
                $endDate = date('Y-12-31 23:59:59');
                $groupBy = 'month';
                break;
            case 'last7days':
            default:
                $startDate = date('Y-m-d 00:00:00', strtotime('-6 days'));
                $endDate = date('Y-m-d 23:59:59');
                break;
        }

        $dashboardModel = new AdminDashboard();
        $chartData = $dashboardModel->getRevenueDataByRange($startDate, $endDate, $groupBy);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'chart_data' => $chartData]);
        exit();
    }

}
?>