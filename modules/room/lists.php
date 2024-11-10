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
    'pageTitle' => 'Danh sách phòng trọ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý lọc dữ liệu
$filter = '';
if (isGet()) {
    $body = getBody('get');


    // Xử lý lọc theo từ khóa
    if (!empty($body['keyword'])) {
        $keyword = $body['keyword'];

        if (!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        } else {
            $operator = 'WHERE';
        }

        $filter .= " $operator tenphong LIKE '%$keyword%'";
    }

    //Xử lý lọc Status
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

/// Xử lý phân trang
$allTenant = getRows("SELECT id FROM room $filter");
$perPage = _PER_PAGE; // Mỗi trang có 3 bản ghi
$maxPage = ceil($allTenant / $perPage);

// 3. Xử lý số trang dựa vào phương thức GET
if (!empty(getBody()['page'])) {
    $page = getBody()['page'];
    if ($page < 1 and $page > $maxPage) {
        $page = 1;
    }
} else {
    $page = 1;
}
$offset = ($page - 1) * $perPage;
//lấy thông tin giá thuê từ bảng cost và tên thiết bị từ bảng equipment( distint lấy thông tin mới ,ko trùng)
$listAllroom = getRaw("
    SELECT room.*, 
           cost.giathue, 
           area.tenkhuvuc,
           GROUP_CONCAT(DISTINCT equipment.tenthietbi SEPARATOR ', ') AS tenthietbi
    FROM room 
    LEFT JOIN cost_room ON room.id = cost_room.room_id 
    LEFT JOIN cost ON cost_room.cost_id = cost.id
    LEFT JOIN equipment_room ON room.id = equipment_room.room_id
    LEFT JOIN equipment ON equipment_room.equipment_id = equipment.id
    LEFT JOIN area_room ON room.id = area_room.room_id
    LEFT JOIN area ON area_room.area_id = area.id
    $filter 
    GROUP BY room.id
    ORDER BY tenphong ASC 
    LIMIT $offset, $perPage
");



// Xử lý query string tìm kiếm với phân trang
$queryString = null;
if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = str_replace('module=room', '', $queryString);
    $queryString = str_replace('&page=' . $page, '', $queryString);
    $queryString = trim($queryString, '&');
    $queryString = '&' . $queryString;
}

// Xóa hết
if (isset($_POST['deleteMultip'])) {
    $numberCheckbox = $_POST['records'];

    if (empty($numberCheckbox)) {
        setFlashData('msg', 'Bạn chưa chọn mục nào để xóa!');
        setFlashData('msg_type', 'err');
    } else {
        $extract_id = implode(',', $numberCheckbox);

        // Kiểm tra xem phòng có hợp đồng liên kết không
        $checkContractInRoom = getRaw("SELECT id FROM contract WHERE room_id IN($extract_id)");

        if ($checkContractInRoom) {
            setFlashData('msg', 'Phòng đang có hợp đồng, không thể xóa!');
            setFlashData('msg_type', 'err');
        } else {
            // Kiểm tra xem phòng có tenant liên kết không
            $checkTenantInRoom = getRaw("SELECT room_id FROM tenant WHERE room_id IN($extract_id)");

            if ($checkTenantInRoom) {
                setFlashData('msg', 'Phòng đang có người ở, không thể xóa!');
                setFlashData('msg_type', 'err');
            } else {
                // Xóa các thiết bị liên kết với phòng trọ
                $deleteEquipment = delete('equipment_room', "room_id IN($extract_id)");

                if ($deleteEquipment) {
                    // Xóa các khu vực liên kết với phòng trọ
                    $deleteArea = delete('area_room', "room_id IN($extract_id)");

                    if ($deleteArea) {
                        // Xóa các giá thuê liên kết với phòng trọ
                        $deleteCost = delete('cost_room', "room_id IN($extract_id)");

                        if ($deleteCost) {
                            // Xóa phòng trọ
                            $checkDeleteRoom = delete('room', "id IN($extract_id)");

                            if ($checkDeleteRoom) {
                                setFlashData('msg', 'Xóa thông tin phòng trọ thành công!');
                                setFlashData('msg_type', 'suc');
                            } else {
                                setFlashData('msg', 'Không thể xóa phòng trọ!');
                                setFlashData('msg_type', 'err');
                            }
                        }
                    }
                }
            }
        }
    }
    redirect('?module=room');
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
                <div class="col-3">
                    <div class="form-group">
                        <select name="status" id="" class="form-select">
                            <option value="0" disabled selected>Chọn trạng thái</option>
                            <option value="1" <?php echo (!empty($status) && $status == 1) ? 'selected' : false; ?>>Đang ở</option>
                            <option value="2" <?php echo (!empty($status) && $status == 2) ? 'selected' : false; ?>>Đang trống</option>
                        </select>
                    </div>
                </div>

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
            <button type="submit" name="deleteMultip" value="delete" onclick="return confirm('Bạn có chắn chắn muốn xóa không ?')" class="btn btn-secondary"><i class="fa fa-trash"></i> Xóa</button>
            <!-- <a href="<?php echo getLinkAdmin('room', 'import'); ?>" class="btn btn-secondary"><i class="fa fa-upload"></i> Import</a> -->
            <a href="<?php echo getLinkAdmin('room', 'export'); ?>" class="btn btn-secondary"><i class="fa fa-save"></i> Xuất Excel</a>

            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="check-all" onclick="toggle(this)">
                        </th>
                        <th>STT</th>
                        <th>Ảnh</th>
                        <th>Khu vực</th>
                        <th>Tên phòng</th>
                        <th>Diện tích</th>
                        <th>Giá thuê</th>
                        <th>Giá tiền cọc</th>
                        <th>Khách thuê</th>
                        <th style="width: 6%; text-align: center;">Ngày lập hoá đơn</th>
                        <th>Chu kỳ thu tiền</th>
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
                        $count = 0; // Hiển thi số thứ tự
                        foreach ($listAllroom as $item):
                            $count++;

                    ?>
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="records[]" value="<?= $item['id'] ?>">
                                </td>


                                <td style="text-align: center;"><?php echo $count; ?></td>
                                <td style="text-align: center;"><img style="width: 70px; height: 50px" src="<?php echo $item['image'] ?>" alt=""></td>
                                <td style="text-align: center;"><b><?php echo $item['tenkhuvuc']; ?></b></td>
                                <td style="text-align: center;"><b><?php echo $item['tenphong']; ?></b></td>
                                <td style="text-align: center;"><?php echo $item['dientich'] ?> m2</td>
                                <td style="text-align: center;"><b><?php echo number_format($item['giathue'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><b><?php echo number_format($item['tiencoc'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.svg" alt=""> <?php echo $item['soluong'] ?> người</td>
                                <td style="text-align: center;">Ngày <?php echo $item['ngaylaphd'] ?></td>
                                <td style="text-align: center;"><?php echo $item['chuky'] ?> tháng</td>
                                <td style="text-align: center;"><?php echo $item['ngayvao'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['ngayvao'], 'd-m-Y'); ?></td>
                                <td style="text-align: center;"><?php echo $item['ngayra']  == '0000-00-00' ? 'Không xác định' : getDateFormat($item['ngayra'], 'd-m-Y'); ?></td>
                                <td style="text-align: center;">
                                    <?php
                                    echo $item['trangthai'] == 1 ? '<span class="btn-status-suc">Đang ở</span>' : '<span class="btn-status-err">Đang trống</span>';
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <!-- Thông tin -->
                                    <span class="tooltip-icon">
                                    <i class="fa-solid fa-eye"></i>
                                        <span class="tooltiptext"><?php echo $item['tenthietbi']; ?></span>
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

            <nav aria-label="Page navigation example" class="d-flex justify-content-center">
                <ul class="pagination pagination-sm">
                    <?php
                    if ($page > 1) {
                        $prePage = $page - 1;
                        echo '<li class="page-item"><a class="page-link" href="' . _WEB_HOST_ROOT . '/?module=room' . $queryString . '&page=' . $prePage . '">Pre</a></li>';
                    }
                    ?>

                    <?php
                    // Giới hạn số trang
                    $begin = $page - 2;
                    $end = $page + 2;
                    if ($begin < 1) {
                        $begin = 1;
                    }
                    if ($end > $maxPage) {
                        $end = $maxPage;
                    }
                    for ($index = $begin; $index <= $end; $index++) {  ?>
                        <li class="page-item <?php echo ($index == $page) ? 'active' : false; ?> ">
                            <a class="page-link" href="<?php echo _WEB_HOST_ROOT . '?module=room' . $queryString . '&page=' . $index;  ?>"> <?php echo $index; ?> </a>
                        </li>
                    <?php  } ?>

                    <?php
                    if ($page < $maxPage) {
                        $nextPage = $page + 1;
                        echo '<li class="page-item"><a class="page-link" href="' . _WEB_HOST_ROOT . '?module=room' . $queryString . '&page=' . $nextPage . '">Next</a></li>';
                    }
                    ?>
                </ul>
            </nav>
    </div>

</div>

<?php

layout('footer', 'admin');
?>

<script>
    function toggle(__this) {
        let isChecked = __this.checked;
        let checkbox = document.querySelectorAll('input[name="records[]"]');
        for (let index = 0; index < checkbox.length; index++) {
            checkbox[index].checked = isChecked
        }
    }
</script>