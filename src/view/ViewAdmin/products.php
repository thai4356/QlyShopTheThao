<?php
// Gán giá trị mặc định nếu chưa có (phòng trường hợp)
$products = $products ?? [];
$product_image_base_url = $product_image_base_url ?? '../../view/ViewUser/ProductImage/'; // Phải là URL
$all_categories = $all_categories ?? [];

$current_sort_column = $current_sort_column ?? 'created_at';
$current_sort_order = $current_sort_order ?? 'DESC';

$current_page = $current_page ?? 1;
$total_pages = $total_pages ?? 1;

// Hàm trợ giúp để tạo link sắp xếp và icon
function getSortLinkAndIcon($columnKey, $displayName, $currentSortColumn, $currentSortOrder) {
    // ... (Nội dung hàm này giữ nguyên như đã cung cấp ở lượt trả lời về sort server-side)
    $icon_down_up = '<i class="bi bi-arrow-down-up"></i>';
    $icon_down = '<i class="bi bi-arrow-down"></i>';
    $icon_up = '<i class="bi bi-arrow-up"></i>';

    $sortIcon = $icon_down_up;
    $nextSortOrder = 'ASC';

    if ($currentSortColumn === $columnKey) {
        if (strtoupper($currentSortOrder) === 'ASC') {
            $sortIcon = $icon_up;
            $nextSortOrder = 'DESC';
        } elseif (strtoupper($currentSortOrder) === 'DESC') {
            $sortIcon = $icon_down;
            $nextSortOrder = 'ASC';
        }
    }
    $queryParams = $_GET;
    // unset($queryParams['p']); // Reset về trang 1 khi sort cột mới, tùy chọn
    $queryParams['sort_col'] = $columnKey;
    $queryParams['sort_order'] = $nextSortOrder;
    // Giữ lại ctrl, act, page cho trang sản phẩm
    $queryParams['ctrl'] = $_GET['ctrl'] ?? 'adminproduct';
    $queryParams['act'] = $_GET['act'] ?? 'listProducts';
    $queryParams['page'] = $_GET['page'] ?? 'products';

    $queryString = http_build_query($queryParams);
    return "<a href=\"index.php?{$queryString}\" class=\"sortable-column text-decoration-none text-dark fw-bold\">{$displayName} {$sortIcon}</a>";
}
?>
<div class="page-header">
    <h4 class="page-title">Quản Lý Sản Phẩm</h4>
    <ul class="breadcrumbs">
        <li class="nav-home"><a href="index.php?page=dashboard"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Quản lý</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">
            <a href="index.php?ctrl=adminproduct&act=listProducts&page=products">Sản phẩm</a>
        </li>
    </ul>
</div>
<div class="page-category">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Danh sách sản phẩm</h4>
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                                data-bs-target="#addRowModal">
                            <i class="fa fa-plus"></i> Thêm sản phẩm
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-labelledby="addRowModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addRowModalLabel">Thêm Sản Phẩm Mới</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeAddRowModal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="addProductForm" enctype="multipart/form-data">
                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="addThumbnailInput" class="form-label">Ảnh đại diện (Thumbnail) <span class="text-danger">*</span></label>
                                                        <input type="file" class="form-control" id="addThumbnailInput" name="thumbnail_image_original" accept="image/*">
                                                        <small class="form-text text-muted">Tối đa 2MB. Chọn ảnh để cắt (crop).</small>
                                                        <div class="mt-2 text-center" id="thumbnailPreviewContainer" style="width: 100%; min-height: 200px; border: 1px dashed #ccc; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #f8f9fa; overflow: hidden; position: relative; padding:10px; cursor: pointer;">
                                                            <img id="thumbnailPreview" src="#" alt="Xem trước Thumbnail" style="display: none; max-width: 100%; max-height: 180px; object-fit: contain; margin-bottom: 10px;">
                                                            <span id="thumbnailPlaceholder" style="color: #aaa;">Xem trước ảnh đại diện đã cắt</span>
                                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="deleteThumbnailButton" style="display: none;">Xóa ảnh đại diện</button>
                                                        </div>
                                                        <div id="thumbnailError" class="text-danger mt-1" style="font-size: 0.875em;"></div>
                                                        <input type="hidden" id="croppedThumbnailData" name="cropped_thumbnail_data">
                                                    </div>
                                                </div>

                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label for="addProductName" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="addProductName" name="name" required>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="addProductPrice" class="form-label">Giá gốc <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="addProductPrice" name="price" step="1000" min="0" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="addProductDiscountPrice" class="form-label">Giá khuyến mãi</label>
                                                            <input type="number" class="form-control" id="addProductDiscountPrice" name="discount_price" step="1000" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="addProductStock" class="form-label">Số lượng tồn <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="addProductStock" name="stock" min="0" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="addProductCategory" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="addProductCategory" name="category_id" required>
                                                                <option value="">Chọn danh mục</option>
                                                                <?php if (!empty($all_categories)): ?>
                                                                    <?php foreach ($all_categories as $category): ?>
                                                                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                                                            <?php echo htmlspecialchars($category['name']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="addProductBrand" class="form-label">Thương hiệu</label>
                                                    <input type="text" class="form-control" id="addProductBrand" name="brand">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="addProductLocation" class="form-label">Nơi bán/Xuất xứ</label>
                                                    <input type="text" class="form-control" id="addProductLocation" name="location">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="addProductDescription" class="form-label">Mô tả</label>
                                                <textarea class="form-control" id="addProductDescription" name="description" rows="3"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="addProductImagesInput" class="form-label">Các hình ảnh khác (chọn nhiều ảnh)</label>
                                                <input type="file" class="form-control" id="addProductImagesInput" name="product_images_original[]" multiple accept="image/*">
                                                <small class="form-text text-muted">Mỗi ảnh tối đa 2MB. Chọn ảnh và sau đó cắt ảnh nếu cần.</small>
                                                <div id="otherImagesPreviewContainer" class="mt-2 d-flex flex-wrap" style="gap: 0.75rem;">
                                                </div>
                                                <div id="otherImagesError" class="text-danger mt-1" style="font-size: 0.875em;"></div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelAddProductButton">Hủy</button>
                                    <button type="button" class="btn btn-primary" id="submitAddProductButton">Thêm mới</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="imageCropperModal" tabindex="-1" aria-labelledby="imageCropperModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="imageCropperModalLabel">Cắt Ảnh</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeImageCropperModal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="img-container-cropper" style="max-height: 500px; overflow: hidden; margin-bottom: 1rem;">
                                        <img id="imageToCropInModal" src="#" alt="Image to crop" style="max-width: 100%;">
                                    </div>
                                    <small class="form-text text-muted">Ảnh sẽ được resize để không vượt quá 800x800px sau khi cắt.</small>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelCropButton">Hủy</button>
                                    <button type="button" class="btn btn-primary" id="confirmCropButton">Cắt & Sử dụng</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="add-row" class="display table table-striped table-hover">
                            <thead>
                            <tr>
                                <th style="width: 70px;">Ảnh</th>
                                <th><?php echo getSortLinkAndIcon('name', 'Tên sản phẩm', $current_sort_column, $current_sort_order); ?></th>
                                <th><?php echo getSortLinkAndIcon('price', 'Giá', $current_sort_column, $current_sort_order); ?></th>
                                <th><?php echo getSortLinkAndIcon('stock', 'Tồn kho', $current_sort_column, $current_sort_order); ?></th>
                                <th><?php echo getSortLinkAndIcon('sold_quantity', 'Đã bán', $current_sort_column, $current_sort_order); ?></th>
                                <th><?php echo getSortLinkAndIcon('category_name', 'Danh mục', $current_sort_column, $current_sort_order); ?></th>
                                <th style="width: 100px;">Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr class="product-row-clickable"
                                        data-id="<?php echo htmlspecialchars($product['id']); ?>"
                                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                        data-category_id="<?php echo htmlspecialchars($product['category_id']);?>"
                                        data-price_raw="<?php echo htmlspecialchars($product['price']); ?>"
                                        data-discount_price_raw="<?php echo htmlspecialchars($product['discount_price'] ?? 0); ?>"
                                        data-stock="<?php echo htmlspecialchars($product['stock']); ?>"
                                        data-brand="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>"
                                        data-location="<?php echo htmlspecialchars($product['location'] ?? ''); ?>"
                                        data-description="<?php echo htmlspecialchars($product['description'] ?? ''); ?>"
                                        style="cursor: pointer;"
                                        title="Click để Xem chi tiết"
                                    >
                                        <td>
                                            <?php if (!empty($product['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($product_image_base_url . $product['image_url']); ?>"
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <img src="<?php echo htmlspecialchars($product_image_base_url . 'default-placeholder.png'); ?>"
                                                     alt="No image"
                                                     style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #eee;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td>
                                            <?php if (isset($product['discount_price']) && $product['discount_price'] > 0 && (float)$product['discount_price'] < (float)$product['price']): ?>
                                                <span style="text-decoration: line-through; color: #999; font-size:0.9em;"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</span><br>
                                                <strong style="color: red;"><?php echo number_format($product['discount_price'], 0, ',', '.'); ?>₫</strong>
                                            <?php else: ?>
                                                <?php echo number_format($product['price'], 0, ',', '.'); ?>₫
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['stock'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($product['sold_quantity'] ?? 0); ?></td>
                                        <td><?php echo isset($product['category_name']) ? htmlspecialchars($product['category_name']) : 'N/A'; ?></td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" data-bs-toggle="tooltip" title="Sửa"
                                                        class="btn btn-link btn-primary btn-lg edit-product-button"
                                                        data-product-id="<?php echo htmlspecialchars($product['id']); // QUAN TRỌNG ?>">
                                                    <i class="bi bi-pen"></i>
                                                </button>
                                                <button type="button" data-bs-toggle="tooltip" title="Xóa"
                                                        class="btn btn-link btn-danger delete-product-button"
                                                        data-product-id="<?php echo htmlspecialchars($product['id']); // QUAN TRỌNG ?>"
                                                        data-product-name="<?php echo htmlspecialchars($product['name']); // QUAN TRỌNG ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Không có sản phẩm nào để hiển thị.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <div class="card-footer d-flex justify-content-center">
                        <nav aria-label="Product navigation">
                            <ul class="pagination mb-0">
                                <?php // Nút Previous
                                if ($current_page > 1):
                                    $prevPageParams = $_GET; $prevPageParams['p'] = $current_page - 1; ?>
                                    <li class="page-item"><a class="page-link" href="index.php?<?php echo http_build_query($prevPageParams); ?>">&laquo;</a></li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                <?php endif; ?>

                                <?php // Nút số trang
                                $range = 2;
                                $start_range = max(1, $current_page - $range);
                                $end_range = min($total_pages, $current_page + $range);
                                if ($start_range > 1) { $firstPageParams = $_GET; $firstPageParams['p'] = 1; echo '<li class="page-item"><a class="page-link" href="index.php?'.http_build_query($firstPageParams).'">1</a></li>'; if ($start_range > 2) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; } }
                                for ($i = $start_range; $i <= $end_range; $i++): $pageParams = $_GET; $pageParams['p'] = $i; ?>
                                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>"><a class="page-link" href="index.php?<?php echo http_build_query($pageParams); ?>"><?php echo $i; ?></a></li>
                                <?php endfor;
                                if ($end_range < $total_pages) { if ($end_range < $total_pages - 1) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; } $lastPageParams = $_GET; $lastPageParams['p'] = $total_pages; echo '<li class="page-item"><a class="page-link" href="index.php?'.http_build_query($lastPageParams).'">'.$total_pages.'</a></li>'; } ?>

                                <?php // Nút Next
                                if ($current_page < $total_pages):
                                    $nextPageParams = $_GET; $nextPageParams['p'] = $current_page + 1; ?>
                                    <li class="page-item"><a class="page-link" href="index.php?<?php echo http_build_query($nextPageParams); ?>">&raquo;</a></li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="productEditModal" tabindex="-1" role="dialog" aria-labelledby="productEditModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productEditModalLabel">Chi tiết/Chỉnh sửa Sản phẩm</h5>
                <button type="button" class="btn-close" id="closeProductEditModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productEditForm" enctype="multipart/form-data">
                    <input type="hidden" id="modalEditProductId" name="id">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editThumbnailInput" class="form-label">Ảnh đại diện (Thumbnail)</label>
                                    <input type="file" class="form-control" id="editThumbnailInput" name="new_thumbnail_image_original" accept="image/*" style="display:none;">
                                    <small class="form-text text-muted">Tối đa 2MB. Nhấp vào ảnh để thay đổi/cắt.</small>
                                    <div class="mt-2 text-center" id="editThumbnailPreviewContainer" style="width: 100%; min-height: 200px; border: 1px dashed #ccc; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #f8f9fa; overflow: hidden; position: relative; padding:10px; cursor: pointer;">
                                        <img id="editThumbnailPreview" src="#" alt="Ảnh đại diện" style="display: none; max-width: 100%; max-height: 180px; object-fit: contain; margin-bottom: 10px;">
                                        <span id="editThumbnailPlaceholder" style="color: #aaa;">Chọn/Thay đổi ảnh đại diện</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="editDeleteThumbnailButton" style="display: none;">Xóa ảnh đại diện</button>
                                    </div>
                                    <div id="editThumbnailError" class="text-danger mt-1" style="font-size: 0.875em;"></div>
                                    <input type="hidden" id="editCroppedThumbnailData" name="cropped_thumbnail_data">
                                    <input type="hidden" id="editThumbnailAction" name="thumbnail_action" value="keep">
                                    <input type="hidden" id="editExistingThumbnailId" name="existing_thumbnail_id" value="">
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Mã sản phẩm:</label>
                                    <p id="modalDisplayProductId" class="form-control-plaintext"></p>
                                </div>
                                <div class="mb-3">
                                    <label for="modalEditProductName" class="form-label">Tên sản phẩm: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-field-edit" id="modalEditProductName" name="name" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="modalEditProductPrice" class="form-label">Giá gốc: <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-field-edit" id="modalEditProductPrice" name="price" step="1000" min="0" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="modalEditProductDiscountPrice" class="form-label">Giá khuyến mãi:</label>
                                        <input type="number" class="form-control form-field-edit" id="modalEditProductDiscountPrice" name="discount_price" step="1000" min="0">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="modalEditProductStock" class="form-label">Số lượng tồn: <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-field-edit" id="modalEditProductStock" name="stock" min="0" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Đã bán:</label>
                                        <p id="modalDisplaySoldCount" class="form-control-plaintext"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="modalEditProductCategory" class="form-label">Danh mục: <span class="text-danger">*</span></label>
                                        <select class="form-select form-field-edit" id="modalEditProductCategory" name="category_id" required>
                                            <option value="">Chọn danh mục</option>
                                            <?php if (!empty($all_categories)): /* Biến này controller listProducts truyền cho view products.php */ ?>
                                                <?php foreach ($all_categories as $category): ?>
                                                    <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="modalEditProductBrand" class="form-label">Thương hiệu:</label>
                                        <input type="text" class="form-control form-field-edit" id="modalEditProductBrand" name="brand">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="modalEditProductLocation" class="form-label">Nơi bán/Xuất xứ:</label>
                                    <input type="text" class="form-control form-field-edit" id="modalEditProductLocation" name="location">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modalEditProductDescription" class="form-label">Mô tả:</label>
                            <textarea class="form-control form-field-edit" id="modalEditProductDescription" name="description" rows="3"></textarea>
                        </div>

                        <hr>
                        <h6 class="mt-3">Các Hình Ảnh Khác</h6>
                        <div class="mb-3">
                            <label for="editAddMoreImagesInput" class="form-label">Thêm ảnh mới:</label>
                            <input type="file" class="form-control" id="editAddMoreImagesInput" name="new_other_images_original[]" multiple accept="image/*">
                            <small class="form-text text-muted">Mỗi ảnh tối đa 2MB. Chọn ảnh và sau đó cắt ảnh nếu cần.</small>
                        </div>
                        <div id="editOtherImagesPreviewContainer" class="mt-2 d-flex flex-wrap" style="gap: 0.75rem; min-height:120px; border: 1px solid #eee; padding:10px; background-color:#fdfdfd;">
                            <span id="editOtherImagesPlaceholder" class="text-muted">Chưa có ảnh nào khác hoặc chưa thêm ảnh mới.</span>
                        </div>
                        <div id="editOtherImagesError" class="text-danger mt-1" style="font-size: 0.875em;"></div>

                    </div> </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeProductEditModalButton">Đóng</button>
                <button type="button" class="btn btn-danger" id="modalOpenDeleteConfirmButton">Xóa sản phẩm</button>
                <button type="button" class="btn btn-primary" id="modalOpenSaveChangesConfirmButton" disabled>Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-labelledby="saveConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveConfirmModalLabel">Xác nhận Lưu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn lưu những thay đổi này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmSaveChangesButton">Lưu</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Xác nhận Xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn ẩn sản phẩm <strong id="deleteProductNameConfirm"></strong> này khỏi người dùng không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Xóa</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="discardConfirmModal" tabindex="-1" aria-labelledby="discardConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discardConfirmModalLabel">Thay đổi chưa lưu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có thay đổi chưa được lưu. Bạn có muốn hủy bỏ những thay đổi này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelDiscardButton">Không (Tiếp tục sửa)</button>
                <button type="button" class="btn btn-primary" id="confirmDiscardButton">Có (Hủy thay đổi)</button>
            </div>
        </div>
    </div>
</div>