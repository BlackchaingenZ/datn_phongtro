<?php

if(!defined('_INCODE'))
die('Access denied...');


$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId); 
$roomId  = $userDetail['room_id'];

$data = [
    'pageTitle' => 'Lịch sử hóa đơn'
];

layout('header-tenant', 'admin', $data);
layout('sidebar', 'admin', $data);


$allService = getRaw("SELECT * FROM services");
$currentMonthYear = date('Y-m');

// Xử lý lọc dữ liệu
$filter = '';

/// Xử lý phân trang
$allBill = getRows("SELECT id FROM bill $filter");
$perPage = _PER_PAGE; // Mỗi trang có 3 bản ghi
$maxPage = ceil($allBill / $perPage);

// 3. Xử lý số trang dựa vào phương thức GET
if(!empty(getBody()['page'])) {
    $page = getBody()['page'];
    if($page < 1 and $page > $maxPage) {
        $page = 1;
    }
}else {
    $page = 1;
}
$offset = ($page - 1) * $perPage;
$listAllBill = getRaw("SELECT *, bill.id, bill.chuky, room.tenphong, tenant.zalo FROM bill 
INNER JOIN room ON bill.room_id = room.id LEFT JOIN tenant ON bill.tenant_id = tenant.id $filter  ORDER BY bill.create_at DESC  LIMIT $offset, $perPage");

// Xử lý query string tìm kiếm với phân trang
$queryString = null;
if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = str_replace('module=bill','', $queryString);
    $queryString = str_replace('&page='.$page, '', $queryString);
    $queryString = trim($queryString, '&');
    $queryString = '&'.$queryString;
}

$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>

<div class="container-fluid">

        <div id="MessageFlash">          
                <?php getMsg($msg, $msgType);?>          
        </div>

    <!-- Tìm kiếm -->
    <div class="box-content">
        <form action="" method="POST" class="mt-3">
    <div>
    <h3>Lịch sử hóa đơn tiền nhà</h3>
</div>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th width="3%" rowspan="2"></th>
                        <th rowspan="2">Tên phòng</th>
                        <th colspan="3">Tiền phòng</th>
                        <th colspan="3">Tiền điện</th>
                        <th colspan="3">Tiền nước</th>
                        <th colspan="2">Tiền rác</th>
                        <th colspan="2">Tiền Wifi</th>
                        <th width="3%" rowspan="2">Cộng thêm</th>
                        <th rowspan="2">Tổng cộng</th>
                        <th rowspan="2">Còn nợ</th>
                        <th rowspan="2">Ngày lập</th>
                        <th width="7%" rowspan="2">Trạng thái</th>
                    </tr>
                    <tr>
                        <th width="3%">Số tháng</th>
                        <th width="4%">Ngày lẻ</th>
                        <th>Tiền phòng</th>
                        <th>Số cũ</th>
                        <th>Số mới</th>
                        <th>Thành tiền</th>
                        <th>Số cũ</th>
                        <th>Số mới</th>
                        <th>Thành tiền</th>
                        <th>Người</th>
                        <th>Thành tiền</th>
                        <th>Tháng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody id="roomData">
        
                    <?php
                        if(!empty($listAllBill)):
                            $count = 0; // Hiển thi số thứ tự
                            foreach($listAllBill as $item):
                                $count ++;
        
                    ?>
                     <tr>
                        <td>
                            <div class="image__bill">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/bill-icon.svg" class="image__bill-img" alt="">
                            </div>
                        </td>
                        <td><?php echo $item['tenphong']; ?></td>
                        <td><?php echo $item['chuky']; ?></td>
                        <td><?php echo $item['songayle']; ?></td>
                        <td><b><?php echo number_format($item['tienphong'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['sodiencu']; ?></td>
                        <td>
                            <?php echo $item['sodienmoi']; ?>
                            <a target="_blank" href="<?php echo getLinkAdmin('bill','img_sdm',['id' => $item['id']]); ?>" class="fa fa-eye"></a>
                        </td>
                        <td><b><?php echo number_format($item['tiendien'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['sonuoccu']; ?></td>
                        <td>
                            <?php echo $item['sonuocmoi']; ?>
                            <a target="_blank" href="<?php echo getLinkAdmin('bill','img_snm',['id' => $item['id']]); ?>" class="fa fa-eye"></a>
                        </td>
                        <td><b><?php echo number_format($item['tiennuoc'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['songuoi']; ?></td>
                        <td><b><?php echo number_format($item['tienrac'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['chuky']; ?></td>
                        <td><b><?php echo number_format($item['tienmang'], 0, ',', '.') ?> đ</b></td>
                        <td><b><?php echo number_format($item['nocu'], 0, ',', '.') ?> đ</b></td>
                        <td style="color: #ed6004">
                            <b><?php echo number_format($item['tongtien'], 0, ',', '.') ?> đ</b> <br />
                            <i style="color: #000">Số tiền đã trả</i><br />
                            <b style="color: #15a05c"><?php echo number_format($item['sotiendatra'], 0, ',', '.') ?> đ</b>
                        </td>
                        <td style="color: #db2828"><b><?php echo number_format($item['sotienconthieu'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['create_at'] ?></td>
                        <td>
                            <?php 
                                 if($item['trangthaihoadon'] == 1) {
                                    echo '<span class="btn-kyhopdong-suc">Đã thu</span>';
                                 } elseif($item['trangthaihoadon'] == 0) {
                                    echo '<span class="btn-kyhopdong-warning">Chưa thu</span>';
                                 } else {
                                    echo '<span class="btn-kyhopdong-err">Đang nợ</span>';
                                 }
                            ?>
                        </td>
                    </tr>               
                         
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="19">
                                <div class="alert alert-danger text-center">Không có dữ liệu hóa đơn</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <nav aria-label="Page navigation example" class="d-flex justify-content-center">
            <ul class="pagination pagination-sm">
                <?php
                    if($page > 1) {
                        $prePage = $page - 1;
                    echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT.'/?module=bill'.$queryString. '&page='.$prePage.'">Pre</a></li>';
                    }
                ?>

                <?php 
                    // Giới hạn số trang
                    $begin = $page - 2;
                    $end = $page + 2;
                    if($begin < 1) {
                        $begin = 1;
                    }
                    if($end > $maxPage) {
                        $end = $maxPage;
                    }
                    for($index = $begin; $index <= $end; $index++){  ?>
                <li class="page-item <?php echo ($index == $page) ? 'active' : false; ?> ">
                    <a class="page-link" href="<?php echo _WEB_HOST_ROOT.'?module=bill'.$queryString.'&page='.$index;  ?>"> <?php echo $index;?> </a>
                </li>
                <?php  } ?>
                
                <?php
                    if($page < $maxPage) {
                        $nextPage = $page + 1;
                        echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT.'?module=bill'.$queryString.'&page='.$nextPage.'">Next</a></li>';
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
    function toggle(__this){
       let isChecked = __this.checked;
       let checkbox = document.querySelectorAll('input[name="records[]"]');
        for (let index = 0; index < checkbox.length; index++) {
            checkbox[index].checked = isChecked
        }
    }
</script>
