<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Thêm mới áp dụng khu vực'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý phân bổ cơ sở vật chất
if (isPost()) {
    $body = getBody();
    $errors = [];

    // Validate area
    if (empty(trim($body['area_id']))) {
        $errors['area_id']['required'] = '** Bạn chưa chọn tên khu vực';
    }

    // Validate phòng trọ
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng trọ!';
    }

    // Kiểm tra mảng error
    if (isPost()) {
        $body = getBody();
        $errors = [];

        // Validate giá
        if (empty(trim($body['area_id']))) {
            $errors['area_id']['required'] = '** Bạn chưa chọn tên khu vực!';
        }

        // Validate phòng trọ
        if (empty(trim($body['room_id']))) {
            $errors['room_id']['required'] = '** Bạn chưa chọn phòng trọ!';
        }

        // Kiểm tra mảng error
        if (empty($errors)) {
            // Kiểm tra xem phòng đã có khu vực chưa
            $checkExistsQuery = "
                SELECT COUNT(*) AS count
                FROM area_room
                WHERE room_id = :room_id 
            ";
            $stmt = $pdo->prepare($checkExistsQuery);
            $stmt->execute([
                ':room_id' => $body['room_id'],
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                // Nếu giá đã tồn tại trong phòng
                setFlashData('msg', 'Không thể thêm vì phòng này đã có khu vực rồi !');
                setFlashData('msg_type', 'err');
                redirect('?module=cost&action=applycost'); // Chuyển hướng về trang phân bổ
            } else {
                // Không có lỗi nào, tiến hành thêm giá
                $dataInsert = [
                    'area_id' => $body['area_id'],
                    'room_id' => $body['room_id'],
                ];

                $insertStatus = insert('area_room', $dataInsert); // Sử dụng bảng equipment_room để lưu thông tin phân bổ
                if ($insertStatus) {
                    setFlashData('msg', 'Thêm khu vực thành công');
                    setFlashData('msg_type', 'suc');
                    redirect('?module=area&action=applyarea');
                } else {
                    setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                    setFlashData('msg_type', 'err');
                    redirect('?module=area&action=applyarea');
                }
            }
        }

        // Nếu có lỗi, xử lý thông báo lỗi
        if (!empty($errors)) {
            setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
            setFlashData('msg_type', 'err');
            setFlashData('errors', $errors);
            setFlashData('old', $body);
            redirect('?module=area&action=addapply');
        }
    }


    // Nếu có lỗi, xử lý thông báo lỗi
    if (!empty($errors)) {
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=area&action=addapply');
    }
}



$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');



// Lấy danh sách phòng và area
$listAllArea = getRaw("SELECT * FROM area ORDER BY tenkhuvuc ASC");

// kiểm tra nếu phòng nào có khu vực rồi thì không hiện
$listAllRoom = getRaw("
    SELECT room.id, room.tenphong
    FROM room 
    LEFT JOIN area_room ON area_room.room_id = room.id
    WHERE area_room.area_id IS NULL
    ORDER BY room.tenphong
");



// Hàm lấy danh sách phòng và area
function getRoomAndAreaList()
{
    $sql = "
        SELECT r.id AS room_id, r.tenphong, 
        GROUP_CONCAT(e.tenkhuvuc SEPARATOR ', ') AS tenkhuvuc
        FROM room r
        LEFT JOIN area_room er ON r.id = er.room_id
        LEFT JOIN area e ON er.area_id = e.id
        GROUP BY r.id
        ORDER BY r.id ASC
    ";
    return getRaw($sql);
}


$listRoomAndArea = getRoomAndAreaList();

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
                    <label for="">Chọn tên khu vực <span style="color: red">*</span></label>
                    <select name="area_id" class="form-control">
                        <option value="">Chọn tên khu vực</option>
                        <?php
                        if (!empty($listAllArea)) {
                            foreach ($listAllArea as $item) {
                        ?>
                                <option value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($item['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('area_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Chọn phòng trọ <span style="color: red">*</span></label>
                    <select name="room_id" class="form-control">
                        <option value="">Chọn phòng</option>
                        <?php
                        if (!empty($listAllRoom)) {
                            foreach ($listAllRoom as $item) {
                        ?>
                                <option value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($item['tenphong'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

            </div>

            <div class="form-group">
                <a href="<?php echo getLinkAdmin('area', 'applyarea'); ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-plus"></i> Áp dụng
                </button>
            </div>
        </form>
    </div>

</div>

<?php layout('footer', 'admin'); ?>