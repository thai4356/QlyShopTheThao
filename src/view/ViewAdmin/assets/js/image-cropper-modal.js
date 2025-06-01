// Đảm bảo MyAppAdmin namespace đã tồn tại
var MyAppAdmin = window.MyAppAdmin || {};

MyAppAdmin.ImageCropper = (function($) {
    'use strict';

    var cropperModalEl;
    var cropperModalInstance;
    var imageToCropEl;
    var cropperJsInstance; // Đổi tên từ cropperInstance để tránh nhầm lẫn với biến toàn cục (nếu có)

    var currentCroppingConfig = { // Sử dụng object để lưu trữ ngữ cảnh và callback
        context: null,           // { type: 'thumbnail', tempId: null } hoặc { type: 'otherImage', tempId: 'xyz' } etc.
        onComplete: null         // function(croppedDataURL, originalContext)
    };

    const BASE_MODAL_Z_INDEX = 1050; // Z-index của modal hiện tại (ví dụ productEditModal)
    const CROP_MODAL_Z_INDEX = BASE_MODAL_Z_INDEX + 10; // Ví dụ: 1060
    const CROP_MODAL_BACKDROP_Z_INDEX = CROP_MODAL_Z_INDEX - 5; // Ví dụ: 1055

    function init() {
        cropperModalEl = document.getElementById('imageCropperModal'); // ID modal crop của bạn
        imageToCropEl = document.getElementById('imageToCropInModal'); // ID thẻ <img> bên trong modal crop

        if (!cropperModalEl || !imageToCropEl) {
            console.error('HTML cho Image Cropper Modal không đầy đủ (thiếu #imageCropperModal hoặc #imageToCropInModal).');
            return;
        }

        cropperModalInstance = new bootstrap.Modal(cropperModalEl);

        cropperModalEl.addEventListener('shown.bs.modal', function () {
            // Tăng z-index khi modal crop được hiển thị
            $(cropperModalEl).css('z-index', CROP_MODAL_Z_INDEX);
            // Tìm backdrop của modal này và tăng z-index (Bootstrap 5 tạo backdrop ngay sau modal trong DOM)
            $('.modal-backdrop.show').last().css('z-index', CROP_MODAL_BACKDROP_Z_INDEX);

            if (cropperJsInstance) {
                cropperJsInstance.destroy();
            }
            if (imageToCropEl.src && imageToCropEl.src !== '#' && !imageToCropEl.src.startsWith(window.location.href) ) {
                var aspectRatioSetting = NaN; // Mặc định là tự do
                if (currentCroppingConfig.context) {
                    switch(currentCroppingConfig.context.type) {
                        case 'thumbnail':
                        case 'editThumbnail':
                            aspectRatioSetting = 1/1; // Vuông cho thumbnail
                            break;
                        default:
                            aspectRatioSetting = 1/1; // Mặc định vẫn là vuông nếu type không xác định rõ ràng cho tỷ lệ khác
                    }
                }

                cropperJsInstance = new Cropper(imageToCropEl, {
                    aspectRatio: aspectRatioSetting,
                    viewMode: 1,
                    dragMode: 'move',
                    responsive: true,
                    autoCropArea: 0.85,
                    checkCrossOrigin: false, // Quan trọng khi dùng createObjectURL
                    // guides: false,
                    // background: false,
                });
            } else {
                console.warn("Không có ảnh nguồn hợp lệ để khởi tạo Cropper. Đóng modal crop.");
                if(cropperModalInstance) cropperModalInstance.hide();
            }
        });

        cropperModalEl.addEventListener('hidden.bs.modal', function () {
            $(cropperModalEl).css('z-index', '');
            if (cropperJsInstance) {
                cropperJsInstance.destroy();
                cropperJsInstance = null;
            }
            $(imageToCropEl).attr('src', '#'); // Reset ảnh
            currentCroppingConfig.context = null;
            currentCroppingConfig.onComplete = null;
        });

        $('#confirmCropButton').on('click', function() { // ID nút "Cắt & Sử dụng"
            if (cropperJsInstance && typeof currentCroppingConfig.onComplete === 'function') {
                const canvas = cropperJsInstance.getCroppedCanvas({
                    width: 800, // Giới hạn output
                    height: 800,
                    fillColor: '#fff',
                    imageSmoothingQuality: 'medium' // 'low', 'medium', 'high'
                });
                if (canvas) {
                    const croppedDataURL = canvas.toDataURL('image/jpeg', 0.85); // Chất lượng JPEG
                    currentCroppingConfig.onComplete(croppedDataURL, currentCroppingConfig.context);
                    if(cropperModalInstance) cropperModalInstance.hide();
                } else {
                    alert('Không thể tạo ảnh đã cắt. Vui lòng thử lại.');
                }
            } else {
                console.error("Cropper instance chưa sẵn sàng hoặc callback chưa được đặt.");
                if(cropperModalInstance) cropperModalInstance.hide();
            }
        });

        $('#cancelCropButton').on('click', function() { // Xử lý nút Hủy trong modal crop
            if(cropperModalInstance) cropperModalInstance.hide();
        });
        $('#closeImageCropperModal').on('click', function() { // Xử lý nút 'x' trong modal crop
            if(cropperModalInstance) cropperModalInstance.hide();
        });


        console.log("Image Cropper Module Initialized.");
    }

    // Phương thức public để các module khác gọi
    function show(imageUrl, context, callback) {
        if (!cropperModalInstance) {
            console.error("Image Cropper Modal chưa được khởi tạo.");
            return;
        }
        currentCroppingConfig.context = context;
        currentCroppingConfig.onComplete = callback;
        $(imageToCropEl).attr('src', imageUrl);
        cropperModalInstance.show();
    }

    return {
        init: init,
        show: show
    };

})(jQuery);

// Khởi tạo module ImageCropper sau khi DOM sẵn sàng
$(document).ready(function() {
    if (typeof Cropper !== 'undefined') { // Chỉ khởi tạo nếu thư viện CropperJS đã được tải
        MyAppAdmin.ImageCropper.init();
    } else {
        console.error("Thư viện Cropper.js chưa được tải. Chức năng crop ảnh sẽ không hoạt động.");
    }
});