<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Chỉnh sửa bảng giá'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Lấy ID bảng giá từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin bảng giá cũ lấy thêm cả tengia cost
$costDetail = firstRaw("SELECT *, tengia FROM cost WHERE id = $id");
if (empty($costDetail)) {
    redirect('?module=cost&action=costroom');
}

// Xử lý sửa bảng giá
if (isPost()) {
    $body = getBody(); // Lấy dữ liệu từ form
    $errors = []; // Mảng lưu trữ các lỗi

    // Kiểm tra thông tin bảng giá
    if (empty(trim($body['tengia']))) {
        $errors['tengia']['required'] = 'Bạn chưa nhập tên giá!';
    }

    if (empty(trim($body['giathue']))) {
        $errors['giathue']['required'] = 'Bạn chưa nhập giá thuê!';
    }

    if (empty(trim($body['ngaybatdau']))) {
        $errors['ngaybatdau']['required'] = 'Bạn chưa nhập ngày bắt đầu!';
    }

    if (empty(trim($body['ngayketthuc']))) {
        $errors['ngayketthuc']['required'] = 'Bạn chưa nhập ngày kết thúc!';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // Không có lỗi nào
        $dataUpdate = [
            'tengia' => $body['tengia'],
            'giathue' => $body['giathue'],
            'ngaybatdau' => $body['ngaybatdau'],
            'ngayketthuc' => $body['ngayketthuc']
        ];

        $condition = "id=$id"; // Điều kiện cập nhật
        $updateStatus = update('cost', $dataUpdate, $condition); // Cập nhật dữ liệu

        if ($updateStatus) {
            // Nếu cập nhật thành công
            setFlashData('msg', 'Chỉnh sửa bảng giá thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=cost&action=costroom'); // Chuyển hướng về danh sách
        } else {
            // Nếu cập nhật không thành công
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            setFlashData('errors', ['update' => 'Có lỗi xảy ra khi cập nhật!']);
            setFlashData('old', $body); 
            redirect('?module=cost&action=editcostroom&id=' . $id); // Giữ lại dữ liệu
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors); // Lưu trữ lỗi
        setFlashData('old', $body); // Giữ lại dữ liệu đã nhập
        redirect('?module=cost&action=editcostroom&id=' . $id); // Giữ lại dữ liệu
    }
}



$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($costDetail) && empty($old)) {
    $old = $costDetail;
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
                    <label for="">Thông tin bảng giá:</label>
                    <p><?php echo htmlspecialchars($costDetail['tengia'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="form-group">
                    <label for="tengia">Tên giá <span style="color: red">*</span></label>
                    <input type="text" name="tengia" class="form-control" value="<?php echo old('tengia', $old); ?>">
                    <?php echo form_error('tengia', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="giathue">Giá thuê <span style="color: red">*</span></label>
                    <input type="number" name="giathue" class="form-control" value="<?php echo old('giathue', $old); ?>">
                    <?php echo form_error('giathue', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="ngaybatdau">Ngày bắt đầu <span style="color: red">*</span></label>
                    <input type="date" name="ngaybatdau" class="form-control" value="<?php echo old('ngaybatdau', $old); ?>">
                    <?php echo form_error('ngaybatdau', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="ngayketthuc">Ngày kết thúc <span style="color: red">*</span></label>
                    <input type="date" name="ngayketthuc" class="form-control" value="<?php echo old('ngayketthuc', $old); ?>">
                    <?php echo form_error('ngayketthuc', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="btn-row">
                <a style="margin-right: 20px" href="<?php echo getLinkAdmin('cost', 'costroom') ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Chỉnh sửa bảng giá</button>
            </div>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>
