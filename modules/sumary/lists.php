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
            <a href="<?php echo getLinkAdmin('sumary', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <h3 class="sumary-title">THỐNG KÊ DOANH THU</h3>
            <!-- <p><i>Số liệu dưới đây mặc định được thống kê trong tháng hiện tại</i></p> -->
            <p style="color:red"><i>Lợi nhuận (thực tế) = ( Tổng khoản thu (đã thu) - tổng khoản chi - tổng tiền cọc ) </i></p>

            <div class="report-receipt-spend">
                <div class="report-receipt">
                    <p>Tổng khoản thu (dự kiến) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthudukien, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-receipt">
                    <p>Tổng khoản thu (đã thu) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
                <div class="report-receipt">
                    <p>Tổng khoản thu (chưa thu) </p>
                    <div class="report-ts">
                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/trend-up.svg" alt="">
                        <p style="color: blue"><?php echo number_format($tongthuconthieu, 0, ',', '.') . 'đ'; ?></p>
                    </div>
                </div>
            </div>
            <div class="report-receipt-spend">
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

            <?php
            // Kiểm tra nếu người dùng đã gửi form tìm kiếm
            $month = isset($_POST['month']) ? $_POST['month'] : '';
            $year = isset($_POST['year']) ? $_POST['year'] : '';

            $sql_chuathu = "
            SELECT 
                room.tenphong AS tenphong,
                bill.tongtien AS tongtien
            FROM 
                room
            INNER JOIN 
                bill
            ON 
                room.id = bill.room_id
            LEFT JOIN 
                receipt
            ON 
                bill.id = receipt.bill_id
            WHERE 
                 receipt.bill_id IS NULL
        ";

            // Thêm điều kiện tìm kiếm theo tháng/năm cho phòng chưa thu
            if ($month) {
                $sql_chuathu .= " AND MONTH(bill.create_at) = :month";
            }
            if ($year) {
                $sql_chuathu .= " AND YEAR(bill.create_at) = :year";
            }

            $sql_chuathu .= " ORDER BY room.tenphong ASC";

            // Truy vấn lấy danh sách phòng đã thu
            $sql_dathu = "
    SELECT 
        room.tenphong AS tenphong,
        bill.sotiendatra AS sotiendatra
    FROM 
        room
    INNER JOIN 
        bill
    ON 
        room.id = bill.room_id
    WHERE 
        bill.trangthaihoadon = 1
";

            // Thêm điều kiện tìm kiếm theo tháng/năm cho phòng đã thu
            if ($month) {
                $sql_dathu .= " AND MONTH(bill.create_at) = :month";
            }
            if ($year) {
                $sql_dathu .= " AND YEAR(bill.create_at) = :year";
            }

            $sql_dathu .= " ORDER BY room.tenphong ASC";

            // Truy vấn lấy danh sách phòng còn thiếu
            $sql_conno = "
    SELECT 
        room.tenphong AS tenphong,
        bill.sotienconthieu AS sotienconthieu
    FROM 
        room
    INNER JOIN 
        bill
    ON 
        room.id = bill.room_id
    WHERE 
        bill.trangthaihoadon = 3
";

            // Thêm điều kiện tìm kiếm theo tháng/năm cho phòng còn nợ
            if ($month) {
                $sql_conno .= " AND MONTH(bill.create_at) = :month";
            }
            if ($year) {
                $sql_conno .= " AND YEAR(bill.create_at) = :year";
            }

            $sql_conno .= " ORDER BY room.tenphong ASC";
            // Chuẩn bị truy vấn và thực thi cho phòng chưa thu
            $stmt_chuathu = $pdo->prepare($sql_chuathu);
            if ($month) {
                $stmt_chuathu->bindParam(':month', $month, PDO::PARAM_INT);
            }
            if ($year) {
                $stmt_chuathu->bindParam(':year', $year, PDO::PARAM_INT);
            }
            $stmt_chuathu->execute();
            $results_chuathu = $stmt_chuathu->fetchAll(PDO::FETCH_ASSOC);

            // Chuẩn bị truy vấn và thực thi cho phòng đã thu
            $stmt_dathu = $pdo->prepare($sql_dathu);
            if ($month) {
                $stmt_dathu->bindParam(':month', $month, PDO::PARAM_INT);
            }
            if ($year) {
                $stmt_dathu->bindParam(':year', $year, PDO::PARAM_INT);
            }
            $stmt_dathu->execute();
            $results_dathu = $stmt_dathu->fetchAll(PDO::FETCH_ASSOC);

            // Chuẩn bị truy vấn và thực thi cho phòng còn nợ
            $stmt_conno = $pdo->prepare($sql_conno);
            if ($month) {
                $stmt_conno->bindParam(':month', $month, PDO::PARAM_INT);
            }
            if ($year) {
                $stmt_conno->bindParam(':year', $year, PDO::PARAM_INT);
            }
            $stmt_conno->execute();
            $results_conno = $stmt_conno->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <br>
            <!-- Form tìm kiếm -->
            <?php
            // Khởi tạo các biến cho tháng và năm
            $month = isset($_POST['month']) ? $_POST['month'] : '';
            $year = isset($_POST['year']) ? $_POST['year'] : '';

            // Kiểm tra xem có tháng và năm được chọn từ form không
            if ($month && $year) {
                // In ra kết quả tháng và năm đã chọn
                echo "<h3>Kết quả cho tháng " . htmlspecialchars($month) . " năm " . htmlspecialchars($year) . "</h3>";
            } else {
                echo "<p>Vui lòng chọn tháng và năm để xem kết quả.</p>";
            }
            ?>

            <form method="post" action="" class="form-inline">
                <div class="form-group mb-2">
                    <label for="month" class="mr-2">Tháng:</label>
                    <input type="number" id="month" name="month" class="form-control" min="1" max="12" value="<?php echo htmlspecialchars($month); ?>">
                </div>
                <div class="form-group mb-2 ml-3">
                    <label for="year" class="mr-2">Năm:</label>
                    <input type="number" id="year" name="year" class="form-control" value="<?php echo htmlspecialchars($year); ?>">
                </div>
                <button type="submit" class="btn btn-primary mb-2 ml-3"><i class="fa fa-search"></i></button>
            </form>
            <p></p>
            <a href="<?php echo getLinkAdmin('sumary', 'print.all'); ?>" class="btn btn-secondary"><i class="fa fa-save"></i> Xuất </a>

            <h3 class="sumary-title">
                Danh sách phòng chưa thu
            </h3>
            <p style="color:red"><i>(Phòng đã có hoá đơn nhưng chưa thu gì cả)</i></p>
            <?php if (empty($results_chuathu)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Số tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" style="text-align: center;">Không có dữ liệu.</td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Số tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results_chuathu as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['tenphong'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format($row['tongtien'], 0, ',', '.') ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3 class="sumary-title">
                Danh sách phòng đã thu
            </h3>
            <p style="color:red"><i>(Phòng đã thu hết và không còn nợ)</i></p>
            <?php if (empty($results_dathu)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Số tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" style="text-align: center;">Không có dữ liệu.</td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Số tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results_dathu as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['tenphong'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format($row['sotiendatra'], 0, ',', '.') ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3 class="sumary-title">
                Danh sách phòng còn nợ
            </h3>
            <p style="color:red"><i>(Phòng đã trả trước nhưng vẫn còn nợ)</i></p>
            <?php if (empty($results_conno)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Số tiền nợ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" style="text-align: center;">Không có dữ liệu.</td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Số tiền nợ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results_conno as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['tenphong'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format($row['sotienconthieu'], 0, ',', '.') ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

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