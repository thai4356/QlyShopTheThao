<?php // view/ViewAdmin/products.php ?>
<div class="page-header">
    <h4 class="page-title">Quản lý Sản phẩm</h4>
    <ul class="breadcrumbs">
        <li class="nav-home"><a href="index.php?page=dashboard"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Quản lý</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="index.php?page=products">Sản phẩm</a></li>
    </ul>
</div>
<div class="page-category">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Danh sách sản phẩm</h4>
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
                            <i class="fa fa-plus"></i> Thêm sản phẩm
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
                    </div>

                    <div class="table-responsive">
                        <table id="add-row" class="display table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Danh mục</th>
                                <th style="width: 10%">Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Găng tay tập gym</td>
                                <td>150,000₫</td>
                                <td>50</td>
                                <td>Dụng cụ tập gym</td>
                                <td>
                                    <div class="form-button-action">
                                        <button type="button" data-bs-toggle="tooltip" title="Sửa" class="btn btn-link btn-primary btn-lg"><i class="fa fa-edit"></i></button>
                                        <button type="button" data-bs-toggle="tooltip" title="Xóa" class="btn btn-link btn-danger"><i class="fa fa-times"></i></button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
