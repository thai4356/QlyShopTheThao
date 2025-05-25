<?php
require_once 'Connect.php';

    class Product {
        private $conn;
        private $table = "product";

        public $id, $name, $description, $price, $stock, $image_url, $created_at, $updated_at;

        public function __construct() {
            $this->conn = (new Connect())->getConnection();
        }

        public function getAll() {
            $query = "SELECT * FROM $this->table ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function getById($id) {
            $query = "SELECT * FROM $this->table WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function getPaged($limit, $offset) {
            $sql = "SELECT * FROM product ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $sql = "SELECT * FROM product WHERE 1=1";
            $params = [];

            // -- Build filter conditions
            if (!empty($filters['location'])) {
                $placeholders = implode(',', array_fill(0, count($filters['location']), '?'));
                $sql .= " AND location IN ($placeholders)";
                $params = array_merge($params, $filters['location']);
            }

            if (!empty($filters['brand'])) {
                $placeholders = implode(',', array_fill(0, count($filters['brand']), '?'));
                $sql .= " AND brand IN ($placeholders)";
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

            // -- Add limit & offset (không để dấu nháy trong SQL!)
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;

            $stmt = $this->conn->prepare($sql);

            // -- Gắn toàn bộ biến đúng thứ tự
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

    }
