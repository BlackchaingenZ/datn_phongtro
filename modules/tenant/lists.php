<?php

if (!defined('_INCODE'))
    die('Access denied...');

// Ngăn chặn quyền truy cập
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);

$grouId = $userDetail['group_id'];

if ($grouId != 7) {
    setFlashData('msg', 'Trang bạn muốn truy cập không tồn tại');
    setFlashData('msg_type', 'err');
    redirect('?module=dashboard');
}

$data = [
    'pageTitle' => 'Quản lý khách thuê'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


// Xử lý lọc dữ liệu
$allRoom = getRaw("SELECT id, tenphong, soluong, trangthai FROM room ORDER BY tenphong");
$filter = '';
if (isGet()) {
    $body = getBody('get');


    // Xử lý lọc theo từ khóa
    if (!empty($body['keyword'])) {
        $keyword = $body['keyword'];


        if (ctype_digit($keyword)) {
            if (strlen($keyword) === 9 || strlen($keyword) === 12) {
                $searchField = 'cmnd';
            } else if (strlen($keyword) >= 10 && strlen($keyword) <= 11) {
                $searchField = 'sdt';
            } else {
                $searchField = '';
            }
        } else {
            $searchField = 'tenkhach';
        }



        // Xác định toán tử (WHERE hoặc AND)
        if (!empty($filter) && strpos($filter, 'WHERE') !== false) {
            $operator = 'AND';
        } else {
            $operator = 'WHERE';
        }

        // Bổ sung điều kiện tìm kiếm vào bộ lọc
        $filter .= " $operator $searchField LIKE '%$keyword%'";
    }


    //Xử lý lọc theo groups
    if (!empty($body['room_id'])) {
        $roomId = $body['room_id'];

        if (!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        } else {
            $operator = 'WHERE';
        }

        $filter .= " $operator room_id = $roomId";
    }
}


$listAllTenant = getRaw("
    SELECT *, tenant.id, room.tenphong, area.tenkhuvuc, tenant.trangthai
    FROM tenant
    LEFT JOIN room ON tenant.room_id = room.id  
    INNER JOIN area_room ON area_room.room_id = room.id
    INNER JOIN area ON area_room.area_id = area.id
    $filter 
    ORDER BY tenant.id DESC
");


$allRoomId = getRaw("SELECT room_id FROM contract");

$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
// Phân loại phòng theo khu vực
$roomsByArea = [];
foreach ($allRoom as $room) {

    $areaIds = getRaw("SELECT area_id FROM area_room WHERE room_id = " . $room['id']);
    foreach ($areaIds as $area) {
        // Thêm thông tin số người vào mỗi phòng theo khu vực
        $roomsByArea[$area['area_id']][] = [
            'id' => $room['id'],
            'tenphong' => $room['tenphong'],
        ];
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

    <!-- Tìm kiếm -->
    <div class="box-content">
        <!-- Tìm kiếm , Lọc dưz liệu -->
        <form action="" method="get">
            <div class="row">
                <div class="col-2">

                </div>
                <div class="col-2">
                    <select name="area_id" id="area-select" class="form-select">
                        <option value="" disabled selected>Chọn khu vực</option>
                        <?php
                        if (!empty($allArea)) {
                            foreach ($allArea as $item) {
                        ?>
                                <option value="<?php echo $item['id'] ?>"
                                    <?php echo (!empty($areaId) && $areaId == $item['id']) ? 'selected' : '' ?>>
                                    <?php echo $item['tenkhuvuc'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('area_id', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="col-2">
                    <select name="room_id" id="room-select" class="form-select">
                        <option value="" disabled selected>Chọn phòng</option>
                        <!-- Danh sách phòng sẽ được cập nhật qua JavaScript -->
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="col-4">
                    <input style="height: 50px" type="search" name="keyword" class="form-control" placeholder="Nhập tên khách, số điện thoại hoặc cmnd/cccd để tìm" value="<?php echo (!empty($keyword)) ? $keyword : false; ?>">
                </div>

                <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" class="btn btn-secondary"> <i class="fa fa-search"></i></button>
                </div>
            </div>
            <input type="hidden" name="module" value="tenant">
        </form>

        <form action="" method="POST" class="mt-3">
            <div>

            </div>
            <a href="<?php echo getLinkAdmin('tenant', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <a href="<?php echo getLinkAdmin('tenant', 'export'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Xuất Excel</a>
            <table class="table table-bordered mt-3" id="dataTable">
                <thead>
                    <tr>
                        <!-- <th>
                            <input type="checkbox" id="check-all" onclick="toggle(this)">
                        </th> -->
                        <th>STT</th>
                        <th>Tên khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Ngày sinh</th>
                        <th>Giới tính</th>
                        <th width="10%">Địa chỉ & Nghề nghiệp</th>
                        <th width="6%">Số CMND/CCCD</th>
                        <th>Ngày cấp</th>
                        <th>Mặt trước CCCD</th>
                        <th>Mặt sau CCCD</th>
                        <th>Phòng ở</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    if (!empty($listAllTenant)):
                        $count = 0; // Hiển thi số thứ tự
                        foreach ($listAllTenant as $item):
                            $count++;

                    ?>
                            <tr>
                                <!-- <td style="text-align: center;">
                                    <input type="checkbox" name="records[]" value="<?= $item['id'] ?>">
                                </td> -->

                                <td style="text-align: center;"><?php echo $count; ?></td>
                                <td style="text-align: center;"><b><?php echo $item['tenkhach']; ?></b></td>
                                <td style="text-align: center;">
                                    <?php
                                    if (empty($item['sdt'])) {
                                        echo "Trống";
                                    } else {
                                        echo "" . $item['sdt'];
                                    }
                                    ?>
                                </td>
                                <!-- <td style="text-align: center;">
                                    <?php
                                    if (empty($item['sdt'])) {
                                        echo "Trống";
                                    } else {

                                        $phone = $item['sdt'];
                                        if (substr($phone, 0, 1) == '0') {
                                            $phone = '+84' . substr($phone, 1);
                                        }
                                        echo $phone;
                                    }
                                    ?>
                                </td> -->

                                <td style="text-align: center;">
                                    <?php
                                    if (!empty($item['ngaysinh'])) {
                                        // Giả sử $item['gioitinh'] là ngày có định dạng Y-m-d (năm-tháng-ngày)
                                        $date = DateTime::createFromFormat('Y-m-d', $item['ngaysinh']);

                                        // Kiểm tra nếu chuyển đổi thành công
                                        if ($date && $date->format('Y-m-d') === $item['ngaysinh']) {
                                            echo $date->format('d-m-Y'); // Hiển thị ngày tháng năm
                                        } else {
                                            echo "Không đúng định dạng ngày";
                                        }
                                    } else {
                                        echo "Trống";
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;"><?php echo $item['gioitinh'] ?></td>
                                <td style="text-align: center;">
                                    <div>
                                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/local.svg" alt=""><b style="font-size: 13px">Địa chỉ:</b>
                                        <?php echo $item['diachi'] ?>
                                    </div>
                                    <div style="margin-top: 5px">
                                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/work.svg" alt=""><b style="font-size: 13px">Nghề nghiệp:</b>
                                        <?php
                                        if (empty($item['nghenghiep'])) {
                                            echo "Trống";
                                        } else {
                                            echo "" . $item['nghenghiep'];
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td style="text-align:center" ;><?php echo $item['cmnd'] ?></td>
                                <td style="text-align: center;">
                                    <?php
                                    if (!empty($item['ngaycap'])) {
                                        // Giả sử $item['gioitinh'] là ngày có định dạng Y-m-d (năm-tháng-ngày)
                                        $date = DateTime::createFromFormat('Y-m-d', $item['ngaycap']);

                                        // Kiểm tra nếu chuyển đổi thành công
                                        if ($date && $date->format('Y-m-d') === $item['ngaycap']) {
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
                                    <?php if (!empty($item['anhmattruoc'])): ?>
                                        <a href="<?php echo getLinkAdmin('tenant', 'view-pre', ['id' => $item['id']]); ?>" target="_blank">
                                            <?php
                                            echo isFontIcon($item['anhmattruoc'])
                                                ? $item['anhmattruoc']
                                                : '<img src="' . $item['anhmattruoc'] . '" width="70" height="50"/>';
                                            ?>
                                        </a>
                                    <?php else: ?>
                                        Trống
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($item['anhmatsau'])): ?>
                                        <a href="<?php echo getLinkAdmin('tenant', 'view-after', ['id' => $item['id']]); ?>" target="_blank">
                                            <?php
                                            echo isFontIcon($item['anhmatsau'])
                                                ? $item['anhmatsau']
                                                : '<img src="' . $item['anhmatsau'] . '" width="70" height="50"/>';
                                            ?>
                                        </a>
                                    <?php else: ?>
                                        Trống
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($item['tenphong'])) { ?>
                                        <p class="btn btn-primary btn-sm" style="color: #fff; font-size: 12px"><?php echo $item['tenphong'] ?></p>
                                    <?php } else {
                                    ?>
                                        <p class="btn btn-warning btn-sm" style="color: #fff; font-size: 12px">
                                            Đang trống
                                        </p>
                                    <?php
                                    } ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php
                                    echo $item['trangthai'] == 1 ? '<span class="btn-status-suc">Đã từng ở</span>' : '<span class="btn-status-err">Đang ở</span>';
                                    ?>
                                </td>
                                <td class="" style="text-align: center;">
                                    <a href="<?php echo getLinkAdmin('tenant', 'edit', ['id' => $item['id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> </a>
                                    <a href="<?php echo getLinkAdmin('tenant', 'delete', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                                </td>

                            <?php endforeach;
                    else: ?>
                            <tr>
                                <td colspan="14">
                                    <div class="alert alert-danger text-center">Không có dữ liệu khách thuê</div>
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

<script>
    const roomsByArea = <?php echo json_encode($roomsByArea); ?>; // Chuyển đổi mảng PHP sang JS
    const areaSelect = document.getElementById('area-select');
    const roomSelect = document.getElementById('room-select');

    areaSelect.addEventListener('change', function() {
        const areaId = this.value;
        roomSelect.innerHTML = '<option value=""disabled selected>Chọn phòng</option>'; // Reset danh sách phòng

        if (areaId && roomsByArea[areaId]) {
            roomsByArea[areaId].forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent =
                    `${room.tenphong}`; // Hiển thị tên phòng 
                roomSelect.appendChild(option);
            });
        }
    });
</script>