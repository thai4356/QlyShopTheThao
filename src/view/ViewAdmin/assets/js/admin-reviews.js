$(document).ready(function () {
    var reviewsTable = $('#reviews-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "index.php?ctrl=adminreview&act=ajaxGetReviewsForDataTable",
            "type": "POST",
            "data": function (d) {
                d.product_id = $('#product-filter').val();
                d.user_id = $('#user-filter').val();
                d.rating = $('#rating-filter').val();
                d.status = $('#status-filter').val();
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "product_name" },
            { "data": "user_email" },
            {
                "data": "rating",
                "render": function(data) {
                    let stars = '';
                    for (let i = 0; i < 5; i++) {
                        stars += i < data ? '★' : '☆';
                    }
                    return `<span style="color: #ffc107;">${stars}</span>`;
                }
            },
            {
                "data": "comment",
                "render": function(data) {
                    return data.length > 50 ? data.substr(0, 50) + '...' : data;
                }
            },
            {
                "data": "status",
                "render": function(data) {
                    let badgeClass = '';
                    let statusText = '';
                    switch (data) {
                        case 'approved':
                            badgeClass = 'bg-success';
                            statusText = 'Đã duyệt';
                            break;
                        case 'pending':
                            badgeClass = 'bg-warning';
                            statusText = 'Chờ duyệt';
                            break;
                        case 'hidden':
                            badgeClass = 'bg-secondary';
                            statusText = 'Đã ẩn';
                            break;
                        default:
                            badgeClass = 'bg-dark';
                            statusText = data;
                    }
                    return `<span class="badge ${badgeClass}">${statusText}</span>`;
                }
            },
            {
                "data": "created_at",
                "render": function(data) {
                    let date = new Date(data);
                    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
                }
            },
            {
                "data": null,
                "orderable": false,
                "render": function (data, type, row) {
                    // **BẮT ĐẦU SỬA Ở ĐÂY**
                    // Đảm bảo toàn bộ các chuỗi HTML này được bọc trong dấu `backtick`

                    let viewBtn = `<button class="btn btn-info btn-sm btn-icon" data-bs-toggle="tooltip" title="Xem chi tiết" onclick="viewReview(${row.id})"><i class="fa fa-eye"></i></button>`;

                    let newStatus = (row.status === 'approved') ? 'hidden' : 'approved';
                    let toggleTitle = (row.status === 'approved') ? 'Ẩn đánh giá' : 'Hiện đánh giá';
                    let toggleIcon = (row.status === 'approved') ? 'fa-eye-slash' : 'fa-eye';
                    let toggleBtnClass = (row.status === 'approved') ? 'btn-warning' : 'btn-success';

                    // Chuỗi này chứa nhiều biến, nên BẮT BUỘC phải dùng dấu `backtick`
                    let toggleBtn = `<button class="btn ${toggleBtnClass} btn-sm btn-icon" data-bs-toggle="tooltip" title="${toggleTitle}" onclick="toggleReviewStatus(${row.id}, '${newStatus}')"><i class="fa ${toggleIcon}"></i></button>`;

                    let deleteBtn = `<button class="btn btn-danger btn-sm btn-icon" data-bs-toggle="tooltip" title="Xóa vĩnh viễn" onclick="deleteReview(${row.id})"><i class="fa fa-trash"></i></button>`;

                    // Chuỗi này cũng phải dùng dấu `backtick`
                    return `<div class="form-button-action">${viewBtn} ${toggleBtn} ${deleteBtn}</div>`;
                    // **KẾT THÚC PHẦN SỬA**
                }
            }
        ],
        "language": { "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json" },
        "order": [[ 0, "desc" ]] ,// Mặc định sắp xếp theo ID mới nhất

        // **BƯỚC 2: Thêm drawCallback để khởi tạo lại tooltip**
        "drawCallback": function(settings) {
            // Tìm tất cả các element có data-bs-toggle="tooltip" trong bảng và kích hoạt chúng
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('#reviews-table [data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    $('.review-filter').on('change', function () {
        // Tải lại dữ liệu của bảng
        reviewsTable.ajax.reload();
    });

    // Bắt sự kiện click vào một dòng của bảng (tr)
    $('#reviews-table tbody').on('click', 'tr', function (e) {
        // Chỉ xử lý khi click vào ô (td), không phải nút bấm bên trong
        if ($(e.target).is('td')) {
            var data = reviewsTable.row(this).data();
            if (data) {
                viewReview(data.id); // Gọi hàm điều hướng
            }
        }
    });

    // THÊM MỚI: Hàm để điều hướng
    window.viewReview = function(reviewId) {
        // Ngăn sự kiện của dòng nếu có
        if (event) event.stopPropagation();
        // Điều hướng đến trang chi tiết
        window.location.href = `index.php?page=review_details&id=${reviewId}`;
    }

});

// Ở cuối file admin-reviews.js, bên ngoài $(document).ready()
function toggleReviewStatus(reviewId, newStatus) {
    if (event) event.stopPropagation();

    const actionText = (newStatus === 'hidden') ? 'ẩn' : 'hiện';

    Swal.fire({
        title: `Bạn có chắc muốn ${actionText} đánh giá này?`,
        text: "Hành động này có thể được hoàn tác.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: `Vâng, ${actionText}!`,
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            // Gửi yêu cầu AJAX
            $.ajax({
                url: 'index.php?ctrl=adminreview&act=ajaxToggleReviewStatus',
                type: 'POST',
                data: {
                    review_id: reviewId,
                    new_status: newStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Thành công!', response.message, 'success');
                        // Tải lại bảng để cập nhật giao diện
                        $('#reviews-table').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Lỗi!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Lỗi!', 'Không thể kết nối đến máy chủ.', 'error');
                }
            });
        }
    });
}

// Đặt hàm này vào scope global để onclick có thể gọi được
window.toggleReviewStatus = toggleReviewStatus;

// HÀM MỚI: Xử lý việc xóa review
function deleteReview(reviewId) {
    if (event) event.stopPropagation();

    Swal.fire({
        title: 'Bạn có chắc chắn không?',
        text: "Hành động này sẽ xóa vĩnh viễn đánh giá! Bạn không thể hoàn tác.",
        icon: 'error', // Dùng icon 'error' để nhấn mạnh sự nguy hiểm
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Vâng, xóa nó!',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            // Gửi yêu cầu AJAX
            $.ajax({
                url: 'index.php?ctrl=adminreview&act=ajaxDeleteReview',
                type: 'POST',
                data: {
                    review_id: reviewId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Đã xóa!', response.message, 'success');
                        // Tải lại bảng để loại bỏ dòng đã xóa
                        $('#reviews-table').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Lỗi!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Lỗi!', 'Không thể kết nối đến máy chủ.', 'error');
                }
            });
        }
    });
}

// Đặt hàm này vào scope global để onclick có thể gọi được
window.deleteReview = deleteReview;