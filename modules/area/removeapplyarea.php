<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Gỡ khu vực khỏi phòng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Kết nối PDO, đảm bảo kết nối được thực hiện
require_once 'includes/pdo_connection.php'; // Đường dẫn tới file kết nối PDO

// Xử lý gỡ khu vực
if (isPost()) {
    $body = getBody();
    $errors = [];

    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng!';
    }

    if (empty($errors)) {
        $roomId = $body['room_id'];

        // Kiểm tra xem phòng có thiết bị hay không
        $areaCount = checkAreaInRoom($pdo, $roomId);
        if ($areaCount == 0) {
            setFlashData('msg', 'Phòng này chưa có khu vực');
            setFlashData('msg_type', 'err');
            redirect('?module=area&action=removeapplyarea');
        }

        if (empty(trim($body['area_id']))) {
            $errors['area_id']['required'] = '** Bạn chưa chọn khu vực để gỡ!';
        }

        if (empty($errors)) {
            $areaId = $body['area_id'];

            // Kiểm tra xem thiết bị có trong phòng hay không
            $isAreaInRoom = checkAreaInRoomById($pdo, $roomId, $areaId);
            if (!$isAreaInRoom) {
                setFlashData('msg', 'Khu vực này chưa chứa phòng này');
                setFlashData('msg_type', 'err');
                redirect('?module=area&action=removeapplyarea');
            }

            // Tiến hành gỡ thiết bị nếu tồn tại
            $deleteStatus = deleteAreaFromRoom($pdo, $roomId, $areaId);
            if ($deleteStatus) {
                setFlashData('msg', 'Gỡ khu vực thành công');
                setFlashData('msg_type', 'suc');
            } else {
                setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
            redirect('?module=area&action=applyarea');
        } else {
            setFlashData('errors', $errors);
            setFlashData('old', $body);
            redirect('?module=area&action=applyarea');
        }
    } else {
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=area&action=applyarea');
    }
}

// Lấy danh sách phòng và thiết bị
$listAllRoom = getRaw("SELECT * FROM room ORDER BY tenphong ASC"); // Lấy tất cả phòng
$listAllArea = getRaw("SELECT * FROM area ORDER BY tenkhuvuc ASC"); // Lấy tất cả khuvuc

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

$linkreturnapplyarea = getLinkAdmin('area', 'applyarea');

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
                    <label for="">Chọn khu vực <span style="color: red">*</span></label>
                    <select name="area_id" class="form-control">
                        <option value="">Chọn khu vực</option>
                        <?php foreach ($listAllArea as $area): ?>
                            <option value="<?php echo $area['id']; ?>" <?php echo old('area_id', $old) == $area['id'] ? 'selected' : ''; ?>>
                                <?php echo $area['tenkhuvuc']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php echo form_error('area_id', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>
            <div class="form-group">
                <a style="margin-right: 20px " href="<?php echo $linkreturnapplyarea ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-trash"></i> Gỡ khu vực</button>
            </div>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>