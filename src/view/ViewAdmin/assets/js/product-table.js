// view/ViewAdmin/assets/js/product-table.js
$(document).ready(function() {
    var productTable = $('#add-row').DataTable({
        "processing": true, // Hiển thị thông báo "đang xử lý"
        "serverSide": true, // Kích hoạt server-side processing
        "ajax": {
            "url": "index.php?ctrl=adminproduct&act=ajaxGetProductsForDataTable", // URL đến action controller
            "type": "POST" // Hoặc GET tùy bạn cấu hình controller
            // "data": function ( d ) {
            //     // Thêm các tham số tùy chỉnh nếu cần
            //     // d.myKey = "myValue";
            // }
        },
        "columns": [
            { "data": "image_display", "name": "image_display", "orderable": false, "searchable": false },
            { "data": "name", "name": "name" }, // 'name' phải khớp với key trong JSON từ server và $allowed_sort_cols_map
            { "data": "price_display", "name": "price_display" , "orderable": true, "searchable": false}, // Đặt tên cho cột giá
            { "data": "stock", "name": "stock" },
            { "data": "sold_quantity", "name": "sold_quantity" },
            { "data": "category_name", "name": "category_name" },
            { "data": "actions", "name": "actions", "orderable": false, "searchable": false }
        ],
        "order": [[1, "desc"]], // Mặc định sắp xếp theo cột tên sản phẩm (index 1), giảm dần (có thể đổi thành ngày tạo nếu có cột đó)
                                // Nếu bạn muốn mặc định theo sản phẩm mới nhất, bạn cần một cột như 'created_at_timestamp'
                                // và đặt nó làm cột sắp xếp mặc định, hoặc server tự sắp xếp nếu không có order param.
                                // Controller hiện tại mặc định sort theo 'created_at' DESC nếu không có order param từ DataTables.
        "language": { // Tùy chỉnh ngôn ngữ (nếu cần)
            "processing": "Đang xử lý...",
            "lengthMenu": "Hiển thị _MENU_ dòng",
            "zeroRecords": "Không tìm thấy sản phẩm nào phù hợp",
            "info": "Hiển thị _START_ đến _END_ của _TOTAL_ sản phẩm",
            "infoEmpty": "Không có sản phẩm nào",
            "infoFiltered": "(được lọc từ _MAX_ tổng số sản phẩm)",
            "search": "Tìm kiếm:",
            "paginate": {
                "first": "Đầu",
                "last": "Cuối",
                "next": "Tiếp",
                "previous": "Trước"
            }
        },
        "responsive": true,
        "pageLength": 10, // Số dòng mặc định mỗi trang
        "lengthMenu": [[10, 20, 50, -1], [10, 20, 50, "Tất cả"]],
        "drawCallback": function( settings ) {
            // Khởi tạo lại tooltip cho các nút trong bảng sau mỗi lần vẽ lại (quan trọng!)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('#add-row [data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                // Hủy tooltip cũ nếu có để tránh bị duplicate
                var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (existingTooltip) {
                    existingTooltip.dispose();
                }
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Gắn lại sự kiện cho các nút edit/delete nếu chúng không dùng event delegation từ phạm vi cao hơn
            // Tuy nhiên, các module product-modal-edit.js và product-delete-handler.js
            // đã dùng event delegation trên 'tbody' nên không cần gắn lại ở đây.
        }
    });

    // Nếu bạn có các nút filter tùy chỉnh (ngoài ô search của DataTables)
    // thì bạn cần bắt sự kiện của chúng và gọi productTable.ajax.reload() hoặc productTable.draw()
    // Ví dụ:
    // $('#myCustomSearchButton').on('click', function(){
    //    productTable.column(1).search( $('#myCustomSearchInput').val() ).draw();
    // });

    console.log("DataTable for products initialized with server-side processing.");
});