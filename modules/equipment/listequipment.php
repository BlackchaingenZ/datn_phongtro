<?php

if (!defined('_INCODE')) die('Access denied...');


// Ngăn chặn quyền truy cập nếu người dùng không thuộc nhóm có quyền
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);

$groupId = $userDetail['group_id'];

// Kiểm tra nếu người dùng không thuộc nhóm 7 thì chặn truy cập
if ($groupId != 7) {
    setFlashData('msg', 'Bạn không được truy cập vào trang này');
    setFlashData('msg_type', 'err');
    redirect('admin/?module=dashboard');
}

$data = [
    'pageTitle' => 'Danh sách cơ sở vật chất'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

function getRoomAndEquipmentList()
{
    $sql = "
        SELECT room.id AS room_id, room.tenphong, 
               GROUP_CONCAT(equipment.tenthietbi SEPARATOR ', ') AS tenthietbi, 
               GROUP_CONCAT(equipment_room.thoigiancap SEPARATOR ', ') AS thoigiancap
        FROM room
        LEFT JOIN equipment_room ON room.id = equipment_room.room_id
        LEFT JOIN equipment ON equipment_room.equipment_id = equipment.id
        GROUP BY room.id
        ORDER BY room.id ASC
    ";

    return getRaw($sql); 
}


$searchTerm = ''; // Từ khóa tìm kiếm
if (!empty($_POST['search_term'])) {
    $searchTerm = $_POST['search_term'];
}

// Truy vấn để tìm tên phòng và thiết bị
$sqlSearchRooms = "
    SELECT room.id AS room_id, room.tenphong, 
           equipment.id AS equipment_id,
           equipment.tenthietbi, 
           equipment.giathietbi,
           equipment.mathietbi,
           equipment.soluongnhap,
           equipment.soluongtonkho,
           equipment.thoihanbaohanh,
           equipment.ngaybaotri,
           equipment.ngaynhap
    FROM room
    JOIN equipment_room ON room.id = equipment_room.room_id
    JOIN equipment ON equipment_room.equipment_id = equipment.id
    WHERE equipment.tenthietbi LIKE '%$searchTerm%' OR equipment.mathietbi LIKE '%$searchTerm%'
    GROUP BY equipment.id 
    ORDER BY equipment.tenthietbi ASC
";


$searchResults = getRaw($sqlSearchRooms);
$listRoomAndEquipment = getRoomAndEquipmentList();

// Lấy danh sách thiết bị từ cơ sở dữ liệu
$listAllEquipment = getRaw("SELECT id AS equipment_id, tenthietbi, mathietbi, giathietbi, soluongnhap, soluongtonkho,thoihanbaohanh,ngaybaotri, ngaynhap FROM equipment ORDER BY tenthietbi ASC");

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
?>

<?php layout('navbar', 'admin', $data); ?>

<div class="container-fluid">

    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <!-- Hiển thị danh sách thiết bị -->
    <div class="box-content">
        <form method="POST" action="">
            <table class="table table-bordered mt-3">
                <div class="row">
                    <div class="col-4"></div>
                    <div class="col-4">
                        <input style="height: 50px" type="search" name="search_term" class="form-control" placeholder="Nhập tên thiết bị cần tìm" value="<?php echo (!empty($searchTerm)) ? htmlspecialchars($searchTerm) : ''; ?>">
                    </div>
                    <div class="col">
                        <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="module" value="equipment">
                <p></p>
                <a style="margin-right: 5px" href="<?php echo getLinkAdmin('equipment', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <a href="<?php echo getLinkAdmin('equipment', 'add') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm mới </a>
                <a href="<?php echo getLinkAdmin('equipment', 'listequipment'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã thiết bị</th>
                        <th>Tên thiết bị</th>
                        <th>Giá thiết bị</th>
                        <th>Số lượng nhập </th>
                        <th>Số lượng tồn kho</th>
                        <!-- <th>Bảo hành</th> -->
                        <th>Ngày nhập</th>
                        <!-- <th>Thời hạn bảo hành</th>
                        <th>Tình trạng bảo hành</th>
                        <th>Ngày bảo trì</th>
                        <th>Tình trạng bảo trì</th> -->
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="equipmentData">
                    <?php
                    // Lấy danh sách thiết bị dựa trên từ khóa tìm kiếm
                    $resultsToDisplay = !empty($searchTerm) ? $searchResults : $listAllEquipment;

                    // Hiển thị danh sách thiết bị
                    if (!empty($resultsToDisplay)):
                        $count = 0;
                        foreach ($resultsToDisplay as $item):
                            $count++;
                    ?>
                            <tr>
                                <!-- <tr style="background-color:<?php echo (in_array($count, [1, 2, 3])) ? 'red' : (in_array($count, [4, 6]) ? 'green' : 'transparent'); ?>;"> -->
                                <td><?php echo $count; ?></td>
                                <td><?php echo $item['mathietbi']; ?></td>
                                <td><b><?php echo $item['tenthietbi']; ?></b></td>
                                <td><?php echo number_format($item['giathietbi'], 0, ',', '.'); ?> VND</td>
                                <td><?php echo $item['soluongnhap']; ?></td>
                                <td><?php echo $item['soluongtonkho']; ?></td>
                                <!-- <td><?php
                                            if (empty($item['thoihanbaohanh'])) {
                                                echo "Trống";
                                            } else {
                                                // Tính số tháng giữa ngaynhap và thoihanbaohanh
                                                $ngayNhap = strtotime($item['ngaynhap']);
                                                $thoiHanBaoHanh = strtotime($item['thoihanbaohanh']);

                                                $thang = (date('Y', $thoiHanBaoHanh) - date('Y', $ngayNhap)) * 12 + (date('m', $thoiHanBaoHanh) - date('m', $ngayNhap));

                                                // Hiển thị số tháng
                                                echo "" . $thang . " tháng";
                                            }
                                            ?>
                                </td> -->
                                <td><?php echo getDateFormat($item['ngaynhap'], 'd-m-Y'); ?></td>
                                <!-- <td><?php
                                            if (empty($item['thoihanbaohanh'])) {
                                                echo "Trống";
                                            } else {
                                                echo getDateFormat($item['thoihanbaohanh'], 'd-m-Y');
                                            }
                                            ?>
                                </td> -->
                                <!-- <td>
                                    <?php
                                    $getEquipmentStatus = getThoihanbaohanhStatus($item['thoihanbaohanh']);
                                    if ($getEquipmentStatus == "Đã hết hạn bảo hành") {
                                        echo '<span class = "btn-dahethan-err">' . $getEquipmentStatus . '</span>';
                                    } elseif ($getEquipmentStatus == "Sắp hết hạn bảo hành") {
                                        echo '<span class = "btn-saphethan-err">' . $getEquipmentStatus . '</span>';
                                    } elseif ($getEquipmentStatus == "Trong thời hạn bảo hành") {
                                        echo '<span class = "btn-trongthoihan-err">' . $getEquipmentStatus . '</span>';
                                    }

                                    ?>
                                </td> -->
                                <!-- <td>
                                    <?php
                                    $compareResult = sosanh($item['soluongtonkho'], $item['soluongnhap']);

                                    if ($compareResult == "Bằng nhau") {
                                        echo '<span class="btn-bangnhau-err">' . $compareResult . '</span>';
                                    } elseif ($compareResult == "Nhỏ hơn") {
                                        echo '<span class="btn-nhohon-err">' . $compareResult . '</span>';
                                    } elseif ($compareResult == "Lớn hơn") {
                                        echo '<span class="btn-lonhon-err">' . $compareResult . '</span>';
                                    }
                                    ?>
                                </td> -->
                                <!-- <td>
                                    <?php
                                    $compareDatesResult = compareDates($item['ngaynhap'], $item['thoihanbaohanh']);

                                    if ($compareDatesResult == "Bằng nhau") {
                                        echo '<span class="btn-bangnhau-err">' . $compareDatesResult . '</span>';
                                    } elseif ($compareDatesResult == "Ngày nhập nhỏ hơn ngày bảo hành") {
                                        echo '<span class="btn-nhohon-err">' . $compareDatesResult . '</span>';
                                    } elseif ($compareDatesResult == "Ngày nhập lớn hơn ngày bảo hành") {
                                        echo '<span class="btn-lonhon-err">' . $compareDatesResult . '</span>';
                                    }
                                    ?>
                                </td> -->

                                <!-- <td><?php
                                            if (empty($item['ngaybaotri'])) {
                                                echo "Trống";
                                            } else {
                                                echo "" . getDateFormat($item['ngaybaotri'], 'd-m-Y' . ' ');
                                            }
                                            ?>
                                </td> -->
                                <!-- <td><?php
                                            if (empty($item['ngaybaotri'])) {
                                                echo "Trống";
                                            } else {
                                                $getNgaybaotriStatus = getNgaybaotriStatus($item['ngaybaotri']);
                                                if ($getNgaybaotriStatus == "Đã đến ngày") {
                                                    echo '<span class = "btn-dadenngay-err">' . $getNgaybaotriStatus . '</span>';
                                                } elseif ($getNgaybaotriStatus == "Sắp đến ngày") {
                                                    echo '<span class = "btn-saphetngay-err">' . $getNgaybaotriStatus . '</span>';
                                                } elseif ($getNgaybaotriStatus == "Chưa đến ngày") {
                                                    echo '<span class = "btn-chuadenngay-err">' . $getNgaybaotriStatus . '</span>';
                                                }
                                            }
                                            ?>
                                </td> -->
                                <td class="" style="width: 100px; height: 50px; text-align:center">
                                    <a href="<?php echo getLinkAdmin('equipment', 'editequipment', ['id' => $item['equipment_id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> </a>
                                    <a href="<?php echo getLinkAdmin('equipment', 'deleteequipment', ['id' => $item['equipment_id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="13">
                                <div class="alert alert-danger text-center">Không tìm thấy kết quả nào</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </form>
    </div>


</div>

<?php layout('footer', 'admin'); ?>