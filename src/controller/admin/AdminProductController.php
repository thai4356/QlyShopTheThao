<?php
// src/controller/admin/AdminProductController.php
require_once __DIR__ . '/../../model/admin/AdminProduct.php'; // Đường dẫn tới model Product trong admin
require_once __DIR__ . '/../../model/admin/AdminCategory.php';

class AdminProductController {
    private $uploadDir;

    public function __construct() {
        // Xác định đường dẫn tuyệt đối đến thư mục upload
        $targetPath = __DIR__ . '/../../view/ViewUser/ProductImage/';
        $this->uploadDir = realpath($targetPath);

        // Kiểm tra sau khi dùng realpath
        if ($this->uploadDir === false || !is_dir($this->uploadDir)) {
            // Nếu realpath thất bại hoặc không phải là thư mục, thử tạo (nếu chưa có)
            // Hoặc kiểm tra đường dẫn không dùng realpath trước nếu thư mục có thể chưa tồn tại
            // Để đơn giản, giả sử thư mục đã tồn tại và realpath hoạt động
            error_log("Upload directory could not be resolved or does not exist: " . $targetPath . " (Resolved: " . ($this->uploadDir ?: 'false') . ")");
            // Xử lý lỗi nghiêm trọng ở đây, ví dụ: throw new Exception("Thư mục upload không hợp lệ.");
            // Tạm thời, để an toàn, bạn có thể đặt một đường dẫn mặc định hoặc dừng lại
            // For now, let's ensure it ends with a slash if valid
            if ($this->uploadDir) {
                $this->uploadDir .= '/';
            } else {
                // Fallback or error handling if realpath fails
                // This indicates a potential issue with the path itself or directory existence
                // For now, we'll reconstruct without realpath to see if it helps,
                // but realpath is safer to get canonical path.
                $this->uploadDir = $targetPath; // Thử dùng đường dẫn chưa qua realpath
                if (substr($this->uploadDir, -1) !== '/') {
                    $this->uploadDir .= '/';
                }
                error_log("Fallback upload directory used: " . $this->uploadDir);
            }

        } else {
            $this->uploadDir .= '/'; // Đảm bảo có dấu / ở cuối
        }

        // Kiểm tra quyền ghi một lần nữa sau khi đã có đường dẫn cuối cùng
        if (!is_writable($this->uploadDir)) {
            error_log("Upload directory is not writable: " . $this->uploadDir);
            // Xử lý lỗi
        }
         error_log("Final upload directory set to: " . $this->uploadDir); // Để debug
    }

    public function listProducts() {
        $productModel = new AdminProduct();
        $categoryModel = new AdminCategory(); // <-- KHỞI TẠO CATEGORY MODEL

        $limit = 20;
        $offset = 0;
        $filters = [];

        $productsData = $productModel->getFiltered($limit, $offset, $filters);
        $allCategories = $categoryModel->getAllActiveCategories(); // <-- LẤY DANH MỤC

        $viewData = [
            'products' => $productsData,
            'all_categories' => $allCategories, // <-- TRUYỀN DANH MỤC CHO VIEW
            'pageTitle' => 'Quản lý Sản phẩm',
            'product_image_base_url' => '../../view/ViewUser/ProductImage/',
            'page_name' => 'products'
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
}
?>

