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
    }
    $year = date('Y', strtotime($dateInput));
    // thực hiện việc trích xuất năm từ một chuỗi ngày tháng cụ thể được cung cấp trong biến $dateInput
    if ($filterType == 'year') {
        // Doanh thu
        $sql = firstRaw("SELECT SUM(sotien) as tong_thu FROM receipt WHERE YEAR(ngaythu) = $year");
        $tongthu = $sql['tong_thu'];

        $sql = firstRaw("
        SELECT 
            SUM(tong_tien) AS tong_tien
        FROM (
            SELECT SUM(sotien) AS tong_tien 
            FROM receipt 
            WHERE YEAR(ngaythu) = $year
            
            UNION ALL
            
            SELECT SUM(tongtien) AS tong_tien 
            FROM bill 
            WHERE YEAR(create_at) = $year
            
            UNION ALL
            
            SELECT SUM(contract.sotiencoc) AS tong_tien
            FROM contract
            LEFT JOIN receipt ON receipt.contract_id = contract.id
            WHERE YEAR(contract.create_at) = $year
            AND receipt.contract_id IS NULL
        ) AS combined
        ");
        $tongthudukien = $sql['tong_tien'];
        $tongthuconthieu = $tongthudukien - $tongthu;

        // Tiền cọc
        $sql = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND danhmucthu_id = 2");
        $tiencoc = $sql['tien_coc'];

        $sqlReceipt = firstRaw("SELECT SUM(sotien) as tien_coc FROM receipt WHERE YEAR(ngaythu) = $year AND danhmucthu_id = 2");

        $sqlContract = firstRaw("SELECT SUM(contract.sotiencoc) AS tien_coc_contract 
        FROM contract 
        WHERE YEAR(contract.create_at) = $year 
        AND NOT EXISTS (
          SELECT 1 
          FROM receipt 
          WHERE receipt.contract_id = contract.id
        )");

        $tiencocdukien = ($sqlReceipt['tien_coc'] ?? 0) + ($sqlContract['tien_coc_contract'] ?? 0);
        $tiencocconthieu = $tiencocdukien - $tiencoc;

        // Tiền chi
        $sql = firstRaw("SELECT SUM(sotien) as tong_chi FROM payment WHERE YEAR(ngaychi) = $year");
        $tongchi = $sql['tong_chi'];

        $loinhuan = $tongthu - $tongchi - $tiencoc;
        $labels[] = "$year";
        $profits = [$loinhuan];
        $loinhuandukien = $tongthudukien - $tongchi - $tiencocdukien;
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
                            <option value="" disabled selected>Tổng kết theo</option>
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
            <a href="<?php echo getLinkAdmin('sumary', 'search') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Tra cứu</a>
            <a href="<?php echo getLinkAdmin('sumary', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <h3 class="sumary-title">THỐNG KÊ DOANH THU</h3>
            <!-- <p><i>Số liệu dưới đây mặc định được thống kê trong tháng hiện tại</i></p> -->
            <p style="color:red"><i>Lợi nhuận (thực tế) = ( Tổng thu (đã thu) - tổng chi - tổng tiền cọc ) </i></p>

            <div class="report-receipt-spend">
                <div class="report-receipt">
                    <p>Tổng thu (dự kiến) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthudukien, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-receipt">
                    <p>Tổng thu (đã thu) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-receipt">
                    <p>Tổng thu (chưa thu) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthuconthieu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
            </div>
            <!-- <div class="report-receipt-spend">
                <div class="report-spend">
                    <p>Tổng tiền cọc (dự kiến)</p>
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
                    <p>Tổng tiền cọc (chưa thu)</p>
                    <div class="report-ts">
                        <img src="" alt="">
                        <p style="color: red"><?php echo number_format($tiencocconthieu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
            </div> -->
            <div class="report-receipt-spend">
                <div class="report-spend">
                    <p>Tổng tiền cọc (đã thu)</p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: red"><?php echo number_format($tiencoc, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-spend">
                    <p>Tổng chi (tiền ra)</p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-down.svg" alt="">
                        <p style="color: orange"><?php echo number_format($tongchi, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-spend">
                    <p>Lợi nhuận (dự kiến)</p>
                    <div class="report-ts">
                        <img src="" alt="">
                        <p><?php echo number_format($loinhuandukien, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-spend">
                    <p>Lợi nhuận (thực tế)</p>
                    <div class="report-ts">
                        <img src="" alt="">
                        <p><?php echo number_format($loinhuan, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
layout('footer', 'admin');
?>