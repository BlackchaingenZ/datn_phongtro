<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Gỡ thiết bị khỏi phòng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Kết nối PDO, đảm bảo kết nối được thực hiện
require_once 'includes/pdo_connection.php'; // Đường dẫn tới file kết nối PDO

// Xử lý gỡ thiết bị
if (isPost()) {
    $body = getBody();
    $errors = [];

    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng!';
    }

    if (empty($errors)) {
        $roomId = $body['room_id'];

        // Kiểm tra xem phòng có thiết bị hay không
        $equipmentCount = checkEquipmentInRoom($pdo, $roomId);
        if ($equipmentCount == 0) {
            setFlashData('msg', 'Phòng này chưa có thiết bị');
            setFlashData('msg_type', 'err');
            redirect('?module=equipment&action=removedistribute');
        }

        if (empty(trim($body['equipment_id']))) {
            $errors['equipment_id']['required'] = '** Bạn chưa chọn thiết bị để gỡ!';
        }

        if (empty($errors)) {
            $equipmentId = $body['equipment_id'];

            // Kiểm tra xem thiết bị có trong phòng hay không
            $isEquipmentInRoom = checkEquipmenntInRoomById($pdo, $roomId, $equipmentId);
            if (!$isEquipmentInRoom) {
                setFlashData('msg', 'Thiết bị này chưa có trong phòng');
                setFlashData('msg_type', 'err');
                redirect('?module=equipment&action=removedistribute');
            }

            // Tiến hành gỡ thiết bị nếu tồn tại
            $deleteStatus = deleteEquipmentFromRoom($pdo, $roomId, $equipmentId);
            if ($deleteStatus) {
                setFlashData('msg', 'Gỡ thiết bị thành công');
                setFlashData('msg_type', 'suc');
            } else {
                setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
            redirect('?module=equipment&action=listdistribute');
        } else {
            setFlashData('errors', $errors);
            setFlashData('old', $body);
            redirect('?module=equipment&action=removedistribute');
        }
    } else {
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=equipment&action=removedistribute');
    }
}

// Lấy danh sách phòng và thiết bị
$listAllRoom = getRaw("SELECT * FROM room ORDER BY tenphong ASC"); // Lấy tất cả phòng
$listAllEquipment = getRaw("SELECT * FROM equipment ORDER BY tenthietbi ASC"); // Lấy tất cả thiết bị

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

$linkreturndistribite = getLinkAdmin('equipment', 'listdistribute');

?>

<?php layout('navbar', 'admin', $data); ?>

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content">
        <form action="" method="post" class="row">
            <div class="col-5">
                <div class="form-group">
                    <label for="">Chọn phòng <span style="color: red">*</span></label>
                    <select name="room_id" class="form-control">
                        <option value="">Chọn phòng</option>
                        <?php foreach ($listAllRoom as $room): ?>
                            <option value="<?php echo $room['id']; ?>" <?php echo old('room_id', $old) == $room['id'] ? 'selected' : ''; ?>>
                                <?php echo $room['tenphong']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Chọn thiết bị <span style="color: red">*</span></label>
                    <select name="equipment_id" class="form-control">
                        <option value="">Chọn thiết bị</option>
                        <?php foreach ($listAllEquipment as $equipment): ?>
                            <option value="<?php echo $equipment['id']; ?>" <?php echo old('equipment_id', $old) == $equipment['id'] ? 'selected' : ''; ?>>
                                <?php echo $equipment['tenthietbi']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php echo form_error('equipment_id', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>
            <div class="form-group">
            <a style="margin-right: 20px " href="<?php echo $linkreturndistribite ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-trash"></i> Gỡ thiết bị</button>
            </div>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>