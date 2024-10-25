<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Chỉnh sửa khu vực'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Lấy ID bảng giá từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin bảng giá cũ lấy thêm cả tengia cost
$AreaDetail = firstRaw("SELECT *, tenkhuvuc FROM area WHERE id = $id");
if (empty($AreaDetail)) {
    redirect('?module=area&action=listarea');
}

// Xử lý sửa bảng giá
if (isPost()) {
    $body = getBody(); // Lấy dữ liệu từ form
    $errors = []; // Mảng lưu trữ các lỗi

    // Kiểm tra thông tin bảng giá
    if (empty(trim($body['tenkhuvuc']))) {
        $errors['tenkhuvuc']['required'] = 'Bạn chưa nhập tên khu vực!';
    }

    if (empty(trim($body['mota']))) {
        $errors['mota']['required'] = 'Bạn chưa nhập mô tả!';
    }

    if (empty(trim($body['ngaytao']))) {
        $errors['ngaytao']['required'] = 'Bạn chưa nhập ngày tạo!';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // Không có lỗi nào
        $dataUpdate = [
            'tenkhuvuc' => $body['tenkhuvuc'],
            'mota' => $body['mota'],
            'ngaytao' => $body['ngaytao'],
        ];

        $condition = "id=$id"; // Điều kiện cập nhật
        $updateStatus = update('area', $dataUpdate, $condition); // Cập nhật dữ liệu

        if ($updateStatus) {
            // Nếu cập nhật thành công
            setFlashData('msg', 'Chỉnh sửa khu vực thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=area&action=listarea'); // Chuyển hướng về danh sách
        } else {
            // Nếu cập nhật không thành công
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            setFlashData('errors', ['update' => 'Có lỗi xảy ra khi cập nhật!']);
            setFlashData('old', $body); 
            redirect('?module=area&action=editarea&id=' . $id); // Giữ lại dữ liệu
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors); // Lưu trữ lỗi
        setFlashData('old', $body); // Giữ lại dữ liệu đã nhập
        redirect('?module=area&action=editarea&id=' . $id); // Giữ lại dữ liệu
    }
}



$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($areaDetail) && empty($old)) {
    $old = $areaDetail;
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
                    <label for="">Thông tin khu vực:</label>
                    <p><?php echo htmlspecialchars($AreaDetail['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="form-group">
                    <label for="tengia">Tên khu vực <span style="color: red">*</span></label>
                    <input type="text" name="tenkhuvuc" class="form-control" value="<?php echo old('tenkhuvuc', $old); ?>">
                    <?php echo form_error('tenkhuvuc', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group" >
                    <label for="giathue">Mô tả <span style="color: red">*</span></label>
                    <input type="text" name="mota" class="form-control" value="<?php echo old('mota', $old); ?>  " style="width: 100%;height:100px"s>
                    <?php echo form_error('mota', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="ngaytao">Ngày tạo <span style="color: red">*</span></label>
                    <input type="date" name="ngaytao" class="form-control" value="<?php echo old('ngaytao', $old); ?>">
                    <?php echo form_error('ngaytao', $errors, '<span class="error">', '</span>'); ?>
                </div>

            </div>

            <div class="btn-row">
                <a style="margin-right: 20px" href="<?php echo getLinkAdmin('area', 'listarea') ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Chỉnh sửa khu vực</button>
            </div>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>
