<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Thêm danh mục bảng giá'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý thêm danh mục chi phí
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    // Validate tên giá
    if (empty(trim($body['tengia']))) {
        $errors['tengia']['required'] = '** Bạn chưa nhập tên giá!';
    }

    // Validate giá thuê
    if (empty(trim($body['giathue']))) {
        $errors['giathue']['required'] = '** Bạn chưa nhập giá thuê!';
    }

    // Validate ngày bắt đầu
    if (empty(trim($body['ngaybatdau']))) {
        $errors['ngaybatdau']['required'] = '** Bạn chưa nhập ngày bắt đầu!';
    }

    // Validate ngày kết thúc
    if (empty(trim($body['ngayketthuc']))) {
        $errors['ngayketthuc']['required'] = '** Bạn chưa nhập ngày kết thúc!';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataInsert = [
            'tengia' => $body['tengia'],
            'giathue' => $body['giathue'],
            'ngaybatdau' => $body['ngaybatdau'],
            'ngayketthuc' => $body['ngayketthuc'],
        ];

        $insertStatus = insert('cost', $dataInsert); // Giả định bạn có hàm insert để thêm dữ liệu vào bảng cost
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin bảng giá thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=cost&action=costroom'); // Đường dẫn đến danh sách danh mục chi phí
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=cost&action=addcost');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lệ khi nhập vào
        redirect('?module=cost&action=addcost');
    }
}

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// Tạo URL quay về trang danh sách bảng giá
$linkreturnlist = getLinkAdmin('cost', 'list');

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
                    <label for="">Tên giá <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên giá" name="tengia" class="form-control" value="<?php echo old('tengia', $old); ?>">
                    <?php echo form_error('tengia', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Giá thuê <span style="color: red">*</span></label>
                    <input type="text" placeholder="Giá thuê (VND)" name="giathue" class="form-control" value="<?php echo old('giathue', $old); ?>">
                    <?php echo form_error('giathue', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ngày bắt đầu <span style="color: red">*</span></label>
                    <input type="date" name="ngaybatdau" class="form-control" value="<?php echo old('ngaybatdau', $old); ?>">
                    <?php echo form_error('ngaybatdau', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ngày kết thúc <span style="color: red">*</span></label>
                    <input type="date" name="ngayketthuc" class="form-control" value="<?php echo old('ngayketthuc', $old); ?>">
                    <?php echo form_error('ngayketthuc', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="col-5">
            </div>
            <div class="form-group">
                <a style="margin-right: 20px" href="<?php echo $linkreturnlist ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Thêm mới </button>
            </div>
        </form>
    </div>
</div>

<?php layout('footer', 'admin'); ?>
