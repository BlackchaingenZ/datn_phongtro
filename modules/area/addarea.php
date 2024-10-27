<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Thêm khu vực'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý thêm danh mục chi phí
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    // Validate tên 
    if (empty(trim($body['tenkhuvuc']))) {
        $errors['tenkhuvuc']['required'] = '** Bạn chưa nhập tên khu vực!';
    }

    // Validate mota
    if (empty(trim($body['mota']))) {
        $errors['mota']['required'] = '** Bạn chưa nhập mô tả!';
    }

    if (empty(trim($body['ngaytao']))) {
        $errors['ngaytao']['required'] = '** Bạn chưa nhập ngày tạo!';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataInsert = [
            'tenkhuvuc' => $body['tenkhuvuc'],
            'mota' => $body['mota'],
            'ngaytao' => $body['ngaytao'],
        ];

        $insertStatus = insert('area', $dataInsert); // Giả định bạn có hàm insert để thêm dữ liệu vào bảng cost
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin khu vực thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=area&action=listarea'); // Đường dẫn đến danh sách danh mục chi phí
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=area&action=listarea');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lệ khi nhập vào
        redirect('?module=area&action=addarea');
    }
}

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');


?>

<?php layout('navbar', 'admin', $data); ?>

<d class="container-fluid">

    <div class="container">
        <div id="MessageFlash">
            <?php getMsg($msg, $msgType); ?>
        </div>

        <div class="box-content">
            <form action="" method="post" class="row">
                <div class="col-5">
                    <div class="form-group">
                        <label for="">Tên khu vực <span style="color: red">*</span></label>
                        <input type="text" placeholder="Tên khu vực" name="tenkhuvuc" class="form-control" value="<?php echo old('tenkhuvuc', $old); ?>">
                        <?php echo form_error('tenkhuvuc', $errors, '<span class="error">', '</span>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="">Mô tả <span style="color: red">*</span></label>
                        <input type="text" placeholder="" name="mota" class="form-control" value="<?php echo old('mota', $old); ?>" style="width: 100%;height:100px">
                        <?php echo form_error('mota', $errors, '<span class="error">', '</span>'); ?>
                    </div>

                    <div class="form-group">
                        <label for=""> Ngày tạo <span style="color: red">*</span></label>
                        <input type="date" placeholder="Ngày tạo" name="ngaytao" class="form-control" value="<?php echo old('ngaytao', $old); ?>">
                        <?php echo form_error('ngaytao', $errors, '<span class="error">', '</span>'); ?>
                    </div>


                </div>
                <div class="col-5">
                </div>
                <div class="form-group">
                    <a style="margin-right: 20px" href="<?php echo getLinkAdmin('area', 'listarea') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Thêm mới </button>
                </div>
            </form>
        </div>
    </div>
</d>
<?php layout('footer', 'admin'); ?>