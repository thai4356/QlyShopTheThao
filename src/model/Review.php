<?php
require_once 'Connect.php';

class Review {
    private $conn;
    private $table = "review";
    public $id, $user_id, $product_id, $rating, $comment, $created_at, $updated_at;

    public function __construct() {
        $this->conn = (new Connect())->getConnection();
    }

    public function getByProductId($productId) {
        $stmt = $this->conn->prepare("
        SELECT r.*, u.email 
        FROM $this->table r 
        INNER JOIN username u ON r.user_id = u.id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private $sensitiveWords = [
        // Tiếng Anh
        'fuck', 'shit', 'bitch', 'asshole', 'bastard',
        // Tiếng Việt
        'địt', 'đụ', 'lồn', 'cặc', 'chó', 'đĩ', 'mẹ mày', 'con mẹ', 'vãi lồn'
    ];


    private function containsSensitiveWords($comment) {
        foreach ($this->sensitiveWords as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
            if (preg_match($pattern, $comment)) {
                return true;
            }
        }
        return false;
    }


    public function addReview($userId, $productId, $rating, $comment) {
        if ($this->containsSensitiveWords($comment)) {
            // Có từ cấm, không lưu và trả về lỗi
            return [
                'success' => false,
                'message' => 'Bình luận chứa từ ngữ không phù hợp!'
            ];
        }

        // Bình luận hợp lệ, tiến hành lưu
        $stmt = $this->conn->prepare("INSERT INTO $this->table (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$userId, $productId, $rating, $comment]);

        return [
            'success' => $success,
            'message' => $success ? 'Bình luận đã được gửi!' : 'Đã có lỗi xảy ra!'
        ];
    }



}
