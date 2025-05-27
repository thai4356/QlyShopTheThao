<?php
require_once 'Connect.php';

    class Product {
        private $conn;
        private $table = "product";

        public $id, $name, $description, $price, $stock, $image_url, $created_at, $updated_at;

        public function __construct() {
            $this->conn = (new Connect())->getConnection();
        }

        public function getById($id) {
            $query = "SELECT p.*, pi.image_url
              FROM $this->table p
              LEFT JOIN product_image pi ON p.id = pi.product_id
              WHERE p.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) return null;

            $product = $rows[0]; // thông tin sản phẩm
            $product['images'] = array_column($rows, 'image_url'); // mảng các ảnh

            return $product;
        }




        public function countAll() {
            $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM product");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'];
        }

        public function countFiltered($filters = []) {
            $sql = "SELECT COUNT(*) AS total FROM product WHERE 1=1";
            $params = [];

            if (!empty($filters['location'])) {
                $in = str_repeat('?,', count($filters['location']) - 1) . '?';
                $sql .= " AND location IN ($in)";
                $params = array_merge($params, $filters['location']);
            }

            if (!empty($filters['brand'])) {
                $in = str_repeat('?,', count($filters['brand']) - 1) . '?';
                $sql .= " AND brand IN ($in)";
                $params = array_merge($params, $filters['brand']);
            }

            if (!empty($filters['price_min'])) {
                $sql .= " AND price >= ?";
                $params[] = (int)$filters['price_min'];
            }

            if (!empty($filters['price_max'])) {
                $sql .= " AND price <= ?";
                $params[] = (int)$filters['price_max'];
            }

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
        WHERE 1=1 and pi.is_thumbnail = 1
    ";


            $params = [];

            // -- Build filter conditions
            if (!empty($filters['location'])) {
                $placeholders = implode(',', array_fill(0, count($filters['location']), '?'));
                $sql .= " AND p.location IN ($placeholders)";
                $params = array_merge($params, $filters['location']);
            }

            if (!empty($filters['brand'])) {
                $placeholders = implode(',', array_fill(0, count($filters['brand']), '?'));
                $sql .= " AND p.brand IN ($placeholders)";
                $params = array_merge($params, $filters['brand']);
            }

            if (!empty($filters['price_min'])) {
                $sql .= " AND p.price >= ?";
                $params[] = (int)$filters['price_min'];
            }

            if (!empty($filters['price_max'])) {
                $sql .= " AND p.price <= ?";
                $params[] = (int)$filters['price_max'];
            }

            // -- Sắp xếp
            // Nếu người dùng chọn sắp xếp theo giá
            if (!empty($filters['sort']) && in_array($filters['sort'], ['asc', 'desc'])) {
                $sql .= " ORDER BY p.price " . strtoupper($filters['sort']);
            } else {
                $sql .= " ORDER BY p.created_at DESC";
            }

            $sql .= " LIMIT ? OFFSET ?";

            $params[] = (int)$limit;
            $params[] = (int)$offset;

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
