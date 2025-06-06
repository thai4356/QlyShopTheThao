$(document).ready(function() {
    console.log('Initializing DataTables for #categoriesTable from categories-datatable-init.js');
    if ($('#categoriesTable').length) {
        console.log('#categoriesTable element found.');
    } else {
        console.error('#categoriesTable element NOT found!');
        return;
    }

    $('#categoriesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "index.php?ctrl=admincategory&act=ajaxGetCategoriesForDataTable",
            "type": "POST",
            "error": function (xhr, error, thrown) {
                console.error("DataTables AJAX error: ", error);
                console.error("Thrown error: ", thrown);
                console.error("ResponseText: ", xhr.responseText);
                $('#categoriesTable_processing').hide();
                // Bạn có thể dùng SweetAlert ở đây nếu muốn thông báo lỗi thân thiện hơn
                alert("Lỗi khi tải dữ liệu cho bảng. Vui lòng kiểm tra console.");
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "name" },
            { "data": "description" },
            { "data": "status_html", "orderable": false, "searchable": false },
            { "data": "actions_html", "orderable": false, "searchable": false }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
        },
        "responsive": true,
        "stateSave": true
    });

});