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
        SELECT r.id AS room_id, r.tenphong, e.id AS equipment_id, e.tenthietbi
        FROM room r
        LEFT JOIN equipment_room er ON r.id = er.room_id
        LEFT JOIN equipment e ON er.equipment_id = e.id
        ORDER BY r.id ASC, e.id ASC
    ";

    return getRaw($sql); // Hàm getRaw() sẽ thực hiện truy vấn và trả về kết quả
}

// Xử lý tìm kiếm
$searchKeyword = '';
$filter = '';

if (!empty($_POST['search_keyword'])) {
    $searchKeyword = $_POST['search_keyword'];

    // Kiểm tra xem có điều kiện WHERE chưa, nếu có thì dùng AND, nếu chưa thì dùng WHERE
    if (!empty($filter) && strpos($filter, 'WHERE') >= 0) {
        $operator = 'AND';
    } else {
        $operator = 'WHERE';
    }

    // Thêm điều kiện tìm kiếm vào câu truy vấn
    $filter .= " $operator tenthietbi LIKE '%$searchKeyword%'";
}

// Truy vấn thiết bị dựa trên điều kiện tìm kiếm
$sqlSearch = "SELECT * FROM equipment $filter ORDER BY tenthietbi ASC";
$searchResults = getRaw($sqlSearch);




// Lấy danh sách thiết bị từ cơ sở dữ liệu
$listAllEquipment = getRaw("SELECT * FROM equipment ORDER BY tenthietbi ASC");
if (isset($_POST['deleteMultip'])) {
    $numberCheckbox = $_POST['records']; // Lấy các ID thiết bị đã chọn
    if (empty($numberCheckbox)) {
        setFlashData('msg', 'Bạn chưa chọn mục nào để xóa!');
        setFlashData('msg_type', 'err');
    } else {
        // Chuyển mảng các ID thiết bị thành chuỗi để sử dụng trong câu truy vấn SQL
        $extract_id = implode(',', array_map('intval', $numberCheckbox));

        try {
            // Kiểm tra trước nếu có thiết bị nào đang được sử dụng trong bảng equipment_room
            $sqlCheck = "SELECT COUNT(*) AS count FROM equipment_room WHERE equipment_id IN ($extract_id)";
            $count = getRow($sqlCheck)['count'];

            if ($count > 0) {
                // Nếu thiết bị đang được sử dụng trong phòng, không thực hiện xóa
                setFlashData('msg', 'Không thể xóa thiết bị vì nó đang được sử dụng trong phòng.');
                setFlashData('msg_type', 'err');
                redirect('?module=equipment&action=listequipment');
                exit(); // Dừng việc thực hiện thêm
            } else {
                // Thực hiện xóa các thiết bị đã chọn từ cơ sở dữ liệu nếu không có thiết bị nào đang được sử dụng
                $checkDelete = delete('equipment', "id IN($extract_id)");

                if ($checkDelete) {
                    setFlashData('msg', 'Xóa thiết bị thành công');
                    setFlashData('msg_type', 'suc');
                } else {
                    setFlashData('msg', 'Có lỗi xảy ra khi xóa thiết bị');
                    setFlashData('msg_type', 'err');
                }
            }
        } catch (PDOException $e) {
            setFlashData('msg', 'Đã xảy ra lỗi: ' . $e->getMessage());
            setFlashData('msg_type', 'err');
        }
    }
    redirect('?module=equipment&action=listequipment'); // Chuyển hướng về trang danh sách
}



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
                    <div class="col-4">
                        <input style="height: 50px" type="search" name="search_keyword" class="form-control" placeholder="Nhập tên thiết bị cần tìm" value="<?php echo (!empty($searchKeyword)) ? htmlspecialchars($searchKeyword) : ''; ?>">
                    </div>

                    <div class="col">
                        <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="module" value="equipment">
                <p></p>
                <a href="<?php echo getLinkAdmin('equipment', 'add') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm mới </a>
                <a href="<?php echo getLinkAdmin('equipment', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
                <button type="submit" name="deleteMultip" value="Delete" onclick="return confirm('Bạn có chắn chắn muốn xóa không ?')" class="btn btn-secondary"><i class="fa fa-trash"></i> Xóa</button>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="check-all" onclick="toggle(this)"></th>
                        <th>STT</th>
                        <th>Tên thiết bị</th>
                        <th>Giá thiết bị</th>
                        <th>Ngày nhập</th>
                    </tr>
                </thead>
                <tbody id="equipmentData">
                    <?php
                    $equipmentList = !empty($searchKeyword) ? $searchResults : $listAllEquipment;
                    if (!empty($equipmentList)):
                        $count = 0; // Hiển thị số thứ tự
                        foreach ($equipmentList as $item):
                            $count++;
                    ?>
                            <tr>
                                <td><input type="checkbox" name="records[]" value="<?php echo $item['id']; ?>"></td>
                                <td><?php echo $count; ?></td>
                                <td><b><?php echo $item['tenthietbi']; ?></b></td>
                                <td><?php echo number_format($item['giathietbi'], 0, ',', '.'); ?> VND</td>
                                <td><?php echo getDateFormat($item['ngaynhap'], 'd-m-Y'); ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="6">
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

<script>
    function toggle(__this) {
        let isChecked = __this.checked;
        let checkbox = document.querySelectorAll('input[name="records[]"]');
        for (let index = 0; index < checkbox.length; index++) {
            checkbox[index].checked = isChecked
        }
    }
</script>