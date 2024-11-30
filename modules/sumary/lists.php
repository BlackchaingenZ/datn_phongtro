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

if ($filterType && $dateInput) {
    if ($filterType == 'month') {
        $year = date('Y', strtotime($dateInput));
        $month = date('m', strtotime($dateInput));

        // Doanh thu
        $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
        $tongthu = $sql['tong_thu'];

        $sql = firstRaw("
        SELECT 
            SUM(tong_tien) AS tong_tien
        FROM (
            SELECT SUM(sotien) AS tong_tien 
            FROM receipt 
            WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month
            
            UNION ALL
            
            SELECT SUM(tongtien) AS tong_tien 
            FROM bill 
            WHERE YEAR(create_at) = $year AND MONTH(create_at) = $month
            
            UNION ALL
            
        SELECT SUM(contract.sotiencoc) AS tong_tien
        FROM contract
        LEFT JOIN receipt ON receipt.contract_id = contract.id
        WHERE YEAR(contract.create_at) = $year 
        AND MONTH(contract.create_at) = $month
        AND receipt.contract_id IS NULL

        ) AS combined
    ");
        $tongthudukien = $sql['tong_tien'];
        $tongthuconthieu = $tongthudukien - $tongthu;
        // Tiền cọcS
        $sql = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month AND danhmucthu_id = 2");
        $tiencoc = $sql['tien_coc'];

        // Truy vấn tổng tiền cọc từ bảng receipt
        $sqlReceipt = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month AND danhmucthu_id = 2");

        $sqlContract = firstRaw("SELECT SUM(contract.sotiencoc) AS tien_coc_contract 
        FROM contract 
        WHERE YEAR(contract.create_at) = $year 
        AND MONTH(contract.create_at) = $month 
        AND NOT EXISTS (
          SELECT 1 
          FROM receipt 
          WHERE receipt.contract_id = contract.id
      )");

        // Lấy tổng tiền cọc từ cả hai bảng
        $tiencocdukien = ($sqlReceipt['tien_coc'] ?? 0) + ($sqlContract['tien_coc_contract'] ?? 0);
        $tiencocconthieu = $tiencocdukien - $tiencoc;

        // Tiền chi
        $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
        $tongchi = $sql['tong_chi'];

        $loinhuan = $tongthu - $tongchi - $tiencoc;

        $labels[] = "$month-$year";
        $profits = [$loinhuan];
        $loinhuandukien = $tongthudukien - $tongchi - $tiencocdukien;
    } elseif ($filterType == 'year') {
        $year = date('Y', strtotime($dateInput));

        for ($month = 1; $month <= 12; $month++) {
            // Tính tổng thu từ bảng receipt
            $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
            $monthly_thu = $sql['tong_thu'] ?: 0;

            // Tính tổng tiền đặt cọc trong receipt
            $sql = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month AND danhmucthu_id = 2");
            $monthly_datcoc = $sql['tien_coc'] ?: 0;

            // Tính tổng thu từ bảng bill
            $sql = firstRaw("SELECT SUM(tongtien) as tong_bill FROM bill WHERE YEAR(create_at) = $year AND MONTH(create_at) = $month");
            $monthly_bill = $sql['tong_bill'] ?: 0;

            // Tính tổng tiền đặt cọc trong hợp đồng
            $sql = firstRaw("SELECT SUM(sotiencoc) as tong_coc FROM contract WHERE YEAR(create_at) = $year AND MONTH(create_at) = $month");
            $monthly_coc = $sql['tong_coc'] ?: 0;

            // Tính tổng thu nhập của tháng
            $total_income = $monthly_thu + $monthly_bill + $monthly_coc;

            // Tính tổng chi từ bảng payment
            $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
            $monthly_chi = $sql['tong_chi'] ?: 0;

            // Lợi nhuận của tháng (tổng thu - tổng chi)
            $monthly_profit = $total_income - $monthly_chi;

            // Lưu lại thông tin cho đồ thị
            $labels[] = "$month-$year";
            $profits[] = $monthly_profit;
        }


        $tongthudukien = array_sum(array_map(function ($month) use ($year) {
            $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
            return $sql['tong_thu'] ?: 0;
        }, range(1, 12)));
        $tongthuconthieu = $tongthudukien - $tongthu;
        $tiencoc = array_sum(array_map(function ($month) use ($year) {
            $sql = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month AND danhmucthu_id = 2");
            return $sql['tien_coc'] ?: 0;
        }, range(1, 12)));

        $tongchi = array_sum(array_map(function ($month) use ($year) {
            $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
            return $sql['tong_chi'] ?: 0;
        }, range(1, 12)));

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
            <a href="<?php echo getLinkAdmin('sumary', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <h3 class="sumary-title">Thống kê doanh thu theo từng tháng</h3>
            <p><i>Số liệu dưới đây mặc định được thống kê trong tháng hiện tại</i></p>
            <p style="color:red"><i>Lợi nhuận = ( Tổng khoản thu(đã thu) - tổng khoản chi - tổng tiền cọc ) </i></p>

            <div class="report-receipt-spend">
                <div class="report-receipt">
                    <p>Tổng khoản thu(dự kiến) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthudukien, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-receipt">
                    <p>Tổng khoản thu(đã thu) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-receipt">
                    <p>Tổng khoản thu(chưa thu) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthuconthieu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
            </div>
            <div class="report-receipt-spend">
                <div class="report-spend">
                    <p>Tổng tiền cọc(dự kiến)</p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-down.svg" alt="">
                        <p style="color: red"><?php echo number_format($tiencocdukien, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>

                <div class="report-spend">
                    <p>Tổng tiền cọc (đã thu)</p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: red"><?php echo number_format($tiencoc, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>

                <div class="report-spend">
                    <p>Tổng tiền cọc(chưa thu)</p>
                    <div class="report-ts">
                        <img src="" alt="">
                        <p style="color: red"><?php echo number_format($tiencocconthieu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
            </div>
            <div class="report-receipt-spend">
                <div class="report-spend">
                    <p>Tổng khoản chi (tiền ra)</p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-down.svg" alt="">
                        <p style="color: orange"><?php echo number_format($tongchi, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-spend">
                    <p>Lợi nhuận(dự kiến)</p>
                    <div class="report-ts">
                        <img src="" alt="">
                        <p><?php echo number_format($loinhuandukien, 0, ',', '.') . 'đ'; ?></p>
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

        </div>

        <div class="sumary-right">
            <h3 class="" style="text-align:center">Biểu đồ lợi nhuận</h3>
            <canvas id="profitChart" width="400" height="200"></canvas>

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
</script>