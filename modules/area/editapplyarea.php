<?php 
if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Chỉnh sửa khu vực'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Lấy ID phòng từ tham số area trong URL
$roomId = isset($_GET['applyarea']) ? (int)$_GET['applyarea'] : 0; // Đổi tham số từ applycost thành applyarea

// Lấy thông tin phòng, lấy tên phòng
$roomDetail = firstRaw("SELECT * FROM room WHERE id = $roomId");
if (empty($roomDetail)) {
    redirect('?module=area&action=applyarea'); // Chỉnh sửa đường dẫn chuyển hướng nếu không tìm thấy
}

// Kiểm tra xem phòng đã có khu vực chưa (nếu cần)
$areaCheck = firstRaw("SELECT * FROM area_room WHERE room_id = $roomId");
if (empty($areaCheck)) {
    setFlashData('msg', 'Hãy thêm khu vực cho phòng trước!');
    setFlashData('msg_type', 'err');
    redirect('?module=area&action=applyarea'); // Chỉnh sửa đường dẫn chuyển hướng
}

// Xử lý sửa thông tin khi gửi biểu mẫu
if (isPost()) {
    $body = getBody();
    $errors = [];

    // Validate thông tin nhập vào
    if (empty(trim($body['area_id']))) {
        $errors['area_id']['required'] = '** Bạn chưa chọn khu vực';
    }
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng trọ!';
    }
    if (empty(trim($body['mota']))) {
        $errors['mota']['required'] = '** Bạn chưa nhập mô tả!';
    }

    // Kiểm tra mảng lỗi
    if (empty($errors)) {
        // Cập nhật thông tin khu vực
        $dataUpdate = [
            'area_id' => $body['area_id'],
            'room_id' => $body['room_id'],
            'mota' => $body['mota']
        ];
        // Chạy câu lệnh cập nhật
        $updateStatus = update('area_room', $dataUpdate, "room_id = $roomId");
        if ($updateStatus) {
            setFlashData('msg', 'Cập nhật khu vực thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=area&action=applyarea'); // Chỉnh sửa đường dẫn chuyển hướng
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=area&action=applyarea'); // Chỉnh sửa đường dẫn chuyển hướng
        }
        
    } else {
        // Nếu có lỗi, xử lý thông báo lỗi
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=area&action=editapplyarea&applyarea=' . $roomId); // Redirect với tham số applyarea
    }
}

// Lấy danh sách phòng và area
$listAllArea = getRaw("SELECT * FROM area ORDER BY tenkhuvuc ASC"); // Giả định bạn có bảng area

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($areaDetail) && empty($old)) {
    $old = $areaDetail;
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
                    <label for="">Chọn khu vực <span style="color: red">*</span></label>
                    <select name="area_id" class="form-control">
                        <option value="">Chọn khu vực</option>
                        <?php
                        if (!empty($listAllArea)) {
                            foreach ($listAllArea as $item) {
                                $selected = ($item['id'] == $old['area_id']) ? 'selected' : '';
                        ?>
                                <option value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($item['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('area_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($roomId); ?>">
                </div>

                <div class="form-group">
                    <label for="">Mô tả <span style="color: red">*</span></label>
                    <input type="type" name="mota" class="form-control" value="<?php echo isset($old['mota']) ? htmlspecialchars($old['mota'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <?php echo form_error('mota', $errors, '<span class="error">', '</span>'); ?>
                </div>

            </div>

            <div class="form-group">
                <a href="<?php echo getLinkAdmin('area', 'applyarea'); ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-edit"></i> Cập nhật khu vực
                </button>
            </div>
        </form>
    </div>

</div>

<?php layout('footer', 'admin'); ?>
