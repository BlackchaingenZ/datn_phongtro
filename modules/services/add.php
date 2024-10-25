<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Thêm dịch vụ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý thêm danh mục chi phí
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    // Validate tên giá
    if (empty(trim($body['tendichvu']))) {
        $errors['tendichvu']['required'] = '** Bạn chưa nhập tên dịch vụ!';
    }

    if (empty(trim($body['donvitinh']))) {
        $errors['donvitinh']['required'] = '** Bạn chưa nhập đơn vị tính!';
    }

    // Validate giá thuê
    if (empty(trim($body['giadichvu']))) {
        $errors['giadichvu']['required'] = '** Bạn chưa nhập giá dịch vụ!';
    }

    // Validate ngày bắt đầu
    if (empty(trim($body['create_at']))) {
        $errors['create_at']['required'] = '** Bạn chưa nhập ngày tạo!';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataInsert = [
            'tendichvu' => $body['tendichvu'],
            'donvitinh' => $body['donvitinh'],
            'giadichvu' => $body['giadichvu'],
            'create_at' => $body['create_at'],
        ];

        $insertStatus = insert('services', $dataInsert); // Giả định bạn có hàm insert để thêm dữ liệu vào bảng cost
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin bảng giá thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=services'); // Đường dẫn đến danh sách danh mục chi phí
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=services');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lệ khi nhập vào
        redirect('?module=services&action=add');
    }
}

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// Tạo URL quay về trang danh sách bảng giá
$linkreturnservices = getLinkAdmin('services', 'lists');

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
                    <label for="">Tên dịch vụ <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên dịch vụ" name="tendichvu" class="form-control" value="<?php echo old('tendichvu', $old); ?>">
                    <?php echo form_error('tendichvu', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Đơn vị tính <span style="color: red">*</span></label>
                    <input type="text" placeholder="Đơn vị tính" name="donvitinh" class="form-control" value="<?php echo old('donvitinh', $old); ?>">
                    <?php echo form_error('donvitinh', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Giá dịch vụ <span style="color: red">*</span></label>
                    <input type="text" placeholder="Giá dịch vụ" name="giadichvu" class="form-control" value="<?php echo old('giadichvu', $old); ?>">
                    <?php echo form_error('giadichvu', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ngày tạo <span style="color: red">*</span></label>
                    <input type="date" name="create_at" class="form-control" value="<?php echo old('create_at', $old); ?>">
                    <?php echo form_error('create_at', $errors, '<span class="error">', '</span>'); ?>
                </div>

            </div>

            <div class="col-5">
            </div>
            <div class="form-group">
                <a style="margin-right: 20px" href="<?php echo $linkreturnservices ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Thêm mới </button>
            </div>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>
