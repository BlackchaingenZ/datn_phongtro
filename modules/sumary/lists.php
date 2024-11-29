<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Báo cáo thu chi'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$currentMonthYear = date('Y-m');
$currentYear = date('Y');

$filterType = isset($_POST['filter_type']) ? $_POST['filter_type'] : 'month';
$dateInput = isset($_POST['date_input']) ? $_POST['date_input'] : $currentMonthYear;

$tongthu = 0;
$tongchi = 0;
$loinhuan = 0;

$labels = [];
$profits = [];

// Tính doanh thu theo từng khu vực
$areaRevenue = []; // Khởi tạo mảng lưu trữ doanh thu từng khu vực
$sql = "SELECT tenkhuvuc AS tenkhuvuc, SUM(receipt.sotien) AS doanhthu
        FROM area
        JOIN area_room ON area.id = area_room.area_id
        JOIN room ON area_room.room_id = room.id
        JOIN receipt ON room.id = receipt.room_id
        WHERE receipt.ngaythu IS NOT NULL
        GROUP BY tenkhuvuc
        ORDER BY doanhthu DESC";
$areaRevenue = getRaw($sql); // Lấy kết quả từ cơ sở dữ liệu

// Tính doanh thu theo từng phòng
$roomRevenue = []; // Khởi tạo mảng lưu trữ doanh thu từng phòng
$sqlRoom = "SELECT tenphong AS tenphong, SUM(receipt.sotien) AS doanhthu
            FROM room
            JOIN receipt ON room.id = receipt.room_id
            WHERE receipt.ngaythu IS NOT NULL
            GROUP BY tenphong
            ORDER BY doanhthu DESC";
$roomRevenue = getRaw($sqlRoom); // Lấy kết quả từ cơ sở dữ liệu



if ($filterType && $dateInput) {
    if ($filterType == 'month') {
        $year = date('Y', strtotime($dateInput));
        $month = date('m', strtotime($dateInput));

        // Doanh thu
        $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
        $tongthu = $sql['tong_thu'];

        // Tiền cọc
        $sql = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month AND danhmucthu_id = 2");
        $tiencoc = $sql['tien_coc'];

        // Tiền chi
        $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
        $tongchi = $sql['tong_chi'];

        $loinhuan = $tongthu - $tongchi - $tiencoc;

        $labels[] = "$month-$year";
        $profits = [$loinhuan];
    } elseif ($filterType == 'year') {
        $year = date('Y', strtotime($dateInput));

        for ($month = 1; $month <= 12; $month++) {
            $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
            $monthly_thu = $sql['tong_thu'] ?: 0;

            // Tính tổng tiền đặt cọc hàng tháng
            $sql = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month AND danhmucthu_id = 2");
            $monthly_datcoc = $sql['tien_coc'] ?: 0;

            $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
            $monthly_chi = $sql['tong_chi'] ?: 0;

            $monthly_profit = $monthly_thu - $monthly_chi - $monthly_datcoc;

            $labels[] = "$month-$year";
            $profits[] = $monthly_profit;
        }

        $tongthu = array_sum(array_map(function ($month) use ($year) {
            $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
            return $sql['tong_thu'] ?: 0;
        }, range(1, 12)));

        $tiencoc = array_sum(array_map(function ($month) use ($year) {
            $sql = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month AND danhmucthu_id = 2");
            return $sql['tien_coc'] ?: 0;
        }, range(1, 12)));

        $tongchi = array_sum(array_map(function ($month) use ($year) {
            $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
            return $sql['tong_chi'] ?: 0;
        }, range(1, 12)));

        $loinhuan = $tongthu - $tongchi - $tiencoc;
    } elseif ($filterType == 'quarter') {
        $year = date('Y', strtotime($dateInput));

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;

            $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) BETWEEN $startMonth AND $endMonth");
            $quarterly_thu = $sql['tong_thu'] ?: 0;

            $sql = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND danhmucthu_id = 2 AND MONTH(ngaythu) BETWEEN $startMonth AND $endMonth");
            $quarterly_coc = $sql['tien_coc'] ?: 0;

            $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) BETWEEN $startMonth AND $endMonth");
            $quarterly_chi = $sql['tong_chi'] ?: 0;

            $quarterly_profit = $quarterly_thu - $quarterly_chi - $quarterly_coc;

            $labels[] = "Quý $quarter / $year";
            $profits[] = $quarterly_profit;
        }

        $currentQuarter = ceil(date('n', strtotime($dateInput)) / 3);
        $startMonth = ($currentQuarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        $tongthu = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) BETWEEN $startMonth AND $endMonth")['tong_thu'];
        $tongchi = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) BETWEEN $startMonth AND $endMonth")['tong_chi'];
        $tiencoc = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND danhmucthu_id = 2 AND MONTH(ngaythu) BETWEEN $startMonth AND $endMonth")['tien_coc'];
        $loinhuan = $tongthu - $tongchi - $tiencoc;
    }
}

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>

<?php
layout('navbar', 'admin', $data);
?>
<div class="container-fluid">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content sumary-content">
        <div class="sumary-left">
            <form method="POST" action="" style="margin-bottom: 30px">
                <div class="row">
                    <div class="col-3">
                        <select name="filter_type" class="form-select" onchange="toggleInputType()">
                            <option value="">Tổng kết theo</option>
                            <option value="month" <?php echo ($filterType == 'month') ? 'selected' : ''; ?>>Theo tháng</option>
                            <option value="quarter" <?php echo ($filterType == 'quarter') ? 'selected' : ''; ?>>Theo quý</option>
                            <option value="year" <?php echo ($filterType == 'year') ? 'selected' : ''; ?>>Theo năm</option>
                        </select>
                    </div>

                    <div class="col-3" id="date_input_container">
                        <input type="month" name="date_input" class="form-control" value="<?php echo $dateInput; ?>" <?php echo !$filterType ? 'disabled' : ''; ?>>
                    </div>

                    <div class="col">
                        <button style="height: 50px; width: 50px" type="submit" class="btn btn-secondary" <?php echo !$filterType ? 'disabled' : ''; ?>><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
            <h3 class="sumary-title">Thống kê doanh thu theo từng tháng</h3>
            <p><i>Số liệu dưới đây mặc định được thống kê trong tháng hiện tại</i></p>
            <p style="color:red"><i>Lợi nhuận = ( tổng khoản thu - tổng khoản chi - tổng tiền cọc ) </i></p>

            <div class="report-receipt-spend">
                <div class="report-receipt">
                    <p>Tổng khoản thu </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p><?php echo number_format($tongthu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>

                <div class="report-spend">
                    <p>Tổng khoản chi (tiền ra)</p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-down.svg" alt="">
                        <p style="color: red"><?php echo number_format($tongchi, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>

                <div class="report-spend">
                    <p>Tổng tiền cọc (đã thu)</p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-down.svg" alt="">
                        <p style="color: #ed6004"><?php echo number_format($tiencoc, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>

                <div class="report-spend">
                    <p>Lợi nhuận</p>
                    <div class="report-ts">
                        <img src="" alt="">
                        <p><?php echo number_format($loinhuan, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
            </div>
            <br>


            <div class="report-area-revenue">
                <h3 class="sumary-title">Doanh thu theo từng khu vực</h3>
                <p></p>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Khu vực</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($areaRevenue)) : ?>
                            <?php foreach ($areaRevenue as $area) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($area['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo number_format($area['doanhthu'], 0, ',', '.') . 'đ'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="2">Không có dữ liệu doanh thu.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <br>


            <div class="report-area-revenue">
                <h3 class="sumary-title">Doanh thu theo từng phòng</h3>
                <p></p>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tên Phòng</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($roomRevenue)) : ?>
                            <?php foreach ($roomRevenue as $room) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($room['tenphong'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo number_format($room['doanhthu'], 0, ',', '.') . 'đ'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="2">Không có dữ liệu doanh thu.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sumary-right">
            <h3 class="" style="text-align:center">Biểu đồ lợi nhuận</h3>
            <canvas id="profitChart" width="400" height="200"></canvas>
            <p></p>
            <!-- Thêm phần tử canvas cho biểu đồ -->
            <h3 style="text-align:center">Biểu đồ doanh thu theo khu vực</h3>
            <div style="display: flex; justify-content: center; align-items: center; height: 35vh;">
                <canvas id="revenueChart1"></canvas>
            </div>
            <p></p>
            <h3 style="text-align:center">Biểu đồ doanh thu theo phòng</h3>
            <div style="display: flex; justify-content: center; align-items: center; height: 43vh;">
                <canvas id="revenueChart2"></canvas>
            </div>
        </div>

    </div>
</div>

<?php
layout('footer', 'admin');
?>
<!-- Thêm thư viện Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Hàm toggleInputType điều chỉnh trạng thái của input và nút submit
    function toggleInputType() {
        const filterType = document.querySelector('select[name="filter_type"]').value;
        const dateInput = document.querySelector('input[name="date_input"]');
        const submitButton = document.querySelector('button[type="submit"]');

        if (!filterType) {
            dateInput.disabled = true;
            submitButton.disabled = true;
        } else {
            dateInput.disabled = false;
            submitButton.disabled = false;
        }

        if (filterType === 'year') {
            dateInput.type = 'month';
            dateInput.setAttribute('data-filter-type', 'year');
        } else if (filterType === 'quarter') {
            dateInput.type = 'month';
            dateInput.setAttribute('data-filter-type', 'quarter');
        } else {
            dateInput.type = 'month';
            dateInput.setAttribute('data-filter-type', 'month');
        }
    }

    // Chạy toggleInputType khi tài liệu đã sẵn sàng
    document.addEventListener('DOMContentLoaded', function() {
        toggleInputType();
    });

    // Dữ liệu lợi nhuận và vẽ biểu đồ Bar
    const ctxProfit = document.getElementById('profitChart').getContext('2d');
    const profitChart = new Chart(ctxProfit, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Lợi nhuận',
                data: <?php echo json_encode($profits); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Dữ liệu doanh thu theo khu vực và vẽ biểu đồ Pie
    var areaRevenueData = <?php echo json_encode($areaRevenue); ?>;
    var areaNames = areaRevenueData.map(function(area) {
        return area.tenkhuvuc;
    });

    var revenueValues = areaRevenueData.map(function(area) {
        return area.doanhthu;
    });

    // Lấy ngữ cảnh của canvas và tạo biểu đồ hình tròn
    var ctxRevenue = document.getElementById('revenueChart1').getContext('2d');
    var revenueChart = new Chart(ctxRevenue, {
        type: 'pie', // Loại biểu đồ là hình tròn
        data: {
            labels: areaNames, // Các tên khu vực
            datasets: [{
                label: 'Doanh thu theo khu vực',
                data: revenueValues, // Dữ liệu doanh thu
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'], // Màu sắc của từng phần
                borderColor: '#fff', // Màu đường viền
                borderWidth: 1
            }]
        },
        options: {
            responsive: true, // Đảm bảo biểu đồ đáp ứng với kích thước màn hình
            plugins: {
                legend: {
                    position: 'top', // Vị trí của legend (thanh chú thích)
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            // Định dạng tooltip hiển thị doanh thu
                            return tooltipItem.label + ': ' + tooltipItem.raw.toLocaleString() + 'đ';
                        }
                    }
                }
            }
        }
    });

    // Dữ liệu doanh thu theo phòng và vẽ biểu đồ Pie
    var roomRevenueData = <?php echo json_encode($roomRevenue); ?>;
    var roomNames = roomRevenueData.map(function(room) {
        return room.tenphong;
    });

    var revenueValues = roomRevenueData.map(function(room) {
        return room.doanhthu;
    });

    // Lấy ngữ cảnh của canvas và tạo biểu đồ hình tròn
    var ctxRevenue = document.getElementById('revenueChart2').getContext('2d');
    var revenueChart = new Chart(ctxRevenue, {
        type: 'pie', // Loại biểu đồ là hình tròn
        data: {
            labels: roomNames, // Các tên khu vực
            datasets: [{
                label: 'Doanh thu theo phòng',
                data: revenueValues, // Dữ liệu doanh thu
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'], // Màu sắc của từng phần
                borderColor: '#fff', // Màu đường viền
                borderWidth: 1
            }]
        },
        options: {
            responsive: true, // Đảm bảo biểu đồ đáp ứng với kích thước màn hình
            plugins: {
                legend: {
                    position: 'top', // Vị trí của legend (thanh chú thích)
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            // Định dạng tooltip hiển thị doanh thu
                            return tooltipItem.label + ': ' + tooltipItem.raw.toLocaleString() + 'đ';
                        }
                    }
                }
            }
        }
    });
</script>