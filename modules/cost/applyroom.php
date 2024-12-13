<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Danh sách phòng-bảng giá'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// Lấy danh sách cơ sở vật chất và phòng trọ
$listAllCost = getRaw("SELECT * FROM cost ORDER BY giathue ASC");
$listAllRoom = getRaw("SELECT * FROM room ORDER BY tenphong ASC");

// Hàm lấy danh sách phòng và bảng giá
function getRoomAndCostList()
{
    $sql = "
        SELECT room.id AS room_id, room.tenphong, 
               GROUP_CONCAT(cost.tengia SEPARATOR ', ') AS tengia, 
               GROUP_CONCAT(cost_room.thoigianapdung SEPARATOR ', ') AS thoigianapdung
        FROM room
        LEFT JOIN cost_room ON room.id = cost_room.room_id
        LEFT JOIN cost ON cost_room.cost_id = cost.id
        GROUP BY room.id
        HAVING tengia IS NOT NULL  
        -- Chỉ hiển thị phòng có giá thuê
        ORDER BY room.id DESC
    ";
    return getRaw($sql);
}


$searchTerm = '';
if (!empty($_POST['search_term'])) {
    $searchTerm = $_POST['search_term'];
}

// Truy vấn để tìm tên phòng và cost theo từ khóa tìm kiếm
$sqlSearchRooms = "
    SELECT room.id AS room_id, 
           room.tenphong, 
           GROUP_CONCAT(cost.tengia SEPARATOR ', ') AS tengia, 
           GROUP_CONCAT(cost_room.thoigianapdung SEPARATOR ', ') AS thoigianapdung
    FROM room
    LEFT JOIN cost_room ON room.id = cost_room.room_id
    LEFT JOIN cost ON cost_room.cost_id = cost.id
    WHERE room.tenphong LIKE '%$searchTerm%' OR cost.tengia LIKE '%$searchTerm%'
    GROUP BY room.id, room.tenphong
    ORDER BY room.tenphong ASC
";


$searchResults = getRaw($sqlSearchRooms);
$listRoomAndCost = getRoomAndCostList();


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
                    <input style="height: 50px" type="search" name="search_term" class="form-control" placeholder="Nhập tên phòng cần tìm loại giá" value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>

                <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="form-group mt-3">
                <a style="margin-right: 5px" href="<?php echo getLinkAdmin('cost', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <a href="<?php echo getLinkAdmin('cost', 'applycost') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Áp dụng </a>
                <a href="<?php echo getLinkAdmin('cost', 'applyroom'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
                <a href="<?php echo getLinkAdmin('cost', 'historycost') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa-solid fa-clock-rotate-left"></i> Lịch sử áp dụng</a>
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
                        <th>Tên bảng giá</th>
                        <th>Ngày áp dụng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="costData">
                    <?php
                    $resultsToDisplay = !empty($searchTerm) ? $searchResults : $listRoomAndCost;

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
                                <td>
                                    <?php
                                    if (empty($item['tengia'])) {
                                        echo "Trống";
                                    } else {
                                        echo "" . $item['tengia'];
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($item['thoigianapdung'])) {
                                        // Giả sử $item['thoigianapdung'] là ngày có định dạng Y-m-d (năm-tháng-ngày)
                                        $date = DateTime::createFromFormat('Y-m-d', $item['thoigianapdung']);

                                        // Kiểm tra nếu chuyển đổi thành công
                                        if ($date && $date->format('Y-m-d') === $item['thoigianapdung']) {
                                            echo $date->format('d-m-Y'); // Hiển thị ngày tháng năm
                                        } else {
                                            echo "Không đúng định dạng ngày";
                                        }
                                    } else {
                                        echo "Trống";
                                    }
                                    ?>
                                </td>
                                <td class="" style="width: 100px; height: 50px; text-align: center">
                                    <a href="<?php echo getLinkAdmin('cost', 'editapplycost', ['applycost' => $item['room_id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>

                                    <a href="<?php echo getLinkAdmin('cost', 'deleteapplycost', ['room_id' => $item['room_id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i></a>

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

<!-- <script>
    function toggle(checkbox) {
        let isChecked = checkbox.checked;
        let checkboxes = document.querySelectorAll('input[name="records[]"]');
        checkboxes.forEach(function(cb) {
            cb.checked = isChecked;
        });
    }
</script> -->