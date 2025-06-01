var MyAppAdmin = window.MyAppAdmin || {};

MyAppAdmin.ProductEdit = (function($) {
    'use strict';

    // --- DOM Elements & Modal Instances (khởi tạo trong init) ---
    var productEditModalEl, productEditModalInstance;
    var saveConfirmModalInstance, deleteConfirmModalInstance, discardConfirmModalInstance; // Bootstrap 5 modal instances

    // --- State Variables cho Form Sửa ---
    var originalEditFormData = {}; // Dữ liệu gốc của các trường input text/select khi modal mở
    var currentEditProductServerData = null; // Toàn bộ dữ liệu sản phẩm gốc từ server (bao gồm images_data)
    var editFormIsDirty = false; // Cờ báo hiệu form sửa có thay đổi

    // Trạng thái ảnh cho form sửa
    var editCroppedThumbnailData = null;    // Base64 thumbnail mới đã crop
    var editThumbnailAction = 'keep';       // 'keep', 'replace_new_cropped', 'delete_existing'
    var editExistingThumbnailInfo = { id: null, url: null }; // Thông tin thumbnail hiện tại từ DB

    var editOtherImages_NewFilesData = []; // Mảng các { tempId: '...', file: File, originalName: '...', croppedDataUrl: null } cho ảnh mới thêm
    var editOtherImages_ToDeleteDbIds = []; // Mảng các image_db_id của ảnh hiện có được đánh dấu xóa
    var editOtherImages_UpdatedCroppedData = {}; // { image_db_id: base64Data } cho ảnh hiện có được crop lại
    var editOtherImages_NewCroppedData = {};

    var nextEditOtherImageTempId = 0;
    var product_image_base_url_js = ''; // Sẽ được lấy từ global scope hoặc config

    const MAX_FILE_SIZE_EDIT_FORM = 2 * 1024 * 1024; // 2MB


    // Hàm reset toàn bộ trạng thái của modal sửa
    function resetEditModalState() {
        $('#productEditForm')[0].reset();
        $('#modalEditProductCategory').val(''); // Reset select box

        $('#editThumbnailPreview').attr('src', '#').hide();
        $('#editThumbnailPlaceholder').show();
        $('#editDeleteThumbnailButton').hide();
        $('#editThumbnailError').text('');
        $('#editCroppedThumbnailData').val(''); // Hidden input cho base64 thumbnail mới
        $('#editThumbnailAction').val('keep');
        $('#editExistingThumbnailId').val('');

        $('#editOtherImagesPreviewContainer').empty().html('<span id="editOtherImagesPlaceholder" class="text-muted" style="display: block; width:100%; text-align:center;">Chưa có ảnh nào khác hoặc chưa thêm ảnh mới.</span>');
        $('#editOtherImagesError').text('');
        $('#editAddMoreImagesInput').val('');

        originalEditFormData = {};
        currentEditProductServerData = null;
        editFormIsDirty = false;

        editCroppedThumbnailData = null;
        editThumbnailAction = 'keep';
        editExistingThumbnailInfo = { id: null, url: null };

        editOtherImages_NewFilesData = [];
        editOtherImages_NewCroppedData = {};
        editOtherImages_ToDeleteDbIds = [];
        editOtherImages_UpdatedCroppedData = {};
        nextEditOtherImageTempId = 0;

        $('#modalOpenSaveChangesConfirmButton').prop('disabled', true);
        console.log("Edit Product form state reset.");
    }

    // Hàm kiểm tra thay đổi trên form sửa
    function checkEditFormChanges() {
        editFormIsDirty = false;
        // So sánh các trường input text/select/textarea
        $('#productEditForm .form-field-edit').each(function() {
            var $field = $(this);
            var fieldName = $field.attr('name');
            if (!originalEditFormData.hasOwnProperty(fieldName)) return true; // Bỏ qua nếu trường không có trong dữ liệu gốc (vd: file input)

            var currentValue = $field.val();
            var originalValue = originalEditFormData[fieldName] === null || typeof originalEditFormData[fieldName] === 'undefined' ? '' : originalEditFormData[fieldName].toString();
            currentValue = currentValue === null || typeof currentValue === 'undefined' ? '' : currentValue.toString();

            if (currentValue !== originalValue) {
                // console.log("Change detected in field:", fieldName, "| Original:", originalValue, "| Current:", currentValue);
                editFormIsDirty = true; return false;
            }
        });

        // Kiểm tra thay đổi về ảnh
        if (editThumbnailAction !== 'keep' ||
            editCroppedThumbnailData !== null ||
            editOtherImages_ToDeleteDbIds.length > 0 ||
            editOtherImages_NewFilesData.length > 0 || // Có file mới được thêm (dù đã crop hay chưa)
            Object.keys(editOtherImages_UpdatedCroppedData).length > 0) {
            // console.log("Image change detected:", editThumbnailAction, editCroppedThumbnailData, editOtherImages_ToDeleteDbIds, editOtherImages_NewFilesData, editOtherImages_UpdatedCroppedData);
            editFormIsDirty = true;
        }
        // console.log("editFormIsDirty:", editFormIsDirty);
        $('#modalOpenSaveChangesConfirmButton').prop('disabled', !editFormIsDirty);
    }

    // Hàm hiển thị các ảnh khác hiện có
    function displayExistingOtherImages(imagesData) {
        const container = $('#editOtherImagesPreviewContainer');
        const placeholder = $('#editOtherImagesPlaceholder');
        container.empty(); // Xóa placeholder hoặc các ảnh cũ
        let hasOtherImages = false;

        imagesData.forEach(function(img) {
            if (parseInt(img.is_thumbnail) === 0 && img.image_url) {
                hasOtherImages = true;
                // Kiểm tra xem ảnh này có bị đánh dấu xóa không
                const isMarkedForDelete = editOtherImages_ToDeleteDbIds.includes(img.image_db_id);
                const opacityStyle = isMarkedForDelete ? 'opacity: 0.5; text-decoration: line-through;' : '';
                const buttonDisabled = isMarkedForDelete ? 'disabled' : '';

                const previewDiv = $(`
                    <div class="edit-other-image-item position-relative border p-1" data-image-db-id="${img.image_db_id}" style="width: 110px; height: 110px; margin-right: 0.5rem; margin-bottom: 0.5rem; display: flex; flex-direction: column; align-items: center; justify-content: space-between; ${opacityStyle}">
                        <img src="${product_image_base_url_js + img.image_url}" style="width: 100%; height: 70px; object-fit: cover; margin-bottom: 5px;" alt="Ảnh sản phẩm">
                        <div class="btn-group btn-group-sm w-100">
                            <button type="button" class="btn btn-outline-primary p-0 edit-crop-existing-other-image-btn" title="Cắt lại ảnh" style="font-size:10px; flex-grow:1;" ${buttonDisabled}><i class="fa fa-crop-alt"></i> Cắt</button>
                            <button type="button" class="btn btn-outline-danger p-0 edit-delete-existing-other-image-btn" title="Xóa ảnh này" style="font-size:10px; flex-grow:1;" ${buttonDisabled}><i class="fa fa-times"></i> Xóa</button>
                        </div>
                    </div>
                `);
                container.append(previewDiv);
            }
        });

        if (!hasOtherImages && editOtherImages_NewFilesData.length === 0) {
            container.html('<span id="editOtherImagesPlaceholder" class="text-muted" style="display: block; width:100%; text-align:center;">Chưa có ảnh nào khác hoặc chưa thêm ảnh mới.</span>');
        } else {
            placeholder.hide();
        }
    }

    // Hàm mở modal sửa và tải dữ liệu
    function populateAndOpenEditModal(productId) {
        resetEditModalState(); // Dọn dẹp trạng thái cũ
        $('#modalOpenSaveChangesConfirmButton').prop('disabled', true);

        $.ajax({
            url: 'index.php?ctrl=adminproduct&act=ajaxGetProductDetailsForEdit',
            type: 'GET',
            data: { id: productId },
            dataType: 'json',
            beforeSend: function() { /* TODO: Show loading indicator */ },
            success: function(response) {
                if (response.success && response.data) {
                    currentEditProductServerData = response.data;
                    originalEditFormData = {};

                    $('#modalEditProductId').val(currentEditProductServerData.id);
                    $('#modalDisplayProductId').text(currentEditProductServerData.id);

                    var fields = ['name', 'price', 'discount_price', 'stock', 'category_id', 'brand', 'location', 'description'];
                    fields.forEach(function(field) {
                        var val = currentEditProductServerData[field] !== null ? currentEditProductServerData[field] : '';
                        $('#productEditForm [name="' + field + '"]').val(val);
                        originalEditFormData[field] = val.toString();
                    });
                    $('#modalDisplaySoldCount').text(currentEditProductServerData.sold_quantity || 0);

                    // Xử lý thumbnail
                    var thumbnail = currentEditProductServerData.images_data ? currentEditProductServerData.images_data.find(img => parseInt(img.is_thumbnail) === 1) : null;
                    if (thumbnail && thumbnail.image_url) {
                        $('#editThumbnailPreview').attr('src', product_image_base_url_js + thumbnail.image_url).show();
                        $('#editThumbnailPlaceholder').hide();
                        $('#editDeleteThumbnailButton').show();
                        editExistingThumbnailInfo = { id: thumbnail.image_db_id, url: thumbnail.image_url };
                        editThumbnailAction = 'keep';
                        $('#editExistingThumbnailId').val(thumbnail.image_db_id);
                    } else {
                        // Không có thumbnail, chuẩn bị cho việc thêm mới
                        editThumbnailAction = 'none_or_new';
                    }

                    // Hiển thị các ảnh khác
                    displayExistingOtherImages(currentEditProductServerData.images_data || []);

                    if(productEditModalInstance) productEditModalInstance.show();
                    editFormIsDirty = false;
                    $('#modalOpenSaveChangesConfirmButton').prop('disabled', true);
                } else {
                    $.notify({ message: response.message || "Không thể tải chi tiết sản phẩm." },{ type: 'danger' });
                }
            },
            error: function() { /* ... */ },
            complete: function() { /* TODO: Hide loading indicator */ }
        });
    }

    function attachEditModalEventListeners() {
        // Mở modal sửa từ bảng
        $('#add-row tbody').on('click', 'tr.product-row-clickable', function() {
            var productId = $(this).data('id');
            if (productId) populateAndOpenEditModal(productId);
        });
        $('#add-row tbody').on('click', '.edit-product-button', function(event) {
            event.stopPropagation();
            var productId = $(this).closest('tr.product-row-clickable').data('id');
            if (productId) populateAndOpenEditModal(productId);
        });

        // Đóng modal sửa
        $('#closeProductEditModal, #closeProductEditModalButton').on('click', function() {
            if (editFormIsDirty) {
                if(discardConfirmModalInstance) discardConfirmModalInstance.show();
            } else {
                if(productEditModalInstance) productEditModalInstance.hide();
            }
        });
        // (Xử lý các nút trong discardConfirmModal đã có ở file footer_scripts.php cũ, có thể chuyển vào đây hoặc file common)
        // Giả sử discardConfirmModal được quản lý chung và sự kiện click của #confirmDiscardButton sẽ gọi productEditModal.hide()

        // Input/change trên form sửa
        $('#productEditForm').on('input change', '.form-field-edit, #editThumbnailInput, #editAddMoreImagesInput', function() {
            // Gọi checkEditFormChanges sau một khoảng trễ nhỏ để giá trị file input được cập nhật
            setTimeout(checkEditFormChanges, 50);
        });

        // --- Xử lý ảnh trong Modal Sửa ---
        // Thay đổi/Chọn Thumbnail
        $('#editThumbnailPreviewContainer').on('click', function(e) {
            if (!$(e.target).is('#editDeleteThumbnailButton') && !$(e.target).closest('#editDeleteThumbnailButton').length) {
                $('#editThumbnailInput').click();
            }
        });

        $('#editThumbnailInput').on('change', function(event) {
            const fileInput = this;
            const errorDiv = $('#editThumbnailError');
            errorDiv.text('');

            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                if (file.type.startsWith('image/') && file.size <= MAX_FILE_SIZE_EDIT_FORM) {
                    if (MyAppAdmin.ImageCropper) {
                        MyAppAdmin.ImageCropper.show(URL.createObjectURL(file),
                            { type: 'editThumbnail' },
                            function(croppedDataURL, context) {
                                if (context.type === 'editThumbnail') {
                                    $('#editThumbnailPreview').attr('src', croppedDataURL).show();
                                    $('#editThumbnailPlaceholder').hide();
                                    editCroppedThumbnailData = croppedDataURL; // Lưu base64
                                    editThumbnailAction = 'replace_new_cropped';
                                    $('#editDeleteThumbnailButton').show();
                                    checkEditFormChanges();
                                }
                            }
                        );
                    }
                } else { /* Xử lý lỗi size/type */
                    errorDiv.text(file.size > MAX_FILE_SIZE_EDIT_FORM ? 'Ảnh > 2MB' : 'File không phải ảnh');
                    $(this).val('');
                }
            }
        });

        // Xóa Thumbnail
        $('#editDeleteThumbnailButton').on('click', function(e) {
            e.stopPropagation();
            $('#editThumbnailPreview').attr('src', '#').hide();
            $('#editThumbnailPlaceholder').show();
            $('#editThumbnailInput').val('');
            editCroppedThumbnailData = null;
            editThumbnailAction = 'delete_existing';
            $(this).hide();
            checkEditFormChanges();
        });

        // Thêm Ảnh Khác Mới
        $('#editAddMoreImagesInput').on('change', function(event) {
            const files = event.target.files;
            const container = $('#editOtherImagesPreviewContainer');
            const errorDiv = $('#editOtherImagesError');
            errorDiv.text('');

            if (files && files.length > 0) {
                $('#editOtherImagesPlaceholder').hide();
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const tempId = `edit_new_${Date.now()}_${i}`;
                    if (file.type.startsWith('image/') && file.size <= MAX_FILE_SIZE_EDIT_FORM) {
                        editOtherImages_NewFilesData.push({ file: file, id: tempId, originalName: file.name, croppedDataUrl: null });
                        const reader = new FileReader();
                        reader.onload = function(e_reader) {
                            const previewDiv = $(`
                                <div class="edit-new-other-image-item position-relative border p-1" data-temp-id="${tempId}" style="width: 110px; height: 110px; margin-right: 0.5rem; margin-bottom: 0.5rem; display: flex; flex-direction: column; align-items: center; justify-content: space-between;">
                                    <img src="${e_reader.target.result}" style="width: 100%; height: 70px; object-fit: cover; margin-bottom: 5px;" alt="Ảnh mới">
                                    <div class="btn-group btn-group-sm w-100">
                                        <button type="button" class="btn btn-outline-primary p-0 edit-crop-new-other-image-btn" title="Cắt ảnh" style="font-size:10px; flex-grow:1;"><i class="fa fa-crop-alt"></i> Cắt</button>
                                        <button type="button" class="btn btn-outline-danger p-0 edit-delete-new-other-image-btn" title="Xóa ảnh" style="font-size:10px; flex-grow:1;"><i class="fa fa-times"></i> Xóa</button>
                                    </div>
                                </div>`);
                            container.append(previewDiv);
                        }
                        reader.readAsDataURL(file);
                    } else { /* Xử lý lỗi file */ }
                }
                checkEditFormChanges();
            }
            $(this).val('');
        });

        // Xóa Ảnh Mới Thêm (trong modal sửa)
        $('#editOtherImagesPreviewContainer').on('click', '.edit-delete-new-other-image-btn', function() {
            const itemDiv = $(this).closest('.edit-new-other-image-item');
            const tempId = itemDiv.data('temp-id');
            itemDiv.remove();
            editOtherImages_NewFilesData = editOtherImages_NewFilesData.filter(item => item.id !== tempId);
            delete editOtherImages_NewCroppedData[tempId]; // Xóa nếu đã từng crop
            checkEditFormChanges();
            if ($('#editOtherImagesPreviewContainer').children().not('#editOtherImagesPlaceholder').length === 0) {
                $('#editOtherImagesPlaceholder').show();
            }
        });

        // Xóa Ảnh Hiện Có (trong modal sửa)
        $('#editOtherImagesPreviewContainer').on('click', '.edit-delete-existing-other-image-btn', function() {
            const itemDiv = $(this).closest('.edit-other-image-item');
            const imageDbId = parseInt(itemDiv.data('image-db-id')); // Đảm bảo là số
            if (imageDbId && !editOtherImages_ToDeleteDbIds.includes(imageDbId)) {
                editOtherImages_ToDeleteDbIds.push(imageDbId);
            }
            itemDiv.css('opacity', '0.4').addClass('marked-for-deletion');
            $(this).hide();
            itemDiv.find('.edit-crop-existing-other-image-btn').hide();
            checkEditFormChanges();
        });

        // Crop Ảnh Mới Thêm (trong modal sửa)
        $('#editOtherImagesPreviewContainer').on('click', '.edit-crop-new-other-image-btn', function() {
            const tempId = $(this).closest('.edit-new-other-image-item').data('temp-id');
            const fileDataItem = editOtherImages_NewFilesData.find(item => item.id === tempId);
            if (fileDataItem && fileDataItem.file) { // Crop từ file gốc
                if (MyAppAdmin.ImageCropper) {
                    MyAppAdmin.ImageCropper.show(URL.createObjectURL(fileDataItem.file),
                        { type: 'editNewOtherImage', tempId: tempId },
                        function(croppedDataURL, context) {
                            if (context.type === 'editNewOtherImage' && context.tempId) {
                                editOtherImages_NewCroppedData[context.tempId] = croppedDataURL;
                                $(`.edit-new-other-image-item[data-temp-id="${context.tempId}"] img`).attr('src', croppedDataURL);
                                $(`.edit-new-other-image-item[data-temp-id="${context.tempId}"] .edit-crop-new-other-image-btn`).html('<i class="fa fa-check"></i> Đã cắt').prop('disabled',true);
                                // Đánh dấu file gốc đã được xử lý (không cần gửi nữa)
                                const idx = editOtherImages_NewFilesData.findIndex(item => item.id === context.tempId);
                                if(idx > -1) editOtherImages_NewFilesData[idx].file = null;
                                checkEditFormChanges();
                            }
                        }
                    );
                }
            } else if (editOtherImages_NewCroppedData[tempId]) { // Re-crop ảnh đã crop
                if (MyAppAdmin.ImageCropper) {
                    MyAppAdmin.ImageCropper.show(editOtherImages_NewCroppedData[tempId],
                        { type: 'editNewOtherImage', tempId: tempId },
                        function(croppedDataURL, context) { /* ... như trên ... */ }
                    );
                }
            }
        });

        // Crop Ảnh Hiện Có (trong modal sửa)
        $('#editOtherImagesPreviewContainer').on('click', '.edit-crop-existing-other-image-btn', function() {
            const itemDiv = $(this).closest('.edit-other-image-item');
            const imageDbId = itemDiv.data('image-db-id');
            const imageUrl = itemDiv.find('img').attr('src'); // Đây là URL đầy đủ (đã có base_path)

            if (imageDbId && imageUrl) {
                if (MyAppAdmin.ImageCropper) {
                    MyAppAdmin.ImageCropper.show(imageUrl, // Truyền URL đầy đủ cho cropper
                        { type: 'editExistingOtherImage', imageDbId: imageDbId },
                        function(croppedDataURL, context) {
                            if (context.type === 'editExistingOtherImage' && context.imageDbId) {
                                editOtherImages_UpdatedCroppedData[context.imageDbId] = croppedDataURL;
                                $(`.edit-other-image-item[data-image-db-id="${context.imageDbId}"] img`).attr('src', croppedDataURL);
                                $(`.edit-other-image-item[data-image-db-id="${context.imageDbId}"] .edit-crop-existing-other-image-btn`).html('<i class="fa fa-check"></i> Đã cắt lại').prop('disabled',true);
                                checkEditFormChanges();
                            }
                        }
                    );
                }
            }
        });

        $('#confirmDiscardButton').on('click', function() {
            console.log("Xác nhận hủy thay đổi.");
            if(discardConfirmModalInstance) {
                discardConfirmModalInstance.hide();
            }

            // Reset form về giá trị gốc từ currentEditProductServerData
            if (currentEditProductServerData) {
                console.log("Đang reset form về dữ liệu gốc từ server.");
                originalEditFormData = {}; // Reset lại object này để checkFormChanges hoạt động đúng

                $('#modalEditProductId').val(currentEditProductServerData.id);
                // Không cần reset modalDisplayProductId vì nó không đổi

                var fieldsToReset = ['name', 'price', 'discount_price', 'stock', 'category_id', 'brand', 'location', 'description'];
                fieldsToReset.forEach(function(fieldName) {
                    var originalValue = currentEditProductServerData[fieldName] !== null ? currentEditProductServerData[fieldName] : '';
                    $('#productEditForm [name="' + fieldName + '"]').val(originalValue);
                    originalEditFormData[fieldName] = originalValue.toString(); // Cập nhật lại cho lần checkFormChanges sau
                });

                // Reset ảnh thumbnail về trạng thái gốc
                var thumbnail = currentEditProductServerData.images_data ? currentEditProductServerData.images_data.find(img => parseInt(img.is_thumbnail) === 1) : null;
                if (thumbnail && thumbnail.image_url) {
                    $('#editThumbnailPreview').attr('src', product_image_base_url_js + thumbnail.image_url).show();
                    $('#editThumbnailPlaceholder').hide();
                    $('#editDeleteThumbnailButton').show(); // Hiển thị lại nút xóa nếu thumbnail gốc tồn tại
                    editExistingThumbnailInfo = { id: thumbnail.image_db_id, url: thumbnail.image_url };
                    $('#editExistingThumbnailId').val(thumbnail.image_db_id);

                } else {
                    $('#editThumbnailPreview').attr('src', '#').hide();
                    $('#editThumbnailPlaceholder').show();
                    $('#editDeleteThumbnailButton').hide();
                    editExistingThumbnailInfo = { id: null, url: null };
                    $('#editExistingThumbnailId').val('');
                }
                $('#editThumbnailInput').val(''); // Reset input file
                editCroppedThumbnailData = null; // Xóa dữ liệu crop mới
                $('#editCroppedThumbnailData').val('');
                editThumbnailAction = 'keep'; // Đặt lại hành động thumbnail
                $('#editThumbnailAction').val('keep');
                $('#editThumbnailError').text('');


                // Reset và hiển thị lại các ảnh khác về trạng thái gốc
                editOtherImages_NewFilesData = [];
                editOtherImages_NewCroppedData = {};
                editOtherImages_ToDeleteDbIds = [];
                editOtherImages_UpdatedCroppedData = {};
                $('#editAddMoreImagesInput').val('');
                $('#editOtherImagesError').text('');
                displayExistingOtherImages(currentEditProductServerData.images_data || []);

            } else {
                // Nếu không có dữ liệu server gốc, chỉ reset form đơn thuần
                $('#productEditForm')[0].reset();
                console.warn("Không có currentEditProductServerData để reset form chi tiết, chỉ reset field.");
            }

            editFormIsDirty = false;
            $('#modalOpenSaveChangesConfirmButton').prop('disabled', true);

            if (productEditModalInstance) {
                console.log("Đang đóng productEditModal.");
                productEditModalInstance.hide();
            } else {
                console.error("productEditModalInstance không tồn tại khi cố gắng đóng.");
            }
        });

        // Nút "Lưu thay đổi" (mở modal xác nhận)
        $('#modalOpenSaveChangesConfirmButton').on('click', function() {
            if (!$(this).prop('disabled') && editFormIsDirty) {
                if(saveConfirmModalInstance) saveConfirmModalInstance.show();
            }
        });

        // Nút "Lưu" cuối cùng trong modal xác nhận
        $('#confirmSaveChangesButton').on('click', function() {
            var productId = $('#modalEditProductId').val();
            var $button = $(this);
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang lưu...');

            var formData = new FormData();
            formData.append('id', productId); // Luôn gửi ID sản phẩm

            // Lấy các trường input text/select/textarea
            $('#productEditForm').find('input[type="text"], input[type="number"], input[type="hidden"]:not(#editCroppedThumbnailData):not(#editThumbnailAction):not(#editExistingThumbnailId), select, textarea').each(function() {
                if ($(this).attr('name') && $(this).attr('name') !== 'id') { // ID đã được thêm riêng
                    formData.append($(this).attr('name'), $(this).val());
                }
            });

            // 1. Thông tin Thumbnail
            formData.append('thumbnail_action', editThumbnailAction); // 'keep', 'replace_new_cropped', 'delete_existing'
            if (editThumbnailAction === 'replace_new_cropped' && editCroppedThumbnailData) {
                formData.append('cropped_thumbnail_data', editCroppedThumbnailData); // base64
                if (editExistingThumbnailInfo.id) { // Nếu thay thế thumbnail cũ thì gửi ID cũ để xóa file
                    formData.append('existing_thumbnail_id', editExistingThumbnailInfo.id);
                }
            } else if (editThumbnailAction === 'delete_existing' && editExistingThumbnailInfo.id) {
                formData.append('existing_thumbnail_id_to_delete', editExistingThumbnailInfo.id);
            }


            // 2. Các ảnh khác hiện có bị xóa
            if (editOtherImages_ToDeleteDbIds.length > 0) {
                editOtherImages_ToDeleteDbIds.forEach(function(id) {
                    formData.append('delete_other_image_ids[]', id);
                });
            }

            // 3. Các ảnh khác hiện có được crop lại (gửi dưới dạng {image_db_id: base64data})
            // Backend sẽ nhận dạng updated_other_images_cropped_data[THE_ID_FROM_DB] = BASE64_DATA
            Object.keys(editOtherImages_UpdatedCroppedData).forEach(function(dbId) {
                if (editOtherImages_UpdatedCroppedData[dbId]) {
                    formData.append('updated_other_images_cropped_data[' + dbId + ']', editOtherImages_UpdatedCroppedData[dbId]);
                }
            });

            // 4. Các ảnh khác MỚI THÊM
            // Ảnh mới đã crop (base64)
            Object.keys(editOtherImages_NewCroppedData).forEach(function(tempId) {
                if (editOtherImages_NewCroppedData[tempId]) {
                    formData.append('new_other_images_cropped_data[]', editOtherImages_NewCroppedData[tempId]);
                }
            });
            // Ảnh mới chưa crop (file gốc)
            editOtherImages_NewFilesData.forEach(function(item) {
                if (item.file) { // Chỉ gửi nếu file object còn tồn tại (chưa bị crop và làm null)
                    formData.append('new_other_images_original[]', item.file, item.originalName);
                }
            });

            // Log FormData để debug
            console.log("--- FormData for Product Update (Sending to Backend) ---");
            for(var pair of formData.entries()) {
                if (pair[1] instanceof File) { console.log(pair[0] + ": File - " + pair[1].name); }
                else if (typeof pair[1] === 'string' && pair[1].startsWith('data:image')) { console.log(pair[0] + ": Base64 Image"); }
                else { console.log(pair[0] + ": " + pair[1]); }
            }
            console.log("---------------------------------");


            $.ajax({
                url: 'index.php?ctrl=adminproduct&act=ajaxUpdateProduct',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if(saveConfirmModalInstance) saveConfirmModalInstance.hide();
                        if(productEditModalInstance) productEditModalInstance.hide();
                        $.notify({ message: response.message },{ type: 'success' });
                        setTimeout(function(){ location.reload(); }, 1500);
                    } else {
                        $.notify({ message: response.message || "Lỗi cập nhật sản phẩm." },{ type: 'danger' });
                    }
                },
                error: function(xhr, status, error) { /* ... xử lý lỗi ... */ },
                complete: function() { /* ... reset nút ... */ }
            });
        });




        console.log("Product Edit Module Initialized.");
    } // Kết thúc hàm init của ProductEdit

    // --- HÀM KHỞI TẠO CHÍNH CỦA MODULE ---
    function mainInit() {
        productEditModalEl = document.getElementById('productEditModal');
        // Khởi tạo các instance modal liên quan đến form sửa
        if (productEditModalEl) {
            productEditModalInstance = new bootstrap.Modal(productEditModalEl);
            // Sự kiện khi modal sửa được đóng hoàn toàn
            productEditModalEl.addEventListener('hidden.bs.modal', function () {
                resetEditModalState(); // Gọi hàm reset state
            });
        } else {
            console.error("#productEditModal not found. Edit functionality will be affected.");
            return; // Không thể tiếp tục nếu modal chính không có
        }

        var saveConfirmModalEl = document.getElementById('saveConfirmModal');
        if (saveConfirmModalEl) saveConfirmModalInstance = new bootstrap.Modal(saveConfirmModalEl);
        else console.warn("#saveConfirmModal for edit not found.");

        // deleteConfirmModalInstance và discardConfirmModalInstance có thể được khởi tạo ở đây
        // hoặc trong admin-common.js nếu chúng thực sự dùng chung cho nhiều module.
        // Để module này tự quản lý, bạn có thể khởi tạo chúng ở đây:
        var localDeleteConfirmModalEl = document.getElementById('deleteConfirmModal');
        if(localDeleteConfirmModalEl) deleteConfirmModalInstance = new bootstrap.Modal(localDeleteConfirmModalEl);
        else console.warn("#deleteConfirmModal for edit/delete handler not found.");

        var localDiscardConfirmModalEl = document.getElementById('discardConfirmModal');
        if(localDiscardConfirmModalEl) discardConfirmModalInstance = new bootstrap.Modal(localDiscardConfirmModalEl);
        else console.warn("#discardConfirmModal for edit not found.");


        // Lấy base URL cho ảnh sản phẩm
        // Đảm bảo biến product_image_base_url_js đã được định nghĩa ở phạm vi toàn cục
        // (ví dụ trong header.php hoặc admin-common.js)
        if (window.MyAppAdmin && MyAppAdmin.config && MyAppAdmin.config.productImageBaseUrl) {
            product_image_base_url_js = MyAppAdmin.config.productImageBaseUrl;
        } else {
            console.warn("product_image_base_url_js not found in MyAppAdmin.config. Using fallback.");
            product_image_base_url_js = '../../view/ViewUser/ProductImage/'; // Đường dẫn fallback
        }

        attachEditModalEventListeners(); // Quan trọng: Gọi hàm để gắn các sự kiện
        console.log("Product Edit Module Initialized and Event Listeners Attached.");
    }

    // Public init
    // Public methods / properties
    return {
        init: mainInit, // Trả về hàm mainInit để được gọi từ bên ngoài
        // Thêm các phương thức public khác nếu cần, ví dụ:
        isModalOpenForProduct: function(productId) {
            return productEditModalInstance && $(productEditModalEl).hasClass('show') && $('#modalEditProductId').val() == productId;
        },
        closeModal: function() {
            if (productEditModalInstance) {
                editFormIsDirty = false; // Giả sử khi đóng từ bên ngoài là đã xử lý hoặc hủy thay đổi
                productEditModalInstance.hide(); // Sẽ trigger 'hidden.bs.modal' và reset state
            }
        }
        // getCurrentProductName: function() { ... } // Nếu cần
    };

})(jQuery);

// Khởi tạo các module
$(document).ready(function() {
    if (typeof MyAppAdmin !== 'undefined' && MyAppAdmin.ProductEdit && $('#productEditModal').length && MyAppAdmin.ImageCropper) {
        MyAppAdmin.ProductEdit.init();
    } else {
        if(!$('#productEditModal').length) console.error("ProductEdit.init: HTML element #productEditModal not found.");
        if(typeof MyAppAdmin === 'undefined' || !MyAppAdmin.ProductEdit) console.error("ProductEdit.init: MyAppAdmin.ProductEdit is not defined. Ensure the module script is loaded.");
        if(typeof MyAppAdmin === 'undefined' || !MyAppAdmin.ImageCropper) console.error("ProductEdit.init: MyAppAdmin.ImageCropper module is not defined or not loaded before ProductEdit.");
    }
});