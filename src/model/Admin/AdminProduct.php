<?php
require_once 'AdminConnect.php';

class AdminProduct {
    public $conn;
    private $table = "product";
    private $image_table = "product_image"; // Thêm định nghĩa bảng product_image

    public $id, $name, $description, $price, $stock, $image_url, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new AdminConnect())->getConnection();
    }

    /**
     * Lấy thông tin chi tiết sản phẩm bao gồm tất cả các ảnh.
     * @param int $id ID của sản phẩm.
     * @return array|null Dữ liệu sản phẩm hoặc null.
     */
    public function getById($id) {
        // Lấy thông tin cơ bản của sản phẩm
        $queryProduct = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmtProduct = $this->conn->prepare($queryProduct);
        $stmtProduct->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtProduct->execute();
        $productData = $stmtProduct->fetch(PDO::FETCH_ASSOC);

        if (!$productData) {
            return null;
        }

        // Lấy tất cả các ảnh liên quan đến sản phẩm
        $queryImages = "SELECT id as image_db_id, image_url, is_thumbnail 
                        FROM " . $this->image_table . " 
                        WHERE product_id = :product_id 
                        ORDER BY is_thumbnail DESC, id ASC"; // Ưu tiên thumbnail, rồi theo ID
        $stmtImages = $this->conn->prepare($queryImages);
        $stmtImages->bindParam(':product_id', $id, PDO::PARAM_INT);
        $stmtImages->execute();

        $productData['images_data'] = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

        return $productData;
    }

    public function countAll() {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM product");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function countFiltered($filters = []) {
        $sql = "SELECT COUNT(*) AS total FROM product WHERE 1=1";
        $params = [];

        // Lọc theo nơi bán
        if (!empty($filters['location'])) {
            $placeholders = implode(',', array_fill(0, count($filters['location']), '?'));
            $sql .= " AND location IN ($placeholders)";
            $params = array_merge($params, $filters['location']);
        }

        // Lọc theo thương hiệu
        if (!empty($filters['brand'])) {
            $placeholders = implode(',', array_fill(0, count($filters['brand']), '?'));
            $sql .= " AND brand IN ($placeholders)";
            $params = array_merge($params, $filters['brand']);
        }

        // Lọc theo danh mục
        if (!empty($filters['category_id'])) {
            $placeholders = implode(',', array_fill(0, count($filters['category_id']), '?'));
            $sql .= " AND category_id IN ($placeholders)";
            $params = array_merge($params, $filters['category_id']);
        }

        // Lọc theo tên sản phẩm
        if (!empty($filters['search'])) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        // Khoảng giá
        if (!empty($filters['price_min'])) {
            $sql .= " AND price >= ?";
            $params[] = (int)$filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $sql .= " AND price <= ?";
            $params[] = (int)$filters['price_max'];
        }

        // Chuẩn bị và thực thi câu lệnh
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }


    public function getFiltered($limit, $offset, $filters = []) {
        $sql = "
        SELECT DISTINCT 
            p.*, 
            pi.image_url,
            c.name AS category_name  -- Thêm dòng này để lấy tên danh mục
        FROM 
            $this->table p
        LEFT JOIN 
            product_image pi ON p.id = pi.product_id 
        LEFT JOIN -- Sử dụng LEFT JOIN để vẫn hiển thị sản phẩm nếu category_id không hợp lệ (tùy chọn)
            category c ON p.category_id = c.id -- Thêm JOIN với bảng category
        WHERE 
            pi.is_thumbnail = 1 AND p.is_active = 1
        "; //

        $whereClauses = []; // Mảng chứa các điều kiện WHERE
        $paramsForWhere = [];       // Mảng chứa các tham số cho prepared statement

        // --- Xử lý các FILTERS (tìm kiếm, giá, danh mục, v.v.) ---
        // Ví dụ: Lọc theo tên sản phẩm
        if (!empty($filters['search'])) {
            $whereClauses[] = "p.name LIKE ?";
            $paramsForWhere[] = '%' . $filters['search'] . '%';
        }
        // Ví dụ: Lọc theo danh mục (nếu bạn có filter này)
        if (!empty($filters['category_id'])) {
            $whereClauses[] = "p.category_id = ?";
            $paramsForWhere[] = (int)$filters['category_id'];
        }
        // Thêm các điều kiện lọc khác vào $whereClauses và $params nếu cần...


        // Nối các điều kiện WHERE vào câu SQL
        if (!empty($whereClauses)) {
            $sql .= " AND " . implode(" AND ", $whereClauses);
        }

        // --- Xử lý SẮP XẾP (ORDER BY) ---
        $orderByClause = " ORDER BY p.created_at DESC"; // Mặc định: sản phẩm mới nhất
        if (!empty($filters['sort_column']) && !empty($filters['sort_order'])) {
            // Controller đã validate $filters['sort_column'] và $filters['sort_order']
            $column = $filters['sort_column']; // Ví dụ: 'p.name', 'c.name', 'p.price'
            $order = $filters['sort_order'];   // 'ASC' hoặc 'DESC'

            // Xử lý đặc biệt cho sắp xếp giá (ưu tiên giá khuyến mãi nếu có)
            if ($column === 'p.price') {
                $orderByClause = " ORDER BY (CASE WHEN p.discount_price IS NOT NULL AND p.discount_price > 0 THEN p.discount_price ELSE p.price END) " . $order;
            } else {
                $orderByClause = " ORDER BY " . $column . " " . $order;
            }
        }
        $sql .= $orderByClause;

        // --- Xử lý PHÂN TRANG (LIMIT OFFSET) ---
        $sql .= " LIMIT ? OFFSET ?";
        try {
            $stmt = $this->conn->prepare($sql);

            // Bind các tham số cho phần WHERE
            $paramIndex = 1;
            foreach ($paramsForWhere as $value) {
                $stmt->bindValue($paramIndex++, $value, PDO::PARAM_STR); // Giả sử hầu hết là string, hoặc kiểm tra kiểu
            }

            // Bind các tham số cho LIMIT và OFFSET với PDO::PARAM_INT
            $stmt->bindValue($paramIndex++, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getFiltered: SQLSTATE[" . $e->getCode() . "] " . $e->getMessage() . " | SQL: " . $sql . " | Params for WHERE: " . print_r($paramsForWhere, true) . " | Limit: " . $limit . " | Offset: " . $offset);
            return [];
        }
    }

    /**
     * Cập nhật thông tin chi tiết của một sản phẩm.
     * @param int $productId ID của sản phẩm cần cập nhật.
     * @param array $data Mảng chứa dữ liệu cần cập nhật (key là tên cột, value là giá trị mới).
     * @return bool True nếu cập nhật thành công, False nếu thất bại.
     */
    public function updateProductDetails($productId, $data) {
        if (empty($data) || empty($productId)) {
            return false;
        }

        $setClauses = [];
        $params = [];

        // Xây dựng phần SET của câu lệnh SQL một cách động và an toàn
        // Các trường cho phép cập nhật (đảm bảo chúng khớp với tên cột trong CSDL và tên input trong form)
        $allowedFields = ['name', 'price', 'discount_price', 'stock', 'category_id', 'brand', 'location', 'description'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                // Nếu giá trị là chuỗi rỗng và trường đó cho phép NULL, có thể gán NULL
                // Ví dụ: discount_price có thể là NULL nếu người dùng xóa giá trị.
                if ($data[$field] === '' && ($field === 'discount_price' || $field === 'brand' || $field === 'location' || $field === 'description')) { // Các trường có thể NULL
                    $setClauses[] = "`" . $field . "` = NULL";
                } else {
                    $setClauses[] = "`" . $field . "` = :" . $field;
                    $params[':' . $field] = $data[$field];
                }
            }
        }

        if (empty($setClauses)) {
            return false; // Không có trường nào hợp lệ để cập nhật
        }

        $sql = "UPDATE " . $this->table . " SET " . implode(', ', $setClauses) . " WHERE id = :id";
        $params[':id'] = $productId;

        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            // Ghi lại lỗi hoặc xử lý tùy ý
            error_log("Error updating product: " . $e->getMessage());
            return false;
        }
    }



    /**
     * Thực hiện xóa mềm một sản phẩm bằng cách đặt is_active = 0.
     * @param int $productId ID của sản phẩm cần xóa mềm.
     * @return bool True nếu cập nhật thành công, False nếu thất bại.
     */
    public function softDeleteProduct($productId) {
        if (empty($productId)) {
            return false;
        }

        $sql = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Ghi lại lỗi hoặc xử lý tùy ý
            error_log("Error soft deleting product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thêm một sản phẩm mới vào cơ sở dữ liệu.
     * @param array $data Mảng chứa dữ liệu sản phẩm (name, description, price, stock, category_id, brand, location, etc.)
     * Không bao gồm is_active vì nó có giá trị mặc định là 1 trong CSDL.
     * @return string|false ID của sản phẩm vừa thêm nếu thành công, ngược lại là false.
     */
    public function addProduct($data) {
        if (empty($data['name']) || !isset($data['price']) || !isset($data['stock']) || empty($data['category_id'])) {
            error_log("Add product failed: Missing required fields.");
            return false;
        }

        // Các trường được phép và sẽ được thêm
        $fields = ['name', 'description', 'price', 'stock', 'brand', 'location', 'discount_price', 'category_id'];
        $sqlFields = [];
        $sqlPlaceholders = [];
        $params = [];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                // Xử lý giá trị rỗng cho các trường có thể NULL
                if ($data[$field] === '' && in_array($field, ['description', 'brand', 'location', 'discount_price'])) {
                    $sqlFields[] = "`" . $field . "`";
                    $sqlPlaceholders[] = ":" . $field;
                    $params[':' . $field] = null;
                } else {
                    $sqlFields[] = "`" . $field . "`";
                    $sqlPlaceholders[] = ":" . $field;
                    $params[':' . $field] = $data[$field];
                }
            }
        }
        // is_active mặc định là 1 trong CSDL, không cần thêm vào đây trừ khi muốn ghi đè.
        // created_at, updated_at cũng tự động.

        if (empty($sqlFields)) {
            error_log("Add product failed: No valid fields to insert.");
            return false;
        }

        $sql = "INSERT INTO " . $this->table . " (" . implode(', ', $sqlFields) . ") VALUES (" . implode(', ', $sqlPlaceholders) . ")";

        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute($params)) {
                return $this->conn->lastInsertId();
            }
            error_log("Add product execution failed: " . implode(", ", $stmt->errorInfo()));
            return false;
        } catch (PDOException $e) {
            error_log("PDOException in addProduct: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thêm các ảnh cho một sản phẩm.
     * @param int $productId ID của sản phẩm.
     * @param string $thumbnailFilename Tên file của ảnh đại diện.
     * @param array $otherImageFilenames Mảng các tên file của các ảnh khác.
     * @return bool True nếu tất cả ảnh được thêm thành công, False nếu có lỗi.
     */
    public function addProductImages($productId, $thumbnailFilename = null, $otherImageFilenames = []) {
        if (empty($productId)) return false;
        $allSuccess = true;

        try {
            // Thêm ảnh đại diện (nếu có)
            if (!empty($thumbnailFilename)) {
                $sqlThumb = "INSERT INTO " . $this->image_table . " (product_id, image_url, is_thumbnail) VALUES (:product_id, :image_url, 1)";
                $stmtThumb = $this->conn->prepare($sqlThumb);
                $stmtThumb->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmtThumb->bindParam(':image_url', $thumbnailFilename, PDO::PARAM_STR);
                if (!$stmtThumb->execute()) {
                    error_log("Failed to add thumbnail: " . $thumbnailFilename . " for product ID: " . $productId . " - " . implode(", ", $stmtThumb->errorInfo()));
                    $allSuccess = false;
                }
            }

            // Thêm các ảnh khác
            if (!empty($otherImageFilenames)) {
                $sqlOther = "INSERT INTO " . $this->image_table . " (product_id, image_url, is_thumbnail) VALUES (:product_id, :image_url, 0)";
                $stmtOther = $this->conn->prepare($sqlOther);
                $stmtOther->bindParam(':product_id', $productId, PDO::PARAM_INT);

                foreach ($otherImageFilenames as $filename) {
                    if (!empty($filename)) {
                        $stmtOther->bindParam(':image_url', $filename, PDO::PARAM_STR);
                        if (!$stmtOther->execute()) {
                            error_log("Failed to add other image: " . $filename . " for product ID: " . $productId . " - " . implode(", ", $stmtOther->errorInfo()));
                            $allSuccess = false;
                            // Có thể chọn dừng lại ở đây hoặc tiếp tục thêm các ảnh còn lại
                        }
                    }
                }
            }
            // Nếu không có thumbnail và cũng không có other images, nhưng sản phẩm đã được tạo, coi như thành công ở bước này.
            if (empty($thumbnailFilename) && empty($otherImageFilenames)) {
                return true;
            }


            return $allSuccess;
        } catch (PDOException $e) {
            error_log("PDOException in addProductImages: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy tên file ảnh từ ID của bản ghi trong product_image.
     * @param int $imageDbId ID của ảnh trong bảng product_image.
     * @return string|false Tên file ảnh hoặc false nếu không tìm thấy.
     */
    public function getImageFilenameById($imageDbId) {
        $sql = "SELECT image_url FROM " . $this->image_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $imageDbId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['image_url'] : false;
    }

    /**
     * Xóa một bản ghi ảnh khỏi bảng product_image.
     * @param int $imageDbId ID của ảnh trong bảng product_image.
     * @return bool True nếu xóa thành công, False nếu thất bại.
     */
    public function deleteProductImageRecord($imageDbId) {
        $sql = "DELETE FROM " . $this->image_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $imageDbId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Xóa TẤT CẢ ảnh của một sản phẩm (trừ ảnh được chỉ định giữ lại nếu cần).
     * Hữu ích khi thay thế toàn bộ ảnh hoặc xóa sản phẩm.
     * @param int $productId ID của sản phẩm.
     * @param array $keepImageIds Mảng các image_db_id cần giữ lại (ví dụ khi chỉ cập nhật 1 ảnh).
     * @return array Mảng các tên file đã bị xóa khỏi CSDL (để xóa file vật lý).
     */
    public function deleteAllProductImagesForProduct($productId, $keepImageIds = []) {
        $deletedFilenames = [];
        $placeholders = '';
        $params = [':product_id' => $productId];

        if (!empty($keepImageIds)) {
            // Tạo placeholders cho danh sách ID cần giữ lại
            $placeholders = implode(',', array_fill(0, count($keepImageIds), '?'));
            $sqlSelect = "SELECT image_url FROM " . $this->image_table . " WHERE product_id = :product_id AND id NOT IN (" . $placeholders . ")";
            $sqlDelete = "DELETE FROM " . $this->image_table . " WHERE product_id = :product_id AND id NOT IN (" . $placeholders . ")";
        } else {
            $sqlSelect = "SELECT image_url FROM " . $this->image_table . " WHERE product_id = :product_id";
            $sqlDelete = "DELETE FROM " . $this->image_table . " WHERE product_id = :product_id";
        }

        // Lấy danh sách tên file sẽ xóa
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->bindValue(':product_id', $productId, PDO::PARAM_INT);
        if (!empty($keepImageIds)) {
            foreach ($keepImageIds as $k => $id) {
                $stmtSelect->bindValue(($k + 2), $id, PDO::PARAM_INT); // Bắt đầu từ param thứ 2
            }
        }
        $stmtSelect->execute();
        $results = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $deletedFilenames[] = $row['image_url'];
        }

        // Thực hiện xóa
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->bindValue(':product_id', $productId, PDO::PARAM_INT);
        if (!empty($keepImageIds)) {
            foreach ($keepImageIds as $k => $id) {
                $stmtDelete->bindValue(($k + 2), $id, PDO::PARAM_INT);
            }
        }
        $stmtDelete->execute();
        return $deletedFilenames;
    }


    /**
     * Cập nhật is_thumbnail cho các ảnh của sản phẩm.
     * Đặt is_thumbnail=0 cho tất cả ảnh của sản phẩm trừ ảnh thumbnail mới (nếu có).
     * @param int $productId ID sản phẩm.
     * @param int $newThumbnailImageId ID của ảnh thumbnail mới (từ bảng product_image). Nếu null, sẽ không có thumbnail nào.
     * @return bool
     */
    public function ensureSingleThumbnail($productId, $newThumbnailImageId = null) {
        // 1. Đặt tất cả is_thumbnail = 0 cho sản phẩm này
        $sqlReset = "UPDATE " . $this->image_table . " SET is_thumbnail = 0 WHERE product_id = :product_id";
        $stmtReset = $this->conn->prepare($sqlReset);
        $stmtReset->bindParam(':product_id', $productId, PDO::PARAM_INT);
        if (!$stmtReset->execute()) {
            error_log("Failed to reset thumbnails for product ID: " . $productId);
            return false;
        }

        // 2. Nếu có thumbnail mới, đặt is_thumbnail = 1 cho nó
        if ($newThumbnailImageId !== null) {
            $sqlSet = "UPDATE " . $this->image_table . " SET is_thumbnail = 1 WHERE id = :image_id AND product_id = :product_id";
            $stmtSet = $this->conn->prepare($sqlSet);
            $stmtSet->bindParam(':image_id', $newThumbnailImageId, PDO::PARAM_INT);
            $stmtSet->bindParam(':product_id', $productId, PDO::PARAM_INT);
            if (!$stmtSet->execute()) {
                error_log("Failed to set new thumbnail ID: " . $newThumbnailImageId . " for product ID: " . $productId);
                return false;
            }
        }
        return true;
    }

    /**
     * Thêm một bản ghi ảnh vào product_image và trả về ID của nó.
     * @param int $productId
     * @param string $imageFilename
     * @param bool $isThumbnail
     * @return string|false Last insert ID or false
     */
    public function addSingleProductImage($productId, $imageFilename, $isThumbnail = false) {
        $sql = "INSERT INTO " . $this->image_table . " (product_id, image_url, is_thumbnail) VALUES (:product_id, :image_url, :is_thumbnail)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':image_url', $imageFilename, PDO::PARAM_STR);
        $stmt->bindValue(':is_thumbnail', $isThumbnail ? 1 : 0, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        error_log("Failed to add single image: " . $imageFilename . " for product ID: " . $productId . " - " . implode(", ", $stmt->errorInfo()));
        return false;
    }

    /**
     * Cập nhật tên file ảnh trong product_image.
     * @param int $imageDbId ID của ảnh trong bảng product_image.
     * @param string $newFilename Tên file mới.
     * @return bool True nếu cập nhật thành công, False nếu thất bại.
     */
    public function updateProductImageFilename($imageDbId, $newFilename) {
        $sql = "UPDATE " . $this->image_table . " SET image_url = :image_url WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':image_url', $newFilename, PDO::PARAM_STR);
        $stmt->bindParam(':id', $imageDbId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Đếm tổng số sản phẩm đang hoạt động (is_active = 1).
     * @return int Tổng số sản phẩm.
     */
    public function countAllActive() {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM " . $this->table . " WHERE is_active = 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Đếm tổng số sản phẩm sau khi áp dụng các bộ lọc (cho DataTables).
     * @param array $filters Mảng chứa các bộ lọc.
     * @return int Tổng số sản phẩm đã lọc.
     */
    public function countFilteredForDataTable($filters = []) {
        $sql = "SELECT COUNT(DISTINCT p.id) AS total 
                FROM " . $this->table . " p
                LEFT JOIN category c ON p.category_id = c.id 
                WHERE p.is_active = 1"; // Chỉ đếm sản phẩm active

        $params = [];
        $whereClauses = [];

        if (!empty($filters['search_value'])) { // DataTables gửi search[value]
            // Tìm kiếm trên nhiều cột nếu cần
            $searchableCols = ['p.name', 'p.description', 'p.brand', 'c.name']; // Các cột bạn muốn tìm kiếm
            $searchClauses = [];
            foreach($searchableCols as $col) {
                $searchClauses[] = $col . " LIKE ?";
                $params[] = '%' . $filters['search_value'] . '%';
            }
            if (!empty($searchClauses)) {
                $whereClauses[] = "(" . implode(" OR ", $searchClauses) . ")";
            }
        }
        // Thêm các filter cụ thể khác nếu cần
        // Ví dụ: if (!empty($filters['category_id'])) { ... }

        if (!empty($whereClauses)) {
            $sql .= " AND " . implode(" AND ", $whereClauses);
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error in countFilteredForDataTable: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Tăng lại số lượng tồn kho cho một sản phẩm (khi hủy đơn).
     * @param int $productId ID sản phẩm.
     * @param int $quantity Số lượng cần hoàn trả.
     * @return bool
     */
    public function restockProduct($productId, $quantity) {
        $stmt = $this->conn->prepare("UPDATE product SET stock = stock + ? WHERE id = ?");
        return $stmt->execute([$quantity, $productId]);
    }

    /**
     * Giảm số lượng đã bán của một sản phẩm (khi hủy đơn).
     * @param int $productId ID sản phẩm.
     * @param int $quantity Số lượng cần giảm.
     * @return bool
     */
    public function decreaseSoldCount($productId, $quantity) {
        // Đảm bảo số lượng đã bán không bị âm
        $stmt = $this->conn->prepare("UPDATE product SET sold_quantity = sold_quantity - ? WHERE id = ? AND sold_quantity >= ?");
        return $stmt->execute([$quantity, $productId, $quantity]);
    }


}

