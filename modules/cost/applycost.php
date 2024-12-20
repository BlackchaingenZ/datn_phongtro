<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Thêm mới áp dụng giá'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý phân bổ cơ sở vật chất
if (isPost()) {
    $body = getBody();
    $errors = [];

    // Validate cơ sở vật chất
    if (empty(trim($body['cost_id']))) {
        $errors['cost_id']['required'] = '** Bạn chưa chọn tên giá';
    }

    // Validate phòng trọ
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng trọ!';
    }

    // Validate thời gian cấp
    if (empty(trim($body['thoigianapdung']))) {
        $errors['thoigianapdung']['required'] = '** Bạn chưa nhập thời gian áp dụng!';
    }

    // Kiểm tra mảng error
    if (isPost()) {
        $body = getBody();
        $errors = [];

        // Validate giá
        if (empty(trim($body['cost_id']))) {
            $errors['cost_id']['required'] = '** Bạn chưa chọn tên giá!';
        }

        // Validate phòng trọ
        if (empty(trim($body['room_id']))) {
            $errors['room_id']['required'] = '** Bạn chưa chọn phòng trọ!';
        }

        // Validate thời gian cấp
        if (empty(trim($body['thoigianapdung']))) {
            $errors['thoigianapdung']['required'] = '** Bạn chưa nhập thời gian áp dụng!';
        }

        // Kiểm tra mảng error
        if (empty($errors)) {
            // Kiểm tra xem phòng đã có giá thuê chưa
            $checkExistsQuery = "
                SELECT COUNT(*) AS count
                FROM cost_room
                WHERE room_id = :room_id 
            ";
            $stmt = $pdo->prepare($checkExistsQuery);
            $stmt->execute([
                ':room_id' => $body['room_id'],
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                // Nếu giá đã tồn tại trong phòng
                setFlashData('msg', 'Không thể thêm vì phòng này đã có giá thuê rồi !');
                setFlashData('msg_type', 'err');
                redirect('?module=cost&action=applycost'); // Chuyển hướng về trang phân bổ
            } else {
                // Không có lỗi nào, tiến hành thêm giá
                $dataInsert = [
                    'cost_id' => $body['cost_id'],
                    'room_id' => $body['room_id'],
                    'thoigianapdung' => $body['thoigianapdung'], // Thêm thời gian cấp vào mảng chèn
                ];

                $insertStatus = insert('cost_room', $dataInsert); // Sử dụng bảng cost_room để lưu thông tin phân bổ
                if ($insertStatus) {
                    setFlashData('msg', 'Thêm loại giá thành công');
                    setFlashData('msg_type', 'suc');
                    redirect('?module=cost&action=applyroom');
                } else {
                    setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                    setFlashData('msg_type', 'err');
                    redirect('?module=cost&action=applyroom');
                }
            }
        }

        // Nếu có lỗi, xử lý thông báo lỗi
        if (!empty($errors)) {
            setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
            setFlashData('msg_type', 'err');
            setFlashData('errors', $errors);
            setFlashData('old', $body);
            redirect('?module=cost&action=applycost');
        }
    }


    // Nếu có lỗi, xử lý thông báo lỗi
    if (!empty($errors)) {
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=cost&action=applycost');
    }
}



$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');


$linkreturndistribite = getLinkAdmin('equipment', 'listdistribute');

// Lấy danh sách phòng và cost
$listAllCost = getRaw("SELECT * FROM cost ORDER BY giathue ASC");

//láy phòng nào chưa có giathue
$listAllRoom = getRaw("
    SELECT room.id, room.tenphong
    FROM room 
    LEFT JOIN cost_room ON cost_room.room_id = room.id
    WHERE cost_room.cost_id IS NULL
    ORDER BY room.tenphong
");

// Hàm lấy danh sách phòng và cost
function getRoomAndCostList()
{
    $sql = "
        SELECT r.id AS room_id, r.tenphong, 
        GROUP_CONCAT(e.tengia SEPARATOR ', ') AS tengia, 
        GROUP_CONCAT(er.thoigianapdung SEPARATOR ', ') AS thoigianapdung
        FROM room r
        LEFT JOIN cost_room er ON r.id = er.room_id
        LEFT JOIN cost e ON er.cost_id = e.id
        GROUP BY r.id
        ORDER BY r.id ASC
    ";
    return getRaw($sql);
}


$listRoomAndCost = getRoomAndCostList();

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
                    <label for="">Chọn tên giá <span style="color: red">*</span></label>
                    <select name="cost_id" class="form-control">
                        <option value="">Chọn tên giá</option>
                        <?php
                        if (!empty($listAllCost)) {
                            foreach ($listAllCost as $item) {
                        ?>
                                <option value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($item['tengia'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('cost_id', $errors, '<span class="error">', '</span>'); ?>
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

                <div class="form-group">
                    <label for="">Thời gian áp dụng <span style="color: red">*</span></label>
                    <input type="date" name="thoigianapdung" class="form-control" value="<?php echo isset($old['thoigianapdung']) ? htmlspecialchars($old['thoigianapdung'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <?php echo form_error('thoigianapdung', $errors, '<span class="error">', '</span>'); ?>
                </div>

            </div>

            <div class="form-group">
                <a href="<?php echo getLinkAdmin('cost', 'applyroom'); ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-plus"></i> Áp dụng giá
                </button>
            </div>
        </form>
    </div>

</div>

<?php layout('footer', 'admin'); ?>