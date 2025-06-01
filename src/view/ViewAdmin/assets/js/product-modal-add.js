// Đảm bảo MyAppAdmin namespace đã tồn tại
var MyAppAdmin = window.MyAppAdmin || {};

MyAppAdmin.ProductAdd = (function($) {
    'use strict';

    var addRowModalEl;
    var addRowModalInstance;
    // Các biến khác cho form thêm sản phẩm
    var originalFilesData_Add = { thumbnail: null, otherImages: [] }; // Đổi tên để phân biệt với form edit
    var croppedImageData_Add = { thumbnail: null, otherImages: {} };
    var nextOtherImageTempId_Add = 0;

    const MAX_FILE_SIZE_MB_ADD_FORM = 2; // Đổi tên hằng số
    const MAX_FILE_SIZE_BYTES_ADD_FORM = MAX_FILE_SIZE_MB_ADD_FORM * 1024 * 1024;

    function init() {
        addRowModalEl = document.getElementById('addRowModal');
        if (!addRowModalEl) {
            console.error("#addRowModal not found for Product Add module.");
            return;
        }
        addRowModalInstance = new bootstrap.Modal(addRowModalEl);

        // --------- Xử lý Ảnh Đại Diện (Thumbnail) trong addRowModal ---------
        $('#addThumbnailInput').on('change', function(event) {
            const fileInput = this;
            const errorDiv = $('#thumbnailError');
            errorDiv.text('');
            croppedImageData_Add.thumbnail = null; // Xóa data crop cũ
            $('#croppedThumbnailData').val(''); // Xóa hidden input

            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                if (file.type.startsWith('image/')) {
                    if (file.size > MAX_FILE_SIZE_BYTES_ADD_FORM) {
                        errorDiv.text('Lỗi: Kích thước ảnh > ' + MAX_FILE_SIZE_MB_ADD_FORM + 'MB.');
                        fileInput.value = ''; return;
                    }
                    originalFilesData_Add.thumbnail = file; // Lưu file gốc

                    // Gọi module crop ảnh
                    if (MyAppAdmin.ImageCropper) {
                        MyAppAdmin.ImageCropper.show(URL.createObjectURL(file),
                            { type: 'thumbnail' }, // Ngữ cảnh
                            function(croppedDataURL, context) { // Hàm callback
                                if (context.type === 'thumbnail') {
                                    $('#thumbnailPreview').attr('src', croppedDataURL).show();
                                    $('#thumbnailPlaceholder').hide();
                                    $('#croppedThumbnailData').val(croppedDataURL); // Lưu vào hidden input
                                    croppedImageData_Add.thumbnail = croppedDataURL; // Lưu vào biến JS
                                    $('#deleteThumbnailButton').show();
                                    originalFilesData_Add.thumbnail = null; // Không cần file gốc nữa
                                    $('#addThumbnailInput').val(''); // Reset input file gốc
                                }
                            }
                        );
                    } else {
                        console.error("MyAppAdmin.ImageCropper module not available.");
                        // Fallback: Hiển thị ảnh gốc nếu không có module crop
                        const reader = new FileReader();
                        reader.onload = function(e) { $('#thumbnailPreview').attr('src', e.target.result).show(); $('#thumbnailPlaceholder').hide(); }
                        reader.readAsDataURL(file);
                    }
                } else { /* ... xử lý lỗi type ... */ }
            }
        });

        // Click vào preview box để thay đổi thumbnail (giữ nguyên)
        $('#thumbnailPreviewContainer').on('click', function(e) {
            if (!$(e.target).is('#deleteThumbnailButton') && !$(e.target).closest('#deleteThumbnailButton').length) {
                $('#addThumbnailInput').click();
            }
        }).css('cursor', 'pointer');


        // Nút xóa ảnh đại diện (giữ nguyên, nhưng cập nhật biến JS)
        $('#deleteThumbnailButton').on('click', function(e) {
            e.stopPropagation();
            $('#thumbnailPreview').attr('src', '#').hide();
            $('#thumbnailPlaceholder').show();
            $('#addThumbnailInput').val('');
            $('#croppedThumbnailData').val('');
            originalFilesData_Add.thumbnail = null;
            croppedImageData_Add.thumbnail = null;
            $(this).hide();
            $('#thumbnailError').text('');
        });

        // --------- Xử lý Các Ảnh Khác trong addRowModal ---------
        $('#addProductImagesInput').on('change', function(event) {
            const files = event.target.files;
            const errorDiv = $('#otherImagesError');
            errorDiv.text('');
            let oversizedFiles = [];

            if (files && files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const tempId = `add_other_${nextOtherImageTempId_Add++}_${Date.now()}`;

                    if (file.type.startsWith('image/') && file.size <= MAX_FILE_SIZE_BYTES_ADD_FORM) {
                        originalFilesData_Add.otherImages.push({ file: file, id: tempId, originalName: file.name });

                        const reader = new FileReader();
                        reader.onload = function(e_reader) {
                            const previewDiv = $(`
                                <div class="other-image-preview-item position-relative border p-1" data-temp-id="${tempId}" style="width: 110px; height: 110px; margin-right: 0.5rem; margin-bottom: 0.5rem; display: flex; flex-direction: column; align-items: center; justify-content: space-between;">
                                    <img src="${e_reader.target.result}" style="width: 100%; height: 70px; object-fit: cover; margin-bottom: 5px;">
                                    <div class="btn-group btn-group-sm w-100">
                                        <button type="button" class="btn btn-outline-primary p-0 crop-other-image-btn" title="Cắt ảnh" style="font-size:10px; flex-grow:1;"><i class="fa fa-crop-alt"></i> Cắt</button>
                                        <button type="button" class="btn btn-outline-danger p-0 delete-other-image-btn" title="Xóa ảnh" style="font-size:10px; flex-grow:1;"><i class="fa fa-times"></i> Xóa</button>
                                    </div>
                                </div>
                            `);
                            $('#otherImagesPreviewContainer').append(previewDiv);
                        }
                        reader.readAsDataURL(file);
                    } else { oversizedFiles.push(file.name); }
                }
                if (oversizedFiles.length > 0) { /* ... thông báo lỗi ... */ }
            }
            $(this).val('');
        });

        $('#otherImagesPreviewContainer').on('click', '.crop-other-image-btn', function() {
            const tempId = $(this).closest('.other-image-preview-item').data('temp-id');
            const fileDataItem = originalFilesData_Add.otherImages.find(item => item.id === tempId);

            if (fileDataItem && fileDataItem.file) {
                if (MyAppAdmin.ImageCropper) {
                    MyAppAdmin.ImageCropper.show(URL.createObjectURL(fileDataItem.file),
                        { type: 'otherImage', tempId: tempId, originalFileIndex: null /* có thể thêm index nếu cần */ },
                        function(croppedDataURL, context) { // Callback
                            if (context.type === 'otherImage' && context.tempId) {
                                croppedImageData_Add.otherImages[context.tempId] = croppedDataURL;
                                $(`.other-image-preview-item[data-temp-id="${context.tempId}"] img`).attr('src', croppedDataURL);
                                $(`.other-image-preview-item[data-temp-id="${context.tempId}"] .crop-other-image-btn`)
                                    .html('<i class="fa fa-check"></i> Đã cắt')
                                    .removeClass('btn-outline-primary').addClass('btn-outline-success').prop('disabled', true);

                                // Đánh dấu file gốc đã được xử lý
                                const originalFileIndex = originalFilesData_Add.otherImages.findIndex(item => item.id === context.tempId);
                                if (originalFileIndex > -1) {
                                    originalFilesData_Add.otherImages[originalFileIndex].file = null;
                                }
                            }
                        }
                    );
                }
            } else if (croppedImageData_Add.otherImages[tempId]) { // Re-crop ảnh đã crop
                if (MyAppAdmin.ImageCropper) {
                    MyAppAdmin.ImageCropper.show(croppedImageData_Add.otherImages[tempId], // Nguồn là base64 đã crop
                        { type: 'otherImage', tempId: tempId, originalFileIndex: null },
                        function(croppedDataURL, context) { /* ... như trên ... */ }
                    );
                }
            }
        });

        $('#otherImagesPreviewContainer').on('click', '.delete-other-image-btn', function() {
            const itemDiv = $(this).closest('.other-image-preview-item');
            const tempId = itemDiv.data('temp-id');
            itemDiv.remove();
            originalFilesData_Add.otherImages = originalFilesData_Add.otherImages.filter(item => item.id !== tempId);
            delete croppedImageData_Add.otherImages[tempId];
        });

        // Dọn dẹp form khi modal "Thêm sản phẩm" được đóng (giữ nguyên, cập nhật biến)
        if (addRowModalEl) {
            addRowModalEl.addEventListener('hidden.bs.modal', function (event) {
                $('#addProductForm')[0].reset();
                // ... (reset các trường khác) ...
                originalFilesData_Add = { thumbnail: null, otherImages: [] };
                croppedImageData_Add = { thumbnail: null, otherImages: {} };
                nextOtherImageTempId_Add = 0;
            });
        }

        // Xử lý nút "Thêm mới" (submitAddProductButton) (giữ nguyên logic tạo FormData, cập nhật cách lấy ảnh)
        $('#submitAddProductButton').on('click', function() {

            var $button = $(this); // <<< ***** THÊM DÒNG NÀY *****

            var addProductForm = document.getElementById('addProductForm');
            if (!addProductForm.checkValidity()) {
                addProductForm.reportValidity();
                return;
            }

            var productNameValue = $('#addProductName').val();
            console.log('Giá trị Tên Sản Phẩm từ input (#addProductName):', productNameValue);

            if (!productNameValue || productNameValue.trim() === '') {
                console.error('Lỗi JS: Tên sản phẩm rỗng ở client-side.');
                $('#addProductName').focus();
                if (typeof $.notify === 'function') {
                    $.notify({ message: 'Tên sản phẩm không được để trống (kiểm tra client).' },{ type: 'warning' });
                } else {
                    alert('Tên sản phẩm không được để trống (kiểm tra client).');
                }
                return;
            }

            // Ngay cả khi có giá trị ở client, kiểm tra FormData một lần nữa
            var tempFormDataForNameCheck = new FormData(addProductForm);
            var nameFromFormData = tempFormDataForNameCheck.get('name');
            console.log("Giá trị 'name' lấy trực tiếp từ FormData:", nameFromFormData);

            if (!nameFromFormData || nameFromFormData.trim() === '') {
                console.error('Lỗi JS: Tên sản phẩm trong FormData vẫn rỗng.');
                if (typeof $.notify === 'function') {
                    $.notify({ message: 'Lỗi: Tên sản phẩm trong FormData rỗng. Vui lòng kiểm tra lại.' },{ type: 'danger' });
                } else {
                    alert('Lỗi: Tên sản phẩm trong FormData rỗng. Vui lòng kiểm tra lại.');
                }
                return;
            }


            // Chỉ tiếp tục nếu tên sản phẩm có giá trị
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang thêm...');

            var formData = new FormData(addProductForm); // Tạo FormData ở đây, sau khi đã validate

            // ... (Phần append ảnh vào formData giữ nguyên) ...
            if (croppedImageData_Add.thumbnail) {
                formData.append('cropped_thumbnail_data', croppedImageData_Add.thumbnail);
            }
            Object.keys(croppedImageData_Add.otherImages).forEach(function(tempId) {
                if (croppedImageData_Add.otherImages[tempId]) {
                    formData.append('product_other_images_cropped_data[]', croppedImageData_Add.otherImages[tempId]);
                }
            });
            originalFilesData_Add.otherImages.forEach(function(item) {
                if (item.file && !croppedImageData_Add.otherImages[item.id]) { // Chỉ gửi file gốc nếu nó chưa được crop
                    formData.append('product_images_original[]', item.file, item.originalName);
                }
            });


            console.log('--- FormData chuẩn bị gửi đi (sau khi kiểm tra tên) ---');
            for (var pair of formData.entries()) {
                if (pair[1] instanceof File) { console.log(pair[0] + ': File -', pair[1].name); }
                else if (typeof pair[1] === 'string' && pair[1].startsWith('data:image')) { console.log(pair[0] + ': Base64 Image Data');}
                else { console.log(pair[0] + ': ', pair[1]); }
            }

            // Log giá trị 'name' từ FormData
            console.log("Giá trị 'name' trong FormData:", formData.get('name'));
            $.ajax({
                url: 'index.php?ctrl=adminproduct&act=ajaxAddProduct', // URL đến action controller mới
                type: 'POST',
                data: formData,
                processData: false, // Quan trọng: không xử lý dữ liệu (vì là FormData)
                contentType: false, // Quan trọng: không đặt header contentType (để browser tự đặt với boundary)
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (typeof $.notify === 'function') {
                            $.notify({ icon: 'fas fa-check', title: '<strong>Thành công!</strong>', message: response.message },{ type: 'success' });
                        } else {
                            alert(response.message);
                        }
                        if (addRowModalInstance) addRowModalInstance.hide(); // Tự động trigger 'hidden.bs.modal' để reset form
                        // TODO: Tải lại danh sách sản phẩm hoặc thêm sản phẩm mới vào bảng bằng JS
                        // Ví dụ đơn giản: location.reload(); (tải lại cả trang)
                        setTimeout(function(){ location.reload(); }, 1500);
                    } else {
                        if (typeof $.notify === 'function') {
                            $.notify({ icon: 'fas fa-exclamation-triangle', title: '<strong>Lỗi!</strong>', message: response.message },{ type: 'danger' });
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error Add Product: ", status, error, xhr.responseText);
                    let errorMsg = 'Lỗi AJAX: Không thể thêm sản phẩm. Vui lòng thử lại.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        // Cố gắng parse lỗi nếu có
                        try {
                            let err = JSON.parse(xhr.responseText);
                            if(err.message) errorMsg = err.message;
                        } catch(e){}
                    }

                    if (typeof $.notify === 'function') {
                        $.notify({ icon: 'fas fa-times-circle', title: '<strong>Lỗi AJAX!</strong>', message: errorMsg },{ type: 'danger' });
                    } else {
                        alert(errorMsg);
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).html('Thêm mới');
                }
            });
        });
    }

    // Public init
    return {
        init: init
    };

})(jQuery);

// Khởi tạo module ProductAdd
$(document).ready(function() {
    MyAppAdmin.ProductAdd.init();
});