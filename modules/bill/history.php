<?php

if (!defined('_INCODE'))
    die('Access denied...');

// Lấy thông tin người dùng hiện tại
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);
$roomId  = $userDetail['room_id'];

// Thiết lập dữ liệu trang
$data = [
    'pageTitle' => 'Lịch sử hóa đơn'
];

// Gọi các layout cần thiết
layout('header-tenant', 'admin', $data);
layout('sidebar', 'admin', $data);

// Lấy danh sách tất cả dịch vụ
$allService = getRaw("SELECT * FROM services");
$currentMonthYear = date('Y-m');

// Thiết lập bộ lọc để lấy hóa đơn của phòng hiện tại
$filter = "WHERE bill.room_id = $roomId";

// Lấy danh sách tất cả hóa đơn mà không áp dụng phân trang
$listAllBill = getRaw("SELECT *, bill.id, room.tenphong 
FROM bill 
INNER JOIN room ON bill.room_id = room.id 
LEFT JOIN tenant ON bill.tenant_id = tenant.id 
$filter  
ORDER BY bill.id DESC");

// Lấy thông báo và dữ liệu từ flash (nếu có)
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>

<div class="container-fluid">

    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <!-- Tìm kiếm -->
    <div class="box-container">
        <form action="" method="POST" class="mt-3">
            <div>
                <h3>Lịch sử hóa đơn tiền phòng</h3>
            </div>

            <table class="table table-bordered mt-3" style="overflow-x: auto;">
                <thead>
                    <tr>
                        <th width="3%" rowspan="2"> STT</th>
                        <th rowspan="2">Mã hoá đơn</th>
                        <th rowspan="2">Tên phòng</th>
                        <th rowspan="2">Tháng</th>
                        <th colspan="1">Tiền phòng</th>
                        <th colspan="3">Tiền điện</th>
                        <th colspan="3">Tiền nước</th>
                        <th colspan="2">Tiền rác</th>
                        <th colspan="1">Tiền Wifi</th>
                        <th rowspan="2">Tổng cộng</th>
                        <th rowspan="2">Cần thanh toán</th>
                        <th width="6%" rowspan="2">Trạng thái</th>
                        <th width="6%" rowspan="2">Ngày lập</th>
                    </tr>
                    <tr>
                        <th>Thành tiền</th>
                        <th>Số cũ</th>
                        <th>Số mới</th>
                        <th>Thành tiền</th>
                        <th>Số cũ</th>
                        <th>Số mới</th>
                        <th>Thành tiền</th>
                        <th>Người</th>
                        <th>Thành tiền</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody id="roomData">

                    <?php
                    if (!empty($listAllBill)):
                        $count = 0; // Hiển thi số thứ tự
                        foreach ($listAllBill as $item):
                            $count++;

                    ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $count; ?></td>
                                <td style="text-align: center; color: red"><?php echo $item['mahoadon']; ?></td>
                                <td style="text-align: center;"><?php echo $item['tenphong']; ?></td>
                                <td style="text-align: center;"><?php echo $item['thang']; ?></td>
                                <td style="text-align: center;"><b><?php echo number_format($item['tienphong'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><?php echo $item['sodiencu']; ?></td>
                                <td style="text-align: center;">
                                    <?php echo $item['sodienmoi']; ?>
                                    <a target="_blank" href="<?php echo getLinkAdmin('bill', 'img_sdm', ['id' => $item['id']]); ?>" class="fa fa-eye"></a>
                                </td>
                                <td style="text-align: center;"><b><?php echo number_format($item['tiendien'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><?php echo $item['sonuoccu']; ?></td>
                                <td style="text-align: center;">
                                    <?php echo $item['sonuocmoi']; ?>
                                    <a target="_blank" href="<?php echo getLinkAdmin('bill', 'img_snm', ['id' => $item['id']]); ?>" class="fa fa-eye"></a>
                                </td>
                                <td style="text-align: center;"><b><?php echo number_format($item['tiennuoc'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><?php echo $item['songuoi']; ?></td>
                                <td style="text-align: center;"><b><?php echo number_format($item['tienrac'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><b><?php echo number_format($item['tienmang'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center; color: #ed6004;">
                                    <b><?php echo number_format($item['tongtien'], 0, ',', '.'); ?> đ</b> <br />
                                    <i style="color: #000;">Số tiền đã trả</i><br />
                                    <b style="color: #15a05c;">
                                        <?php
                                        // Kiểm tra nếu sotienconthieu = 0 thì hiển thị tongtien
                                        if ($item['sotienconthieu'] == 0) {
                                            echo number_format($item['tongtien'], 0, ',', '.');
                                        } else {
                                            echo number_format($item['sotiendatra'], 0, ',', '.');
                                        }
                                        ?> đ
                                    </b>
                                </td>

                                <td style="text-align: center; color: #db2828"><b><?php echo number_format($item['sotienconthieu'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;">

                                    <?php
                                    if ($item['trangthaihoadon'] == 1) {
                                        echo '<span class="btn-kyhopdong-suc">Đã thu hết</span>';
                                    } elseif ($item['trangthaihoadon'] == 2) {
                                        echo '<span class="btn-kyhopdong-warning">Chưa thu</span>';
                                    } else {
                                        echo '<span class="btn-kyhopdong-err">Còn nợ</span>';
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;"><?php echo getDateFormat($item['create_at'], 'd-m-Y') ?></td>
                            </tr>

                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="22">
                                <div class="alert alert-danger text-center">Không có dữ liệu hóa đơn</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>

</div>

<?php layout('footer', 'admin'); ?>
