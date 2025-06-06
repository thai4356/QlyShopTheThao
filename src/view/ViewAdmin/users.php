<?php
// File này sẽ được nạp bởi layout.php
// Các biến như $pageTitle sẽ được layout.php hoặc index.php xử lý

// Chuẩn bị biến để nạp script JS riêng cho trang này
// Biến $page_scripts sẽ được đọc bởi footer_scripts.php
$page_scripts[] = 'assets/js/admin-users.js';
$page_scripts[] = 'assets/js/admin-user-add.js';
?>

<div class="page-header">
    <h4 class="page-title">Quản Lý Người Dùng</h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="index.php?page=dashboard">
                <i class="flaticon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">Quản Lý Cửa Hàng</a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="index.php?page=users">Người Dùng</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Danh sách Người Dùng</h4>
                    <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fa fa-plus"></i>
                        Thêm người dùng mới
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="users-table" class="display table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Xác thực</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th style="width: 10%">Hành động</th>
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

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Thêm người dùng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" required>
                        <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                    </div>
                    <div class="mb-3">
                        <label for="userPassword" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="userPassword" required>
                        <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                    </div>
                    <div class="mb-3">
                        <label for="userConfirmPassword" class="form-label">Xác nhận Mật khẩu</label>
                        <input type="password" class="form-control" id="userConfirmPassword" required>
                        <div class="invalid-feedback">Mật khẩu xác nhận không khớp.</div>
                    </div>
                    <div class="mb-3">
                        <label for="userRole" class="form-label">Vai trò</label>
                        <select class="form-select" id="userRole" required>
                            <option value="2" selected>User</option>
                            <option value="1">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="save-user-btn">Lưu</button>
            </div>
        </div>
    </div>
</div>