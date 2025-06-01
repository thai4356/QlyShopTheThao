<?php
// src/controller/admin/AdminProductController.php
require_once __DIR__ . '/../../model/admin/AdminProduct.php'; // Đường dẫn tới model Product trong admin
require_once __DIR__ . '/../../model/admin/AdminCategory.php';

class AdminProductController {
    private $uploadDir;

    public function __construct() {
        $targetPath = __DIR__ . '/../../view/ViewUser/ProductImage/'; // Đường dẫn tương đối từ thư mục controller/admin
        $this->uploadDir = realpath($targetPath);
        if ($this->uploadDir) {
            $this->uploadDir .= DIRECTORY_SEPARATOR;
        } else {
            error_log("Lỗi: Thư mục upload ProductImage không hợp lệ hoặc không tồn tại tại: " . $targetPath);
        }
    }

    public function listProducts() {
        $productModel = new AdminProduct();
        $categoryModel = new AdminCategory();

        // --- XỬ LÝ PHÂN TRANG ---
        $items_per_page = 10; // Số sản phẩm mỗi trang (bạn có thể đặt thành 20 như trước)
        $current_page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($current_page < 1) {
            $current_page = 1;
        }
        $offset = ($current_page - 1) * $items_per_page;

        // --- XỬ LÝ SẮP XẾP ---
        $sort_col_input = $_GET['sort_col'] ?? 'created_at';
        $sort_order_input = strtoupper($_GET['sort_order'] ?? 'DESC');
        if (!in_array($sort_order_input, ['ASC', 'DESC'])) {
            $sort_order_input = 'DESC';
        }
        $allowed_sort_cols = [
            'name' => 'p.name', 'price' => 'p.price', 'discount_price' => 'p.discount_price',
            'stock' => 'p.stock', 'sold_quantity' => 'p.sold_quantity',
            'category_name' => 'c.name', 'created_at' => 'p.created_at'
        ];
        $db_sort_column = $allowed_sort_cols['created_at'];
        $client_sort_column = 'created_at';
        if (array_key_exists($sort_col_input, $allowed_sort_cols)) {
            $db_sort_column = $allowed_sort_cols[$sort_col_input];
            $client_sort_column = $sort_col_input;
        }

        // --- XỬ LÝ FILTERS ---
        $filters_for_model = [
            'sort_column' => $db_sort_column,
            'sort_order' => $sort_order_input
        ];
        $search_term = $_GET['search'] ?? null; // Lấy từ khóa tìm kiếm từ URL
        if ($search_term) {
            $filters_for_model['search_value'] = $search_term; // Key 'search_value' như đã dùng trong countFilteredForDataTable
        }
        // Ví dụ: Thêm filter theo category_id nếu có
        // if (!empty($_GET['filter_category_id'])) {
        //    $filters_for_model['category_id'] = (int)$_GET['filter_category_id'];
        // }


        // Lấy tổng số sản phẩm (sau khi lọc) để tính tổng số trang
        // Sử dụng countFilteredForDataTable nếu có filter, nếu không thì countAllActive
        if (!empty($filters_for_model['search_value']) /* || !empty($filters_for_model['category_id']) */) {
            // Giả sử countFilteredForDataTable đã được tạo trong Model AdminProduct.php
            // và nó chỉ nhận filter 'search_value' như đã định nghĩa cho DataTables.
            // Nếu bạn có nhiều filter phức tạp hơn, countFilteredForDataTable cần xử lý chúng.
            $total_products = $productModel->countFilteredForDataTable(['search_value' => $filters_for_model['search_value'] ?? null]);
        } else {
            $total_products = $productModel->countAllActive(); // Phương thức này cần tồn tại trong Model
        }

        $total_pages = ceil($total_products / $items_per_page);
        if ($current_page > $total_pages && $total_pages > 0) { // Nếu trang hiện tại vượt quá tổng số trang
            $current_page = $total_pages;
            $offset = ($current_page - 1) * $items_per_page;
        }


        $productsData = $productModel->getFiltered($items_per_page, $offset, $filters_for_model);
        $allCategories = $categoryModel->getAllActiveCategories();

        $viewData = [
            'products' => $productsData,
            'all_categories' => $allCategories,
            'pageTitle' => 'Quản lý Sản phẩm',
            'product_image_base_url' => '../../view/ViewUser/ProductImage/',
            'page_name' => 'products',
            'current_sort_column' => $client_sort_column,
            'current_sort_order' => $sort_order_input,
            // Biến cho phân trang
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'items_per_page' => $items_per_page
        ];

        return $viewData;
    }

    /**
     * Lấy chi tiết sản phẩm (bao gồm tất cả ảnh) cho việc sửa, trả về JSON.
     */
    public function ajaxGetProductDetailsForEdit() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $productId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            exit;
        }

        $productModel = new AdminProduct();
        $productDetails = $productModel->getById($productId);

        if ($productDetails) {
            // Thêm đường dẫn base cho image_url để tiện cho frontend
            // $base_image_url = '../../view/ViewUser/ProductImage/'; // Cần tính toán lại đường dẫn này cho đúng
            // Bạn đã có $this->uploadDir trong controller, nhưng đó là path hệ thống.
            // Cho client, bạn cần URL. Giả sử bạn có một hằng số hoặc cấu hình cho base URL của ảnh.
            // Hoặc client sẽ tự nối. Hiện tại $product_image_base_url đã được truyền cho listProducts view.
            // Chúng ta sẽ để client tự nối với product_image_base_url đã có.
            echo json_encode(['success' => true, 'data' => $productDetails]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
        }
        exit;
    }

    /**
     * Xử lý yêu cầu AJAX để cập nhật sản phẩm.
     */
    public function ajaxUpdateProduct() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $productId = $_POST['id'] ?? null;
        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID sản phẩm.']);
            exit;
        }

        // Dữ liệu text fields
        $productDataFields = [
            'name' => $_POST['name'] ?? null,
            'price' => $_POST['price'] ?? null,
            'discount_price' => $_POST['discount_price'] ?? null,
            'stock' => $_POST['stock'] ?? null,
            'category_id' => $_POST['category_id'] ?? null,
            'brand' => $_POST['brand'] ?? null,
            'location' => $_POST['location'] ?? null,
            'description' => $_POST['description'] ?? null,
        ];

        // --- Validate dữ liệu cơ bản ---
        if (empty($productDataFields['name'])) {
            echo json_encode(['success' => false, 'message' => 'Tên sản phẩm không được để trống.']);
            exit;
        }
        if (empty($productDataFields['price']) || !is_numeric($productDataFields['price'])) {
            echo json_encode(['success' => false, 'message' => 'Giá sản phẩm không hợp lệ.']);
            exit;
        }
        if (empty($productDataFields['stock']) || !is_numeric($productDataFields['stock'])) {
            echo json_encode(['success' => false, 'message' => 'Số lượng tồn không hợp lệ.']);
            exit;
        }
        if (empty($productDataFields['category_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn danh mục.']);
            exit;
        }
        if (isset($productDataFields['discount_price']) && $productDataFields['discount_price'] !== '' && !is_numeric($productDataFields['discount_price'])) {
            echo json_encode(['success' => false, 'message' => 'Giá khuyến mãi không hợp lệ.']);
            exit;
        }
        if ($productDataFields['discount_price'] === '') { // Chuyển chuỗi rỗng thành null
            $productDataFields['discount_price'] = null;
        }
        if ($productDataFields['discount_price'] !== null && $productDataFields['discount_price'] > $productDataFields['price']) {
            echo json_encode(['success' => false, 'message' => 'Giá khuyến mãi không được lớn hơn giá gốc.']);
            exit;
        }

        $productModel = new AdminProduct();
        $productModel->conn->beginTransaction(); // Bắt đầu Transaction

        // 1. Xử lý các ảnh hiện có bị xóa
        $deleteImageDbIds = $_POST['delete_other_image_ids'] ?? [];
        if (isset($_POST['existing_thumbnail_id_to_delete']) && $_POST['thumbnail_action'] === 'delete_existing') {
            $deleteImageDbIds[] = $_POST['existing_thumbnail_id_to_delete'];
        }

        foreach ($deleteImageDbIds as $imgDbId) {
            if (!empty($imgDbId)) {
                $filenameToDelete = $productModel->getImageFilenameById((int)$imgDbId);
                if ($filenameToDelete) {
                    if ($productModel->deleteProductImageRecord((int)$imgDbId)) {
                        if (file_exists($this->uploadDir . $filenameToDelete)) {
                            unlink($this->uploadDir . $filenameToDelete);
                        }
                    } else {
                        $productModel->conn->rollBack();
                        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa bản ghi ảnh cũ ID: ' . $imgDbId]);
                        exit;
                    }
                }
            }
        }

        $newlySavedThumbnailDbId = null; // Sẽ lưu ID của ảnh thumbnail mới nếu có

        // 2. Xử lý ảnh đại diện (Thumbnail)
        $thumbnailAction = $_POST['thumbnail_action'] ?? 'keep';
        if ($thumbnailAction === 'replace_new_cropped' && !empty($_POST['cropped_thumbnail_data'])) {
            // Xóa thumbnail cũ (nếu có) trước khi thêm cái mới
            $oldThumbnailId = $_POST['existing_thumbnail_id'] ?? null;
            if ($oldThumbnailId) {
                $oldThumbFilename = $productModel->getImageFilenameById((int)$oldThumbnailId);
                if ($oldThumbFilename) {
                    $productModel->deleteProductImageRecord((int)$oldThumbnailId);
                    if (file_exists($this->uploadDir . $oldThumbFilename)) unlink($this->uploadDir . $oldThumbFilename);
                }
            }
            // Lưu thumbnail mới
            $newThumbnailFilename = $this->processAndSaveImage($_POST['cropped_thumbnail_data'], 'thumb_edit');
            if ($newThumbnailFilename) {
                $newlySavedThumbnailDbId = $productModel->addSingleProductImage((int)$productId, $newThumbnailFilename, true); // true for is_thumbnail
                if (!$newlySavedThumbnailDbId) {
                    $productModel->conn->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu ảnh đại diện mới vào CSDL.']);
                    exit;
                }
            } else {
                $productModel->conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Lỗi xử lý file ảnh đại diện mới.']);
                exit;
            }
        } elseif ($thumbnailAction === 'delete_existing') {
            // Đã xử lý ở bước 1 (nếu existing_thumbnail_id_to_delete được gửi)
            // Nếu không, đảm bảo không có thumbnail nào được set
            $productModel->ensureSingleThumbnail((int)$productId, null);
        }

        // 3. Xử lý các ảnh khác hiện có được crop lại (updated_other_images_cropped_data)
        // Key là image_db_id, value là base64 data
        if (!empty($_POST['updated_other_images_cropped_data']) && is_array($_POST['updated_other_images_cropped_data'])) {
            foreach ($_POST['updated_other_images_cropped_data'] as $imgDbId => $base64Data) {
                $oldFilename = $productModel->getImageFilenameById((int)$imgDbId);
                $newFilename = $this->processAndSaveImage($base64Data, 'other_upd');
                if ($newFilename) {
                    // Cần phương thức update image_url trong model
                    if($productModel->updateProductImageFilename((int)$imgDbId, $newFilename)) { // Giả sử có phương thức này
                        if ($oldFilename && $oldFilename !== $newFilename && file_exists($this->uploadDir . $oldFilename)) {
                            unlink($this->uploadDir . $oldFilename);
                        }
                    } else { /* Lỗi update CSDL */ }
                } else { /* Lỗi xử lý ảnh */ }
            }
        }

        // 4. Xử lý các ảnh khác MỚI THÊM
        $newOtherImageFilenames = [];
        // Từ base64 đã crop ở client
        if (!empty($_POST['new_other_images_cropped_data']) && is_array($_POST['new_other_images_cropped_data'])) {
            foreach ($_POST['new_other_images_cropped_data'] as $base64Image) {
                $filename = $this->processAndSaveImage($base64Image, 'new_other_cr');
                if ($filename) $newOtherImageFilenames[] = $filename;
            }
        }
        // Từ file gốc (nếu client không crop)
        if (!empty($_FILES['new_other_images_original'])) {
            $originalFiles = $_FILES['new_other_images_original'];
            if (is_array($originalFiles['name'])) {
                for ($i = 0; $i < count($originalFiles['name']); $i++) {
                    if ($originalFiles['error'][$i] === UPLOAD_ERR_OK) {
                        $filename = $this->processAndSaveImage($originalFiles['tmp_name'][$i], 'new_other_orig', true);
                        if ($filename) $newOtherImageFilenames[] = $filename;
                    }
                }
            } elseif ($originalFiles['error'] === UPLOAD_ERR_OK) {
                $filename = $this->processAndSaveImage($originalFiles['tmp_name'], 'new_other_orig', true);
                if ($filename) $newOtherImageFilenames[] = $filename;
            }
        }

        // Thêm các ảnh mới này vào CSDL
        if (!empty($newOtherImageFilenames)) {
            foreach ($newOtherImageFilenames as $filename) {
                if (!$productModel->addSingleProductImage((int)$productId, $filename, false)) { // false for is_thumbnail
                    $productModel->conn->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm một trong các ảnh mới vào CSDL.']);
                    exit;
                }
            }
        }

        // 5. Đảm bảo chỉ có một thumbnail (nếu thumbnail mới được tạo ở bước 2)
        if ($thumbnailAction === 'replace_new_cropped' && $newlySavedThumbnailDbId) {
            if (!$productModel->ensureSingleThumbnail((int)$productId, (int)$newlySavedThumbnailDbId)) {
                $productModel->conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái thumbnail.']);
                exit;
            }
        }


        // 6. Cập nhật thông tin text của sản phẩm
        if ($productModel->updateProductDetails((int)$productId, $productDataFields)) {
            $productModel->conn->commit();
            echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!']);
        } else {
            $productModel->conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật thông tin sản phẩm.']);
        }
        exit;
    }

    /**
     * Xử lý yêu cầu AJAX để xóa mềm sản phẩm.
     */
    public function ajaxSoftDeleteProduct() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $productId = $_POST['id'] ?? null;

        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID sản phẩm để xóa.']);
            exit;
        }

        $productModel = new AdminProduct();
        $result = $productModel->softDeleteProduct((int)$productId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được ẩn (xóa mềm) thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi ẩn sản phẩm. Vui lòng thử lại.']);
        }
        exit;
    }

    /**
     * Xử lý yêu cầu AJAX để thêm sản phẩm mới.
     */
    public function ajaxAddProduct() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        error_log("ajaxAddProduct - Dữ liệu POST nhận được: " . print_r($_POST, true));

        // Dữ liệu từ form (không bao gồm file)
        $productDataFields = [
            'name' => $_POST['name'] ?? null,
            'price' => $_POST['price'] ?? null,
            'discount_price' => $_POST['discount_price'] ?? null,
            'stock' => $_POST['stock'] ?? null,
            'category_id' => $_POST['category_id'] ?? null,
            'brand' => $_POST['brand'] ?? null,
            'location' => $_POST['location'] ?? null,
            'description' => $_POST['description'] ?? null,
        ];

        error_log("ajaxAddProduct - Tên sản phẩm đã xử lý: " . ($productDataFields['name'] ?? 'NULL'));

        // --- Validate dữ liệu cơ bản ---
        if (empty($productDataFields['name'])) {
            echo json_encode(['success' => false, 'message' => 'Tên sản phẩm không được để trống.']);
            exit;
        }
        if (empty($productDataFields['price']) || !is_numeric($productDataFields['price'])) {
            echo json_encode(['success' => false, 'message' => 'Giá sản phẩm không hợp lệ.']);
            exit;
        }
        if (empty($productDataFields['stock']) || !is_numeric($productDataFields['stock'])) {
            echo json_encode(['success' => false, 'message' => 'Số lượng tồn không hợp lệ.']);
            exit;
        }
        if (empty($productDataFields['category_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn danh mục.']);
            exit;
        }
        if (isset($productDataFields['discount_price']) && $productDataFields['discount_price'] !== '' && !is_numeric($productDataFields['discount_price'])) {
            echo json_encode(['success' => false, 'message' => 'Giá khuyến mãi không hợp lệ.']);
            exit;
        }
        if ($productDataFields['discount_price'] === '') { // Chuyển chuỗi rỗng thành null
            $productDataFields['discount_price'] = null;
        }
        if ($productDataFields['discount_price'] !== null && $productDataFields['discount_price'] > $productDataFields['price']) {
            echo json_encode(['success' => false, 'message' => 'Giá khuyến mãi không được lớn hơn giá gốc.']);
            exit;
        }


        $savedThumbnailName = null;
        $savedOtherImageNames = [];

        // --- Xử lý ảnh đại diện đã crop (base64) ---
        if (!empty($_POST['cropped_thumbnail_data'])) {
            $savedThumbnailName = $this->processAndSaveImage($_POST['cropped_thumbnail_data'], 'thumb');
            if (!$savedThumbnailName) {
                echo json_encode(['success' => false, 'message' => 'Lỗi xử lý ảnh đại diện.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Ảnh đại diện là bắt buộc.']);
            exit;
        }

        // --- Xử lý các ảnh khác đã crop (mảng base64) ---
        if (!empty($_POST['product_other_images_cropped_data']) && is_array($_POST['product_other_images_cropped_data'])) {
            foreach ($_POST['product_other_images_cropped_data'] as $base64Image) {
                $filename = $this->processAndSaveImage($base64Image, 'other');
                if ($filename) {
                    $savedOtherImageNames[] = $filename;
                }
            }
        }

        // --- Xử lý các ảnh khác gốc (từ $_FILES) ---
        if (!empty($_FILES['product_images_original'])) {
            $originalFiles = $_FILES['product_images_original'];
            // Sắp xếp lại mảng $_FILES nếu có nhiều file
            if (is_array($originalFiles['name'])) {
                for ($i = 0; $i < count($originalFiles['name']); $i++) {
                    if ($originalFiles['error'][$i] === UPLOAD_ERR_OK) {
                        $tempFilePath = $originalFiles['tmp_name'][$i];
                        // Không cần đọc file thành base64, processAndSaveImage sẽ nhận trực tiếp file path
                        $filename = $this->processAndSaveImage($tempFilePath, 'other_file', true);
                        if ($filename) {
                            $savedOtherImageNames[] = $filename;
                        }
                    }
                }
            } elseif ($originalFiles['error'] === UPLOAD_ERR_OK) { // Trường hợp chỉ có 1 file
                $tempFilePath = $originalFiles['tmp_name'];
                $filename = $this->processAndSaveImage($tempFilePath, 'other_file', true);
                if ($filename) {
                    $savedOtherImageNames[] = $filename;
                }
            }
        }

        $productModel = new AdminProduct();
        // Bắt đầu transaction
        $productModel->conn->beginTransaction(); // Sử dụng conn của model

        $newProductId = $productModel->addProduct($productDataFields);

        if ($newProductId) {
            $imagesAdded = $productModel->addProductImages($newProductId, $savedThumbnailName, array_unique($savedOtherImageNames)); // array_unique để tránh trùng lặp nếu có
            if ($imagesAdded) {
                $productModel->conn->commit(); // Hoàn tất transaction
                echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công!', 'new_product_id' => $newProductId]);
            } else {
                $productModel->conn->rollBack(); // Hoàn tác nếu thêm ảnh lỗi
                echo json_encode(['success' => false, 'message' => 'Thêm sản phẩm thành công nhưng có lỗi khi thêm ảnh.']);
            }
        } else {
            $productModel->conn->rollBack(); // Hoàn tác nếu thêm sản phẩm lỗi
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm thông tin sản phẩm.']);
        }
        exit;
    }

    /**
     * Xử lý và lưu ảnh (từ base64 hoặc file path), sử dụng Imagick.
     * @param string $source Dữ liệu base64 hoặc đường dẫn file tạm.
     * @param string $prefix Tiền tố cho tên file.
     * @param bool $isFilePath True nếu $source là đường dẫn file, False nếu là base64.
     * @return string|false Tên file đã lưu hoặc false nếu lỗi.
     */
    private function processAndSaveImage($source, $prefix = 'img', $isFilePath = false) {
        try {
            $imagick = new Imagick();
            if ($isFilePath) {
                if (!file_exists($source) || !is_readable($source)) {
                    error_log("Source file does not exist or is not readable: " . $source);
                    return false;
                }
                $imagick->readImage($source);
            } else { // Base64
                // Loại bỏ phần header của base64 data URL
                if (preg_match('/^data:image\/(\w+);base64,/', $source, $type)) {
                    $source = substr($source, strpos($source, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif

                    if (!in_array($type, ['jpeg', 'jpg', 'png', 'gif'])) {
                        error_log("Unsupported image type from base64: " . $type);
                        return false;
                    }
                    $imageData = base64_decode($source);
                    if ($imageData === false) {
                        error_log("Base64 decoding failed.");
                        return false;
                    }
                    $imagick->readImageBlob($imageData);
                } else {
                    error_log("Invalid base64 string format.");
                    return false;
                }
            }

            // Xử lý ảnh: resize để vừa 800x800, giữ tỷ lệ, chuyển sang WebP
            $imagick->setimageformat('webp');
            $imagick->setImageCompressionQuality(85); // Chất lượng WebP

            // Resize nếu kích thước lớn hơn 800x800
            $geo = $imagick->getImageGeometry();
            if ($geo['width'] > 800 || $geo['height'] > 800) {
                $imagick->adaptiveResizeImage(800, 800, true); // true = bestfit (giữ tỷ lệ)
            }

            $filename = uniqid($prefix . '_') . '_' . time() . '.webp';
            $filePath = $this->uploadDir . $filename;

            if (!is_writable(dirname($filePath))) {
                error_log("Upload directory is not writable: " . dirname($filePath));
                return false;
            }

            $imagick->writeImage($filePath);
            $imagick->clear();
            $imagick->destroy();

            return $filename;

        } catch (ImagickException $e) {
            error_log("ImagickException in processAndSaveImage: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("General Exception in processAndSaveImage: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xử lý yêu cầu AJAX từ DataTables để lấy danh sách sản phẩm.
     */
    public function ajaxGetProductsForDataTable() {
        header('Content-Type: application/json');
        $productModel = new AdminProduct();

        $requestData = $_REQUEST; // DataTables gửi tham số qua GET hoặc POST

        // Các tham số từ DataTables
        $draw = isset($requestData['draw']) ? intval($requestData['draw']) : 0;
        $start = isset($requestData['start']) ? intval($requestData['start']) : 0; // offset
        $length = isset($requestData['length']) ? intval($requestData['length']) : 10; // limit

        // Sắp xếp
        $orderColumnIndex = $requestData['order'][0]['column'] ?? 0; // Cột được sort
        $orderColumnDir = strtoupper($requestData['order'][0]['dir'] ?? 'DESC'); // Hướng sort (ASC/DESC)

        // Lấy tên cột từ DataTables column definition (quan trọng)
        // Client-side sẽ định nghĩa tên cho mỗi cột data
        $columns = $requestData['columns'] ?? [];
        $sortableColumnName = isset($columns[$orderColumnIndex]['data']) ? $columns[$orderColumnIndex]['data'] : null;

        // Ánh xạ tên cột từ DataTables (JS) sang tên cột CSDL
        $allowed_sort_cols_map = [
            'name' => 'p.name',
            'price_display' => 'p.price', // Giả sử cột giá trong JS tên là 'price_display'
            'stock' => 'p.stock',
            'sold_quantity' => 'p.sold_quantity',
            'category_name' => 'c.name',
            'created_at' => 'p.created_at' // Cột ẩn hoặc cột mặc định
        ];

        $db_sort_column = $allowed_sort_cols_map['created_at']; // Mặc định
        if ($sortableColumnName && array_key_exists($sortableColumnName, $allowed_sort_cols_map)) {
            $db_sort_column = $allowed_sort_cols_map[$sortableColumnName];
        }
        if (!in_array($orderColumnDir, ['ASC', 'DESC'])) {
            $orderColumnDir = 'DESC';
        }

        // Tìm kiếm
        $searchValue = $requestData['search']['value'] ?? null;

        // Build filters array
        $filters = [
            'sort_column' => $db_sort_column,
            'sort_order' => $orderColumnDir,
        ];
        if (!empty($searchValue)) {
            $filters['search_value'] = $searchValue; // Model sẽ dùng key này
        }
        // Thêm các filter cụ thể khác từ client nếu có

        // Lấy dữ liệu
        $products = $productModel->getFiltered($length, $start, $filters);
        $totalRecords = $productModel->countAllActive(); // Tổng số không lọc (chỉ active)
        $totalFilteredRecords = $productModel->countFilteredForDataTable($filters); // Tổng số sau khi lọc

        $dataOutput = [];
        // Biến này cần được controller listProducts truyền cho view, sau đó view truyền cho JS
        // Hoặc bạn có thể hardcode ở đây nếu nó cố định tương đối với root của web
        $product_image_base_url = '../../view/ViewUser/ProductImage/';

        foreach ($products as $product) {
            $actions = '<div class="form-button-action">';
            $actions .= '<button type="button" data-bs-toggle="tooltip" title="Sửa" class="btn btn-link btn-primary btn-lg edit-product-button" data-product-id="' . $product['id'] . '"><i class="fa fa-edit"></i></button>';
            $actions .= '<button type="button" data-bs-toggle="tooltip" title="Xóa" class="btn btn-link btn-danger delete-product-button" data-product-id="' . $product['id'] . '" data-product-name="' . htmlspecialchars($product['name']) . '"><i class="fa fa-times"></i></button>';
            $actions .= '</div>';

            $priceDisplay = '';
            if (isset($product['discount_price']) && $product['discount_price'] > 0 && (float)$product['discount_price'] < (float)$product['price']) {
                $priceDisplay = '<span style="text-decoration: line-through; color: #999; font-size:0.9em;">' . number_format($product['price'], 0, ',', '.') . '₫</span><br><strong style="color: red;">' . number_format($product['discount_price'], 0, ',', '.') . '₫</strong>';
            } else {
                $priceDisplay = number_format($product['price'], 0, ',', '.') . '₫';
            }

            $imageUrl = !empty($product['image_url']) ? ($product_image_base_url . htmlspecialchars($product['image_url'])) : ($product_image_base_url . 'default-placeholder.png');
            $imageTag = '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($product['name']) . '" style="width: 50px; height: 50px; object-fit: cover;">';


            $dataOutput[] = [
                "image_display" => $imageTag, // Cột ảnh
                "name" => htmlspecialchars($product['name']),
                "price_display" => $priceDisplay, // Cột giá đã xử lý
                "stock" => htmlspecialchars($product['stock'] ?? 0),
                "sold_quantity" => htmlspecialchars($product['sold_quantity'] ?? 0),
                "category_name" => isset($product['category_name']) ? htmlspecialchars($product['category_name']) : 'N/A',
                "actions" => $actions // HTML cho cột hành động
            ];
        }

        $json_data = [
            "draw"            => $draw,
            "recordsTotal"    => $totalRecords,
            "recordsFiltered" => $totalFilteredRecords,
            "data"            => $dataOutput
        ];

        echo json_encode($json_data);
        exit;
    }

}
?>

