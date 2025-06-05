// view/ViewAdmin/assets/js/category-modal-handler.js
$(document).ready(function() {
    const editModalElement = document.getElementById('editCategoryModal');
    const editCategoryModal = new bootstrap.Modal(editModalElement);
    const editCategoryForm = $('#editCategoryForm');
    const categoryIdField = $('#editCategoryId');
    const categoryNameField = $('#editCategoryName');
    const categoryDescriptionField = $('#editCategoryDescription');
    const categoryStatusField = $('#editCategoryStatus');
    const saveChangesBtn = $('#saveCategoryChangesBtn');

    let originalData = {
        name: '',
        description: '',
        status: false
    };
    let hasUnsavedChanges = false;
    let allowModalClose = false; // Flag để cho phép đóng modal sau khi xác nhận hủy

    function storeOriginalData() {
        originalData.name = categoryNameField.val();
        originalData.description = categoryDescriptionField.val();
        originalData.status = categoryStatusField.is(':checked');
    }

    function revertFormChanges() {
        categoryNameField.val(originalData.name);
        categoryDescriptionField.val(originalData.description);
        categoryStatusField.prop('checked', originalData.status);
        checkForChanges(); // Cập nhật lại trạng thái nút lưu
    }

    function checkForChanges() {
        const currentName = categoryNameField.val();
        const currentDescription = categoryDescriptionField.val();
        const currentStatus = categoryStatusField.is(':checked');

        if (currentName !== originalData.name ||
            currentDescription !== originalData.description ||
            currentStatus !== originalData.status) {
            hasUnsavedChanges = true;
            saveChangesBtn.prop('disabled', false);
        } else {
            hasUnsavedChanges = false;
            saveChangesBtn.prop('disabled', true);
        }
    }

    // 2. Tạo lại HTML cho actions_html với dữ liệu mới nhất
    // Hàm helper để escape HTML (an toàn hơn khi tự ghép chuỗi HTML)
    function jsEscapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return unsafe;
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    const addModalElement = document.getElementById('addCategoryModal');
    const addCategoryModal = new bootstrap.Modal(addModalElement);
    const addCategoryForm = $('#addCategoryForm');
    const newCategoryNameField = $('#newCategoryName');
    const newCategoryDescriptionField = $('#newCategoryDescription');
    const newCategoryStatusField = $('#newCategoryStatus');
    const saveNewCategoryBtn = $('#saveNewCategoryBtn');

    let isAddFormDirty = false;
    let allowAddModalClose = false;

    function checkAddFormDirty() {
        const nameVal = newCategoryNameField.val().trim();
        const descVal = newCategoryDescriptionField.val().trim();
        // Mặc định status là checked, nên form dirty nếu nó không checked, hoặc có text
        isAddFormDirty = nameVal !== '' || descVal !== '' || !newCategoryStatusField.is(':checked');

        console.log('checkAddFormDirty - isAddFormDirty:', isAddFormDirty, 'Name:', nameVal, 'Desc:', descVal, 'Status Checked:', newCategoryStatusField.is(':checked'));
    }

    // Khi Modal Thêm Mới được hiển thị
    addModalElement.addEventListener('show.bs.modal', function () {
        addCategoryForm[0].reset(); // Reset form về trạng thái mặc định HTML
        newCategoryStatusField.prop('checked', true); // Đảm bảo status mặc định là active
        isAddFormDirty = false;
        allowAddModalClose = false;
        // Xóa các thông báo lỗi cũ (nếu có) - sẽ thêm sau nếu cần validation chi tiết
    });

    // Khi Modal Thêm Mới đã bị ẩn (để dọn dẹp nếu cần)
    addModalElement.addEventListener('hidden.bs.modal', function () {
        addCategoryForm[0].reset();
        newCategoryStatusField.prop('checked', true);
        isAddFormDirty = false;
    });


    // Lắng nghe thay đổi trên form thêm mới
    newCategoryNameField.on('input', checkAddFormDirty);
    newCategoryDescriptionField.on('input', checkAddFormDirty);
    newCategoryStatusField.on('change', checkAddFormDirty);

    // Xử lý khi nhấn nút "Lưu" trong Modal Thêm Mới
    saveNewCategoryBtn.on('click', function(e) {
        e.preventDefault();

        const categoryName = newCategoryNameField.val().trim();
        const categoryDescription = newCategoryDescriptionField.val().trim(); // Description thô
        const categoryStatus = newCategoryStatusField.is(':checked') ? 1 : 0;

        if (categoryName === '') {
            Swal.fire('Lỗi!', 'Tên danh mục không được để trống.', 'error');
            newCategoryNameField.focus();
            return;
        }

        Swal.fire({
            title: 'Xác nhận thêm',
            text: `Bạn có chắc chắn muốn thêm danh mục "${jsEscapeHtml(categoryName)}"?`, // jsEscapeHtml đã được định nghĩa
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Thêm mới',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Đang xử lý...',
                    text: 'Vui lòng chờ.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'index.php?ctrl=admincategory&act=ajaxAddCategory',
                    type: 'POST',
                    data: {
                        name: categoryName, // Gửi tên thô
                        description: categoryDescription, // Gửi description thô
                        is_active: categoryStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            allowAddModalClose = true;
                            addCategoryModal.hide(); // Đóng modal
                            Swal.fire('Thành công!', response.message, 'success');
                            // Nạp lại DataTables để hiển thị danh mục mới
                            // null, false để giữ nguyên trang hiện tại sau khi reload
                            $('#categoriesTable').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Lỗi!', response.message || 'Không thể thêm danh mục.', 'error');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.close();
                        console.error("AJAX Error (Add Category):", textStatus, errorThrown, jqXHR.responseText);
                        Swal.fire('Lỗi!', 'Có lỗi xảy ra khi gửi yêu cầu thêm danh mục.', 'error');
                    }
                });
            }
        });
    });

    // Xử lý khi Modal Thêm Mới sắp bị đóng (kiểm tra unsaved changes)
    addModalElement.addEventListener('hide.bs.modal', function(event) {
        console.log('Add Modal hide event triggered. allowAddModalClose:', allowAddModalClose, 'isAddFormDirty:', isAddFormDirty);
        if (allowAddModalClose) { // Nếu được phép đóng (ví dụ sau khi lưu thành công hoặc chọn hủy)
            allowAddModalClose = false; // Reset flag
            return;
        }

        if (isAddFormDirty) {
            event.preventDefault();
            console.log('Unsaved changes detected, showing Swal confirmation for Add Modal.'); // Thêm log
            Swal.fire({
                title: 'Thông tin chưa lưu',
                text: "Bạn có thông tin chưa được lưu. Bạn muốn tiếp tục thêm hay hủy bỏ?",
                icon: 'warning',
                showDenyButton: true,
                confirmButtonText: 'Tiếp tục thêm',
                denyButtonText: `Hủy bỏ`,
                confirmButtonColor: '#3085d6',
                denyButtonColor: '#6c757d',
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('User chose to continue adding.');
                } else if (result.isDenied) {
                    // "Hủy bỏ"
                    console.log('User chose to cancel changes and close Add Modal.');
                    allowAddModalClose = true;
                    addCategoryModal.hide();
                } else {
                    console.log('No unsaved changes in Add Modal, closing normally.'); // Thêm log
                }
            });
        }
    });

    // Khi modal bắt đầu hiển thị
    editModalElement.addEventListener('show.bs.modal', function(event) {
        const button = $(event.relatedTarget); // Nút đã trigger modal (nếu có)
        if (button.hasClass('edit-category-btn')) { // Đảm bảo là từ nút sửa
            const id = button.data('id');
            const name = button.data('name');
            const description = button.data('description');
            const status = button.data('status');

            categoryIdField.val(id);
            categoryNameField.val(name);
            categoryDescriptionField.val(description);
            categoryStatusField.prop('checked', status === '1' || status === 1);

            storeOriginalData();
        } else if ($(event.relatedTarget).closest('tr').length) { // Nếu click từ dòng
            const row = $(event.relatedTarget).closest('tr');
            const editButton = row.find('.edit-category-btn');
            if (editButton.length) {
                const id = editButton.data('id');
                const name = editButton.data('name');
                const description = editButton.data('description');
                const status = editButton.data('status');

                categoryIdField.val(id);
                categoryNameField.val(name);
                categoryDescriptionField.val(description);
                categoryStatusField.prop('checked', status === '1' || status === 1);
                storeOriginalData();
            }
        }
        // Reset trạng thái
        hasUnsavedChanges = false;
        saveChangesBtn.prop('disabled', true);
        allowModalClose = false;
    });

    // Xử lý khi click nút "Sửa" trên bảng (chỉ để đảm bảo dữ liệu được nạp đúng)
    $('#categoriesTable tbody').on('click', '.edit-category-btn', function() {
        const button = $(this);
        const id = button.data('id');
        const name = button.data('name');
        const description = button.data('description');
        const status = button.data('status');

        categoryIdField.val(id);
        categoryNameField.val(name);
        categoryDescriptionField.val(description);
        categoryStatusField.prop('checked', status === '1' || status === 1);

        storeOriginalData(); // Lưu trạng thái gốc khi modal chuẩn bị mở từ nút
        hasUnsavedChanges = false; // Reset
        saveChangesBtn.prop('disabled', true); // Reset
        allowModalClose = false;
    });

    // Xử lý khi click vào một dòng (thẻ <tr>)
    $('#categoriesTable tbody').on('click', 'tr', function(event) {
        if ($(event.target).closest('button').length) {
            return; // Bỏ qua nếu click vào nút
        }
        const editButton = $(this).find('.edit-category-btn');
        if (editButton.length) { // Trigger modal và nạp dữ liệu nếu có nút sửa
            // Bootstrap sẽ tự mở modal nếu nút sửa có data-bs-toggle, ta chỉ cần đảm bảo dữ liệu đúng
            // Dữ liệu sẽ được nạp bởi sự kiện 'show.bs.modal'
            editButton.click(); // Giả lập click nút sửa để bootstrap tự xử lý mở modal
                                // Hoặc trực tiếp gọi editCategoryModal.show() sau khi nạp dữ liệu
        }
    });


    // Lắng nghe sự thay đổi trên các trường input
    categoryNameField.on('input', checkForChanges);
    categoryDescriptionField.on('input', checkForChanges);
    categoryStatusField.on('change', checkForChanges);

    saveChangesBtn.on('click', function(e) {
        e.preventDefault();
        if (saveChangesBtn.prop('disabled')) return; // Nếu nút bị vô hiệu hóa, không làm gì

        Swal.fire({
            title: 'Xác nhận',
            text: "Bạn có muốn lưu thay đổi không?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d', // Màu secondary cho nút không
            confirmButtonText: 'Lưu thay đổi',
            cancelButtonText: 'Không'
        }).then((result) => {
            if (result.isConfirmed) {
                const categoryId = categoryIdField.val();
                const categoryName = categoryNameField.val();
                const categoryDescription = categoryDescriptionField.val();
                const categoryStatus = categoryStatusField.is(':checked') ? 1 : 0;

                // Hiển thị loading
                Swal.fire({
                    title: 'Đang xử lý...',
                    text: 'Vui lòng chờ trong giây lát.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'index.php?ctrl=admincategory&act=ajaxUpdateCategory',
                    type: 'POST',
                    data: {
                        id: categoryId,
                        name: categoryName,
                        description: categoryDescription,
                        is_active: categoryStatus
                        // Nếu có CSRF token: 'csrf_token': 'your_token_here'
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close(); // Đóng loading
                        if (response.success) {
                            // Cập nhật originalData với dữ liệu mới thành công
                            originalData.id = response.updatedCategory.id;
                            originalData.name = response.updatedCategory.name;
                            originalData.description = response.updatedCategory.description;
                            originalData.status = response.updatedCategory.is_active;

                            hasUnsavedChanges = false;
                            saveChangesBtn.prop('disabled', true);
                            allowModalClose = true;
                            editCategoryModal.hide();

                            Swal.fire('Thành công!', response.message, 'success');

                            // Cập nhật dòng trong DataTable
                            let table = $('#categoriesTable').DataTable();
                            let categoryIdToUpdate = response.updatedCategory.id;
                            let row = table.row('#category_' + categoryIdToUpdate); // Tìm dòng bằng DT_RowId

                            if (row.any()) {

                                // 1. Tạo lại HTML cho status_html
                                const newStatusHtml = response.updatedCategory.is_active ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-danger">Không hoạt động</span>';



                                let newActionsHtml = '<div class="form-button-action">';
                                newActionsHtml += `<button type="button" class="btn btn-link btn-primary btn-lg edit-category-btn" 
                            data-bs-toggle="modal" data-bs-target="#editCategoryModal" 
                            data-id="${jsEscapeHtml(response.updatedCategory.id.toString())}" 
                            data-name="${jsEscapeHtml(response.updatedCategory.name)}" 
                            data-description="${jsEscapeHtml(response.updatedCategory.description || '')}" 
                            data-status="${response.updatedCategory.is_active ? '1' : '0'}" 
                            data-bs-original-title="Sửa Danh Mục" title="Sửa">
                                <i class="fa fa-edit"></i>
                           </button>`;
                                newActionsHtml += `<button type="button" class="btn btn-link btn-danger delete-category-btn-table" 
                            data-id="${jsEscapeHtml(response.updatedCategory.id.toString())}" 
                            data-name="${jsEscapeHtml(response.updatedCategory.name)}" 
                            data-bs-toggle="tooltip" data-bs-original-title="Xóa Danh Mục" title="Xóa">
                                <i class="fa fa-trash"></i>
                           </button>`;
                                newActionsHtml += '</div>';

                                // 3. Tạo đối tượng dữ liệu mới cho dòng
                                let updatedRowDataObject = {
                                    "id": response.updatedCategory.id,
                                    "name": jsEscapeHtml(response.updatedCategory.name),
                                    "description": jsEscapeHtml(response.updatedCategory.description || ''), // Giữ nl2br nếu bạn muốn hiển thị xuống dòng
                                    "status_html": newStatusHtml,
                                    "actions_html": newActionsHtml
                                };

                                // 4. Cập nhật dòng với đối tượng dữ liệu mới
                                row.data(updatedRowDataObject).draw(false); // draw(false) để giữ nguyên trang hiện tại

                                // Kích hoạt lại tooltip cho các nút mới (nếu cần, vì chúng được tạo lại)
                                // $(row.node()).find('[data-bs-toggle="tooltip"]').tooltip();
                                // Tuy nhiên, nếu tooltip đã được khởi tạo ở cấp độ cao hơn và sử dụng delegation thì có thể không cần.

                            } else {
                                console.warn("Không tìm thấy dòng để cập nhật trong DataTable với ID: " + response.updatedCategory.id);
                                // Có thể cân nhắc table.ajax.reload(null, false); để tải lại toàn bộ dữ liệu nếu không tìm thấy dòng
                                // hoặc nếu việc cập nhật từng dòng trở nên quá phức tạp.
                            }

                        } else {
                            Swal.fire('Lỗi!', response.message || 'Không thể cập nhật danh mục.', 'error');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.close(); // Đóng loading
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                        Swal.fire('Lỗi!', 'Có lỗi xảy ra khi gửi yêu cầu đến máy chủ.', 'error');
                    }
                });
            }
        });
    });


    // Xử lý khi modal sắp bị đóng
    editModalElement.addEventListener('hide.bs.modal', function(event) {
        if (allowModalClose) { // Nếu được phép đóng (ví dụ sau khi lưu thành công)
            allowModalClose = false; // Reset flag
            return; // Cho phép đóng
        }

        if (hasUnsavedChanges) {
            event.preventDefault(); // Ngăn modal đóng lại
            Swal.fire({
                title: 'Thay đổi chưa lưu',
                text: "Bạn có thay đổi chưa được lưu. Bạn muốn tiếp tục sửa hay hủy các thay đổi?",
                icon: 'warning',
                showDenyButton: true,
                showCancelButton: false, // Không cần nút Cancel ở đây
                confirmButtonText: 'Tiếp tục sửa',
                denyButtonText: `Hủy thay đổi`,
                confirmButtonColor: '#3085d6',
                denyButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    // "Tiếp tục sửa" - không làm gì cả, modal vẫn mở
                } else if (result.isDenied) {
                    // "Hủy thay đổi"
                    revertFormChanges(); // Hoàn lại các thay đổi trên form
                    hasUnsavedChanges = false; // Reset flag
                    saveChangesBtn.prop('disabled', true); // Vô hiệu hóa lại nút lưu
                    allowModalClose = true; // Đặt flag để cho phép đóng modal ở lần gọi hide() tiếp theo
                    editCategoryModal.hide(); // Đóng modal
                }
            });
        }
    });


    // Hàm xử lý chung cho việc xóa mềm
    function handleSoftDeleteCategory(categoryId, categoryName, callbackOnSuccess) {
        Swal.fire({
            title: 'Xác nhận ẩn',
            text: `Bạn có chắc chắn muốn ẩn danh mục "${jsEscapeHtml(categoryName)}" và tất cả sản phẩm thuộc danh mục này không? Danh mục sẽ được đánh dấu là không hoạt động.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý ẩn',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ // Loading
                    title: 'Đang xử lý...',
                    text: 'Vui lòng chờ.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'index.php?ctrl=admincategory&act=ajaxSoftDeleteCategory',
                    type: 'POST',
                    data: { id: categoryId },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire('Đã ẩn!', response.message, 'success');

                            let table = $('#categoriesTable').DataTable();
                            let row = table.row('#category_' + categoryId); // Tìm dòng bằng DT_RowId

                            if (row.any()) {
                                // Lấy dữ liệu hiện tại của dòng để cập nhật một phần
                                let currentData = row.data();

                                // Tạo HTML mới cho status và actions
                                const newStatusHtml = '<span class="badge bg-danger">Không hoạt động</span>';

                                // Cập nhật data-status cho nút edit
                                let tempActionsDiv = $('<div>').html(currentData.actions_html);
                                tempActionsDiv.find('.edit-category-btn').attr('data-status', '0').data('status', '0');
                                // Bạn có thể muốn vô hiệu hóa nút xóa mềm hoặc thay đổi nó nếu danh mục đã bị ẩn
                                // Ví dụ: tempActionsDiv.find('.delete-category-btn-table').remove(); // Hoặc thay bằng nút "Kích hoạt lại"
                                const newActionsHtml = tempActionsDiv.html();


                                let updatedRowDataObject = {
                                    id: currentData.id, // Giữ nguyên các giá trị không đổi
                                    name: currentData.name,
                                    description: currentData.description,
                                    status_html: newStatusHtml, // Cập nhật trạng thái
                                    actions_html: newActionsHtml // Cập nhật actions (quan trọng là data-status của nút edit)
                                };
                                row.data(updatedRowDataObject).draw(false);
                            } else {
                                console.warn("Soft delete: Không tìm thấy dòng để cập nhật ID: " + categoryId + ". Tải lại bảng...");
                                table.ajax.reload(null, false); // Tải lại nếu không tìm thấy dòng
                            }

                            if (typeof callbackOnSuccess === 'function') {
                                callbackOnSuccess();
                            }

                        } else {
                            Swal.fire('Lỗi!', response.message || 'Không thể ẩn danh mục.', 'error');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.close();
                        console.error("AJAX Error (soft delete):", textStatus, errorThrown, jqXHR.responseText);
                        Swal.fire('Lỗi!', 'Có lỗi xảy ra khi gửi yêu cầu ẩn danh mục.', 'error');
                    }
                });
            }
        });
    }

    // Xử lý khi click nút "Xóa" (ẩn) trong modal
    $('#deleteCategoryBtnModal').on('click', function() {
        console.log('Nút "Xóa" (ẩn) trong modal được click.');
        const categoryId = categoryIdField.val(); // Lấy từ trường ẩn trong modal
        const categoryName = categoryNameField.val(); // Lấy tên từ trường trong modal
        if (!categoryId) {
            console.error('Không tìm thấy ID danh mục trong modal khi click nút "Xóa".');
            return;
        }

        handleSoftDeleteCategory(categoryId, categoryName, function() {
            allowModalClose = true; // Cho phép đóng modal sau khi xóa thành công từ modal
            editCategoryModal.hide();
        });
    });

    // Xử lý khi click nút "Xóa" (ẩn) trên từng dòng của bảng
    $('#categoriesTable tbody').on('click', '.delete-category-btn-table', function() {
        console.log('Nút "Xóa" (ẩn) trên bảng được click.');
        const button = $(this);
        const categoryId = button.data('id');
        const categoryName = button.data('name');
        if (!categoryId) {
            console.error('Không tìm thấy ID danh mục từ nút "Xóa" trên bảng.');
            return;
        }

        handleSoftDeleteCategory(categoryId, categoryName);
    });
});