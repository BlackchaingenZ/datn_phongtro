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
$listAllEquipment = getRaw("SELECT id AS equipment_id, tenthietbi FROM equipment ORDER BY tenthietbi ASC");

// Lấy danh sách phòng
$roomId = $_GET['id'];
$roomData = firstRaw("SELECT * FROM room WHERE id = $roomId");
$equipmentData = getRaw("SELECT * FROM equipment_room WHERE room_id = $roomId");

if (empty($equipmentData)) {
    setFlashData('msg', 'Hãy phân bổ thiết bị cho phòng này trước!');
    setFlashData('msg_type', 'err');
    redirect('?module=equipment&action=listdistribute');
}

// Xử lý form khi người dùng gửi yêu cầu
if (isPost()) {
    $body = getBody(); // Lấy dữ liệu từ form
    $errors = [];  // Mảng lưu trữ các lỗi

    // Kiểm tra xem có thiết bị nào được chọn không
    if (empty($body['equipment_ids'])) {
        $errors['equipment_ids']['required'] = 'Bạn chưa chọn thiết bị nào!';
    }

    // Kiểm tra thời gian cấp
    if (empty($body['thoigiancap'])) {
        $errors['thoigiancap']['required'] = 'Bạn chưa chọn thời gian cấp!';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // Không có lỗi nào
        // Xóa tất cả các phân bổ cũ trước khi thêm mới
        delete('equipment_room', "room_id = $roomId");

        // Thêm giờ 00:00 vào thời gian cấp
        $thoigiancap = $body['thoigiancap'] . ' 00:00:00';

        // Chèn dữ liệu cho từng thiết bị được chọn
        foreach ($body['equipment_ids'] as $equipmentId) {
            $dataInsert = [
                'room_id' => $roomId,
                'equipment_id' => $equipmentId,
                'thoigiancap' => $thoigiancap // Lưu thời gian cấp từ form
            ];
            insert('equipment_room', $dataInsert);
        }


        setFlashData('msg', 'Cập nhật phân bổ thiết bị thành công!');
        setFlashData('msg_type', 'suc');
        redirect('?module=equipment&action=listdistribute');
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra thông tin nhập vào!');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body); // Giữ lại dữ liệu đã nhập
    }

    redirect('?module=equipment&action=editdistribute');
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
                
                <label for="equipment">Chọn thiết bị:</label>
                <select multiple name="equipment_ids[]" class="form-control" style="width: 40%; height: auto;" size="9" required>
                    <?php foreach ($listAllEquipment as $equipment): ?>
                        <option value="<?php echo $equipment['equipment_id']; ?>"
                            <?php if (in_array($equipment['equipment_id'], array_column($equipmentData, 'equipment_id'))) echo 'selected'; ?>>
                            <?php echo $equipment['tenthietbi']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php echo form_error('equipment_ids', $errors, '<span class="error">', '</span>'); ?>
            </div>

            <!-- Thêm trường nhập thời gian cấp -->
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