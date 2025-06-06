<?php
// Đảm bảo file model được nạp
require_once __DIR__ . '/../../../src/model/admin/AdminUser.php';

class AdminUserController {

    /**
     * Cung cấp dữ liệu cho DataTables dưới dạng JSON
     */
    public function ajaxGetUsersForDataTable() {
        // Lấy các tham số mà DataTables gửi lên
        $draw = $_POST['draw'] ?? 0;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = $_POST['search']['value'] ?? '';
        $orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
        $orderDir = $_POST['order'][0]['dir'] ?? 'asc';

        // Khởi tạo model
        $adminUserModel = new AdminUser();

        // Gọi phương thức từ model để lấy dữ liệu
        $result = $adminUserModel->getUsersForDataTable($start, $length, $searchValue, $orderColumnIndex, $orderDir);

        // Chuẩn bị mảng dữ liệu trả về cho DataTables
        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $result['recordsTotal'],
            "recordsFiltered" => $result['recordsFiltered'],
            "data" => $result['data']
        ];

        // Trả về dữ liệu dạng JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit(); // Dừng thực thi sau khi trả về JSON
    }

    /**
     * Lấy dữ liệu và hiển thị trang chi tiết người dùng
     */
    public function showUserDetails() {
        // Kiểm tra xem ID có được cung cấp và có phải là số không
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            // Có thể hiển thị lỗi hoặc điều hướng về trang danh sách
            header('Location: index.php?page=users&error=invalid_id');
            exit();
        }
        $userId = (int)$_GET['id'];

        // Khởi tạo model
        $adminUserModel = new AdminUser();

        // Lấy thông tin chi tiết người dùng
        $user = $adminUserModel->getUserById($userId);

        // Nếu không tìm thấy người dùng, điều hướng về trang danh sách
        if (!$user) {
            header('Location: index.php?page=users&error=user_not_found');
            exit();
        }

        // Lấy lịch sử đơn hàng của người dùng
        $orders = $adminUserModel->getOrdersByUserId($userId);

        // Chuẩn bị dữ liệu để truyền cho view
        $view_data = [
            'page_name' => 'user_details',
            'pageTitle' => 'Chi tiết người dùng - ' . $user['email'],
            'user' => $user,
            'orders' => $orders
        ];

        return $view_data;
    }

    /**
     * Xử lý yêu cầu AJAX để vô hiệu hóa hoặc kích hoạt người dùng.
     */
    public function ajaxToggleUserStatus() {
        // Mặc định phản hồi là lỗi
        $response = ['status' => 'error', 'message' => 'Có lỗi xảy ra.'];

        // Kiểm tra dữ liệu đầu vào
        if (isset($_POST['id']) && isset($_POST['status'])) {
            $userId = (int)$_POST['id'];
            $newStatus = (int)$_POST['status'];

            // **KIỂM TRA AN TOÀN QUAN TRỌNG: Không cho phép admin tự vô hiệu hóa chính mình**
            if ($userId === (int)$_SESSION['user_id']) {
                $response['message'] = 'Bạn không thể tự vô hiệu hóa tài khoản của mình.';
            } else {
                // Khởi tạo model và gọi phương thức cập nhật
                $adminUserModel = new AdminUser();
                if ($adminUserModel->toggleUserStatus($userId, $newStatus)) {
                    $response = ['status' => 'success', 'message' => 'Cập nhật trạng thái thành công.'];
                } else {
                    $response['message'] = 'Cập nhật trạng thái trong cơ sở dữ liệu thất bại.';
                }
            }
        } else {
            $response['message'] = 'Dữ liệu không hợp lệ.';
        }

        // Trả về kết quả dạng JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    /**
     * Xử lý yêu cầu AJAX để thêm người dùng mới.
     */
    public function ajaxAddUser() {
        $response = ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.'];

        // 1. Validation cơ bản
        if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['roleId'])) {
            $response['message'] = 'Vui lòng điền đầy đủ các trường bắt buộc.';
            echo json_encode($response);
            exit();
        }

        // 2. Lấy và làm sạch dữ liệu
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $roleId = (int)$_POST['roleId'];

        // 3. Validation nâng cao
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Định dạng email không hợp lệ.';
            echo json_encode($response);
            exit();
        }
        if (strlen($password) < 6) { // Ví dụ: yêu cầu mật khẩu tối thiểu 6 ký tự
            $response['message'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            echo json_encode($response);
            exit();
        }

        // 4. Kiểm tra email đã tồn tại chưa
        $adminUserModel = new AdminUser();
        if ($adminUserModel->isEmailExists($email)) {
            $response['message'] = 'Email này đã được sử dụng. Vui lòng chọn email khác.';
            echo json_encode($response);
            exit();
        }

        // 5. Mã hóa mật khẩu bằng bcrypt
        $options = ['cost' => 12];
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, $options);

        // 6. Gọi model để thêm vào CSDL
        if ($adminUserModel->addUser($email, $hashedPassword, $roleId)) {
            $response = ['status' => 'success', 'message' => 'Thêm người dùng mới thành công!'];
        } else {
            $response['message'] = 'Đã xảy ra lỗi khi thêm người dùng vào cơ sở dữ liệu.';
        }

        // Trả về kết quả
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

}
?>