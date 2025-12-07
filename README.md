# 🏋️ QlyShopTheThao - Hệ Thống Quản Lý Cửa Hàng Thể Thao

## 📋 Giới Thiệu

**QlyShopTheThao** là hệ thống quản lý cửa hàng thể thao trực tuyến được xây dựng bằng PHP thuần với kiến trúc MVC. Hệ thống cung cấp đầy đủ các chức năng cho cả người dùng và quản trị viên.

## 🌐 URL Ngrok (Tunneling)

```
https://your-ngrok-url.ngrok-free.app
```

> **Lưu ý:** Thay `your-ngrok-url` bằng URL ngrok thực tế khi chạy ngrok tunnel.

### Cách chạy Ngrok:
```bash
ngrok http 80
```

Sau đó sử dụng URL được cung cấp để truy cập từ internet.

---

## 🛠️ Công Nghệ Sử Dụng

| Công nghệ | Mô tả |
|-----------|-------|
| **PHP 7.4+** | Backend server-side |
| **MySQL** | Cơ sở dữ liệu (PDO) |
| **HTML/CSS/JS** | Frontend |
| **Bootstrap 5** | CSS Framework |
| **jQuery** | JavaScript Library |
| **DataTables** | Server-side processing |
| **Font Awesome** | Icons |
| **SweetAlert2** | Alert dialogs |
| **PHPMailer** | Gửi email |
| **Stripe/PayOS** | Cổng thanh toán |

---

## 📁 Cấu Trúc Thư Mục

```
QlyShopTheThao/
├── config/                 # Cấu hình autoloader
├── helpers/                # Helper functions
├── logs/                   # Log files
├── src/
│   ├── controller/
│   │   ├── admin/          # Controllers cho Admin
│   │   └── ...             # Controllers cho User
│   ├── model/
│   │   ├── Admin/          # Models cho Admin
│   │   └── ...             # Models cho User
│   └── view/
│       ├── ViewAdmin/      # Giao diện Admin
│       ├── ViewUser/       # Giao diện User
│       └── Public/         # Assets công khai
├── vendor/                 # Thư viện Composer
└── composer.json
```

---

## 🌍 DANH SÁCH TẤT CẢ CÁC TRANG

### 📱 TRANG NGƯỜI DÙNG (User)

| # | Trang | URL | Mô tả |
|---|-------|-----|-------|
| 1 | **Trang chủ** | `/src/view/ViewUser/Index.php?module=home` | Trang chủ, banner, sản phẩm nổi bật |
| 2 | **Danh sách sản phẩm** | `/src/view/ViewUser/Index.php?module=sanpham` | Hiển thị tất cả sản phẩm, lọc, tìm kiếm |
| 3 | **Chi tiết sản phẩm** | `/src/view/ViewUser/Index.php?module=chitietsanpham&masp={id}` | Thông tin chi tiết, đánh giá sản phẩm |
| 4 | **Giỏ hàng** | `/src/view/ViewUser/Index.php?module=cart` | Quản lý giỏ hàng |
| 5 | **Thanh toán** | `/src/view/ViewUser/Index.php?module=order` | Đặt hàng và thanh toán |
| 6 | **Lịch sử đơn hàng** | `/src/view/ViewUser/Index.php?module=orderhistory` | Xem các đơn hàng đã đặt |
| 7 | **Chi tiết đơn hàng** | `/src/view/ViewUser/Index.php?module=orderdetail&id={id}` | Xem chi tiết đơn hàng cụ thể |
| 8 | **Danh sách yêu thích** | `/src/view/ViewUser/Index.php?module=wishlist` | Sản phẩm yêu thích |
| 9 | **Đăng nhập/Đăng ký** | `/src/view/login.php` | Trang đăng nhập và đăng ký |
| 10 | **Quên mật khẩu** | `/src/view/ForgotPassword.php` | Yêu cầu đặt lại mật khẩu |
| 11 | **Đặt lại mật khẩu** | `/src/view/resetPassword.php?token={token}` | Đặt lại mật khẩu mới |
| 12 | **Thành công đặt lại** | `/src/view/success_reset.php` | Thông báo đặt lại thành công |
| 13 | **Tài khoản bị cấm** | `/src/view/banned_account.php` | Thông báo tài khoản bị khóa |

### 🎯 Giỏ hàng Actions

| Action | URL | Mô tả |
|--------|-----|-------|
| Thêm vào giỏ | `/src/view/ViewUser/Index.php?module=cart&act=add` | Thêm sản phẩm vào giỏ |
| Xóa khỏi giỏ | `/src/view/ViewUser/Index.php?module=cart&act=remove` | Xóa sản phẩm khỏi giỏ |

### 💳 Thanh toán Callbacks

| Trang | URL | Mô tả |
|-------|-----|-------|
| Stripe Success | `/src/controller/stripe_return_handler.php` | Xử lý thanh toán Stripe thành công |
| Stripe Cancel | `/src/controller/stripe_cancel_handler.php` | Xử lý hủy thanh toán Stripe |
| PayOS Success | `/src/controller/payos_return_handler.php` | Xử lý thanh toán PayOS thành công |
| PayOS Cancel | `/src/controller/payos_cancel_handler.php` | Xử lý hủy thanh toán PayOS |

---

### 🔐 TRANG QUẢN TRỊ (Admin)

**Base URL:** `/src/view/ViewAdmin/index.php`

#### 📊 Dashboard

| # | Trang | URL | Mô tả |
|---|-------|-----|-------|
| 1 | **Dashboard** | `?page=dashboard` | Tổng quan thống kê, biểu đồ doanh thu |

#### 📦 Quản lý Sản phẩm

| # | Trang/Action | URL | Mô tả |
|---|--------------|-----|-------|
| 2 | **Danh sách sản phẩm** | `?page=products` | Bảng danh sách sản phẩm |
| 3 | **Danh sách SP (MVC)** | `?ctrl=adminproduct&act=listProducts` | Danh sách sản phẩm qua controller |
| 4 | AJAX - Lấy dữ liệu DataTable | `?ctrl=adminproduct&act=ajaxGetProductsForDataTable` | API lấy dữ liệu cho DataTables |
| 5 | AJAX - Thêm sản phẩm | `?ctrl=adminproduct&act=ajaxAddProduct` | API thêm sản phẩm mới |
| 6 | AJAX - Cập nhật sản phẩm | `?ctrl=adminproduct&act=ajaxUpdateProduct` | API cập nhật sản phẩm |
| 7 | AJAX - Xóa sản phẩm | `?ctrl=adminproduct&act=ajaxSoftDeleteProduct` | API soft delete sản phẩm |
| 8 | AJAX - Chi tiết (Edit) | `?ctrl=adminproduct&act=ajaxGetProductDetailsForEdit` | API lấy chi tiết để sửa |
| 9 | AJAX - Chi tiết (View) | `?ctrl=adminproduct&act=ajaxGetProductDetailsForView` | API lấy chi tiết để xem |

#### 📂 Quản lý Danh mục

| # | Trang/Action | URL | Mô tả |
|---|--------------|-----|-------|
| 10 | **Danh sách danh mục** | `?page=categories` | Bảng danh sách danh mục |
| 11 | **Danh sách DM (MVC)** | `?ctrl=admincategory&act=listCategories` | Danh sách qua controller |
| 12 | AJAX - Lấy dữ liệu DataTable | `?ctrl=admincategory&act=ajaxGetCategoriesForDataTable` | API lấy dữ liệu cho DataTables |
| 13 | AJAX - Thêm danh mục | `?ctrl=admincategory&act=ajaxAddCategory` | API thêm danh mục mới |
| 14 | AJAX - Cập nhật danh mục | `?ctrl=admincategory&act=ajaxUpdateCategory` | API cập nhật danh mục |
| 15 | AJAX - Xóa danh mục | `?ctrl=admincategory&act=ajaxSoftDeleteCategory` | API soft delete danh mục |

#### 🛒 Quản lý Đơn hàng

| # | Trang/Action | URL | Mô tả |
|---|--------------|-----|-------|
| 16 | **Danh sách đơn hàng** | `?page=orders` | Bảng danh sách đơn hàng |
| 17 | **Chi tiết đơn hàng** | `?page=order_details&id={id}` | Xem chi tiết đơn hàng |
| 18 | AJAX - Lấy dữ liệu DataTable | `?ctrl=adminorder&act=ajaxGetOrdersForDataTable` | API lấy dữ liệu cho DataTables |
| 19 | AJAX - Cập nhật trạng thái | `?ctrl=adminorder&act=ajaxUpdateOrderStatus` | API cập nhật trạng thái đơn |
| 20 | AJAX - Hủy & hoàn kho | `?ctrl=adminorder&act=ajaxCancelAndRestockOrder` | API hủy đơn và hoàn lại kho |
| 21 | In hóa đơn | `?ctrl=adminorder&act=printInvoice&id={id}` | Xuất hóa đơn PDF |

#### 👥 Quản lý Người dùng

| # | Trang/Action | URL | Mô tả |
|---|--------------|-----|-------|
| 22 | **Danh sách người dùng** | `?page=users` | Bảng danh sách người dùng |
| 23 | **Chi tiết người dùng** | `?page=user_details&id={id}` | Xem chi tiết người dùng |
| 24 | AJAX - Lấy dữ liệu DataTable | `?ctrl=adminuser&act=ajaxGetUsersForDataTable` | API lấy dữ liệu cho DataTables |
| 25 | AJAX - Thêm người dùng | `?ctrl=adminuser&act=ajaxAddUser` | API thêm người dùng mới |
| 26 | AJAX - Toggle trạng thái | `?ctrl=adminuser&act=toggleUserStatus` | API kích hoạt/vô hiệu hóa user |

#### ⭐ Quản lý Đánh giá

| # | Trang/Action | URL | Mô tả |
|---|--------------|-----|-------|
| 27 | **Danh sách đánh giá** | `?page=reviews` | Bảng danh sách đánh giá |
| 28 | **Chi tiết đánh giá** | `?page=review_details&id={id}` | Xem chi tiết đánh giá |
| 29 | AJAX - Lấy dữ liệu DataTable | `?ctrl=adminreview&act=ajaxGetReviewsForDataTable` | API lấy dữ liệu cho DataTables |
| 30 | AJAX - Toggle trạng thái | `?ctrl=adminreview&act=ajaxToggleReviewStatus` | API duyệt/ẩn đánh giá |
| 31 | AJAX - Xóa đánh giá | `?ctrl=adminreview&act=ajaxDeleteReview` | API xóa đánh giá |
| 32 | AJAX - Gửi phản hồi | `?ctrl=adminreview&act=ajaxSubmitReply` | API admin phản hồi đánh giá |

#### 📈 Dashboard API

| # | Action | URL | Mô tả |
|---|--------|-----|-------|
| 33 | AJAX - Dữ liệu biểu đồ | `?ctrl=admindashboard&act=ajaxGetChartData` | API lấy dữ liệu cho biểu đồ |

---

## 🔗 URL ĐẦY ĐỦ VỚI NGROK

### Ngrok Base URL
```
https://your-ngrok-url.ngrok-free.app/QlyShopTheThao
```

### Ví dụ URL đầy đủ:

| Trang | URL với Ngrok |
|-------|---------------|
| **Trang chủ User** | `https://your-ngrok-url.ngrok-free.app/QlyShopTheThao/src/view/ViewUser/Index.php?module=home` |
| **Đăng nhập** | `https://your-ngrok-url.ngrok-free.app/QlyShopTheThao/src/view/login.php` |
| **Admin Dashboard** | `https://your-ngrok-url.ngrok-free.app/QlyShopTheThao/src/view/ViewAdmin/index.php?page=dashboard` |
| **Quản lý sản phẩm** | `https://your-ngrok-url.ngrok-free.app/QlyShopTheThao/src/view/ViewAdmin/index.php?page=products` |
| **Quản lý đơn hàng** | `https://your-ngrok-url.ngrok-free.app/QlyShopTheThao/src/view/ViewAdmin/index.php?page=orders` |

---

## 📊 Trạng Thái Trong Hệ Thống

### Trạng thái Đơn hàng
| Status | Mô tả |
|--------|-------|
| `pending` | Chờ xử lý |
| `confirmed` | Đã xác nhận |
| `shipping` | Đang giao hàng |
| `delivered` | Đã giao hàng |
| `cancelled` | Đã hủy |

### Trạng thái Đánh giá
| Status | Mô tả |
|--------|-------|
| `pending` | Chờ duyệt |
| `approved` | Đã duyệt |
| `hidden` | Đã ẩn |

### Trạng thái Người dùng
| Status | Mô tả |
|--------|-------|
| `1` (Active) | Hoạt động |
| `0` (Banned) | Bị khóa |

---

## ⚙️ Cài Đặt

### 1. Clone Project
```bash
git clone https://github.com/your-username/QlyShopTheThao.git
```

### 2. Import Database
- Tạo database mới trong MySQL
- Import file SQL từ `/src/ProductImage/gym.sql`

### 3. Cấu hình Database
Chỉnh sửa file `/src/model/Connect.php`:
```php
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'root';
$password = '';
```

### 4. Cài đặt Dependencies
```bash
composer install
```

### 5. Chạy Project
- Sử dụng XAMPP/WAMP
- Truy cập: `http://localhost/QlyShopTheThao`

### 6. Chạy với Ngrok (Tùy chọn)
```bash
ngrok http 80
```

---

## 👤 Tài Khoản Mặc Định

### Admin
- **Email:** admin@gmail.com
- **Password:** admin123

### User
- Đăng ký tài khoản mới qua trang đăng ký

---

## 🔧 Cổng Thanh Toán

### Stripe
- Cấu hình API keys trong file cấu hình
- Hỗ trợ thanh toán quốc tế

### PayOS
- Cổng thanh toán nội địa Việt Nam
- Hỗ trợ QR Code, ngân hàng nội địa

---

## 📧 Email Service

Sử dụng **PHPMailer** để gửi:
- Email xác thực tài khoản
- Email đặt lại mật khẩu
- Email thông báo đơn hàng

---

## 📝 License

MIT License

---

## 👨‍💻 Tác Giả

- **Project:** QlyShopTheThao
- **Version:** 1.0.0
- **Date:** December 2025

---

## 📞 Liên Hệ

Nếu có vấn đề hoặc câu hỏi, vui lòng tạo issue trên GitHub repository.

