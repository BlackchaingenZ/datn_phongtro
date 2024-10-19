<?php

if(!defined('_INCODE'))
die('Access denied...');

// Ngăn chặn quyền truy cập
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId); 

$grouId = $userDetail['group_id'];

if($grouId != 7) {
    setFlashData('msg', 'Trang bạn muốn truy cập không tồn tại');
    setFlashData('msg_type', 'err');
    redirect('/?module=dashboard');
}

$data = [
    'pageTitle' => 'Lịch sử hợp đồng thuê trọ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý lọc dữ liệu
$filter = '';
if (isGet()) {
    $body = getBody('get');

}

// Lấy thông tin khách của hợp đồng
function getTenantsByRoomId($roomId) {
    return getRaw("SELECT * FROM tenant WHERE room_id = $roomId");
}

/// Xử lý phân trang
$allTenant = getRows("SELECT id FROM rental_history $filter");
$perPage = _PER_PAGE; // Mỗi trang có 3 bản ghi
$maxPage = ceil($allTenant / $perPage);

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
$listRental_history = getRaw("SELECT *, rental_history.id, soluong, tenphong, giathue, tiencoc, rental_history.ngayvao as ngayvaoo, rental_history.ngayra as thoihanhopdong FROM rental_history 
INNER JOIN room ON rental_history.room_id = room.id
$filter LIMIT $offset, $perPage");


// Xử lý query string tìm kiếm với phân trang
$queryString = null;
if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = str_replace('module=rental_history','', $queryString);
    $queryString = str_replace('&page='.$page, '', $queryString);
    $queryString = trim($queryString, '&');
    $queryString = '&'.$queryString;
}


$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>

<?php
layout('navbar', 'admin', $data);
?>

<div class="container-fluid">
        <div id="MessageFlash">          
                <?php getMsg($msg, $msgType);?>          
        </div>

    <div class="box-content">

    <div>
  
</div>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th></th>
                        <th wìdth="5%">STT</th>
                        <th>Tên phòng</th>
                        <th>Thành viên</th>
                        <th>Tổng thành viên</th>
                        <th>Giá thuê</th>
                        <th>Giá tiền cọc</th>
                        <th>Chu kỳ thu</th>
                        <th>Ngày lập</th>
                        <th>Ngày vào ở</th>
                        <th>Thời hạn hợp đồng</th>
                        <th>Tình trạng</th>
                    </tr>
                </thead>
                <tbody id="contractData">
        
                    <?php
                        if(!empty($listRental_history)):
                            $count = 0; // Hiển thi số thứ tự
                            foreach($listRental_history as $item):
                                $count ++;  
                                $tenants = getTenantsByRoomId($item['room_id']);
                    ?>

                    <tr>
                        <td>
                            <div class="image__contract">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/apartment.png" class="image__room-img" alt="">
                            </div>
                        </td>
                        <td><?php echo $count; ?></td>
                        <td><b><?php echo $item['tenphong']; ?></b></td>
                        <td>
                            <?php if(!empty($tenants)) {
                                foreach($tenants as $tenant) {
                                    ?>
                                        <span><?php echo $tenant['tenkhach']?></span> <br/>
                                    <?php
                                }
                            } else {echo '<i>Chưa có ai</i>';} ?>
                        </td>
                        <td><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.svg" alt=""> <?php echo $item['soluong'] ?> người</td>
                        <td><b><?php echo number_format($item['giathue'], 0, ',', '.') ?> đ</b></td>
                        <td><b><?php echo number_format($item['tiencoc'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['chuky'] ?> tháng</td>
                        <td><?php echo $item['ngaylaphopdong'] == '0000-00-00' ? 'Không xác định': getDateFormat($item['ngaylaphopdong'],'d-m-Y'); ?></td> 
                        <td><?php echo $item['ngayvaoo'] == '0000-00-00' ? 'Không xác định': getDateFormat($item['ngayvaoo'],'d-m-Y'); ?></td> 
                        <td><?php echo $item['thoihanhopdong'] == '0000-00-00' ? 'Không xác định': getDateFormat($item['thoihanhopdong'],'d-m-Y'); ?></td> 
                        <td><span class="btn-kyhopdong-err">Đã thanh lý</span></td>               
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="15">
                                <div class="alert alert-danger text-center">Không có dữ liệu lịch sử hợp đồng</div>
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
                    echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT.'/?module=contract'.$queryString. '&page='.$prePage.'">Pre</a></li>';
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
                    <a class="page-link" href="<?php echo _WEB_HOST_ROOT.'?module=contract'.$queryString.'&page='.$index;  ?>"> <?php echo $index;?> </a>
                </li>
                <?php  } ?>
                
                <?php
                    if($page < $maxPage) {
                        $nextPage = $page + 1;
                        echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT.'?module=contract'.$queryString.'&page='.$nextPage.'">Next</a></li>';
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

document.addEventListener('DOMContentLoaded', function() {
        // Select all action buttons
        const actionButtons = document.querySelectorAll('.action');

        actionButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                // Prevent event bubbling
                event.stopPropagation();
                
                // Toggle the active class
                button.classList.toggle('active');
                
                // Hide all other .box-action elements
                actionButtons.forEach(btn => {
                    if (btn !== button) {
                        btn.classList.remove('active');
                    }
                });
            });
        });

        // Hide .box-action when clicking outside
        document.addEventListener('click', function(event) {
            actionButtons.forEach(button => {
                button.classList.remove('active');
            });
        });

        // Prevent .box-action click from closing itself
        const boxActions = document.querySelectorAll('.box-action');
        boxActions.forEach(box => {
            box.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
    });
    
    function toggle(__this){
       let isChecked = __this.checked;
       let checkbox = document.querySelectorAll('input[name="records[]"]');
        for (let index = 0; index < checkbox.length; index++) {
            checkbox[index].checked = isChecked
        }
    }
</script>

