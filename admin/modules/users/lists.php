<?php

if(!defined('_INCODE'))
die('Access denied...');

$data = [
    'pageTitle' => 'Danh sách người dùng hệ thống'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$allGroups = getRaw("SELECT id, name FROM groups ORDER BY id");

// Xử lý lọc dữ liệu
$filter = '';
if (isGet()) {
    $body = getBody();

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
        
        $filter .= "$operator status=$statusSql";
    }

    // Xử lý lọc theo từ khóa
    if(!empty($body['keyword'])) {
        $keyword = $body['keyword'];
        
        if(!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        }else {
            $operator = 'WHERE';
        }

        $filter .= " $operator fullname LIKE '%$keyword%'";

    }

    //Xử lý lọc theo groups
    if(!empty($body['group_id'])) {
        $groupId = $body['group_id'];

        if(!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        }else {
            $operator = 'WHERE';
        }

        $filter .= " $operator group_id = $groupId";

    }


}

/// Xử lý phân trang
$allTenant = getRows("SELECT id FROM users $filter");
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
$listAllUser = getRaw("SELECT users.*, groups.name, room.tenphong 
FROM users 
LEFT JOIN groups ON users.group_id = groups.id 
LEFT JOIN room ON users.room_id = room.id 
$filter LIMIT $offset, $perPage");

// Xử lý query string tìm kiếm với phân trang
$queryString = null;
if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = str_replace('module=users','', $queryString);
    $queryString = str_replace('&page='.$page, '', $queryString);
    $queryString = trim($queryString, '&');
    $queryString = '&'.$queryString;
}

// Xóa hết
if(isset($_POST['deleteMultip'])) {
    $numberCheckbox = $_POST['records'];
        $extract_id = implode(',', $numberCheckbox);
        $checkDelete = delete('users', "id IN($extract_id)");
        if($checkDelete) {
            setFlashData('msg', 'Xóa thông tin người dùng thành công');
            setFlashData('msg_type', 'suc');
        }
        redirect('admin/?module=users');
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
            <div class="col-2">
                    <div class="form-group">
                        <select name="status" id="" class="form-select">
                            <option value="0">Chọn trạng thái</option>
                            <option value="1" <?php echo (!empty($status) && $status==1) ? 'selected':false; ?>>Đã kích hoạt</option>
                            <option value="2" <?php echo (!empty($status) && $status==2) ? 'selected':false; ?>>Chưa kích hoạt</option>
                        </select>
                    </div>
            </div>
            <div class="col-2">
                    <div class="form-group">
                        <select name="group_id" id="" class="form-select">
                            <option value="">Chọn nhóm</option>
                        <?php

                            if(!empty($allGroups)) {
                                foreach($allGroups as $item) {
                            ?>
                                <option value="<?php echo $item['id'] ?>" <?php  echo (!empty($groupId) && $groupId == $item['id'])?'selected':false; ?>><?php echo $item['name'] ?></option> 
                            
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
            </div>
            <div class="col-3">
                    <input type="search" style="height: 50px" name="keyword" class="form-control" placeholder="Nhập tên người dùng" value="<?php echo (!empty($keyword))? $keyword:false; ?>">
            </div>
            <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" class="btn btn-success"> <i class="fa fa-search"></i></button>
            </div>

            </div>
            <input type="hidden" name="module" value="users">
        </form>

        <form action="" method="POST" class="mt-3">
    <div>
  
</div>
            <a href="<?php echo getLinkAdmin('users', 'add') ?>" class="btn btn-success" style="color: #fff"><i class="fa fa-plus"></i> Thêm</a>
            <a href="<?php echo getLinkAdmin('users', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>

            <table class="table table-bordered mt-3" id="dataTable">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="check-all"  onclick="toggle(this)">
                        </th>
                        <th></th>
                        <th>Tên khách hàng</th>
                        <th>Email</th>
                        <th>Nhóm</th>
                        <th>Phòng đang ở</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
        
                    <?php
                        if(!empty($listAllUser)):
                            $count = 0; // Hiển thi số thứ tự
                            foreach($listAllUser as $item):
                                $count ++;
        
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="records[]" value="<?= $item['id'] ?>">                    
                        </td>
                                
                        <td>
                            <div class="tenant_avt">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tenant_avt.svg" class="image__room-img" alt="">
                            </div>
                        </td>
                        <td><b><?php echo $item['fullname']; ?></b></td>
                        <td><?php echo $item['email'] ?> </td>
                        <td style="text-align: center"><span class="btn-kyhopdong-war"><?php echo ($item['name']) ?></span></td>
                        <td style="text-align: center"><?php echo $item['tenphong'] != NULL ? '<span class="btn-kyhopdong-err">'.$item['tenphong'].'</span>' : '<span class="btn-kyhopdong-suc">Không</span>' ?></td>
                        <td><?php echo getDateFormat($item['create_at'],'d-m-Y') ?></td>
                        <td style="text-align: center"><?php echo $item['status'] == 0 ? '<span class="btn-kyhopdong-err">Chưa kích hoạt</span>' : '<span class="btn-kyhopdong-suc">Đã kích hoạt</span>' ?></td>

                        <td class="">
                            <a href="<?php echo getLinkAdmin('users','edit',['id' => $item['id']]); ?>" class="btn btn-warning btn-sm" ><i class="fa fa-edit"></i> </a>
                            <a href="<?php echo getLinkAdmin('users','delete',['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                        </td>                
                         
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="14">
                                <div class="alert alert-danger text-center">Không có dữ liệu người dùng</div>
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
                    echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT_ADMIN.'/?module=users'.$queryString. '&page='.$prePage.'">Pre</a></li>';
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
                    <a class="page-link" href="<?php echo _WEB_HOST_ROOT_ADMIN.'?module=users'.$queryString.'&page='.$index;  ?>"> <?php echo $index;?> </a>
                </li>
                <?php  } ?>
                
                <?php
                    if($page < $maxPage) {
                        $nextPage = $page + 1;
                        echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT_ADMIN.'?module=users'.$queryString.'&page='.$nextPage.'">Next</a></li>';
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
