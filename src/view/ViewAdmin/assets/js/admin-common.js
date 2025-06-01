// Khởi tạo namespace chung cho các module admin nếu chưa có
var MyAppAdmin = window.MyAppAdmin || {};

$(document).ready(function() {
    // Khởi tạo tooltip (nếu dùng Bootstrap 5)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Các khởi tạo chung khác có thể đặt ở đây
    console.log("Admin common scripts loaded.");
});