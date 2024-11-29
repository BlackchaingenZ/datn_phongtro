<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Gỡ bảng giá khỏi phòng'
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
    if (empty(trim($body['cost_id']))) {
        $errors['cost_id']['required'] = '** Bạn chưa chọn tên giá';
    }

    if (empty($errors)) {
        $roomId = $body['room_id'];

        // Kiểm tra xem phòng có bảng giá hay không
        $CostCount = checkCostInRoom($pdo, $roomId);
        if ($CostCount == 0) {
            setFlashData('msg', 'Phòng này chưa có loại giá');
            setFlashData('msg_type', 'err');
            redirect('?module=cost&action=applyroom');
        }

        if (empty(trim($body['cost_id']))) {
            $errors['cost_id']['required'] = '** Bạn chưa chọn giá để gỡ!';
        }

        if (empty($errors)) {
            $costId = $body['cost_id'];

            // Kiểm tra xem giá có trong phòng hay không
            $isCostInRoom = checkCostInRoomById($pdo, $roomId, $costId);
            if (!$isCostInRoom) {
                setFlashData('msg', 'Loại giá này chưa có trong phòng');
                setFlashData('msg_type', 'err');
                redirect('?module=cost&action=applyroom');
            }

            // Tiến hành gỡ loại giá nếu tồn tại
            $deleteStatus = deleteCostFromRoom($pdo, $roomId, $costId);
            if ($deleteStatus) {
                setFlashData('msg', 'Gỡ loại giá thành công');
                setFlashData('msg_type', 'suc');
            } else {
                setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
            redirect('?module=cost&action=applyroom');
        } else {
            setFlashData('errors', $errors);
            setFlashData('old', $body);
            redirect('?module=cost&action=applyroom');
        }
    } else {
        // Nếu có lỗi, xử lý thông báo lỗi
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=cost&action=removecost');
    }
}

// Lấy danh sách phòng và cost
$listAllRoom = getRaw("SELECT * FROM room ORDER BY tenphong ASC"); // Lấy tất cả phòng
$listAllCost = getRaw("SELECT * FROM cost ORDER BY tengia ASC"); // Lấy tất cả giá
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

$allRoom = getRaw("
    SELECT room.id, room.tenphong
    FROM room 
    WHERE room.id IN (SELECT room_id FROM cost_room)  -- Kiểm tra phòng có cost
    ORDER BY room.tenphong
");

$linkreturndistribite = getLinkAdmin('cost', 'applyroom');
$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
$roomsByArea = [];

foreach ($allRoom as $room) {
    // Lấy tên các loại giá (khuyến mại) liên kết với phòng từ bảng cost_room và cost
    $tengia = getRaw("SELECT GROUP_CONCAT(c.tengia SEPARATOR ', ') AS tengia 
                      FROM cost_room cr
                      JOIN cost c ON cr.cost_id = c.id
                      WHERE cr.room_id = " . $room['id'])[0]['tengia'];

    // Lấy các khu vực của phòng từ bảng area_room
    $areaIds = getRaw("SELECT area_id FROM area_room WHERE room_id = " . $room['id']);

    foreach ($areaIds as $area) {
        // Thêm thông tin vào mảng theo khu vực
        $roomsByArea[$area['area_id']][] = [
            'id' => $room['id'],
            'tenphong' => $room['tenphong'],
            'tengia' => $tengia
        ];
    }
}



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
                    <label for="">Chọn loại giá <span style="color: red">*</span></label>
                    <select name="cost_id" class="form-control">
                        <option value="">Chọn loại giá</option>
                        <?php foreach ($listAllCost as $cost): ?>
                            <option value="<?php echo $cost['id']; ?>" <?php echo old('cost_id', $old) == $cost['id'] ? 'selected' : ''; ?>>
                                <?php echo $cost['tengia']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php echo form_error('cost_id', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>
            <div class="form-group">
                <a style="margin-right: 20px " href="<?php echo $linkreturndistribite ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-trash"></i> Gỡ loại giá</button>
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
                option.textContent =
                    `${room.tenphong} - ${room.tengia}`; // Hiển thị tên phòng và khuyến mại
                roomSelect.appendChild(option);
            });
        }
    });
</script>

