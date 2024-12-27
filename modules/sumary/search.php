<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Báo cáo tình trạng thu tiền'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$currentMonthYear = date('Y-m');
$currentYear = date('Y');

$filterType = isset($_POST['filter_type']) ? $_POST['filter_type'] : 'month';
$dateInput = isset($_POST['date_input']) ? $_POST['date_input'] : $currentMonthYear;

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
            <?php
            // Kiểm tra nếu người dùng đã gửi form tìm kiếm
            $month = isset($_POST['month']) ? $_POST['month'] : '';
            $year = isset($_POST['year']) ? $_POST['year'] : '';

            // Truy vấn lấy danh sách phòng chưa thu
            $sql_chuathu = "
            SELECT 
                room.tenphong AS tenphong,
                bill.tongtien AS tongtien,
                area.tenkhuvuc AS tenkhuvuc
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
                LEFT JOIN
                area_room
                ON
                room.id = area_room.room_id
                LEFT JOIN
                area
                ON
                area_room.area_id = area.id
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

            $sql_chuathu .= " GROUP BY room.tenphong, area.tenkhuvuc ORDER BY room.tenphong ASC";;
            // Truy vấn lấy danh sách phòng đã thu
            $sql_dathu = "
SELECT
    room.tenphong AS tenphong,
    bill.sotiendatra AS sotiendatra,
    area.tenkhuvuc AS tenkhuvuc,
    SUM(CASE
        WHEN receipt.ngaythu IS NOT NULL AND receipt.danhmucthu_id = 2 AND MONTH(receipt.ngaythu) = :month AND YEAR(receipt.ngaythu) = :year THEN receipt.sotien
        ELSE 0
    END) AS sotien,
    SUM(CASE
        WHEN receipt.ngaythu IS NOT NULL AND receipt.danhmucthu_id = 1 AND MONTH(receipt.ngaythu) = :month AND YEAR(receipt.ngaythu) = :year THEN receipt.sotien
        ELSE 0
    END) AS sotienphong
FROM 
    room
LEFT JOIN 
    bill
ON 
    room.id = bill.room_id
LEFT JOIN
    area_room
ON
    room.id = area_room.room_id
LEFT JOIN
    area
ON
    area_room.area_id = area.id
LEFT JOIN
    receipt
ON
    room.id = receipt.room_id
WHERE 
    (bill.trangthaihoadon = 1 OR receipt.sotien IS NOT NULL)
    AND (MONTH(bill.create_at) = :month OR MONTH(receipt.ngaythu) = :month)
    AND (YEAR(bill.create_at) = :year OR YEAR(receipt.ngaythu) = :year)
";
            // Thêm điều kiện tìm kiếm theo tháng/năm cho phòng đã thu
            $whereConditions = [];
            if ($month) {
                $whereConditions[] = "(MONTH(bill.create_at) = :month OR MONTH(receipt.ngaythu) = :month)";
            }
            if ($year) {
                $whereConditions[] = "(YEAR(bill.create_at) = :year OR YEAR(receipt.ngaythu) = :year)";
            }

            if (count($whereConditions) > 0) {
                $sql_dathu .= " AND " . implode(" AND ", $whereConditions);
            }

            $sql_dathu .= " GROUP BY room.tenphong, area.tenkhuvuc ORDER BY room.tenphong ASC";
            // Chuẩn bị truy vấn và thực thi cho phòng đã thu
            $stmt_dathu = $pdo->prepare($sql_dathu);
            $stmt_dathu->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt_dathu->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt_dathu->execute();
            $results_dathu = $stmt_dathu->fetchAll(PDO::FETCH_ASSOC);
            // Truy vấn lấy danh sách phòng còn thiếu
            $sql_conno = "
    SELECT 
        room.tenphong AS tenphong,
        bill.sotienconthieu AS sotienconthieu,
        area.tenkhuvuc AS tenkhuvuc
    FROM 
        room
    INNER JOIN 
        bill
    ON 
        room.id = bill.room_id
        LEFT JOIN
        area_room
        ON
        room.id = area_room.room_id
        LEFT JOIN
        area
        ON 
        area_room.area_id = area.id
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
            // Lấy tháng và năm hiện tại
            $currentMonth = date('m');
            $currentYear = date('Y');

            // Khởi tạo các biến cho tháng và năm, với giá trị mặc định là tháng và năm hiện tại
            $month = isset($_POST['month']) && $_POST['month'] ? $_POST['month'] : $currentMonth;
            $year = isset($_POST['year']) && $_POST['year'] ? $_POST['year'] : $currentYear;

            // Kiểm tra xem có tháng và năm được chọn từ form không
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // echo "<h3>Kết quả cho tháng " . htmlspecialchars($month) . " năm " . htmlspecialchars($year) . "</h3>";
            } else {
                // echo "<p></p>";
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
            <a href="<?php echo getLinkAdmin('sumary', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại </a>
            <a href="<?php echo getLinkAdmin('sumary', 'print'); ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Xuất Excel </a>
            <h3 class="sumary-title">
                Danh sách phòng chưa thu
            </h3>
            <p style="color:red"><i>(Phòng đã có hoá đơn nhưng chưa thu gì cả)</i></p>
            <?php if (empty($results_chuathu)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tên khu vực</th>
                            <th>Tên phòng</th>
                            <th>Tiền phòng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" style="text-align: center;">Không có dữ liệu.</td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tên khu vực</th>
                            <th>Tên phòng</th>
                            <th>Tiền phòng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results_chuathu as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></td>
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
            <p style="color:red"></p>
            <?php if (empty($results_dathu)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tên khu vực</th>
                            <th>Tên phòng</th>
                            <th>Tiền phòng</th>
                            <th>Tiền cọc</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" style="text-align: center;">Không có dữ liệu.</td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tên khu vực</th>
                            <th>Tên phòng</th>
                            <th>Tiền phòng</th>
                            <th>Tiền cọc</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results_dathu as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['tenphong'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format($row['sotienphong'], 0, ',', '.') ?> đ</td>
                                <td><?php echo number_format($row['sotien'], 0, ',', '.') ?> đ</td>
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
                            <th>Tên khu vực</th>
                            <th>Tên phòng</th>
                            <th>Tiền phòng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" style="text-align: center;">Không có dữ liệu.</td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tên khu vực</th>
                            <th>Tên phòng</th>
                            <th>Tiền phòng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results_conno as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['tenphong'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format($row['sotienconthieu'], 0, ',', '.') ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
layout('footer', 'admin');
?>