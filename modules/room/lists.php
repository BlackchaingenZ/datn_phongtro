<?php

if (!defined('_INCODE'))
    die('Access denied...');

// Ngăn chặn quyền truy cập
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);

$grouId = $userDetail['group_id'];

if ($grouId != 7) {
    setFlashData('msg', 'Bạn không được truy cập vào trang này');
    setFlashData('msg_type', 'err');
    redirect('admin/?module=dashboard');
}

$data = [
    'pageTitle' => 'Quản lý phòng '
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý lọc dữ liệu
$filter = '';
if (isGet()) {
    $body = getBody('get');


    if (!empty($body['keyword'])) {
        $keyword = $body['keyword'];

        if (!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        } else {
            $operator = 'WHERE';
        }

        $filter .= " $operator tenphong LIKE '%$keyword%'";
    }

    // if (!empty($body['room_id'])) {
    //     $roomId = $body['room_id'];

    //     if (!empty($filter) && strpos($filter, 'WHERE') >= 0) {
    //         $operator = 'AND';
    //     } else {
    //         $operator = 'WHERE';
    //     }

    //     $filter .= " $operator room.id = '$roomId'";
    // }

    if (!empty($body['status'])) {
        $status = $body['status'];

        if ($status == 2) {
            $statusSql = 0;
        } else {
            $statusSql = $status;
        }

        if (!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        } else {
            $operator = 'WHERE';
        }

        $filter .= "$operator trangthai=$statusSql";
    }
}

// $allRooms = getRaw("SELECT id, tenphong FROM room");
$allTenant = getRows("SELECT id FROM room $filter");
$listAllroom = getRaw("
    SELECT room.*, 
           cost.giathue, 
           area.tenkhuvuc,
           contract.ngayvao, 
           contract.ngayra,
           GROUP_CONCAT(
               CASE 
                   WHEN equipment_room.soluongcap > 0 
                   THEN CONCAT(equipment.tenthietbi, ' (', equipment_room.soluongcap, ')')
                   ELSE NULL
               END 
               SEPARATOR ', ') AS tenthietbi
    FROM room 
    LEFT JOIN cost_room ON room.id = cost_room.room_id 
    LEFT JOIN cost ON cost_room.cost_id = cost.id
    LEFT JOIN equipment_room ON room.id = equipment_room.room_id
    LEFT JOIN equipment ON equipment_room.equipment_id = equipment.id
    LEFT JOIN area_room ON room.id = area_room.room_id
    LEFT JOIN area ON area_room.area_id = area.id
    LEFT JOIN contract ON room.id = contract.room_id 
    $filter 
    GROUP BY room.id
    ORDER BY tenphong ASC
");


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

    <!-- Tìm kiếm -->
    <div class="box-content">
        <!-- Tìm kiếm , Lọc dưz liệu -->
        <form action="" method="get">
            <div class="row">
                <div class="col-2">
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <select name="status" id="" class="form-select">
                            <option value="0" disabled selected>Chọn trạng thái</option>
                            <option value="1" <?php echo (!empty($status) && $status == 1) ? 'selected' : false; ?>>Đang ở</option>
                            <option value="2" <?php echo (!empty($status) && $status == 2) ? 'selected' : false; ?>>Đang trống</option>
                        </select>
                    </div>
                </div>

                <!-- <div class="col-4">
                    <div class="form-group">
                        <select name="room_id" id="room_id" class="form-select" style="height: 50px;">
                            <option value="0" disabled selected>Chọn phòng</option>
                            <?php foreach ($allRooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>" <?php echo (!empty($roomId) && $roomId == $room['id']) ? 'selected' : ''; ?>>
                                    <?php echo $room['tenphong']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div> -->

                <div class="col-4">
                    <input style="height: 50px" type="search" name="keyword" class="form-control" placeholder="Nhập tên phòng cần tìm" value="<?php echo (!empty($keyword)) ? $keyword : false; ?>">
                </div>

                <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" class="btn btn-secondary"> <i class="fa fa-search"></i></button>
                </div>
            </div>
            <input type="hidden" name="module" value="room">
        </form>

        <form action="" method="POST" class="mt-3">
            <div>

            </div>
            <a href="<?php echo getLinkAdmin('room', 'add') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm mới </a>
            <a href="<?php echo getLinkAdmin('room', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <a href="<?php echo getLinkAdmin('room', 'export'); ?>" class="btn btn-secondary"><i class="fa fa-save"></i> Xuất Excel</a>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Ảnh</th>
                        <th>Tên phòng</th>
                        <th>Diện tích</th>
                        <th>Giá thuê</th>
                        <th>Giá tiền cọc</th>
                        <th>Khách thuê</th>
                        <th>Ngày vào ở</th>
                        <th>Ngày hết hạn</th>
                        <th>Trạng thái</th>
                        <th style="width: 5%; text-align: center;">Cơ sở vật chất</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="roomData">

                    <?php

                    if (!empty($listAllroom)):
                        $count = 0;
                        foreach ($listAllroom as $item):

                            $count++;

                    ?>
                            <!-- <tr style="<?php echo $item['trangthai'] == 1 ? 'background-color: red; color: white;' : ''; ?>"> -->
                            <tr>
                                <!-- <tr style="background-color:<?php echo (in_array($count, [1, 2, 3])) ? 'red' : (in_array($count, [4, 6]) ? 'green' : 'transparent'); ?>;"> -->
                                <td style="text-align: center;"><?php echo $count; ?></td>
                                <!-- <td style="text-align: center;">
                                    <a href="<?php echo getLinkAdmin('room', 'view', ['id' => $item['id']]); ?>" target="_blank">
                                        <img style="width: 70px; height: 50px;" src="<?php echo $item['image']; ?>" alt="">
                                    </a>
                                </td> -->
                                <td style="text-align: center;">
                                    <img class="" style="width: 70px; height: 50px" src="<?php echo $item['image'] ?>" alt="">
                                </td>
                                <!-- <td style="<?php echo ($item['tenphong'] == 'Phòng A01' || $item['tenphong'] == 'Phòng A02') ? 'color: red;' : ''; ?>">
                                    <?php echo $item['tenphong']; ?>
                                </td> -->
                                <!-- <td style="background-color: <?php echo ($count == 1) ? 'red' : 'transparent'; ?>"><b><?php echo $item['tenphong']; ?></b></td> -->
                                <td style="text-align:center"><?php echo $item['tenphong']; ?></td>
                                <td style="text-align: center;"><?php echo $item['dientich'] ?> m2</td>
                                <td style="text-align: center;"><b><?php echo number_format($item['giathue'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><b><?php echo number_format($item['tiencoc'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.svg" alt=""> <?php echo $item['soluong'] ?>/<?php echo $item['soluongtoida'] ?> người</td>
                                <td style="text-align: center;">
                                    <?php
                                    if (!empty($item['ngayvao'])) {
                                        $date = DateTime::createFromFormat('Y-m-d', $item['ngayvao']);

                                        if ($date && $date->format('Y-m-d') === $item['ngayvao']) {
                                            echo $date->format('d-m-Y'); // Hiển thị ngày tháng năm
                                        } else {
                                            echo "Không đúng định dạng ngày";
                                        }
                                    } else {
                                        echo "Trống";
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php
                                    if (!empty($item['ngayra'])) {

                                        $date = DateTime::createFromFormat('Y-m-d', $item['ngayra']);

                                        if ($date && $date->format('Y-m-d') === $item['ngayra']) {
                                            echo $date->format('d-m-Y'); // Hiển thị ngày tháng năm
                                        } else {
                                            echo "Không đúng định dạng ngày";
                                        }
                                    } else {
                                        echo "Trống";
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php
                                    echo $item['trangthai'] == 1 ? '<span class="btn-status-suc">Đang ở</span>' : '<span class="btn-status-err">Đang trống</span>';
                                    // echo $item['trangthai'] == 1 ? '<span class="btn-status-suc">Đang ở</span>'. $item['soluong'].'<span> người</span>'  : '<span class="btn-status-err">Đang trống</span>';
                                    ?>
                                </td>
                                <td style="text-align: center;">

                                    <span class="tooltip-icon">
                                        <i class="nav-icon fas fa-solid fa-eye"></i>
                                        <span class="tooltiptext">
                                            <?php

                                            echo !empty($item['tenthietbi']) ? $item['tenthietbi'] : 'Trống';
                                            // echo nl2br ($item['tenthietbi']);
                                            ?>
                                        </span>
                                    </span>
                                </td>

                                <td class="" style="text-align: center;">
                                    <a href="<?php echo getLinkAdmin('room', 'edit', ['id' => $item['id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> </a>
                                    <a href="<?php echo getLinkAdmin('room', 'delete', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                                </td>

                            <?php endforeach;
                    else: ?>
                            <tr>
                                <td colspan="14">
                                    <div class="alert alert-danger text-center">Không có dữ liệu phòng trọ</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                </tbody>
            </table>
    </div>

</div>

<?php

layout('footer', 'admin');
?>