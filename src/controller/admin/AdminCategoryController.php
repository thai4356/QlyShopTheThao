<?php
// src/controller/admin/AdminCategoryController.php
require_once __DIR__ . '/../../../src/model/admin/AdminCategory.php';

class AdminCategoryController {

    public function listCategories() {
        // $adminCategoryModel = new AdminCategory(); // Không cần lấy categories ở đây nữa vì DataTables sẽ tự lấy
        // $categories = $adminCategoryModel->getAllCategories();

        $assets_path_for_js = 'assets/'; // Đường dẫn tương đối từ ViewAdmin/

        $view_data = [
            'pageTitle' => 'Quản Lý Danh Mục',
            'page_name' => 'categories',
            // 'categories' => $categories, // Không cần truyền categories nữa
            'page_scripts' => [ // Mảng chứa các file JS cần nạp cho trang này
                $assets_path_for_js . 'js/categories-datatable-init.js'
            ]
        ];
        return $view_data;
    }

    public function ajaxGetCategoriesForDataTable() {
        header('Content-Type: application/json');

        $adminCategoryModel = new AdminCategory();
        $allCategories = $adminCategoryModel->getAllCategories(); // Lấy tất cả danh mục

        // Các tham số từ DataTables
        $draw = $_POST['draw'] ?? 0;
        $start = $_POST['start'] ?? 0; // Vị trí bắt đầu
        $length = $_POST['length'] ?? 10; // Số lượng mục mỗi trang
        $searchValue = $_POST['search']['value'] ?? ''; // Giá trị tìm kiếm chung

        // Thông tin sắp xếp
        $orderColumnIndex = $_POST['order'][0]['column'] ?? 0; // Index của cột sắp xếp
        $orderColumnName = $_POST['columns'][$orderColumnIndex]['data'] ?? 'id'; // Tên cột dữ liệu để sắp xếp
        $orderDir = $_POST['order'][0]['dir'] ?? 'asc'; // Hướng sắp xếp (asc/desc)


        $recordsTotal = count($allCategories);
        $recordsFiltered = $recordsTotal; // Sẽ cập nhật sau khi lọc

        // Xử lý tìm kiếm (đơn giản, trên name và description)
        $filteredCategories = $allCategories;
        if (!empty($searchValue)) {
            $filteredCategories = array_filter($allCategories, function($category) use ($searchValue) {
                return stripos($category['name'], $searchValue) !== false ||
                    stripos($category['description'], $searchValue) !== false;
            });
            $recordsFiltered = count($filteredCategories);
        }

        // Xử lý sắp xếp (đơn giản)
        // Ánh xạ tên cột từ DataTables (gửi qua 'data') sang key của mảng category
        $sortableColumns = ['id' => 'id', 'name' => 'name', 'description' => 'description'];

        if (isset($sortableColumns[$orderColumnName])) {
            $columnKeyToSort = $sortableColumns[$orderColumnName];
            usort($filteredCategories, function($a, $b) use ($columnKeyToSort, $orderDir) {
                $valA = $a[$columnKeyToSort];
                $valB = $b[$columnKeyToSort];

                if (is_numeric($valA) && is_numeric($valB)) {
                    return ($orderDir === 'asc') ? $valA - $valB : $valB - $valA;
                } else {
                    return ($orderDir === 'asc') ? strcmp((string)$valA, (string)$valB) : strcmp((string)$valB, (string)$valA);
                }
            });
        }


        // Xử lý phân trang
        $paginatedCategories = array_slice($filteredCategories, $start, $length);

        $data = [];
        foreach ($paginatedCategories as $category) {
            $statusBadge = $category['is_active'] ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-danger">Không hoạt động</span>';

            // Tạo HTML cho các nút hành động
            // Quan trọng: Đảm bảo các data-* attributes được escape đúng cách
            $actionsHtml = '<div class="form-button-action">';
            $actionsHtml .= '<button type="button" class="btn btn-link btn-primary btn-lg edit-category-btn" ';
            $actionsHtml .= 'data-bs-toggle="modal" data-bs-target="#editCategoryModal" ';
            $actionsHtml .= 'data-id="' . htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') . '" ';
            $actionsHtml .= 'data-name="' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '" ';
            $actionsHtml .= 'data-description="' . htmlspecialchars($category['description'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
            $actionsHtml .= 'data-status="' . ($category['is_active'] ? '1' : '0') . '" ';
            $actionsHtml .= 'data-bs-original-title="Sửa Danh Mục" title="Sửa">';
            $actionsHtml .= '<i class="fa fa-edit"></i></button>';

            $actionsHtml .= '<button type="button" class="btn btn-link btn-danger delete-category-btn-table" ';
            $actionsHtml .= 'data-id="' . htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') . '" ';
            $actionsHtml .= 'data-name="' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '" ';
            $actionsHtml .= 'data-bs-toggle="tooltip" data-bs-original-title="Xóa Danh Mục" title="Xóa">';
            $actionsHtml .= '<i class="fa fa-trash"></i></button>';
            $actionsHtml .= '</div>';

            $data[] = [
                'DT_RowId' => 'category_' . $category['id'], // THÊM DÒNG NÀY
                'id' => $category['id'],
                'name' => htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'),
                'description' => nl2br(htmlspecialchars($category['description'] ?? '', ENT_QUOTES, 'UTF-8')),
                'status_html' => $statusBadge,
                'actions_html' => $actionsHtml
            ];
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ];

        echo json_encode($response);
        exit;
    }

    public function ajaxUpdateCategory() {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $name = $_POST['name'] ?? null;
            $description = $_POST['description'] ?? ''; // Cho phép mô tả rỗng
            $is_active_input = $_POST['is_active'] ?? '0'; // Mặc định là '0' nếu không gửi

            // Xác thực cơ bản
            if (empty($id) || !is_numeric($id)) {
                $response['message'] = 'ID danh mục không hợp lệ.';
                echo json_encode($response);
                exit;
            }
            if (empty($name)) {
                $response['message'] = 'Tên danh mục không được để trống.';
                echo json_encode($response);
                exit;
            }
            if (!in_array($is_active_input, ['0', '1'])) {
                $response['message'] = 'Trạng thái không hợp lệ.';
                echo json_encode($response);
                exit;
            }

            $isActiveBool = ($is_active_input === '1'); // Chuyển đổi thành boolean

            $adminCategoryModel = new AdminCategory();
            // Phương thức updateCategory trong Model giờ đã bao gồm cả việc cập nhật sản phẩm
            if ($adminCategoryModel->updateCategory((int)$id, $name, $description, $isActiveBool)) {
                $response['success'] = true;
                // Cập nhật thông báo để phản ánh rõ hơn
                $status_text = $isActiveBool ? "kích hoạt" : "ẩn";
                $response['message'] = "Danh mục đã được cập nhật. Các sản phẩm liên quan cũng đã được {$status_text}.";

                // Dữ liệu trả về cho client để cập nhật UI
                // Description trả về client nên được nl2br và htmlspecialchars
                $escaped_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                $escaped_description_with_br = nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8'));

                $response['updatedCategory'] = [
                    'id' => (int)$id,
                    'name' => $escaped_name,
                    'description' => $escaped_description_with_br,
                    'is_active' => $isActiveBool
                ];
            } else {
                $response['message'] = 'Lỗi: Không thể cập nhật danh mục vào cơ sở dữ liệu.';
            }
        } else {
            $response['message'] = 'Phương thức yêu cầu không hợp lệ.';
        }

        echo json_encode($response);
        exit;
    }

    public function ajaxSoftDeleteCategory() {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoryId = $_POST['id'] ?? null;

            if (empty($categoryId) || !is_numeric($categoryId)) {
                $response['message'] = 'ID danh mục không hợp lệ.';
                echo json_encode($response);
                exit;
            }

            $adminCategoryModel = new AdminCategory();
            if ($adminCategoryModel->softDeleteCategoryAndRelatedProducts((int)$categoryId)) {
                $response['success'] = true;
                $response['message'] = 'Danh mục và các sản phẩm liên quan đã được ẩn thành công.';
                // Trả về thông tin để client có thể cập nhật UI nếu cần (ví dụ, ID và trạng thái mới là inactive)
                $response['deletedCategory'] = [
                    'id' => (int)$categoryId,
                    'is_active' => false // Trạng thái mới sau khi xóa mềm
                ];
            } else {
                $response['message'] = 'Lỗi: Không thể ẩn danh mục và các sản phẩm liên quan.';
            }
        } else {
            $response['message'] = 'Phương thức yêu cầu không hợp lệ.';
        }
        echo json_encode($response);
        exit;
    }

    public function ajaxAddCategory() {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? null;
            $description = $_POST['description'] ?? '';
            $is_active_input = $_POST['is_active'] ?? '1'; // Mặc định là hoạt động nếu không gửi

            // Xác thực cơ bản
            if (empty($name)) {
                $response['message'] = 'Tên danh mục không được để trống.';
                echo json_encode($response);
                exit;
            }
            if (!in_array($is_active_input, ['0', '1'])) {
                $response['message'] = 'Trạng thái không hợp lệ.';
                echo json_encode($response);
                exit;
            }

            $isActiveBool = ($is_active_input === '1');

            $adminCategoryModel = new AdminCategory();
            $newCategoryId = $adminCategoryModel->addCategory($name, $description, $isActiveBool);

            if ($newCategoryId !== false) {
                $response['success'] = true;
                $response['message'] = 'Danh mục đã được thêm thành công.';
                // Lấy thông tin danh mục vừa thêm để trả về (tùy chọn nhưng hữu ích)
                $newCategoryData = $adminCategoryModel->getCategoryById($newCategoryId);
                if ($newCategoryData) {
                    // Đảm bảo description được xử lý nl2br và escape cho hiển thị client
                    $newCategoryData['name'] = htmlspecialchars($newCategoryData['name'], ENT_QUOTES, 'UTF-8');
                    $newCategoryData['description'] = nl2br(htmlspecialchars($newCategoryData['description'] ?? '', ENT_QUOTES, 'UTF-8'));
                }
                $response['newCategory'] = $newCategoryData;

            } else {
                $response['message'] = 'Lỗi: Không thể thêm danh mục vào cơ sở dữ liệu.';
            }
        } else {
            $response['message'] = 'Phương thức yêu cầu không hợp lệ.';
        }

        echo json_encode($response);
        exit;
    }

}
?>