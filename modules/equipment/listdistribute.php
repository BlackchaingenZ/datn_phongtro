<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Phân bổ thiết bị'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// Lấy danh sách cơ sở vật chất và phòng trọ
$listAllEquipment = getRaw("SELECT * FROM equipment ORDER BY tenthietbi ASC");
$listAllRoom = getRaw("SELECT * FROM room ORDER BY tenphong ASC");

// Hàm lấy danh sách phòng và thiết bị
function getRoomAndEquipmentList()
{
    $sql = "
        SELECT r.id AS room_id, r.tenphong, GROUP_CONCAT(e.tenthietbi SEPARATOR ', ') AS tenthietbi, 
               GROUP_CONCAT(thoigiancap SEPARATOR ', ') AS thoigiancap
        FROM room r
        LEFT JOIN equipment_room er ON r.id = er.room_id
        LEFT JOIN equipment e ON er.equipment_id = e.id
        GROUP BY r.id
        ORDER BY r.id ASC
    ";

    return getRaw($sql); // Hàm getRaw() sẽ thực hiện truy vấn và trả về kết quả
}

$searchTerm = ''; // Từ khóa tìm kiếm
if (!empty($_POST['search_term'])) {
    $searchTerm = $_POST['search_term'];
}

// Truy vấn để tìm tên phòng và thiết bị
$sqlSearchRooms = "
    SELECT r.id AS room_id, r.tenphong, e.tenthietbi, er.thoigiancap
    FROM room r
    JOIN equipment_room er ON r.id = er.room_id
    JOIN equipment e ON er.equipment_id = e.id
    WHERE r.tenphong LIKE '%$searchTerm%'
    ORDER BY r.tenphong ASC
";

$searchResults = getRaw($sqlSearchRooms);
$listRoomAndEquipment = getRoomAndEquipmentList();

?>

<?php layout('navbar', 'admin', $data); ?>

<div class="container-fluid">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content">
        <form action="" method="post" class="row">
            <div class="row">
                <div class="col-4"></div>
                <div class="col-4">
                    <input style="height: 50px" type="search" name="search_term" class="form-control" placeholder="Nhập tên phòng cần tìm thiết bị" value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>

                <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
                <p></p>
            </div>
            <div class="form-group">
                <a style="margin-right: 20px " href="<?php echo getLinkAdmin('equipment', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</>
                    <a href="<?php echo getLinkAdmin('equipment', 'distribute') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Phân bổ </a>
                    <a href="<?php echo getLinkAdmin('equipment', 'listdistribute'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
                    <a href="<?php echo getLinkAdmin('equipment', 'removedistribute') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-edit"></i> Gỡ bỏ</a>
            </div>
        </form>


        <h2>Danh sách phòng và thiết bị</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Mã phòng</th>
                    <th>Tên Phòng</th>
                    <th>Tên Thiết Bị</th>
                    <th>Ngày cấp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Nếu có từ khóa tìm kiếm, sử dụng kết quả tìm kiếm
                $resultsToDisplay = !empty($searchTerm) ? $searchResults : $listRoomAndEquipment;

                if (!empty($resultsToDisplay)) {
                    foreach ($resultsToDisplay as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['room_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['tenphong'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['tenthietbi'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['thoigiancap'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Không có dữ liệu.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php layout('footer', 'admin'); ?>