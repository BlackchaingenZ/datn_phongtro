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

$linkreturndistribite = getLinkAdmin('cost', 'applyroom');

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
