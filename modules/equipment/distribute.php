<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Phân bổ thiết bị'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý phân bổ cơ sở vật chất
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

    // Kiểm tra mảng error
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

        // Kiểm tra mảng error
        if (empty($errors)) {
            // Kiểm tra xem phòng đã có thiết bị này chưa
            $checkExistsQuery = "
                SELECT COUNT(*) AS count
                FROM equipment_room
                WHERE room_id = :room_id AND equipment_id = :equipment_id
            ";
            $stmt = $pdo->prepare($checkExistsQuery);
            $stmt->execute([
                ':room_id' => $body['room_id'],
                ':equipment_id' => $body['equipment_id']
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                // Nếu thiết bị đã tồn tại trong phòng
                setFlashData('msg', 'Không thể thêm vì phòng này đã có thiết bị này trước đó!');
                setFlashData('msg_type', 'err');
                redirect('?module=equipment&action=listdistribute'); // Chuyển hướng về trang phân bổ
            } else {
                // Không có lỗi nào, tiến hành thêm thiết bị
                $dataInsert = [
                    'equipment_id' => $body['equipment_id'],
                    'room_id' => $body['room_id'],
                    'thoigiancap' => $body['thoigiancap'], // Thêm thời gian cấp vào mảng chèn
                ];

                $insertStatus = insert('equipment_room', $dataInsert); // Sử dụng bảng equipment_room để lưu thông tin phân bổ
                if ($insertStatus) {
                    setFlashData('msg', 'Phân bổ cơ sở vật chất thành công');
                    setFlashData('msg_type', 'suc');
                    redirect('?module=equipment&action=listdistribute');
                } else {
                    setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                    setFlashData('msg_type', 'err');
                    redirect('?module=equipment&action=listdistribute');
                }
            }
        }

        // Nếu có lỗi, xử lý thông báo lỗi
        if (!empty($errors)) {
            setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
            setFlashData('msg_type', 'err');
            setFlashData('errors', $errors);
            setFlashData('old', $body);
            redirect('?module=equipment&action=distribute');
        }
    }


    // Nếu có lỗi, xử lý thông báo lỗi
    if (!empty($errors)) {
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=equipment&action=listdistribute');
    }
}



$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');


$linkreturndistribite = getLinkAdmin('equipment', 'listdistribute');

// Lấy danh sách cơ sở vật chất và phòng trọ
$listAllEquipment = getRaw("SELECT * FROM equipment ORDER BY tenthietbi ASC");
//láy phòng nào chưa có thiết bị
$listAllRoom = getRaw("
    SELECT room.id, room.tenphong
    FROM room 
    LEFT JOIN equipment_room ON equipment_room.room_id = room.id
    WHERE equipment_room.equipment_id IS NULL
    ORDER BY room.tenphong
");



// Hàm lấy danh sách phòng và thiết bị
function getRoomAndEquipmentList()
{
    $sql = "
        SELECT r.id AS room_id, r.tenphong, GROUP_CONCAT(e.tenthietbi SEPARATOR ', ') AS tenthietbi
        FROM room r
        LEFT JOIN equipment_room er ON r.id = er.room_id
        LEFT JOIN equipment e ON er.equipment_id = e.id
        GROUP BY r.id
        ORDER BY r.id ASC
    ";

    return getRaw($sql); // Hàm getRaw() sẽ thực hiện truy vấn và trả về kết quả
}

$listRoomAndEquipment = getRoomAndEquipmentList();

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
                    <label for="">Chọn thiết bị <span style="color: red">*</span></label>
                    <select name="equipment_id" class="form-control" >
                        <option value=""disabled selected>Chọn thiết bị</option>
                        <?php
                        if (!empty($listAllEquipment)) {
                            foreach ($listAllEquipment as $item) {
                        ?>
                                <option value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($item['tenthietbi'], ENT_QUOTES, 'UTF-8'); ?></option>
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
                        <option value=""disabled selected>Chọn phòng</option>
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

                <div class="form-group">
                    <label for="">Thời gian cấp <span style="color: red">*</span></label>
                    <input type="date" name="thoigiancap" class="form-control" value="<?php echo isset($old['thoigiancap']) ? htmlspecialchars($old['thoigiancap'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <?php echo form_error('thoigiancap', $errors, '<span class="error">', '</span>'); ?>
                </div>

            </div>

            <div class="form-group">
                <a style="margin-right: 20px " href="<?php echo $linkreturndistribite ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-plus"></i> Phân bổ thiết bị
                </button>
            </div>
        </form>
    </div>

</div>

<?php layout('footer', 'admin'); ?>