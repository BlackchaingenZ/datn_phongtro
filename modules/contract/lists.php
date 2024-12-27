<?php

if (!defined('_INCODE')) {
    die('Access denied...');
}

// Ngăn chặn quyền truy cập
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);

$groupId = $userDetail['group_id'];

if ($groupId != 7) {
    setFlashData('msg', 'Trang bạn muốn truy cập không tồn tại');
    setFlashData('msg_type', 'err');
    redirect('/?module=dashboard');
}

$pageTitle = 'Quản lý hợp đồng thuê trọ';
$data = [
    'pageTitle' => 'Quản lý hợp đồng thuê trọ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

function getContractById($id)
{
    // Lấy hợp đồng từ database
    return firstRaw("SELECT * FROM contract WHERE id = $id");
}

function deleteContract($id)
{
    // Xóa hợp đồng khỏi database
    delete('contract', "id = $id");
}
function getTenantInfoByContractId($contractId)
{
    // Truy vấn tất cả khách thuê liên kết với hợp đồng
    $query = "SELECT tenant.tenkhach FROM tenant
              JOIN contract_tenant ON contract_tenant.tenant_id_1 = tenant.id
              WHERE contract_tenant.contract_id_1 = $contractId";
    $tenants = getAll($query); // Giả sử `getAll` là hàm trả về một mảng các bản ghi

    // Nếu có khách thuê, trả về chuỗi tên khách thuê cách nhau bằng dấu phẩy
    if ($tenants) {
        $tenantNames = array_map(function ($tenant) {
            return $tenant['tenkhach'];
        }, $tenants);

        // return implode(',', $tenantNames); // Nối tên khách thuê với dấu phẩy
        return implode("\n", $tenantNames); // Nối tên khách thuê với ký tự xuống dòng
    }

    return 'Không có khách thuê'; // Trường hợp không có khách thuê
}



// Lấy thông tin khách của hợp đồng
function getTenantsByRoomId($roomId)
{
    return getRaw("SELECT * FROM tenant WHERE room_id = $roomId");
}

$searchContract = isset($_POST['search_contract']) ? $_POST['search_contract'] : '';
$tinhtrangcoc = isset($_POST['tinhtrangcoc']) ? $_POST['tinhtrangcoc'] : ''; // Lấy giá trị tìm kiếm theo tình trạng cọc
$trangthaihopdong = isset($_POST['trangthaihopdong']) ? $_POST['trangthaihopdong'] : null; // Đặt giá trị mặc định là null


// Xử lý truy vấn SQL với điều kiện tìm kiếm
if (!empty($searchContract) || !empty($tinhtrangcoc) || $trangthaihopdong != null) {
    $queryCondition = [];

    // Thêm điều kiện tìm kiếm theo hợp đồng
    if (!empty($searchContract)) {
        $queryCondition[] = "(room.tenphong LIKE '%$searchContract%' OR tenant.tenkhach LIKE '%$searchContract%' OR tenant.cmnd LIKE '%$searchContract%')";
    }

    // Thêm điều kiện trạng thái cọc
    if (!empty($tinhtrangcoc)) {
        if ($tinhtrangcoc == '1') {
            $queryCondition[] = "contract.tinhtrangcoc = '1'";
        } elseif ($tinhtrangcoc == '2') {
            $queryCondition[] = "contract.tinhtrangcoc = '2'";
        }
    }
    if ($trangthaihopdong != null) {
        if ($trangthaihopdong == '1') {
            $queryCondition[] = "contract.trangthaihopdong = '1'";
        } elseif ($trangthaihopdong == '0') {
            $queryCondition[] = "contract.trangthaihopdong = '0'";
        }
    }

    // Chỉ thêm WHERE nếu có điều kiện
    $whereClause = !empty($queryCondition) ? "WHERE " . implode(' AND ', $queryCondition) : "";

    // Câu truy vấn SQL với các điều kiện
    $listAllcontract = getRaw("
        SELECT *, 
            contract.id, 
            tenphong, 
            cost.giathue,
            sotiencoc, 
            contract.ngayvao AS ngayvaoo, 
            contract.ngayra AS thoihanhopdong, 
            contract.ghichu,
            contract.trangthaihopdong,
            tinhtrangcoc, 
            GROUP_CONCAT(DISTINCT CONCAT(tenant.tenkhach, ' (ID: ', tenant.id, ')') ORDER BY tenant.tenkhach DESC SEPARATOR '.') AS tenant_id_1,  
            GROUP_CONCAT(DISTINCT services.tendichvu ORDER BY services.tendichvu ASC SEPARATOR ', ') AS tendichvu 
        FROM contract 
        INNER JOIN room ON contract.room_id = room.id
        INNER JOIN contract_tenant ON contract.id = contract_tenant.contract_id_1
        INNER JOIN tenant ON contract_tenant.tenant_id_1 = tenant.id
        INNER JOIN cost_room ON room.id = cost_room.room_id 
        INNER JOIN cost ON cost_room.cost_id = cost.id
        LEFT JOIN contract_services ON contract.id = contract_services.contract_id 
        LEFT JOIN services ON contract_services.services_id = services.id
        $whereClause
        GROUP BY contract.id
        ORDER BY contract.id DESC
    ");
} else {
    // Nếu không có tìm kiếm, lấy tất cả hợp đồng
    $listAllcontract = getRaw("
        SELECT *, 
            contract.id, 
            tenphong, 
            cost.giathue,
            sotiencoc, 
            contract.ngayvao AS ngayvaoo, 
            contract.ngayra AS thoihanhopdong, 
            contract.ghichu,
            contract.trangthaihopdong,
            tinhtrangcoc, 
GROUP_CONCAT(DISTINCT CONCAT(tenant.tenkhach, ' (ID: ', tenant.id, ')') ORDER BY tenant.tenkhach DESC SEPARATOR '\n') AS tenant_id_1, 
            GROUP_CONCAT(DISTINCT services.tendichvu ORDER BY services.tendichvu ASC SEPARATOR ', ') AS tendichvu 
        FROM contract 
        INNER JOIN room ON contract.room_id = room.id
        INNER JOIN contract_tenant ON contract.id = contract_tenant.contract_id_1
        INNER JOIN tenant ON contract_tenant.tenant_id_1 = tenant.id
        INNER JOIN cost_room ON room.id = cost_room.room_id 
        INNER JOIN cost ON cost_room.cost_id = cost.id
        LEFT JOIN contract_services ON contract.id = contract_services.contract_id 
        LEFT JOIN services ON contract_services.services_id = services.id 
        GROUP BY contract.id
         ORDER BY contract.id DESC -- Sắp xếp theo id để hợp đồng mới nhất lên đầu
    ");
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

        <form action="" method="POST" class="mt-3">
            <div class="row">
                <div class = "col-2">

                </div>
                <div class="col-2">
                    <div class="dropdown">
                        <select name="trangthaihopdong" class="form-control" style="height: 50px; padding-right: 30px;">
                            <option value="">Chọn tình trạng thanh lý</option>
                            <option value="1" <?php echo (isset($_POST['trangthaihopdong']) && $_POST['trangthaihopdong'] == '1') ? 'selected' : ''; ?>>Chưa thanh lý</option>
                            <option value="0" <?php echo (isset($_POST['trangthaihopdong']) && $_POST['trangthaihopdong'] == '0') ? 'selected' : ''; ?>>Đã thanh lý</option>
                        </select>
                        <span class="fa fa-chevron-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"></span>
                    </div>
                </div>
                
                <div class="col-4"> <!-- Cột chứa ô tìm kiếm tên phòng, tên khách hoặc cmnd -->
                    <input style="height: 50px" type="search" name="search_contract" class="form-control" placeholder="Nhập tên phòng, tên khách hoặc cmnd để tìm hợp đồng" value="<?php echo isset($_POST['search_contract']) ? $_POST['search_contract'] : ''; ?>">
                </div>

                <div class="col"> <!-- Cột chứa nút tìm kiếm -->
                    <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                        <i class="fa fa-search"></i> <!-- Icon tìm kiếm -->
                    </button>
                </div>
            </div>
            <p></p>

            <a href="<?php echo getLinkAdmin('contract', 'add') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm mới</a>
            <a href="<?php echo getLinkAdmin('contract'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <a href="<?php echo getLinkAdmin('contract','export'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <!-- <th></th> -->
                        <th style="text-align: center;" width="1%">STT</th>
                        <th style="width: 3%; text-align: center;">Tên phòng</th>
                        <th style="width: 7%; text-align: center;">Người làm hợp đồng</th>
                        <!-- <th style="width: 8%;text-align: center;">Người ở</th> -->
                        <!-- <th style="width: 2%; text-align: center;">Tổng người</th> -->
                        <th style="text-align: center;">Giá thuê</th>
                        <th style="width: 5%; text-align: center;">Giá tiền cọc</th>
                        <th style="width: 6%; text-align: center;">Trạng thái cọc</th>
                        <th style="text-align: center;">Ngày lập</th>
                        <th style="text-align: center;">Ngày vào ở</th>
                        <th style="width: 6%; text-align: center;">Thời hạn hợp đồng</th>
                        <!-- <th style="width: 7%;text-align: center;">Tình trạng</th> -->
                        <th style="text-align: center;">Dịch vụ</th>
                        <th style="text-align: center;">Ghi chú</th>
                        <th style="text-align: center;">Thanh lý</th>
                        <!-- <th style="text-align: center;">Điều khoản 1</th>
                        <th style="text-align: center;">Điều khoản 2</th>
                        <th style="text-align: center;">Điều khoản 3</th> -->
                        <th style="width: 3%; text-align: center;">Thao tác</th>
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
                                <!-- 
                            <td>
                                <div class="image__contract">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/contracts.png" class="image__room-img" alt="">
                                </div>
                            </td> -->
                                <td style="text-align: center;"><?php echo $count; ?></td>
                                <td style="text-align: center;"><b><?php echo $item['tenphong']; ?></b></td>
                                <td style="text-align: center;">
                                    <!--hiển thị nhưng không lấy ID -->
                                    <?php
                                    $tenkhachArray = explode("\n", $item['tenant_id_1']);  // Tách từng khách hàng ra
                                    foreach ($tenkhachArray as $tenkhach) {
                                        // Chỉ hiển thị tên khách hàng, ẩn ID
                                        $name = explode(" (ID:", $tenkhach)[0];  // Tách tên khách hàng từ phần ID
                                        echo "<b>{$name}</b><br>";  // Hiển thị tên khách hàng
                                    }
                                    ?>

                                </td>
                                <!-- <td style="text-align: center;">
                                    <?php if (!empty($tenants)) {
                                        foreach ($tenants as $tenant) {
                                    ?>
                                            <span><?php echo $tenant['tenkhach'] ?></span> <br />
                                    <?php
                                        }
                                    } else {
                                        echo '<i>Trống</i>';
                                    } ?>
                                </td> -->
                                <!-- <td><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.svg" alt=""> <?php echo $item['soluongthanhvien'] ?> người</td> -->
                                <td style="text-align: center;"><b><?php echo number_format($item['giathue'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><b><?php echo number_format($item['sotiencoc'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center;"><?php echo $item['tinhtrangcoc'] == 2 ? '<span class="btn-kyhopdong-err">Chưa thu</span>' : '<span class="btn-kyhopdong-suc">Đã thu</span>' ?></td>
                                <td style="text-align: center;"><?php echo $item['ngaylaphopdong'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['ngaylaphopdong'], 'd-m-Y'); ?></td>
                                <td style="text-align: center;"><?php echo $item['ngayvaoo'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['ngayvaoo'], 'd-m-Y'); ?></td>
                                <td style="text-align: center;"><?php echo $item['thoihanhopdong'] == '0000-00-00' ? 'Không xác định' : getDateFormat($item['thoihanhopdong'], 'd-m-Y'); ?></td>
                                <!-- <td style="text-align: center;">
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
                                </td> -->
                                <td style="text-align: center;">
                                    <?php
                                    if (empty($item['tendichvu'])) {
                                        echo "Trống";
                                    } else {
                                        echo  "" . $item['tendichvu'];
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <!-- Thông tin -->
                                    <span class="tooltip-icon">
                                        <i class="nav-icon fas fa-solid fa-eye"></i>
                                        <span class="tooltiptext"><?php echo $item['ghichu']; ?></span>
                                    </span>
                                </td>
                                <td style="text-align: center;"><?php echo $item['trangthaihopdong'] == 0 ? '<span class="btn-trangthaihopdong-war">Đã thanh lý</span>' : '<span class="btn-trangthaihopdong-second">Chưa thanh lý</span>' ?></td>
                                <!-- <td style=" text-align: center;">
                                  
                                    <span class="tooltip-icon">
                                        <i class="fa-solid fa-eye"></i>
                                        <span class="tooltiptext"><?php echo $item['dieukhoan1']; ?></span>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    
                                    <span class="tooltip-icon">
                                        <i class="fa-solid fa-eye"></i>
                                        <span class="tooltiptext"><?php echo $item['dieukhoan2']; ?></span>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    
                                    <span class="tooltip-icon">
                                        <i class="fa-solid fa-eye"></i>
                                        <span class="tooltiptext"><?php echo $item['dieukhoan3']; ?></span>
                                    </span>
                                </td> -->
                                <td class="" style="text-align: center;">
                                    <div class="action">
                                        <button type="button" class="btn btn-secondary btn-sm"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="box-action">
                                            <!-- Add your actions here -->
                                            <a title="Xem hợp đồng" href="<?php echo getLinkAdmin('contract', 'view', ['id' => $item['id']]); ?>" class="btn btn-success btn-sm"><i class="nav-icon fas fa-solid fa-eye"></i></a>
                                            <a title="In hợp đồng" target="_blank" href="<?php echo getLinkAdmin('contract', 'print', ['id' => $item['id']]) ?>" class="btn btn-dark btn-sm"><i class="fa fa-print"></i></a>
                                            <a title="Chỉnh sửa" href="<?php echo getLinkAdmin('contract', 'edit', ['id' => $item['id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                            <a title="Xoá hợp đồng" href="<?php echo getLinkAdmin('contract', 'delete', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i></a>
                                            <a title="Thanh lý" href="<?php echo getLinkAdmin('contract', 'liquidation', ['id' => $item['id']]); ?>" class="btn btn-warning btn-sm"><i class="fa fa-times"></i></a>
                                        </div>
                                </td>

                            <?php endforeach;
                    else: ?>
                            <tr>
                                <td colspan="19">
                                    <div class="alert alert-danger text-center">Không có dữ liệu hợp đồng</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                </tbody>
            </table>
        </form>
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
</script>