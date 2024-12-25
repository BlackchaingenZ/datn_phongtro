<?php

if (!defined('_INCODE')) die('Access denied...');

$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);
$groupId = $userDetail['group_id'];

if ($groupId != 7) {
    setFlashData('msg', 'Bạn không được truy cập vào trang này');
    setFlashData('msg_type', 'err');
    redirect('admin/?module=dashboard');
}

$data = [
    'pageTitle' => 'Danh sách thiết bị 1'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


$listAllCost = getRaw("SELECT matb, tentb, soluongtb, dongiatb FROM thietbi1 ORDER BY tentb ASC");

layout('navbar', 'admin', $data);

$searchTerm = '';
$searchResults = [];

if (isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];
    $searchTerm = htmlspecialchars($searchTerm); 

    $query = "SELECT * FROM thietbi1 WHERE tentb LIKE '%$searchTerm%'"; 
    $searchResults = executeResult($query); 
} else {

    $query = "SELECT * FROM thietbi1";
    $searchResults = executeResult($query);
}
?>
<div class="container-fluid">
    <div class="box-content">
        <form method="POST" action="">
            <div class="input-group mb-3">
                <input type="text" name="search_term" class="form-control" placeholder="Nhập tên thiết bị..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <div class="input-group-append">
                    <button type="submit" name="search" class="btn btn-primary">Tìm kiếm</button>
                </div>
            </div>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>TT</th>
                    </tr>
                </thead>
                <tbody id="costData">
                    <?php
                    $tongTien = 0;
                    if (!empty($searchResults)):
                        foreach ($searchResults as $item):
                            $thanhTien = $item['soluongtb'] * $item['dongiatb'];
                            $tongTien += $thanhTien; 
                    ?>
                            <tr>
                                <td><b><?php echo $item['matb']; ?></b></td>
                                <td><b><?php echo $item['tentb']; ?></b></td>
                                <td><b><?php echo $item['soluongtb']; ?></b></td>
                                <td><b><?php echo number_format($item['dongiatb'], 0, ',', '.'); ?> </b></td>
                                <td><b><?php echo number_format($thanhTien, 0, ',', '.'); ?> </b></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="alert alert-danger text-center">Không tìm thấy kết quả nào</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;"><b>Tổng tiền</b></td>
                        <td><b><?php echo number_format($tongTien, 0, ',', '.'); ?> </b></td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>


<?php layout('footer', 'admin'); ?>
