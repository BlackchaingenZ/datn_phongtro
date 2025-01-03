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
               GROUP_CONCAT( DISTINCT er.thoigiancap SEPARATOR ', ') AS thoigiancap
        FROM room r
        LEFT JOIN equipment_room er ON r.id = er.room_id
        LEFT JOIN equipment e ON er.equipment_id = e.id
        GROUP BY r.id
        ORDER BY r.id ASC
    ";
    return getRaw($sql);
}

$searchTerm = '';
if (!empty($_POST['search_term'])) {
    $searchTerm = $_POST['search_term'];
}

// Truy vấn để tìm tên phòng và thiết bị theo từ khóa tìm kiếm
$sqlSearchRooms = "
    SELECT r.id AS room_id, 
           r.tenphong, 
           GROUP_CONCAT(e.tenthietbi SEPARATOR ', ') AS tenthietbi, 
           GROUP_CONCAT(er.thoigiancap SEPARATOR ', ') AS thoigiancap
    FROM room r
    JOIN equipment_room er ON r.id = er.room_id
    JOIN equipment e ON er.equipment_id = e.id
    WHERE r.tenphong LIKE '%$searchTerm%' OR e.tenthietbi LIKE '%$searchTerm%'
    GROUP BY r.id, r.tenphong
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
            </div>
            <div class="form-group mt-3">
                <a style="margin-right: 5px" href="<?php echo getLinkAdmin('equipment', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <a href="<?php echo getLinkAdmin('equipment', 'distribute') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Phân bổ </a>
                <a href="<?php echo getLinkAdmin('equipment', 'listdistribute'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
                <a href="<?php echo getLinkAdmin('equipment', 'removedistribute') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-edit"></i> Gỡ bỏ</a>
            </div>
        </form>

        <form method="POST" action="">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <!-- <th><input type="checkbox" id="check-all" onclick="toggle(this)"></th> -->
                        <th>STT</th>
                        <th>Mã phòng</th>
                        <th>Tên Phòng</th>
                        <th>Tên Thiết Bị</th>
                        <th>Ngày cấp</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="equipmentData">
                    <?php
                    $resultsToDisplay = !empty($searchTerm) ? $searchResults : $listRoomAndEquipment;

                    if (!empty($resultsToDisplay)):
                        $count = 0;
                        foreach ($resultsToDisplay as $item):
                            $count++;
                    ?>
                            <tr>
                                <!-- <td><input type="checkbox" name="records[]" value="<?php echo $item['room_id']; ?>"></td> -->
                                <td><?php echo $count; ?></td>
                                <td><?php echo $item['room_id']; ?></td>
                                <td><?php echo $item['tenphong']; ?></td>
                                <td><b><?php echo $item['tenthietbi']; ?></b></td>
                                <td><?php echo $item['thoigiancap']; ?></td>
                                <td class="" style="width: 100px; height: 50px;">
                                    <a href="<?php echo getLinkAdmin('equipment', 'editdistribute', ['id' => $item['room_id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                    <a href="<?php echo getLinkAdmin('equipment', 'deletedistribute', ['room_id' => $item['room_id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i></a>

                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="alert alert-danger text-center">Không có dữ liệu.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>
<!-- 
<script>
    function toggle(checkbox) {
        let isChecked = checkbox.checked;
        let checkboxes = document.querySelectorAll('input[name="records[]"]');
        checkboxes.forEach(function(cb) {
            cb.checked = isChecked;
        });
    }
</script> -->