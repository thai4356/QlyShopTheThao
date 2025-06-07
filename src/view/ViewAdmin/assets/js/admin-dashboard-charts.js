$(document).ready(function() {

    // BƯỚC 1: Khai báo một biến bên ngoài để lưu đối tượng biểu đồ
    let myRevenueChart;

    // Hàm này dùng để cấu hình và vẽ biểu đồ ban đầu
    function initializeChart(chartData) {
        const canvasElement = document.getElementById('revenueChart');
        if (canvasElement && typeof Chart !== 'undefined') {
            const ctx = canvasElement.getContext('2d');

            // BƯỚC 2: Khi tạo biểu đồ mới, gán nó vào biến myRevenueChart
            myRevenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: "Doanh thu",
                        borderColor: '#1d7af3',
                        pointBorderColor: "#FFF",
                        pointBackgroundColor: "#1d7af3",
                        backgroundColor: 'rgba(29, 122, 243, 0.2)',
                        fill: true,
                        borderWidth: 2,
                        data: chartData.data
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000) + ' Tr';
                                    if (value >= 1000) return (value / 1000) + ' K';
                                    return value;
                                }
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }

    // Vẽ biểu đồ lần đầu với dữ liệu mặc định (đã được in ra từ PHP)
    if (typeof revenueChartData !== 'undefined') {
        initializeChart(revenueChartData);
    }

    if (typeof categoryChartData !== 'undefined' && document.getElementById('categoryChart')) {
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');

        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryChartData.labels,
                datasets: [{
                    data: categoryChartData.data,
                    // Chart.js sẽ tự tạo màu, hoặc bạn có thể định nghĩa một mảng màu ở đây
                    backgroundColor: [
                        '#1d7af3',
                        '#f3545d',
                        '#fdaf4b',
                        '#59d05d',
                        '#48abf7',
                        '#716cb0',
                        '#a0b4c9'
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Để biểu đồ lấp đầy thẻ chứa
                plugins: {
                    legend: {
                        position: 'bottom', // Hiển thị chú thích ở dưới
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.raw !== null) {
                                    // Hiển thị số lượng sản phẩm
                                    label += context.raw + ' sản phẩm';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // BƯỚC 3: Thêm sự kiện 'change' cho ComboBox
    $('#revenue-chart-filter').on('change', function() {
        const selectedRange = $(this).val();

        // Gửi yêu cầu AJAX để lấy dữ liệu mới
        $.ajax({
            url: 'index.php?ctrl=admindashboard&act=ajaxGetChartData',
            type: 'POST',
            data: { range: selectedRange },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && myRevenueChart) {
                    // BƯỚC 4: Cập nhật dữ liệu của biểu đồ đã có
                    myRevenueChart.data.labels = response.chart_data.labels;
                    myRevenueChart.data.datasets[0].data = response.chart_data.data;
                    myRevenueChart.update(); // Vẽ lại biểu đồ với dữ liệu mới
                } else {
                    console.error("Lỗi khi lấy dữ liệu biểu đồ: " + response.message);
                }
            },
            error: function() {
                console.error("Lỗi kết nối AJAX để cập nhật biểu đồ.");
                // Có thể thêm thông báo lỗi cho người dùng ở đây
            }
        });
    });
});