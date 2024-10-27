<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Thu/Chi - Tổng kết'
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

        $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
        $tongthu = $sql['tong_thu'];

        $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
        $tongchi = $sql['tong_chi'];

        $loinhuan = $tongthu - $tongchi;

        $labels = ["$year-$month"];
        $profits = [$loinhuan];

    } elseif ($filterType == 'year') {
        $year = date('Y', strtotime($dateInput));

        for ($month = 1; $month <= 12; $month++) {
            $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
            $monthly_thu = $sql['tong_thu'] ?: 0;

            $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
            $monthly_chi = $sql['tong_chi'] ?: 0;

            $monthly_profit = $monthly_thu - $monthly_chi;

            $labels[] = "$year-$month";
            $profits[] = $monthly_profit;
        }

        $tongthu = array_sum(array_map(function($month) use ($year) {
            $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) = $month");
            return $sql['tong_thu'] ?: 0;
        }, range(1, 12)));

        $tongchi = array_sum(array_map(function($month) use ($year) {
            $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) = $month");
            return $sql['tong_chi'] ?: 0;
        }, range(1, 12)));

        $loinhuan = $tongthu - $tongchi;

    } elseif ($filterType == 'quarter') {
        $year = date('Y', strtotime($dateInput));

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;

            $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) BETWEEN $startMonth AND $endMonth");
            $quarterly_thu = $sql['tong_thu'] ?: 0;

            $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) BETWEEN $startMonth AND $endMonth");
            $quarterly_chi = $sql['tong_chi'] ?: 0;

            $quarterly_profit = $quarterly_thu - $quarterly_chi;

            $labels[] = "Quý $quarter / $year";
            $profits[] = $quarterly_profit;
        }

        $currentQuarter = ceil(date('n', strtotime($dateInput)) / 3);
        $startMonth = ($currentQuarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        $tongthu = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year AND MONTH(ngaythu) BETWEEN $startMonth AND $endMonth")['tong_thu'];
        $tongchi = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year AND MONTH(ngaychi) BETWEEN $startMonth AND $endMonth")['tong_chi'];
        $loinhuan = $tongthu - $tongchi;
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
                        <button style="height: 50px; width: 50px" type="submit" class="btn btn-success" <?php echo !$filterType ? 'disabled' : ''; ?>><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>

            <a href="<?php echo getLinkAdmin('collect'); ?>" class="btn btn-success min"><i class="fa fa-save"></i> Quản lý danh mục thu</a>
            <a href="<?php echo getLinkAdmin('spend'); ?>" class="btn btn-success min"><i class="fa fa-save"></i> Quản lý danh mục chi</a>
            <a href="<?php echo getLinkAdmin('receipt'); ?>" class="btn btn-success min"><i class="fa fa-save"></i> Quản lý phiếu thu</a>
            <a href="<?php echo getLinkAdmin('payment'); ?>" class="btn btn-success min"><i class="fa fa-save"></i> Quản lý phiếu chi</a>

            <h3 class="sumary-title">Thống kê doanh thu theo từng tháng</h3>
            <p><i>Số liệu dưới đây mặc định được thống kê trong tháng hiện tại</i></p>
            
            <div class="report-receipt-spend">
                <div class="report-receipt">
                    <p>Tổng khoản thu (tiền vào)</p>
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
                    <p>Lợi nhuận</p>
                    <div class="report-ts">
                        <img src="" alt="">
                        <p><?php echo number_format($loinhuan, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="sumary-right">
            <h3 class="">Biểu đồ lợi nhuận</h3>
            <canvas id="profitChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<?php
layout('footer', 'admin');
?>

<script>
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

    document.addEventListener('DOMContentLoaded', function() {
        toggleInputType();
    });

    const ctx = document.getElementById('profitChart').getContext('2d');
    const profitChart = new Chart(ctx, {
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

       
