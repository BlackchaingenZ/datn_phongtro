<?php 

if(!defined('_INCODE')) die('Access denied...');

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
function getRoomAndEquipmentList() {
    $sql = "
        SELECT r.id AS room_id, r.tenphong, GROUP_CONCAT(e.tenthietbi SEPARATOR ', ') AS tenthietbi
        FROM room r
        LEFT JOIN equipment_room er ON r.id = er.room_id
        LEFT JOIN equipment e ON er.equipment_id = e.id
        GROUP BY r.id
        ORDER BY r.id ASC
    ";
    
    return getRaw($sql); // Hàm getRaw() sẽ thực hiện truy vấn và trả về kết quả
}

$listRoomAndEquipment = getRoomAndEquipmentList();
?>

<?php layout('navbar', 'admin', $data); ?>

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?> 
    </div>

    <div class="box-content">
        <form action="" method="post" class="row">

            <div class="form-group">                 
                <a style="margin-right: 10px;" href="<?php echo getLinkAdmin('equipment'); ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <a href="<?php echo getLinkAdmin('equipment', 'distribute') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Phân bổ </a>
                <a href="<?php echo getLinkAdmin('equipment', 'removedistribute') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-edit"></i> Gỡ bỏ</a>
                </a>
            </div>
        </form>

        <div class="container mt-3">
        <h2>Danh sách phòng và thiết bị</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Mã phòng</th>
                    <th>Tên Phòng</th>
                    <th>Tên Thiết Bị</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($listRoomAndEquipment)) {
                    foreach ($listRoomAndEquipment as $item) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($item['room_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($item['tenphong'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($item['tenthietbi'], ENT_QUOTES, 'UTF-8') . "</td>"; // Sửa ở đây
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Không có dữ liệu.</td></tr>"; // Sửa ở đây
                }
                ?>
            </tbody>
        </table>
    </div>

    </div>

</div>

<?php layout('footer', 'admin'); ?>