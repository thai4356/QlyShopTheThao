// Đảm bảo MyAppAdmin namespace đã tồn tại (nếu bạn dùng)
var MyAppAdmin = window.MyAppAdmin || {};

MyAppAdmin.ProductDeleteHandler = (function($) {
    'use strict';

    var deleteConfirmModalEl;
    var deleteConfirmModalInstance; // Bootstrap 5 modal instance

    // Biến để lưu trữ ID sản phẩm sẽ bị xóa, lấy từ data của modal
    // không cần biến toàn cục ở đây vì nó được truyền qua data attribute của modal

    function init() {
        deleteConfirmModalEl = document.getElementById('deleteConfirmModal');
        if (!deleteConfirmModalEl) {
            console.error("#deleteConfirmModal not found for Product Delete Handler.");
            return;
        }
        deleteConfirmModalInstance = new bootstrap.Modal(deleteConfirmModalEl);

        // 1. Xử lý khi nhấp nút "Xóa" (icon) trong bảng -> mở modal xác nhận xóa
        $('#add-row tbody').on('click', '.delete-product-button', function(event) {
            event.stopPropagation();
            var productId = $(this).data('product-id'); // Lấy trực tiếp từ nút
            var productName = $(this).data('product-name'); // Lấy trực tiếp từ nút

            if (!productId) {
                console.error("Không tìm thấy ID sản phẩm từ data-id của dòng.");
                if (typeof $.notify === 'function') {
                    $.notify({ message: "Lỗi: Không thể xác định sản phẩm cần xóa." },{ type: 'danger' });
                } else {
                    alert("Lỗi: Không thể xác định sản phẩm cần xóa.");
                }
                return;
            }

            console.log('Icon Xóa trên bảng được click. Product ID:', productId, 'Tên SP:', productName);

            $('#deleteProductNameConfirm').text(productName || 'sản phẩm này'); // Hiển thị tên SP trong modal
            // Lưu productId vào data attribute của deleteConfirmModalEl để nút "Xóa" bên trong modal đó có thể lấy
            $(deleteConfirmModalEl).data('product-id-to-delete', productId);
            console.log('Đã lưu product-id-to-delete vào modal:', $(deleteConfirmModalEl).data('product-id-to-delete'));


            if (deleteConfirmModalInstance) {
                deleteConfirmModalInstance.show();
            }
        });

        // 2. Xử lý nút "Xóa sản phẩm" bên trong modal CHỈNH SỬA SẢN PHẨM (#modalOpenDeleteConfirmButton)
        // Nút này cũng sẽ mở deleteConfirmModal
        $('#modalOpenDeleteConfirmButton').on('click', function() {
            var productId = $('#modalEditProductId').val(); // Lấy ID từ form đang chỉnh sửa
            var productName = $('#modalEditProductName').val() ||
                (MyAppAdmin.ProductEdit && MyAppAdmin.ProductEdit.getCurrentProductName ? MyAppAdmin.ProductEdit.getCurrentProductName() : 'sản phẩm này');
            // Cần một cách để lấy tên sản phẩm hiện tại nếu #modalEditProductName rỗng
            // Hoặc bạn có thể truyền tên sản phẩm vào hàm này nếu tách logic của ProductEdit

            if (!productId) {
                console.error("Không tìm thấy ID sản phẩm từ modal chỉnh sửa.");
                if (typeof $.notify === 'function') {
                    $.notify({ message: "Lỗi: Không lấy được ID sản phẩm từ form chỉnh sửa." },{ type: 'danger' });
                } else {
                    alert("Lỗi: Không lấy được ID sản phẩm từ form chỉnh sửa.");
                }
                return;
            }

            console.log('Nút "Xóa SP" trong modal edit được click. Product ID:', productId, 'Tên SP:', productName);

            $('#deleteProductNameConfirm').text(productName);
            $(deleteConfirmModalEl).data('product-id-to-delete', productId); // Lưu ID
            console.log('Đã lưu product-id-to-delete vào modal (từ edit form):', $(deleteConfirmModalEl).data('product-id-to-delete'));


            if (deleteConfirmModalInstance) {
                deleteConfirmModalInstance.show();
            }
        });


        // 3. Xử lý nút "Xóa" cuối cùng trên modal xác nhận xóa (#confirmDeleteButton)
        $('#confirmDeleteButton').on('click', function() {
            var $button = $(this);
            // Lấy productId từ data attribute của deleteConfirmModalEl
            var productId = $(deleteConfirmModalEl).data('product-id-to-delete');

            console.log('Nút "Xóa" xác nhận được click. ID lấy từ data modal:', productId);

            if (!productId) {
                console.error('Lỗi nghiêm trọng: productId là falsy tại #confirmDeleteButton.');
                if (typeof $.notify === 'function') {
                    $.notify({ icon: 'fas fa-exclamation-triangle', title: '<strong>Lỗi!</strong>', message: "Không tìm thấy ID sản phẩm để thực hiện xóa." },{ type: 'danger' });
                } else {
                    alert("Lỗi: Không tìm thấy ID sản phẩm để thực hiện xóa.");
                }
                if(deleteConfirmModalInstance) deleteConfirmModalInstance.hide();
                return;
            }

            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xóa...');

            $.ajax({
                url: 'index.php?ctrl=adminproduct&act=ajaxSoftDeleteProduct',
                type: 'POST',
                data: { id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Cập nhật UI: làm mờ dòng, vô hiệu hóa nút, xóa class clickable
                        var $rowToDelete = $('tr.product-row-clickable[data-id="' + productId + '"]');
                        if ($rowToDelete.length) {
                            $rowToDelete
                                .css('opacity', 0.5)
                                .css('text-decoration', 'line-through')
                                .removeClass('product-row-clickable') // Không cho click/mở modal sửa nữa
                                .find('.edit-product-button, .delete-product-button') // Tìm các nút trong dòng đó
                                .prop('disabled', true)
                                .css('pointer-events', 'none'); // Vô hiệu hóa hoàn toàn sự kiện click
                        }

                        // Nếu modal edit đang mở và sản phẩm đó bị xóa, thì đóng modal edit
                        try {
                            if (typeof MyAppAdmin !== 'undefined' && MyAppAdmin.ProductEdit &&
                                typeof MyAppAdmin.ProductEdit.isModalOpenForProduct === 'function' &&
                                MyAppAdmin.ProductEdit.isModalOpenForProduct(productId) &&
                                typeof MyAppAdmin.ProductEdit.closeModal === 'function') {
                                MyAppAdmin.ProductEdit.closeModal();
                            }
                        } catch (e) {
                            console.error("Lỗi khi cố gắng đóng ProductEdit modal:", e);
                        }

                        if (typeof $.notify === 'function') {
                            $.notify({ icon: 'fas fa-check', title: '<strong>Thành công!</strong>', message: response.message },{ type: 'success' });
                        } else {
                            alert(response.message);
                        }
                    } else {
                        if (typeof $.notify === 'function') {
                            $.notify({ icon: 'fas fa-exclamation-triangle', title: '<strong>Lỗi!</strong>', message: response.message },{ type: 'danger' });
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error for soft delete: ", status, error, xhr.responseText);
                    if (typeof $.notify === 'function') {
                        $.notify({ icon: 'fas fa-times-circle', title: '<strong>Lỗi AJAX!</strong>', message: 'Không thể gửi yêu cầu xóa. Chi tiết: ' + error },{ type: 'danger' });
                    } else {
                        alert('Lỗi AJAX: Không thể gửi yêu cầu xóa. Vui lòng thử lại.');
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).html('Xóa');
                    if(deleteConfirmModalInstance) deleteConfirmModalInstance.hide();
                    $(deleteConfirmModalEl).removeData('product-id-to-delete'); // Xóa ID đã lưu để tránh dùng lại nhầm
                }
            });
        });
        console.log("Product Delete Handler Initialized.");
    }

    return {
        init: init
    };

})(jQuery);

// Khởi tạo module
$(document).ready(function() {
    if ($('#deleteConfirmModal').length) { // Chỉ init nếu có modal tương ứng
        MyAppAdmin.ProductDeleteHandler.init();
    } else {
        console.warn("ProductDeleteHandler.init: #deleteConfirmModal not found.");
    }
});