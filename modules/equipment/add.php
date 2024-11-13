<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Thêm thiết bị'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý thêm thiết bị
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    // Validate tên thiết bị: Bắt buộc phải nhập, >=5 ký tự
    if (empty(trim($body['tenthietbi']))) {
        $errors['tenthietbi']['required'] = '** Bạn chưa nhập tên thiết bị!';
    } else {
        if (strlen(trim($body['tenthietbi'])) < 5) {
            $errors['tenthietbi']['min'] = '** Tên thiết bị phải lớn hơn 5 ký tự!';
        }
    }

    // Validate giá thiết bị
    if (empty(trim($body['giathietbi']))) {
        $errors['giathietbi']['required'] = '** Bạn chưa nhập giá thiết bị!';
    }
    // Validate soluongtonkho
    if (empty(trim($body['soluongnhap']))) {
        $errors['soluongnhap']['required'] = '** Bạn chưa nhập số lượng nhập!';
    }

    // Validate ngày nhập
    if (empty(trim($body['ngaynhap']))) {
        $errors['ngaynhap']['required'] = '** Bạn chưa nhập ngày nhập!';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataInsert = [
            'mathietbi' => generateInvoiceNumber(),
            'tenthietbi' => $body['tenthietbi'],
            'giathietbi' => $body['giathietbi'],
            'ngaynhap' => $body['ngaynhap'],
            'soluongnhap' => $body['soluongnhap'],
            'soluongtonkho' => $body['soluongnhap']  // Gán soluongtonkho bằng soluongnhap
        ];

        $insertStatus = insert('equipment', $dataInsert);
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin thiết bị thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=equipment&action=listequipment');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=equipment&action=add');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lệ khi nhập vào
        redirect('?module=equipment&action=add');
    }
}

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// Tạo URL quay về trang add
$linkreturnlistequipment = getLinkAdmin('equipment', 'listequipment');

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
                    <label for="">Tên thiết bị <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên thiết bị" name="tenthietbi" class="form-control" value="<?php echo old('tenthietbi', $old); ?>">
                    <?php echo form_error('tenthietbi', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Giá thiết bị <span style="color: red">*</span></label>
                    <input type="text" placeholder="Giá thiết bị (đ)" name="giathietbi" class="form-control" value="<?php echo old('giathietbi', $old); ?>" oninput="validateNumber(this)">
                    <?php echo form_error('giathietbi', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>
                    // Hàm kiểm tra chỉ cho phép nhập số
                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, ''); // Loại bỏ ký tự không phải số
                    }
                </script>
                <div class="form-group">
                    <label for=""> Số lượng nhập <span style="color: red">*</span></label>
                    <input type="text" placeholder="Số lượng nhập" name="soluongnhap" class="form-control" value="<?php echo old('soluongnhap', $old); ?>" oninput="validateNumber(this)">
                    <?php echo form_error('soluongnhap', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>
                    // Hàm kiểm tra chỉ cho phép nhập số
                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, ''); // Loại bỏ ký tự không phải số
                    }
                </script>
                <div class="form-group">
                    <label for="">Ngày nhập <span style="color: red">*</span></label>
                    <input type="date" name="ngaynhap" class="form-control" value="<?php echo old('ngaynhap', $old); ?>">
                    <?php echo form_error('ngaynhap', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="col-5">
            </div>
            <div class="form-group">
                <a style="margin-right: 20px " href="<?php echo $linkreturnlistequipment ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Thêm thiết bị</button>
            </div>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>