<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Phân bổ thiết bị'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// Lấy danh sách thiết bị
$listAllEquipment = getRaw("SELECT id AS equipment_id, tenthietbi, soluongnhap, soluongtonkho FROM equipment ORDER BY tenthietbi ASC");

$allRoom = getRaw("
    SELECT room.id, room.tenphong
    FROM room 
    WHERE room.id NOT IN (SELECT room_id FROM equipment_room)  -- Kiểm tra phòng chưa có thiết bị
    ORDER BY room.tenphong
");

$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
$roomsByArea = [];

foreach ($allRoom as $room) {
    // Lấy các khu vực của phòng
    $areaIds = getRaw("SELECT area_id FROM area_room WHERE room_id = " . $room['id']);
    foreach ($areaIds as $area) {
        // Thêm thông tin vào mảng theo khu vực
        $roomsByArea[$area['area_id']][] = [
            'id' => $room['id'],
            'tenphong' => $room['tenphong']
        ];
    }
}

// Xử lý form khi người dùng gửi yêu cầu
if (isPost()) {
    $body = getBody(); // Lấy dữ liệu từ form
    $errors = [];  // Mảng lưu trữ các lỗi
    // Kiểm tra xem phòng và ngày có được chọn chưa
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa nhập phòng!';
    }


    // Kiểm tra mảng error
    if (empty($errors)) {
        // Lấy thông tin phòng đã chọn
        $roomId = $body['room_id'];  // ID phòng chọn từ form
        $roomData = firstRaw("SELECT * FROM room WHERE id = $roomId");

        // Cập nhật phân bổ thiết bị
        if (isset($body['equipment_ids']) && !empty($body['equipment_ids'])) {
            foreach ($body['equipment_ids'] as $equipmentId => $quantity) {
                // Kiểm tra số lượng thiết bị có hợp lệ
                if ($quantity >= 0) {
                    // Kiểm tra thiết bị đã được phân bổ cho phòng chưa
                    $existingAllocation = firstRaw("SELECT * FROM equipment_room WHERE room_id = $roomId AND equipment_id = $equipmentId");

                    if ($existingAllocation) {
                        // Nếu có phân bổ, chỉ cần cập nhật lại số lượng (không cộng dồn)
                        query("UPDATE equipment_room SET soluongcap = $quantity, thoigiancap = '{$body['thoigiancap']}' WHERE room_id = $roomId AND equipment_id = $equipmentId");
                    } else {
                        // Nếu chưa có phân bổ, thêm mới phân bổ thiết bị cho phòng
                        query("INSERT INTO equipment_room (room_id, equipment_id, soluongcap, thoigiancap) VALUES ($roomId, $equipmentId, $quantity, '{$body['thoigiancap']}')");
                    }

                    // Cập nhật lại số lượng tồn kho trong bảng equipment
                    $totalAllocated = firstRaw("SELECT SUM(soluongcap) AS total FROM equipment_room WHERE equipment_id = $equipmentId");
                    $totalAllocated = $totalAllocated['total'] ?: 0;

                    // Lấy số lượng nhập vào của thiết bị
                    $equipment = firstRaw("SELECT soluongnhap FROM equipment WHERE id = $equipmentId");
                    $newStock = $equipment['soluongnhap'] - $totalAllocated;

                    // Cập nhật số lượng tồn kho mới cho thiết bị
                    query("UPDATE equipment SET soluongtonkho = $newStock WHERE id = $equipmentId");
                }
            }
        }

        // Lưu thông báo thành công và chuyển hướng
        setFlashData('msg', 'Cập nhật phân bổ thiết bị thành công!');
        setFlashData('msg_type', 'suc');
        redirect('?module=equipment&action=listdistribute');
    } else {
        // Nếu có lỗi, lưu thông báo lỗi và dữ liệu cũ
        setFlashData('msg', 'Vui lòng kiểm tra thông tin nhập vào!');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body); // Giữ lại dữ liệu đã nhập
        redirect('?module=equipment&action=distribute');
    }
}

layout('navbar', 'admin', $data);
?>

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content">
        <form method="POST" action="" class="row">
            <div class="col-4">
                <div class="form-group">
                    <label for="">Chọn khu vực <span style="color: red">*</span></label>
                    <select name="area_id" id="area-select" class="form-select">
                        <option value="" disabled selected>Chọn khu vực</option>
                        <?php
                        if (!empty($allArea)) {
                            foreach ($allArea as $item) {
                        ?>
                                <option value="<?php echo $item['id'] ?>"
                                    <?php echo (!empty($areaId) && $areaId == $item['id']) ? 'selected' : '' ?>>
                                    <?php echo $item['tenkhuvuc'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('area_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Chọn phòng <span style="color: red">*</span></label>
                    <select name="room_id" id="room-select" class="form-select">
                        <option value="" disabled selected>Chọn phòng</option>
                        <!-- Danh sách phòng sẽ được cập nhật qua JavaScript -->
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for="thoigiancap">Chọn thời gian cấp:</label>
                    <input type="date" name="thoigiancap" class="form-control" required
                        value="<?php echo isset($body['thoigiancap']) ? htmlspecialchars($body['thoigiancap']) : date('Y-m-d'); ?>">
                    <?php echo form_error('thoigiancap', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="btn-row">
                    <a href="<?php echo getLinkAdmin('equipment', 'listdistribute'); ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-circle-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-edit"></i> Cập nhật
                    </button>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label for="equipment">Danh sách thiết bị:</label>
                    <div class="equipment-list">
                        <?php foreach ($listAllEquipment as $equipment): ?>
                            <div class="equipment-item">
                                <label for="equipment_<?php echo $equipment['equipment_id']; ?>"><?php echo $equipment['tenthietbi']; ?>:</label>
                                <input type="number" name="equipment_ids[<?php echo $equipment['equipment_id']; ?>]"
                                    class="form-control" style="width: 40%; height: auto;"
                                    value="<?php echo isset($equipmentAssigned) ? $equipmentAssigned['soluongcap'] : ''; ?>"
                                    placeholder="Số lượng cấp" min="0">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php echo form_error('equipment_ids', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>
<script>
    const roomsByArea = <?php echo json_encode($roomsByArea); ?>; // Chuyển đổi mảng PHP sang JS
    const areaSelect = document.getElementById('area-select');
    const roomSelect = document.getElementById('room-select');

    areaSelect.addEventListener('change', function() {
        const areaId = this.value;
        roomSelect.innerHTML = '<option value="" disabled selected>Chọn phòng</option>'; // Reset danh sách phòng

        if (areaId && roomsByArea[areaId]) {
            roomsByArea[areaId].forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = room.tenphong; // Hiển thị chỉ tên phòng
                roomSelect.appendChild(option);
            });
        }
    });
</script>