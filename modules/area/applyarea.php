<?php

if (!defined('_INCODE'))
    die('Access denied...');

$data = [
    'pageTitle' => 'Danh sách khu vực-phòng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// Hàm lấy danh sách phòng và khuvuc
function getRoomAndAreaList()
{
    $sql = "
        SELECT room.id AS room_id, room.tenphong, 
        GROUP_CONCAT(area.tenkhuvuc SEPARATOR ', ') AS tenkhuvuc,
        GROUP_CONCAT(area.mota SEPARATOR ', ') AS mota
        FROM room
        LEFT JOIN area_room ON room.id = area_room.room_id
        LEFT JOIN area ON area_room.area_id = area.id
        GROUP BY room.id
        ORDER BY room.id DESC
    ";
    return getRaw($sql);
}


$searchTerm = '';
if (!empty($_POST['search_term'])) {
    $searchTerm = $_POST['search_term'];
}

// Truy vấn để tìm tên phòng và thiết bị theo từ khóa tìm kiếm
$sqlSearchRooms = "
    SELECT room.id AS room_id, 
           room.tenphong, 
           area.mota,
           GROUP_CONCAT(area.tenkhuvuc SEPARATOR ', ') AS tenkhuvuc
    FROM room
    JOIN area_room ON room.id = area_room.room_id
    JOIN area ON area_room.area_id = area.id
    WHERE room.tenphong LIKE '%$searchTerm%' OR area.tenkhuvuc LIKE '%$searchTerm%'
    GROUP BY room.id, room.tenphong
    ORDER BY room.tenphong ASC
";



$searchResults = getRaw($sqlSearchRooms);
$listRoomAndArea = getRoomAndAreaList();

?>


<?php
layout('navbar', 'admin', $data);
?>


<div class="container-fluid">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content">
        <form action="" method="post" class="row">
            <div class="row">
                <div class="col-4"></div>
                <div class="col-4">
                    <input style="height: 50px" type="search" name="search_term" class="form-control" placeholder="Nhập tên phòng hoặc khu vực cần tìm" value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>

                <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="form-group mt-3">
                <a style="margin-right: 5px" href="<?php echo getLinkAdmin('area', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <a href="<?php echo getLinkAdmin('area', 'addapply') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Áp dụng </a>
                <a href="<?php echo getLinkAdmin('area', 'applyarea'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            </div>
        </form>

        <form method="POST" action="">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã phòng</th>
                        <th>Tên khu vực</th>
                        <th>Tên Phòng</th>
                        <th>Mô tả</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="AreaData">
                    <?php
                    $resultsToDisplay = !empty($searchTerm) ? $searchResults : $listRoomAndArea;

                    if (!empty($resultsToDisplay)):
                        $count = 0;
                        foreach ($resultsToDisplay as $item):
                            $count++;
                    ?>
                            <!-- <tr <?php if ($item['tenkhuvuc'] === 'Khu A') echo 'style="background-color: red; color: white;"'; ?>> -->
                            <tr>
                                <!-- <tr style="background-color:<?php echo (in_array($count, [1, 2, 3])) ? 'red' : (in_array($count, [4, 6]) ? 'green' : 'transparent'); ?>;"> -->
                                <td><?php echo $count; ?></td>
                                <td><?php echo $item['room_id']; ?></td>
                                <td><b><?php echo $item['tenkhuvuc']; ?></b></td>
                                <td><?php echo $item['tenphong']; ?></td>
                                <td><?php echo $item['mota']; ?></td>

                                <td class="" style="width: 100px; height: 50px;text-align:center">
                                    <a href="<?php echo getLinkAdmin('area', 'editapplyarea', ['applyarea' => $item['room_id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                    <a href="<?php echo getLinkAdmin('area', 'deleteapplyarea', ['room_id' => $item['room_id']]); ?>"
                                        class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="7">
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