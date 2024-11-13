<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Chỉnh sửa phân bổ thiết bị'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// Lấy danh sách thiết bị
$listAllEquipment = getRaw("SELECT id AS equipment_id, tenthietbi, soluongnhap, soluongtonkho FROM equipment ORDER BY tenthietbi ASC");

// Lấy danh sách phòng
$roomId = $_GET['id'];
$roomData = firstRaw("SELECT * FROM room WHERE id = $roomId");
$equipmentData = getRaw("SELECT * FROM equipment_room WHERE room_id = $roomId");

// Xử lý form khi người dùng gửi yêu cầu
if (isPost()) {
    $body = getBody(); // Lấy dữ liệu từ form
    $errors = [];  // Mảng lưu trữ các lỗi

    // Kiểm tra mảng error
    if (empty($errors)) {
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
        redirect('?module=equipment&action=editdistribute');
    }
}


layout('navbar', 'admin', $data);
?>

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content">
        <label for="">Thông tin phòng:</label>
        <p><?php echo $roomData['tenphong']; ?></p>
        <form method="POST" action="">
            <div class="form-group">
                <label for="equipment">Danh sách thiết bị:</label>
                <div class="equipment-list">
                    <?php foreach ($listAllEquipment as $equipment): ?>
                        <?php
                        // Kiểm tra xem thiết bị này đã được phân bổ cho phòng chưa
                        $equipmentAssigned = null;
                        foreach ($equipmentData as $assigned) {
                            if ($assigned['equipment_id'] == $equipment['equipment_id']) {
                                $equipmentAssigned = $assigned;
                                break;
                            }
                        }
                        ?>
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

            <div class="form-group">
                <label for="thoigiancap">Chọn thời gian cấp:</label>
                <input type="date" name="thoigiancap" class="form-control" style="width: 40%; height: auto;" required
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
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>