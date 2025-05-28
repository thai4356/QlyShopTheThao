<?php
require_once 'Connect.php';

    class Product {
        private $conn;
        private $table = "product";

        public $id, $name, $description, $price, $stock, $image_url, $created_at, $updated_at;

        public function __construct() {
            $this->conn = (new Connect())->getConnection();
        }

        // Trong Product.php
        public function getById($id) {
            $query = "SELECT p.*, pi.image_url
            FROM $this->table p
            LEFT JOIN product_image pi ON p.id = pi.product_id AND pi.is_thumbnail = 1
            WHERE p.id = ?
            LIMIT 1"; // Giả sử bạn muốn thumbnail chính
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về một dòng sản phẩm, bao gồm price và discount_price
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
        SELECT DISTINCT p.*, pi.image_url
        FROM product p
        INNER JOIN product_image pi 
            ON p.id = pi.product_id 
        WHERE 1=1 AND pi.is_thumbnail = 1
    ";

            $params = [];

            // Location
            if (!empty($filters['location'])) {
                $placeholders = implode(',', array_fill(0, count($filters['location']), '?'));
                $sql .= " AND p.location IN ($placeholders)";
                $params = array_merge($params, $filters['location']);
            }

            // Brand
            if (!empty($filters['brand'])) {
                $placeholders = implode(',', array_fill(0, count($filters['brand']), '?'));
                $sql .= " AND p.brand IN ($placeholders)";
                $params = array_merge($params, $filters['brand']);
            }

            // Category
            if (!empty($filters['category_id'])) {
                $placeholders = implode(',', array_fill(0, count($filters['category_id']), '?'));
                $sql .= " AND p.category_id IN ($placeholders)";
                $params = array_merge($params, $filters['category_id']);
            }

            // Search
            if (!empty($filters['search'])) {
                $sql .= " AND p.name LIKE ?";
                $params[] = '%' . $filters['search'] . '%';
            }

            // Price
            if (!empty($filters['price_min'])) {
                $sql .= " AND p.price >= ?";
                $params[] = (int)$filters['price_min'];
            }

            if (!empty($filters['price_max'])) {
                $sql .= " AND p.price <= ?";
                $params[] = (int)$filters['price_max'];
            }

            // Sort
            if (!empty($filters['sort']) && in_array($filters['sort'], ['asc', 'desc'])) {
                $sql .= " ORDER BY p.price " . strtoupper($filters['sort']);
            } else {
                $sql .= " ORDER BY p.created_at DESC";
            }

            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;

            // Debug
            // echo $sql; print_r($params); exit;

            $stmt = $this->conn->prepare($sql);
            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }



        public function getAllLocations() {
            $stmt = $this->conn->query("SELECT DISTINCT location FROM product WHERE location IS NOT NULL AND location <> '' LIMIT 5
");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        public function getAllBrands() {
            $stmt = $this->conn->query("SELECT DISTINCT brand FROM product WHERE brand IS NOT NULL AND brand <> '' LIMIT 5
");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        public function reduceStock($productId, $quantity) {
            $stmt = $this->conn->prepare("UPDATE product SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $stmt->execute(array($quantity, $productId, $quantity));
        }

        public function increseSold($productId, $quantity) {
            $stmt = $this->conn->prepare("UPDATE product SET sold_quantity = sold_quantity + ? WHERE id = ? AND stock >= ?");
            $stmt->execute(array($quantity, $productId, $quantity));
        }
    }
