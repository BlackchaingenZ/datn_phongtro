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
    redirect('/?module=dashboard');
}

$data = [
    'pageTitle' => 'Lịch sử hợp đồng thuê trọ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Lấy thông tin khách của hợp đồng
function getTenantsByRoomId($roomId)
{
    return getRaw("SELECT * FROM tenant WHERE room_id = $roomId");
}

$listRental_history = getRaw("SELECT *, 
    rental_history.id, 
    soluong, 
    tenphong, 
    khachthue,
    cost.giathue, 
    rental_history.ngayvao AS ngayvaoo, 
    rental_history.ngayra AS thoihanhopdong
FROM rental_history 
INNER JOIN room ON rental_history.room_id = room.id 
INNER JOIN cost_room ON room.id = cost_room.room_id 
INNER JOIN cost ON cost_room.cost_id = cost.id");


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

    <div class="box-content">
        <div>
            <a style="margin-left: 20px" href="<?php echo getLinkAdmin('contract', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
            <a href="<?php echo getLinkAdmin('contract', 'renatal_history'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
        </div>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>

                    <th width="5%">STT</th>
                    <th>Tên phòng</th>
                    <th>Khách thuê</th>
                    <th>Giá thuê</th>
                    <th>Chu kỳ thu</th>
                    <th>Ngày lập</th>
                    <th>Ngày vào ở</th>
                    <th>Thời hạn hợp đồng</th>
                    <th>Tình trạng</th>
                </tr>
            </thead>
            <tbody id="contractData">
                <?php
                if (!empty($listRental_history)):
                    $count = 0; // Hiển thi số thứ tự
                    foreach ($listRental_history as $item):
                        $count++;
                        $tenants = getTenantsByRoomId($item['room_id']);
                ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $count; ?></td>
                            <td style="text-align: center;"><b><?php echo $item['tenphong']; ?></b></td>
                            <td style="text-align: center;">
                                <!--hiển thị nhưng không lấy ID -->
                                <?php
                                $tenkhachArray = explode("\n", $item['khachthue']);  // Tách từng khách hàng ra
                                foreach ($tenkhachArray as $tenkhach) {
                                    // Chỉ hiển thị tên khách hàng, ẩn ID
                                    $name = explode(" (ID:", $tenkhach)[0];  // Tách tên khách hàng từ phần ID
                                    echo "<b>{$name}</b><br>";  // Hiển thị tên khách hàng
                                }
                                ?>

                            </td>
                            <td style="text-align: center;"><b><?php echo number_format($item['giathue'], 0, ',', '.') ?> đ</b></td>
                            <td style="text-align: center;"><?php echo $item['chuky'] ?> tháng</td>
                            <td style="text-align: center;"><?php echo $item['ngaylaphopdong'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['ngaylaphopdong'], 'd-m-Y'); ?></td>
                            <td style="text-align: center;"><?php echo $item['ngayvaoo'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['ngayvaoo'], 'd-m-Y'); ?></td>
                            <td style="text-align: center;"><?php echo $item['thoihanhopdong'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['thoihanhopdong'], 'd-m-Y'); ?></td>
                            <td style="text-align: center;"><span class="btn-kyhopdong-err">Đã thanh lý</span></td>
                        <?php endforeach;
                else: ?>
                        <tr>
                            <td colspan="15">
                                <div class="alert alert-danger text-center">Không có dữ liệu lịch sử hợp đồng</div>
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