<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Chỉnh sửa phân bổ thiết bị'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Lấy danh sách thiết bị và phòng trọ
$listAllEquipment = getRaw("SELECT * FROM equipment ORDER BY tenthietbi ASC");
$listAllRoom = getRaw("SELECT * FROM room ORDER BY tenphong ASC");

function getGet($key = null) {
    if ($key === null) {
        return $_GET; // Trả về toàn bộ mảng $_GET nếu không truyền key
    }
    return isset($_GET[$key]) ? $_GET[$key] : null; // Trả về giá trị nếu tồn tại, ngược lại trả về null
}

// Lấy thông tin phân bổ dựa trên ID thiết bị và ID phòng
$room_id = getGet('room_id');
$equipment_id = getGet('equipment_id');

$allocation = getRows("SELECT * FROM equipment_room WHERE room_id = :room_id AND equipment_id = :equipment_id", [
    'room_id' => $room_id,
    'equipment_id' => $equipment_id
]);

if (!$allocation) {
    setFlashData('msg', 'Phân bổ không tồn tại.');
    setFlashData('msg_type', 'err');
    redirect('?module=equipment&action=listdistribute');
}

// Lấy thông tin phòng và thiết bị hiện tại
$currentRoom = getRow("SELECT * FROM room WHERE id = :id", ['id' => $room_id]);
$currentEquipment = getRow("SELECT * FROM equipment WHERE id = :id", ['id' => $equipment_id]);

// Xử lý cập nhật phân bổ
if (isPost()) {
    $body = getBody();
    $errors = [];

    // Validate cơ sở vật chất
    if (empty(trim($body['equipment_id']))) {
        $errors['equipment_id']['required'] = '** Bạn chưa chọn cơ sở vật chất!';
    }

    // Validate phòng trọ
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng trọ!';
    }

    // Validate thời gian cấp
    if (empty(trim($body['thoigiancap']))) {
        $errors['thoigiancap']['required'] = '** Bạn chưa nhập thời gian cấp!';
    }

    // Kiểm tra lỗi và cập nhật dữ liệu
    if (empty($errors)) {
        // Cập nhật thông tin phân bổ
        $dataUpdate = [
            'equipment_id' => $body['equipment_id'],
            'room_id' => $body['room_id'],
            'thoigiancap' => $body['thoigiancap'],
        ];

        $updateStatus = update('equipment_room', $dataUpdate, "room_id = :room_id AND equipment_id = :equipment_id", [
            'room_id' => $room_id,
            'equipment_id' => $equipment_id
        ]);

        if ($updateStatus) {
            setFlashData('msg', 'Cập nhật phân bổ thành công.');
            setFlashData('msg_type', 'suc');
            redirect('?module=equipment&action=listdistribute');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau.');
            setFlashData('msg_type', 'err');
        }
    } else {
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào.');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
    }
}

// Lấy thông báo lỗi và dữ liệu cũ (nếu có)
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>

<?php layout('navbar', 'admin', $data); ?>

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content">
        <h4>Thông tin phòng:</h4>
        <p>Tên phòng: <?php echo htmlspecialchars($currentRoom['tenphong'], ENT_QUOTES, 'UTF-8'); ?></p>
        
        <h4>Thông tin thiết bị:</h4>
        <p>Tên thiết bị: <?php echo htmlspecialchars($currentEquipment['tenthietbi'], ENT_QUOTES, 'UTF-8'); ?></p>

        <form action="" method="post" class="row">
            <div class="col-5">
                <div class="form-group">
                    <label for="">Chọn thiết bị <span style="color: red">*</span></label>
                    <select name="equipment_id" class="form-control">
                        <option value="">Chọn thiết bị</option>
                        <?php
                        if (!empty($listAllEquipment)) {
                            foreach ($listAllEquipment as $item) {
                        ?>
                                <option value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($item['id'] == $allocation['equipment_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($item['tenthietbi'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('equipment_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Chọn phòng trọ <span style="color: red">*</span></label>
                    <select name="room_id" class="form-control">
                        <option value="">Chọn phòng</option>
                        <?php
                        if (!empty($listAllRoom)) {
                            foreach ($listAllRoom as $item) {
                        ?>
                                <option value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($item['id'] == $allocation['room_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($item['tenphong'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Thời gian cấp <span style="color: red">*</span></label>
                    <input type="date" name="thoigiancap" class="form-control" value="<?php echo isset($old['thoigiancap']) ? htmlspecialchars($old['thoigiancap'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($allocation['thoigiancap'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo form_error('thoigiancap', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="form-group">
                <a style="margin-right: 20px" href="<?php echo getLinkAdmin('equipment', 'listdistribute'); ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>


<?php layout('footer', 'admin'); ?>
