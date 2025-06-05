<?php
// view/ViewAdmin/categories.php

// Biến $categories và $pageTitle được truyền từ AdminCategoryController thông qua extract() trong index.php
?>

<div class="page-header">
    <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($pageTitle ?? 'Danh Mục'); ?></h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="index.php?page=dashboard">
                <i class="fa-solid fa-house"></i>
            </a>
        </li>
        <li class="separator">
            <i class="bi bi-slash"></i>
        </li>
        <li class="nav-item">
            <a href="#">Quản Lý Cửa Hàng</a>
        </li>
        <li class="separator">
            <i class="bi bi-slash"></i>
        </li>
        <li class="nav-item">
            <a href="index.php?page=categories"><?php echo htmlspecialchars($pageTitle ?? 'Danh Mục'); ?></a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Danh sách Danh Mục</h4>
                    <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fa fa-plus"></i>
                        Thêm Danh Mục Mới
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="categoriesTable" class="display table table-striped table-hover" style="width:100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Danh Mục</th>
                            <th>Mô Tả</th>
                            <th>Trạng Thái</th>
                            <th style="width: 10%">Hành Động</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Thêm Danh Mục Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newCategoryName" class="form-label">Tên Danh Mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newCategoryName" name="categoryName" required>
                    </div>
                    <div class="mb-3">
                        <label for="newCategoryDescription" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="newCategoryDescription" name="categoryDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="newCategoryStatus" name="categoryStatus" checked>
                        <label class="form-check-label" for="newCategoryStatus">Hoạt động</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeAddCategoryModalBtn">Đóng</button>
                    <button type="button" class="btn btn-primary" id="saveNewCategoryBtn">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Chỉnh Sửa Danh Mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm">
                <div class="modal-body">
                    <input type="hidden" id="editCategoryId" name="categoryId">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Tên Danh Mục</label>
                        <input type="text" class="form-control" id="editCategoryName" name="categoryName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategoryDescription" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="editCategoryDescription" name="categoryDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="editCategoryStatus" name="categoryStatus">
                        <label class="form-check-label" for="editCategoryStatus">Hoạt động</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto" id="deleteCategoryBtnModal">Xóa</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="saveCategoryChangesBtn" disabled>Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

