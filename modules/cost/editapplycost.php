<?php
if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Chỉnh sửa áp dụng giá'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Lấy ID phòng từ tham số applycost trong URL
$roomId = isset($_GET['applycost']) ? (int)$_GET['applycost'] : 0;

// Lấy thông tin phòng, lấy tên phòng
$roomDetail = firstRaw("SELECT * FROM room WHERE id = $roomId");
if (empty($roomDetail)) {
    redirect('?module=cost&action=applycost');
}

// Kiểm tra xem phòng đã có giá chưa
$costCheck = firstRaw("SELECT * FROM cost_room WHERE room_id = $roomId");
if (empty($costCheck)) {
    setFlashData('msg', 'Hãy thêm loại giá cho phòng trước!');
    setFlashData('msg_type', 'err');
    redirect('?module=cost&action=applyroom');
}

// Xử lý sửa thông tin khi gửi biểu mẫu
if (isPost()) {
    $body = getBody();
    $errors = [];

    // Validate thông tin nhập vào
    if (empty(trim($body['cost_id']))) {
        $errors['cost_id']['required'] = '** Bạn chưa chọn tên giá';
    }
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng trọ!';
    }
    if (empty(trim($body['thoigianapdung']))) {
        $errors['thoigianapdung']['required'] = '** Bạn chưa nhập thời gian áp dụng!';
    }

    // Kiểm tra mảng lỗi
    if (empty($errors)) {
        // Cập nhật thông tin giá thuê
        $dataUpdate = [
            'cost_id' => $body['cost_id'],
            'room_id' => $body['room_id'],
            'thoigianapdung' => $body['thoigianapdung']
        ];
        // Chạy câu lệnh cập nhật
        $updateStatus = update('cost_room', $dataUpdate, "room_id = $roomId");
        if ($updateStatus) {
            setFlashData('msg', 'Cập nhật loại giá thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=cost&action=applyroom');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=cost&action=applyroom');
        }
    } else {
        // Nếu có lỗi, xử lý thông báo lỗi
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=cost&action=editapplycost&applycost=' . $roomId); // Redirect với tham số applycost
    }
}

// Lấy danh sách phòng và cost
$listAllCost = getRaw("SELECT * FROM cost ORDER BY giathue ASC");

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($costDetail) && empty($old)) {
    $old = $costDetail;
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
                    <label for="">Thông tin phòng:</label>
                    <p><?php echo htmlspecialchars($roomDetail['tenphong'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="form-group">
                    <label for="">Chọn tên giá <span style="color: red">*</span></label>
                    <select name="cost_id" class="form-control">
                        <option value="">Chọn tên giá</option>
                        <?php
                        if (!empty($listAllCost)) {
                            foreach ($listAllCost as $item) {
                                $selected = ($item['id'] == $old['cost_id']) ? 'selected' : '';
                        ?>
                                <option value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($item['tengia'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('cost_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($roomId); ?>">
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
                    <i class="fa fa-edit"></i> Cập nhật giá
                </button>
            </div>
        </form>
    </div>

</div>

<?php layout('footer', 'admin'); ?>