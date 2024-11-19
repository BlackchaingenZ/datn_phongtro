<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật phiếu thu'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];

$allCollect = getRaw("SELECT * FROM category_collect");
$allRoom = getRaw("SELECT * FROM room");


if (!empty($body['id'])) {
    $receiptId = $body['id'];
    $receiptDetail  = firstRaw("SELECT * FROM receipt WHERE id=$receiptId");
    if (!empty($receiptDetail)) {
        // Gán giá trị receiptDetail vào setFalsh
        setFlashData('receiptDetail', $receiptDetail);
    } else {
        redirect('?module=receipt');
    }
}


// Xử lý sửa người dùng
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    //Valide họ tên: Bắt buộc phải nhập, >=5 ký tự

    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataUpdate = [
            'danhmucthu_id' => $body['danhmucthu_id'],
            'room_id' => $body['room_id'],
            'sotien' => $body['sotien'],
            'ghichu' => $body['ghichu'],
            'ngaythu' => $body['ngaythu'],
            'phuongthuc' => $body['phuongthuc'],
        ];

        $condition = "id=$id";
        $updateStatus = update('receipt', $dataUpdate, $condition);
        if ($updateStatus) {
            setFlashData('msg', 'Cập nhật thông tin phiếu thu thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=receipt&action=receipts');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
    }

    redirect('?module=receipt&action=edit&id=' . $receiptId);
}
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($receiptDetail) && empty($old)) {
    $old = $receiptDetail;
}
?>
<?php
layout('navbar', 'admin', $data);
?>

<div class="container">
    <hr />

    <div class="box-content">
        <form action="" method="post" class="row" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="col-5">

                <div class="form-group">
                    <label for="">Danh mục thu <span style="color: red">*</span></label>
                    <select name="danhmucthu_id" id="" class="form-select">
                        <option value="" disabled selected>Chọn danh mục</option>
                        <?php
                        if (!empty($allCollect)) {
                            foreach ($allCollect as $item) {
                        ?>
                                <option value="<?php echo $item['id'] ?>" <?php echo (old('danhmucthu_id', $old) == $item['id']) ? 'selected' : '' ?>><?php echo $item['tendanhmuc'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Chọn phòng lập phiếu thu <span style="color: red">*</span></label>
                    <select name="room_id" id="" class="form-select">
                        <option value="" disabled selected>Chọn phòng</option>
                        <?php
                        if (!empty($allRoom)) {
                            foreach ($allRoom as $item) {
                        ?>
                                <option value="<?php echo $item['id'] ?>" <?php echo (old('room_id', $old) == $item['id']) ? 'selected' : false; ?>><?php echo $item['tenphong'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Nội dung thanh toán <span style="color: red">*</span></label>
                    <textarea rows="5" name="ghichu" id="" class="form-control" value=""><?php echo old('ghichu', $old); ?></textarea>
                    <?php echo form_error('ghichu', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="col-5">

                <div class="form-group">
                    <label for="">Số tiền <span style="color: red">*</span></label>
                    <input type="text" placeholder="Nhập số tiền thu" name="sotien" id="" class="form-control" value="<?php echo old('sotien', $old); ?>">
                    <?php echo form_error('sotien', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ngày thu <span style="color: red">*</span></label>
                    <input type="date" name="ngaythu" id="" class="form-control" value="<?php echo old('ngaythu', $old); ?>">
                    <?php echo form_error('ngaythu', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Phương thức thanh toán</label>
                    <select name="phuongthuc" class="form-select">
                        <option value="" disabled selected>Chọn phương thức</option>
                        <option value="0" <?php if ($receiptDetail['phuongthuc'] == 0) echo 'selected' ?>>Tiền mặt</option>
                        <option value="1" <?php if ($receiptDetail['phuongthuc'] == 1) echo 'selected' ?>>Chuyển khoản</option>
                    </select>
                </div>

            </div>
            <div class="from-group">
                <div class="btn-row">
                <a style="margin-right: 20px " href="<?php echo getLinkAdmin('receipt','receipts') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay về</a>
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-plus"></i> Cập nhật phiếu thu</button>
                </div>
            </div>
        </form>

    </div>
</div>


<?php
layout('footer', 'admin');
