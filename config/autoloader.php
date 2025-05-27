<?php
spl_autoload_register(function ($class_name) {
    // Mảng cấu hình: Namespace Prefix => Đường dẫn thư mục cơ sở tương ứng
    $libraries = [
        'Dotenv\\'                 => __DIR__ . '/../src/phpdotenv-5.6.2/src/',
        'PhpOption\\'              => __DIR__ . '/../src/php-option-1.9.3/src/PhpOption/',
        'GrahamCampbell\\ResultType\\' => __DIR__ . '/../src/Result-Type-1.1.3/src/' // <<< THÊM CẤU HÌNH CHO ResultType
    ];

    foreach ($libraries as $prefix => $base_dir) {
        // Kiểm tra xem class có thuộc namespace prefix hiện tại không
        $len = strlen($prefix);
        if (strncmp($prefix, $class_name, $len) !== 0) {
            // Không, bỏ qua, thử prefix tiếp theo
            continue;
        }

        // Lấy tên class tương đối
        $relative_class = substr($class_name, $len);

        // Thay thế namespace prefix bằng đường dẫn thư mục cơ sở,
        // thay thế namespace separators (\) bằng directory separators (/)
        // và thêm đuôi .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Nếu file tồn tại, require nó và dừng vòng lặp
        if (file_exists($file)) {
            require $file;
            return; // Class đã được nạp
        } else {
            // (Tùy chọn) Ghi log nếu không tìm thấy file cho prefix này
            // error_log("Autoloader: Không tìm thấy file cho class " . $class_name . " (prefix " . $prefix . ") tại " . $file);
        }
    }
});


