<?php

if(!defined('_INCODE'))
die('Access denied...');

// Ngăn chặn quyền truy cập
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId); 

$grouId = $userDetail['group_id'];

if($grouId != 7) {
    setFlashData('msg', 'Bạn không được truy cập vào trang này');
    setFlashData('msg_type', 'err');
    redirect('admin/?module=');
}

$data = [
    'pageTitle' => 'Danh sách phòng trọ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);



// Xử lý lọc dữ liệu
// $allRoom = getRaw("SELECT id, tenphong, soluong, trangthai FROM room ORDER BY tenphong");
$filter = '';
if (isGet()) {
    $body = getBody('get');
    

    // Xử lý lọc theo từ khóa
    if(!empty($body['keyword'])) {
        $keyword = $body['keyword'];
        
        if(!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        }else {
            $operator = 'WHERE';
        }

        $filter .= " $operator tenphong LIKE '%$keyword%'";

    }

    //Xử lý lọc Status
    if(!empty($body['status'])) {
        $status = $body['status'];

        if($status == 2) {
            $statusSql = 0;
        } else {
            $statusSql = $status;
        }

        if(!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        }else {
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
if(!empty(getBody()['page'])) {
    $page = getBody()['page'];
    if($page < 1 and $page > $maxPage) {
        $page = 1;
    }
}else {
    $page = 1;
}
$offset = ($page - 1) * $perPage;
$listAllroom = getRaw("SELECT * FROM room $filter ORDER BY tenphong ASC LIMIT $offset, $perPage");

// Xử lý query string tìm kiếm với phân trang
$queryString = null;
if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = str_replace('module=room','', $queryString);
    $queryString = str_replace('&page='.$page, '', $queryString);
    $queryString = trim($queryString, '&');
    $queryString = '&'.$queryString;
}

// Xóa hết
if(isset($_POST['deleteMultip'])) {
    $numberCheckbox = $_POST['records'];
        $extract_id = implode(',', $numberCheckbox);
        $checkDelete = delete('room', "id IN($extract_id)");
        if($checkDelete) {
            setFlashData('msg', 'Xóa thông tin phòng trọ thành công');
            setFlashData('msg_type', 'suc');
        }
        redirect('admin/?module=room');
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

    <!-- Tìm kiếm -->
    <div class="box-content">
            <!-- Tìm kiếm , Lọc dưz liệu -->
        <form action="" method="get">
            <div class="row">
            <div class="col-3">
                <div class="form-group">
                    <select name="status" id="" class="form-select">
                        <option value="0">Chọn trạng thái</option>
                        <option value="1" <?php echo (!empty($status) && $status==1) ? 'selected':false; ?>>Đang ở</option>
                        <option value="2" <?php echo (!empty($status) && $status==2) ? 'selected':false; ?>>Đang trống</option>
                    </select>
                </div>
            </div>

            <div class="col-4">
                    <input style="height: 50px" type="search" name="keyword" class="form-control" placeholder="Nhập tên phòng cần tìm" value="<?php echo (!empty($keyword))? $keyword:false; ?>" >
            </div>

            <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" class="btn btn-success"> <i class="fa fa-search"></i></button>
            </div>
            </div>
            <input type="hidden" name="module" value="room">
        </form>

        <form action="" method="POST" class="mt-3">
    <div>
  
</div>
            <a href="<?php echo getLinkAdmin('room', 'add') ?>" class="btn btn-success" style="color: #fff"><i class="fa fa-plus"></i> Thêm</a>
            <a href="<?php echo getLinkAdmin('room', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <button type="submit" name="deleteMultip" value="Delete" onclick="return confirm('Bạn có chắn chắn muốn xóa không ?')" class="btn btn-danger"><i class="fa fa-trash"></i> Xóa</button>
            <a href="<?php echo getLinkAdmin('room', 'import'); ?>" class="btn btn-success minn"><i class="fa fa-upload"></i> Import</a>
            <a href="<?php echo getLinkAdmin('room', 'export'); ?>" class="btn btn-success minn"><i class="fa fa-save"></i> Xuất Excel</a>

            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="check-all"  onclick="toggle(this)">
                        </th>
                        <th></th>
                        <th wìdth="5%">STT</th>
                        <th>Tên phòng</th>
                        <th>Diện tích</th>
                        <th>Giá thuê</th>
                        <th>Giá tiền cọc</th>
                        <th>Khách thuê</th>
                        <th>Ngày lập hóa đơn</th>
                        <th>Chu kỳ thu tiền</th>
                        <th>Ngày vào ở</th>
                        <th>Ngày hết hạn</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="roomData">
        
                    <?php
                        if(!empty($listAllroom)):
                            $count = 0; // Hiển thi số thứ tự
                            foreach($listAllroom as $item):
                                $count ++;
        
                    ?>
                    <tr>
                        <td>
                                <input type="checkbox" name="records[]" value="<?= $item['id'] ?>">                    
                        </td>
                                
                        <td>
                            <div class="image__room">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/image-room.png" class="image__room-img" alt="">
                            </div>
                        </td>
                        <td><?php echo $count; ?></td>
                        <td><b><?php echo $item['tenphong']; ?></b></td>
                        <td><?php echo $item['dientich'] ?> m2</td>
                        <td><b><?php echo number_format($item['giathue'], 0, ',', '.') ?> đ</b></td>
                        <td><b><?php echo number_format($item['tiencoc'], 0, ',', '.') ?> đ</b></td>
                        <td><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.svg" alt=""> <?php echo $item['soluong'] ?>/2 người</td>
                        <td>Ngày <?php echo $item['ngaylaphd'] ?></td>
                        <td><?php echo $item['chuky'] ?> tháng</td>
                        <td><?php echo $item['ngayvao'] == '0000-00-00' ? 'Không xác định': getDateFormat($item['ngayvao'],'d-m-Y'); ?></td> 
                        <td><?php echo $item['ngayra']  == '0000-00-00' ? 'Không xác định': getDateFormat($item['ngayra'],'d-m-Y'); ?></td> 
                        <td>                          
                            <?php 
                                 echo $item['trangthai'] == 1 ? '<span class="btn-status-suc">Đang ở</span>':'<span class="btn-status-err">Đang trống</span>';
                            ?>                          
                        </td>
                
                        <td class="">
                            <a href="<?php echo getLinkAdmin('room','edit',['id' => $item['id']]); ?>" class="btn btn-warning btn-sm" ><i class="fa fa-edit"></i> </a>
                            <a href="<?php echo getLinkAdmin('room','delete',['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                        </td>                
                         
                    <?php endforeach; else: ?>
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
                    if($page > 1) {
                        $prePage = $page - 1;
                    echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT_ADMIN.'/?module=room'.$queryString. '&page='.$prePage.'">Pre</a></li>';
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
                    <a class="page-link" href="<?php echo _WEB_HOST_ROOT_ADMIN.'?module=room'.$queryString.'&page='.$index;  ?>"> <?php echo $index;?> </a>
                </li>
                <?php  } ?>
                
                <?php
                    if($page < $maxPage) {
                        $nextPage = $page + 1;
                        echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT_ADMIN.'?module=room'.$queryString.'&page='.$nextPage.'">Next</a></li>';
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
