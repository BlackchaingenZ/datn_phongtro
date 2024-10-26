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
    'pageTitle' => 'Quản lý hợp đồng thuê trọ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý lọc dữ liệu
$filter = '';
if (isGet()) {
    $body = getBody('get');

    // Mảng để lưu trữ các điều kiện lọc
    $filterConditions = [];

    // Xử lý lọc Status theo trạng thái hợp đồng
    if (!empty($body['status'])) {
        $status = $body['status'];

        // Xử lý trạng thái "Trong thời hạn", "Đã hết hạn" và "Sắp hết hạn"
        if ($status == 1) {
            // Trong thời hạn: Hợp đồng có ngày kết thúc lớn hơn ngày hiện tại
            $statusSql = "DATEDIFF(contract.ngayra, NOW()) > 30";
        } elseif ($status == 2) {
            // Đã hết hạn: Hợp đồng có ngày kết thúc nhỏ hơn hoặc bằng ngày hiện tại
            $statusSql = "DATEDIFF(contract.ngayra, NOW()) <= 0";
        } elseif ($status == 3) {
            // Sắp hết hạn: Hợp đồng có ngày kết thúc trong vòng 30 ngày tới và lớn hơn ngày hiện tại
            $statusSql = "DATEDIFF(contract.ngayra, NOW()) <= 30 AND DATEDIFF(contract.ngayra, NOW()) > 0";
        }

        // Thêm điều kiện vào mảng
        $filterConditions[] = $statusSql;
    }

    // Xử lý lọc Status theo tình trạng cọc
    if (!empty($body['coc'])) {
        $status2 = $body['coc'];

        if ($status2 == 2) {
            $statusSql2 = "tinhtrangcoc=0";
        } else {
            $statusSql2 = "tinhtrangcoc=$status2";
        }

        // Thêm điều kiện vào mảng
        $filterConditions[] = $statusSql2;
    }

    // Kết hợp các điều kiện thành một chuỗi SQL
    if (!empty($filterConditions)) {
        $filter = 'WHERE ' . implode(' AND ', $filterConditions);
    }
}

// Xử lý hành động thanh lý hợp đồng
if (isset($_POST['terminate'])) {
    $contractId = $_POST['contract_id'];
    $contract = getContractById($contractId);

    if ($contract) {
        // Thêm vào bảng lịch sử
        addContractToHistory($contract);

        // Xóa khỏi bảng hợp đồng
        deleteContract($contractId);

        setFlashData('msg', 'Thanh lý hợp đồng thuê trọ thành công');
        setFlashData('msg_type', 'suc');
    } else {
        setFlashData('msg', 'Không tìm thấy hợp đồng');
        setFlashData('msg_type', 'err');
    }

    redirect('?module=contract');
}

function getContractById($id)
{
    // Lấy hợp đồng từ database
    return firstRaw("SELECT * FROM contract WHERE id = $id");
}

function addContractToHistory($contract)
{
    // Thêm hợp đồng vào bảng lịch sử
    $data = [
        'contract_id' => $contract['id'],
        'room_id' => $contract['room_id'],
        'soluongthanhvien' => $contract['soluongthanhvien'],
        'ngaylaphopdong' => $contract['ngaylaphopdong'],
        'ngayvao' => $contract['ngayvao'],
        'ngayra' => $contract['ngayra'],
        'ngaythanhly' => date('Y-m-d')
    ];
    insert('rental_history', $data);
}

function deleteContract($id)
{
    // Xóa hợp đồng khỏi database
    delete('contract', "id = $id");
}

// Lấy thông tin khách của hợp đồng
function getTenantsByRoomId($roomId)
{
    return getRaw("SELECT * FROM tenant WHERE room_id = $roomId");
}


$allTenant = getRows("SELECT id FROM contract $filter");
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
//lấy giá thuê từ bảng cost,không lấy từ room
$listAllcontract = getRaw("
    SELECT *, 
        contract.id, 
        tenphong, 
        cost.giathue, 
        tenant1.tenkhach AS tenant_name_1, 
        tenant2.tenkhach AS tenant_name_2, 
        tiencoc, 
        soluong, 
        contract.ngayvao AS ngayvaoo, 
        contract.ngayra AS thoihanhopdong, 
        tinhtrangcoc, 
        GROUP_CONCAT(DISTINCT services.tendichvu ORDER BY services.tendichvu ASC SEPARATOR ', ') AS tendichvu 
    FROM contract 
    INNER JOIN room ON contract.room_id = room.id
    LEFT JOIN tenant AS tenant1 ON contract.tenant_id = tenant1.id
    LEFT JOIN tenant AS tenant2 ON contract.tenant_id_2 = tenant2.id
    INNER JOIN cost_room ON room.id = cost_room.room_id 
    INNER JOIN cost ON cost_room.cost_id = cost.id
    LEFT JOIN contract_services ON contract.id = contract_services.contract_id 
    LEFT JOIN services ON contract_services.services_id = services.id 
    $filter 
GROUP BY 
        contract.id
    LIMIT 
        $offset, $perPage
");



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
            <!--thông báo  trên màn hình -->
            <div class="alert alert-danger alert-dismissible fade show shadow rounded alert-hover" role="alert">
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
                    <button style="height: 50px; width: 50px" type="submit" class="btn btn-secondary"> <i class="fa fa-search"></i></button>
                </div>
            </div>
            <input type="hidden" name="module" value="contract">
        </form>

        <form action="" method="POST" class="mt-3">
            <div>

            </div>
            <a href="<?php echo getLinkAdmin('contract', 'add') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm mới</a>
            <a href="<?php echo getLinkAdmin('contract'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <button type="submit" name="deleteMultip" value="Delete" onclick="return confirm('Bạn có chắn chắn muốn xóa không ?')" class="btn btn-secondary"><i class="fa fa-trash"></i> Xóa</button>
            <a href="<?php echo getLinkAdmin('contract', 'import'); ?>" class="btn btn-secondary"><i class="fa fa-upload"></i> Import</a>
            <a href="<?php echo getLinkAdmin('contract', 'export'); ?>" class="btn btn-secondary"><i class="fa fa-save"></i> Xuất Excel</a>

            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="check-all" onclick="toggle(this)">
                        </th>
                        <th></th>
                        <th wìdth="2%">STT</th>
                        <th>Tên phòng</th>
                        <th style="width: 6%; text-align: center;">Người làm hợp đồng</th>
                        <th>Đang ở</th>
                        <th style="width: 2%; text-align: center;">Tổng người</th>
                        <th>Giá thuê</th>
                        <th style="width: 5%; text-align: center;">Giá tiền cọc</th>
                        <th style="width: 6%; text-align: center;" >Trạng thái cọc</th>
                        <th style="width: 4%; text-align: center;">Chu kỳ thu </th>
                        <th>Ngày lập</th>
                        <th>Ngày vào ở</th>
                        <th style="width: 6%; text-align: center;" >Thời hạn hợp đồng</th>
                        <th>Dịch vụ</th>
                        <th>Tình trạng</th>
                        <th style="width: 3%; text-align: center;" >Thao tác</th>
                    </tr>
                </thead>
                <tbody id="contractData">

                    <?php
                    if (!empty($listAllcontract)):
                        $count = 0; // Hiển thi số thứ tự
                        foreach ($listAllcontract as $item):
                            $count++;
                            $tenants = getTenantsByRoomId($item['room_id']);

                    ?>

                            <tr>
                                <td>
                                    <input type="checkbox" name="records[]" value="<?= $item['id'] ?>">
                                </td>

                                <td>
                                    <div class="image__contract">
                                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/contracts.png" class="image__room-img" alt="">
                                    </div>
                                </td>
                                <td><?php echo $count; ?></td>
                                <td><b><?php echo $item['tenphong']; ?></b></td>
                                <td>
                                    <b><?php echo $item['tenant_name_1']; ?></b><br>
                                    <b><?php echo $item['tenant_name_2']; ?></b>
                                </td>
                                <td>
                                    <?php if (!empty($tenants)) {
                                        foreach ($tenants as $tenant) {
                                    ?>
                                            <span><?php echo $tenant['tenkhach'] ?></span> <br />
                                    <?php
                                        }
                                    } else {
                                        echo '<i>Chưa có ai</i>';
                                    } ?>
                                </td>
                                <td><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.svg" alt=""> <?php echo $item['soluong'] ?> người</td>
                                <td><b><?php echo number_format($item['giathue'], 0, ',', '.') ?> đ</b></td>
                                <td><b><?php echo number_format($item['tiencoc'], 0, ',', '.') ?> đ</b></td>
                                <td><?php echo $item['tinhtrangcoc'] == 0 ? '<span class="btn-kyhopdong-err">Chưa thu tiền</span>' : '<span class="btn-kyhopdong-suc">Đã thu tiền</span>' ?></td>
                                <td><?php echo $item['chuky'] ?> tháng</td>
                                <td><?php echo $item['ngaylaphopdong'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['ngaylaphopdong'], 'd-m-Y'); ?></td>
                                <td><?php echo $item['ngayvaoo'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['ngayvaoo'], 'd-m-Y'); ?></td>
                                <td><?php echo $item['thoihanhopdong'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['thoihanhopdong'], 'd-m-Y'); ?></td>
                                <td><b><?php echo $item['tendichvu']; ?></b></td>
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
                                    <div class="action">
                                        <button type="button" class="btn btn-secondary btn-sm"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="box-action">
                                            <!-- Add your actions here -->
                                            <a title="Xem hợp đồng" href="<?php echo getLinkAdmin('contract', 'view', ['id' => $item['id']]); ?>" class="btn btn-success btn-sm"><i class="nav-icon fas fa-solid fa-eye"></i></a>
                                            <a title="In hợp đồng" target="_blank" href="<?php echo getLinkAdmin('contract', 'print', ['id' => $item['id']]) ?>" class="btn btn-dark btn-sm"><i class="fa fa-print"></i></a>
                                            <a href="<?php echo getLinkAdmin('contract', 'edit', ['id' => $item['id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                            <a href="<?php echo getLinkAdmin('contract', 'delete', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i></a>
                                            <form method="POST" action="">
                                                <button type="submit" name="terminate" class="btn btn-warning btn-sm" onclick="return confirm('Bạn có chắc chắn muốn thanh lý hợp đồng này không?')" title="Thanh lý hợp đồng"><i class="fa fa-times"></i></button>
                                                <input type="hidden" name="contract_id" value="<?php echo $item['id']; ?>">
                                            </form>
                                        </div>
                                    </div>
                                </td>

                            <?php endforeach;
                    else: ?>
                            <tr>
                                <td colspan="16">
                                    <div class="alert alert-danger text-center">Không có dữ liệu hợp đồng</div>
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
                        echo '<li class="page-item"><a class="page-link" href="' . _WEB_HOST_ROOT . '/?module=contract' . $queryString . '&page=' . $prePage . '">Pre</a></li>';
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
                            <a class="page-link" href="<?php echo _WEB_HOST_ROOT . '?module=contract' . $queryString . '&page=' . $index;  ?>"> <?php echo $index; ?> </a>
                        </li>
                    <?php  } ?>

                    <?php
                    if ($page < $maxPage) {
                        $nextPage = $page + 1;
                        echo '<li class="page-item"><a class="page-link" href="' . _WEB_HOST_ROOT . '?module=contract' . $queryString . '&page=' . $nextPage . '">Next</a></li>';
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

    function toggle(__this) {
        let isChecked = __this.checked;
        let checkbox = document.querySelectorAll('input[name="records[]"]');
        for (let index = 0; index < checkbox.length; index++) {
            checkbox[index].checked = isChecked
        }
    }
</script>