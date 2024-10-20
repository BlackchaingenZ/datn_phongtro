<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Cập nhật thiết bị'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý hiện dữ liệu cũ của thiết bị
$body = getBody();
$id = $_GET['id'];

if (!empty($id)) {
    $equipmentId = $id;
    $equipmentDetail = firstRaw("SELECT * FROM equipment WHERE id=$equipmentId");
    if (!empty($equipmentDetail)) {
        setFlashData('equipmentDetail', $equipmentDetail);
    } else {
        redirect('?module=equipment');
    }
}

// Xử lý sửa thiết bị
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    // Kiểm tra thông tin thiết bị
    if (empty(trim($body['tenthietbi']))) {
        $errors['tenthietbi']['required'] = 'Bạn chưa nhập tên thiết bị!';
    }

    if (empty(trim($body['giathietbi']))) {
        $errors['giathietbi']['required'] = 'Bạn chưa nhập giá thiết bị!';
    }

    if (empty(trim($body['ngaynhap']))) {
        $errors['ngaynhap']['required'] = 'Bạn chưa nhập ngày nhập!';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // Không có lỗi nào
        $dataUpdate = [
            'tenthietbi' => $body['tenthietbi'],
            'giathietbi' => $body['giathietbi'],
            'ngaynhap' => $body['ngaynhap']
        ];

        $condition = "id=$equipmentId";
        $updateStatus = update('equipment', $dataUpdate, $condition);

        if ($updateStatus) {
            setFlashData('msg', 'Cập nhật thiết bị thành công');
            setFlashData('msg_type', 'suc');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body); // Giữ lại dữ liệu đã nhập
    }

    redirect('?module=equipment&action=edit&id=' . $equipmentId);
}

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($equipmentDetail) && empty($old)) {
    $old = $equipmentDetail;
}

layout('navbar', 'admin', $data);
?>
<div id="MessageFlash">
    <?php getMsg($msg, $msgType); ?>
</div>
<div class="container">
    <hr />

    <div class="box-content">

        <form action="" method="post" class="row">
            <div class="col-5">
                <div class="form-group">
                    <label for="">Tên thiết bị <span style="color: red">*</span></label>
                    <input type="text" name="tenthietbi" class="form-control" value="<?php echo old('tenthietbi', $old); ?>">
                    <?php echo form_error('tenthietbi', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Giá thiết bị <span style="color: red">*</span></label>
                    <input type="text" name="giathietbi" class="form-control" value="<?php echo old('giathietbi', $old); ?>">
                    <?php echo form_error('giathietbi', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ngày nhập <span style="color: red">*</span></label>
                    <input type="date" name="ngaynhap" class="form-control" value="<?php echo old('ngaynhap', $old); ?>">
                    <?php echo form_error('ngaynhap', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="btn-row">
                <a style="margin-right: 20px" href="<?php echo getLinkAdmin('equipment') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<?php
layout('footer', 'admin');
?>