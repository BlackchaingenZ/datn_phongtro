<?php
// Lấy thông tin khách của hợp đồng
function getTenantsByRoomId($roomId) {
    return getRaw("SELECT tenkhach FROM tenant JOIN contract ON tenant.id = contract.tenant_id WHERE contract.room_id = $roomId");
}

// echo '<pre>';
// print_r($tenantOfcontract); 
// echo '</pre>'; die;
/// Xử lý phân trang
$allTenant = getRows("SELECT id FROM contract $filter");
$perPage = _PER_PAGE; // Mỗi trang có 3 bản ghi
$maxPage = ceil($allTenant / $perPage);

// 3. Xử lý số trang dựa vào phương thức GET
if (!empty(getBody()['page'])) {
    $page = getBody()['page'];
    if ($page < 1 or $page > $maxPage) {
        $page = 1;
    }
} else {
    $page = 1;
}
$offset = ($page - 1) * $perPage;
$listAllcontract = getRaw("SELECT *, contract.id, tenphong, giathue, tiencoc, soluong, contract.ngayvao as ngayvaoo, contract.ngayra as thoihanhopdong, tinhtrangcoc FROM contract 
INNER JOIN room ON contract.room_id = room.id
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
    $queryString = str_replace('module=contract', '', $queryString);
    $queryString = str_replace('&page=' . $page, '', $queryString);
    $queryString = trim($queryString, '&');
    $queryString = '&' . $queryString;
}

// Xóa hết
if (isset($_POST['deleteMultip'])) {
    $numberCheckbox = $_POST['records'];
    $extract_id = implode(',', $numberCheckbox);
    $checkDelete = delete('contract', "id IN($extract_id)");
    if ($checkDelete) {
        setFlashData('msg', 'Xóa thông tin phòng trọ thành công');
        setFlashData('msg_type', 'suc');
    }
    redirect('?module=contract');
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

    <div class="box-content">
        <?php if (!empty($expiringContracts)) { ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Các phòng sắp hết hạn hợp đồng: <strong>
                    <?php foreach ($expiringContracts as $item) {
                        echo $item['tenphong'] . ', ';
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
                            <option value="1" <?php echo (!empty($status) && $status == 1) ? 'selected' : false; ?>>Trong thời hạn</option>
                            <option value="2" <?php echo (!empty($status) && $status == 2) ? 'selected' : false; ?>>Đã hết hạn</option>
                            <option value="3" <?php echo (!empty($status) && $status == 3) ? 'selected' : false; ?>>Sắp hết hạn</option>
                        </select>
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <select name="coc" id="" class="form-select">
                            <option value="">Chọn trạng thái cọc</option>
                            <option value="1" <?php echo (!empty($status2) && $status2 == 1) ? 'selected' : false; ?>>Đã thu</option>
                            <option value="2" <?php echo (!empty($status2) && $status2 == 2) ? 'selected' : false; ?>>Chưa thu</option>
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
                            <input type="checkbox" id="check-all" onclick="toggle(this)">
                        </th>
                        <th></th>
                        <th wìdth="5%">STT</th>
                        <th>Tên phòng</th>
                        <th>Thành viên</th>
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
                    if (!empty($listAllcontract)) :
                        $count = 0; // Hiển thi số thứ tự
                        foreach ($listAllcontract as $item) :
                            $count++;
                            $tenants = getTenantsByRoomId($item['room_id']);
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
                                <td>
                                    <?php if (!empty($tenants)) {
                                        foreach ($tenants as $tenant) {
                                    ?>
                                            <span><?php echo $tenant['tenkhach'] ?></span> <br />
                                    <?php
                                        }
                                    } else {
                                        echo 'Chưa có ai';
                                    } ?>
                                </td>
                                <td><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.svg" alt=""> <?php echo $item['soluong'] ?> người</td
