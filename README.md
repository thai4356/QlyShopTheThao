# Website Cửa Hàng Bán Dụng Cụ Thể Thao Fithub

## Giới thiệu

Đây là website bán dụng cụ thể thao, hỗ trợ khách hàng tìm kiếm, đặt hàng, quản lý giỏ hàng, xem lịch sử mua hàng và đánh giá sản phẩm. Website có phân quyền người dùng (user, admin), xác thực email, quên mật khẩu, và tích hợp reCAPTCHA chống spam.

## Chức năng chính

### Người dùng (Khách hàng)

- **Đăng ký, đăng nhập, xác thực tài khoản**: Tạo tài khoản mới, xác thực qua email, đăng nhập hệ thống, đổi mật khẩu, quên mật khẩu.
<img width="975" height="500" alt="image" src="https://github.com/user-attachments/assets/252daa46-38ab-42fc-869b-7af372680f69" />

- **Tìm kiếm & xem sản phẩm**: Tìm kiếm, lọc, xem chi tiết sản phẩm, xem đánh giá.
<img width="975" height="495" alt="image" src="https://github.com/user-attachments/assets/99820359-3d97-4043-8b60-0b9abf6a6f48" />

- **Giỏ hàng**: Thêm, sửa, xóa sản phẩm trong giỏ hàng.
<img width="975" height="498" alt="image" src="https://github.com/user-attachments/assets/983e0165-4f66-4fec-8c6c-cb3dbb8961a6" />

- **Đặt hàng**: Tạo đơn hàng, chọn phương thức thanh toán, nhập địa chỉ nhận hàng.
- **Lịch sử mua hàng**: Xem danh sách đơn hàng đã đặt, trạng thái đơn hàng.
<img width="975" height="496" alt="image" src="https://github.com/user-attachments/assets/9e8875b8-4634-4c27-8f5d-0b4b0636e463" />

- **Đánh giá sản phẩm**: Gửi đánh giá, nhận xét cho sản phẩm đã mua.
<img width="975" height="494" alt="image" src="https://github.com/user-attachments/assets/f781f2cd-f48c-45e2-906e-fc5f95287234" />

---

### Quản trị viên (Admin)

- **Quản lý sản phẩm**: Thêm, sửa, xóa, cập nhật thông tin sản phẩm, hình ảnh, giá, tồn kho.
<img width="975" height="495" alt="image" src="https://github.com/user-attachments/assets/803b11f5-57bb-4682-8619-373ec8a5d5aa" />
<img width="975" height="499" alt="image" src="https://github.com/user-attachments/assets/86acb75d-b863-4701-869c-f365187cd040" />

- **Quản lý danh mục**: Thêm, sửa, xóa danh mục sản phẩm.
<img width="975" height="499" alt="image" src="https://github.com/user-attachments/assets/92d8e153-8db1-470c-8985-ca93628fbd7d" />

- **Quản lý đơn hàng**: Xem, xác nhận, cập nhật trạng thái, hủy đơn hàng.
<img width="975" height="499" alt="image" src="https://github.com/user-attachments/assets/4e2cea89-b938-4520-96de-b11724e5bb04" />
<img width="975" height="499" alt="image" src="https://github.com/user-attachments/assets/b5abc785-bc18-4853-85c7-3cb44579c261" />

- **Quản lý người dùng**: Xem danh sách, phân quyền, khóa/mở tài khoản người dùng.
<img width="975" height="495" alt="image" src="https://github.com/user-attachments/assets/808b5a38-bd82-410b-afb0-ae6ea68bf433" />
<img width="975" height="497" alt="image" src="https://github.com/user-attachments/assets/776e280f-2c63-4045-ae46-713fa486e35d" />

- **Quản lý đánh giá**: Duyệt, xóa các đánh giá không phù hợp.
<img width="975" height="497" alt="image" src="https://github.com/user-attachments/assets/c1c0e675-9239-474b-a140-cfcce7826c49" />
<img width="975" height="495" alt="image" src="https://github.com/user-attachments/assets/e4559a23-1d5d-43d7-988d-29873df240a7" />

- **Thống kê, báo cáo**: Xem báo cáo doanh thu, số lượng đơn hàng, sản phẩm bán chạy.
<img width="975" height="498" alt="image" src="https://github.com/user-attachments/assets/c4a59270-78bb-4e37-a0d8-60826b9e32dd" />

---

## Công nghệ sử dụng

- PHP (MVC đơn giản)
- MySQL (PDO) (deploy lên RailwayDB)
- [PHPMailer](src/PHPMailer/) gửi email xác thực, quên mật khẩu
- [phpdotenv](src/phpdotenv-5.6.2/) quản lý biến môi trường
- HTML, CSS, JavaScript (validator, hiệu ứng)
- Google reCAPTCHA v2
- PHP/Payos cho việc thanh toán bằng Payos

## Cấu trúc thư mục

- `src/controller/`: Xử lý logic (đăng ký, đăng nhập, giỏ hàng, đơn hàng, đánh giá...)
- `src/model/`: Kết nối và truy vấn database (sản phẩm, giỏ hàng, đơn hàng...)
- `src/view/`: Giao diện người dùng (user, admin, public)
- `config/`: Cấu hình autoloader, thanh toán
- `vendor/`: Thư viện ngoài (autoload qua composer)
- `.env`: Biến môi trường (DB, SMTP, reCAPTCHA...)
