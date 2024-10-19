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
    redirect('admin/?module=');
}

$data = [
    'pageTitle' => 'Quản lý hợp đồng thuê trọ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý lọc dữ liệu
$filter = '';
if (isGet()) {
    $body = getBody('get');

   // Xử lý lọc Status theo trạng thái hợp đồng
if (!empty($body['status'])) {
    $status = $body['status'];

    if (!empty($filter) && strpos($filter, 'WHERE') !== false) {
        $operator = 'AND';
    } else {
        $operator = 'WHERE';
    }

    // Xử lý trạng thái "Trong thời hạn", "Đã hết hạn" và "Sắp hết hạn"
    if ($status == 1) {
        // Trong thời hạn: Hợp đồng có ngày kết thúc lớn hơn ngày hiện tại
        $statusSql = "DATEDIFF(contract.ngayra, NOW()) > 30";
        $filter .= "$operator $statusSql";
    } elseif ($status == 2) {
        // Đã hết hạn: Hợp đồng có ngày kết thúc nhỏ hơn hoặc bằng ngày hiện tại
        $statusSql = "DATEDIFF(contract.ngayra, NOW()) <= 0";
        $filter .= "$operator $statusSql";
    } elseif ($status == 3) {
        // Sắp hết hạn: Hợp đồng có ngày kết thúc trong vòng 30 ngày tới và lớn hơn ngày hiện tại
        $statusSql = "DATEDIFF(contract.ngayra, NOW()) <= 30 AND DATEDIFF(contract.ngayra, NOW()) > 0";
        $filter .= "$operator $statusSql";
    }
}


    // Xử lý lọc Status theo tình trạng cọc
    if(!empty($body['coc'])) {
        $status2 = $body['coc'];

        if($status2 == 2) {
            $statusSql2 = 0;
        } else {
            $statusSql2 = $status2;
        }

        if(!empty($filter) && strpos($filter, 'WHERE') !== false) {
            $operator = 'AND';
        } else {
            $operator = 'WHERE';
        }
        
        $filter .= "$operator tinhtrangcoc=$statusSql2";
    }
}



/// Xử lý phân trang
$allTenant = getRows("SELECT id FROM contract $filter");
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
$listAllcontract = getRaw("SELECT *, contract.id, tenphong, tenkhach, giathue, tiencoc, contract.ngayvao as ngayvaoo, contract.ngayra as thoihanhopdong, zalo FROM contract 
INNER JOIN room ON contract.room_id = room.id
INNER JOIN tenant ON contract.tenant_id = tenant.id
$filter LIMIT $offset, $perPage");


// Danh sách các hợp đồng sắp hết hạn
$expiringContracts = [];

// Thêm các hợp đồng sắp hết hạn vào danh sách
foreach ($listAllcontract as $contract) {
    $daysUntilExpiration = getContractStatus($contract['thoihanhopdong']);
    if ($daysUntilExpiration == "Sắp hết hạn") {
        $expiringContracts[] = $contract;
    }
}

// Xử lý query string tìm kiếm với phân trang
$queryString = null;
if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = str_replace('module=contract','', $queryString);
    $queryString = str_replace('&page='.$page, '', $queryString);
    $queryString = trim($queryString, '&');
    $queryString = '&'.$queryString;
}

// Xóa hết
if(isset($_POST['deleteMultip'])) {
    $numberCheckbox = $_POST['records'];
        $extract_id = implode(',', $numberCheckbox);
        $checkDelete = delete('contract', "id IN($extract_id)");
        if($checkDelete) {
            setFlashData('msg', 'Xóa thông tin phòng trọ thành công');
            setFlashData('msg_type', 'suc');
        }
        redirect('admin/?module=contract');
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
        <?php if(!empty($expiringContracts)) { ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Các phòng sắp hết hạn hợp đồng: <strong>
                <?php foreach($expiringContracts as $item){
                    echo $item['tenphong'].', ';
                } ?>
                </strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php } ?>
            <!-- Tìm kiếm , Lọc dưz liệu -->
        <form action="" method="get">
            <div class="row">
            <div class="col-3">
                <div class="form-group">
                    <select name="status" id="" class="form-select">
                        <option value="">Chọn trạng thái hợp đồng</option>
                        <option value="1" <?php echo (!empty($status) && $status==1) ? 'selected':false; ?>>Trong thời hạn</option>
                        <option value="2" <?php echo (!empty($status) && $status==2) ? 'selected':false; ?>>Đã hết hạn</option>
                        <option value="3" <?php echo (!empty($status) && $status==3) ? 'selected':false; ?>>Sắp hết hạn</option>
                    </select>
                </div>
            </div>

            <div class="col-3">              
                <div class="form-group">
                    <select name="coc" id="" class="form-select">
                        <option value="">Chọn trạng thái cọc</option>
                        <option value="1" <?php echo (!empty($status2) && $status2==1) ? 'selected':false; ?>>Đã thu</option>
                        <option value="2" <?php echo (!empty($status2) && $status2==2) ? 'selected':false; ?>>Chưa thu</option>
                    </select>
                </div>
            </div>

            <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" class="btn btn-success"> <i class="fa fa-search"></i></button>
            </div>
            </div>
            <input type="hidden" name="module" value="contract">
        </form>

        <form action="" method="POST" class="mt-3">
    <div>
  
</div>
            <a href="<?php echo getLinkAdmin('contract', 'add') ?>" class="btn btn-success" style="color: #fff"><i class="fa fa-plus"></i> Thêm</a>
            <a href="<?php echo getLinkAdmin('contract'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <button type="submit" name="deleteMultip" value="Delete" onclick="return confirm('Bạn có chắn chắn muốn xóa không ?')" class="btn btn-danger"><i class="fa fa-trash"></i> Xóa</button>
            <a href="<?php echo getLinkAdmin('contract', 'import'); ?>" class="btn btn-success minn"><i class="fa fa-upload"></i> Import</a>
            <a href="<?php echo getLinkAdmin('contract', 'export'); ?>" class="btn btn-success minn"><i class="fa fa-save"></i> Xuất Excel</a>

            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="check-all"  onclick="toggle(this)">
                        </th>
                        <th></th>
                        <th wìdth="5%">STT</th>
                        <th>Tên phòng</th>
                        <th>Người đại diện</th>
                        <th>Tổng thành viên</th>
                        <th>Giá thuê</th>
                        <th>Giá tiền cọc</th>
                        <th>Trạng thái cọc</th>
                        <th>Chu kỳ thu</th>
                        <th>Ngày lập</th>
                        <th>Ngày vào ở</th>
                        <th>Thời hạn hợp đồng</th>
                        <th>Tình trạng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="contractData">
        
                    <?php
                        if(!empty($listAllcontract)):
                            $count = 0; // Hiển thi số thứ tự
                            foreach($listAllcontract as $item):
                                $count ++;  
                    ?>

                    <tr>
                        <td>
                                <input type="checkbox" name="records[]" value="<?= $item['id'] ?>">                    
                        </td>
                                
                        <td>
                            <div class="image__contract">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/image-room.png" class="image__room-img" alt="">
                            </div>
                        </td>
                        <td><?php echo $count; ?></td>
                        <td><b><?php echo $item['tenphong']; ?></b></td>
                        <td><?php echo $item['tenkhach'] ?></td>
                        <td><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.svg" alt=""> <?php echo $item['soluongthanhvien'] ?> người</td>
                        <td><b><?php echo number_format($item['giathue'], 0, ',', '.') ?> đ</b></td>
                        <td><b><?php echo number_format($item['tiencoc'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['tinhtrangcoc'] == 0 ? '<span class="btn-kyhopdong-err">Chưa thu tiền</span>' : '<span class="btn-kyhopdong-suc">Đã thu tiền</span>' ?></td>
                        <td><?php echo $item['chuky'] ?> tháng</td>
                        <td><?php echo $item['ngaylaphopdong'] == '0000-00-00' ? 'Không xác định': getDateFormat($item['ngaylaphopdong'],'d-m-Y'); ?></td> 
                        <td><?php echo $item['ngayvaoo'] == '0000-00-00' ? 'Không xác định': getDateFormat($item['ngayvaoo'],'d-m-Y'); ?></td> 
                        <td><?php echo $item['thoihanhopdong'] == '0000-00-00' ? 'Không xác định': getDateFormat($item['thoihanhopdong'],'d-m-Y'); ?></td> 
                        <td>
                            <?php
                                $contractStatus = getContractStatus($item['thoihanhopdong']);
                                
                                if ($contractStatus == "Đã hết hạn") {
                                    echo '<span class="btn-kyhopdong-err">' . $contractStatus . '</span>';
                                } elseif ($contractStatus == "Trong thời hạn") {
                                    echo '<span class="btn-kyhopdong-suc">' . $contractStatus . '</span>';
                                } elseif ($contractStatus == "Sắp hết hạn") {
                                    echo '<span class="btn-kyhopdong-warning">' . $contractStatus . '</span>';
                                }
                            ?>
                        </td>           
                        <td class="">
                            <a title="Xem hợp đồng" href="<?php echo getLinkAdmin('contract','view',['id' => $item['id']]); ?>" class="btn btn-primary btn-sm" ><i class="nav-icon fas fa-solid fa-eye"></i> </a>
                            <a title="In hợp đồng" target="_blank" href="<?php echo getLinkAdmin('contract','print',['id' => $item['id']]) ?>" class="btn btn-secondary btn-sm" ><i class="fa fa-print"></i> </a>
                            <a target="_blank" href="<?php echo $item['zalo'] ?>"><img style="width: 30px; height: 30px" src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/zalo.jpg" alt=""></a>
                            <a href="<?php echo getLinkAdmin('contract','edit',['id' => $item['id']]); ?>" class="btn btn-warning btn-sm" ><i class="fa fa-edit"></i> </a>
                            <a href="<?php echo getLinkAdmin('contract','delete',['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                        </td>                
                         
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="15">
                                <div class="alert alert-danger text-center">Không có dữ liệu hợp đồng</div>
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
                    echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT_ADMIN.'/?module=contract'.$queryString. '&page='.$prePage.'">Pre</a></li>';
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
                    <a class="page-link" href="<?php echo _WEB_HOST_ROOT_ADMIN.'?module=contract'.$queryString.'&page='.$index;  ?>"> <?php echo $index;?> </a>
                </li>
                <?php  } ?>
                
                <?php
                    if($page < $maxPage) {
                        $nextPage = $page + 1;
                        echo '<li class="page-item"><a class="page-link" href="'._WEB_HOST_ROOT_ADMIN.'?module=contract'.$queryString.'&page='.$nextPage.'">Next</a></li>';
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

