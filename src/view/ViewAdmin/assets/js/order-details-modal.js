$(document).ready(function() {
    const productDetailModal = $('#productDetailModal');
    const modalLoader = $('#modal-loader');
    const modalContent = $('#modal-content-container');
    // Tận dụng biến global đã được định nghĩa trong footer_scripts.php
    const productImageBaseUrl = MyAppAdmin.config.productImageBaseUrl || '../../view/ViewUser/ProductImage/';

    $('.product-row-clickable').on('click', function() {
        const productId = $(this).data('product-id');

        modalLoader.show();
        modalContent.hide();
        productDetailModal.modal('show');

        $('#modalProductName').text('Chi tiết Sản phẩm');
        $('#carousel-inner-container').html('');

        // SỬA Ở ĐÂY: Gọi đúng endpoint mới
        $.ajax({
            url: `index.php?ctrl=adminproduct&act=ajaxGetProductDetailsForView&id=${productId}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // SỬA Ở ĐÂY: response.data chứa tất cả thông tin
                    const productData = response.data;
                    const images = productData.images_data; // Lấy mảng ảnh từ response

                    // Điền thông tin vào modal
                    $('#modalProductName').text(productData.name);
                    $('#modalProductBrand').text(productData.brand || 'N/A');
                    $('#modalProductStock').text(productData.stock);
                    $('#modalProductPrice').text(new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(productData.price));
                    $('#modalProductDiscountPrice').text(productData.discount_price ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(productData.discount_price) : 'Không có');
                    $('#modalProductDescription').html(productData.description);

                    // Xây dựng carousel ảnh
                    const carouselInner = $('#carousel-inner-container');
                    carouselInner.html(''); // Xóa nội dung cũ trước khi thêm mới

                    if (images && images.length > 0) {
                        images.forEach((image, index) => {
                            const activeClass = index === 0 ? 'active' : '';
                            const imageUrl = productImageBaseUrl + (image.image_url || 'default.png');
                            const itemHtml = `
                                <div class="carousel-item ${activeClass}">
                                    <img src="${imageUrl}" class="d-block w-100" alt="Product image" style="height: 350px; object-fit: contain;">
                                </div>`;
                            carouselInner.append(itemHtml);
                        });
                    } else {
                        carouselInner.html(`<div class="carousel-item active"><img src="${productImageBaseUrl}default.png" class="d-block w-100" alt="No image available"></div>`);
                    }

                    $('#modalProductImagesCarousel').carousel();

                    modalLoader.hide();
                    modalContent.show();

                } else {
                    alert(response.message);
                    productDetailModal.modal('hide');
                }
            },
            error: function() {
                alert('Đã có lỗi xảy ra khi tải dữ liệu sản phẩm.');
                productDetailModal.modal('hide');
            }
        });
    });
});