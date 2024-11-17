<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật phòng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];


if (!empty($body['id'])) {
    $roomId = $body['id'];
    $roomDetail  = firstRaw("SELECT * FROM room WHERE id=$roomId");
    if (!empty($roomDetail)) {
        // Gán giá trị roomDetail vào setFalsh
        setFlashData('roomDetail', $roomDetail);
    } else {
        redirect('?module=room');
    }
}


// Xử lý sửa người dùng
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    //Valide họ tên: Bắt buộc phải nhập, >=5 ký tự
    if (empty(trim($body['tenphong']))) {
        $errors['tenphong']['required'] = '** Bạn chưa nhập tên phòng!';
    } else {
        if (strlen(trim($body['tenphong'])) <= 5) {
            $errors['tenphong']['min'] = '** Tên phòng phải lớn hơn 5 ký tự!';
        }
    }

    if (empty(trim($body['tiencoc']))) {
        $errors['tiencoc']['required'] = '** Bạn chưa nhập giá tiền cọc!';
    }


    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataUpdate = [
            'tenphong' => $body['tenphong'],
            'image' => $body['image'],
            'dientich' => $body['dientich'],
            'tiencoc' => $body['tiencoc'],
            'ngaylaphd' => $body['ngaylaphd'],
            'chuky' => $body['chuky'],
        ];

        $condition = "id=$id";
        $updateStatus = update('room', $dataUpdate, $condition);
        if ($updateStatus) {
            setFlashData('msg', 'Cập nhật thông tin phòng thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=room');
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

    redirect('?module=room&action=edit&id=' . $roomId);
}
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($roomDetail) && empty($old)) {
    $old = $roomDetail;
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

                <!-- Ảnh -->
                <div class="form-group">
                    <label for="name">Ảnh <span style="color: red">*</span></label>
                    <div class="row ckfinder-group">
                        <div class="col-10">
                            <input type="text" placeholder="Ảnh phòng" name="image" id="name" class="form-control image-render" value="<?php echo old('image', $old); ?>">
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn btn-secondary btn choose-image"><i class="fa fa-upload"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Tên phòng <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên phòng" name="tenphong" id="" class="form-control" value="<?php echo old('tenphong', $old); ?>">
                    <?php echo form_error('tenphong', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Diện tích</label>
                    <input type="text" placeholder="Diện tích (m2)" name="dientich" id="" class="form-control" value="<?php echo old('dientich', $old); ?>" oninput="validateNumber(this)">
                    <?php echo form_error('dientich', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>
                    // Hàm kiểm tra chỉ cho phép nhập số
                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, ''); // Loại bỏ ký tự không phải số
                    }
                </script>

            </div>

            <div class="col-5">
                <div class="form-group">
                    <label for="">Giá tiền cọc <span style="color: red">*</span></label>
                    <input type="text" placeholder="Giá cọc (đ)" name="tiencoc" id="" class="form-control" value="<?php echo old('tiencoc', $old); ?>" oninput="validateNumber(this)">
                    <?php echo form_error('tiencoc', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>
                    // Hàm kiểm tra chỉ cho phép nhập số
                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, ''); // Loại bỏ ký tự không phải số
                    }
                </script>
                <div class="form-group">
                    <label for="">Ngày lập hóa đơn</label>
                    <select name="ngaylaphd" id="" class="form-select">
                        <!-- <option value="">Chọn ngày</option> -->
                        <?php
                        for ($i = 1; $i <= 31; $i++) {
                            $selected = ($i == $roomDetail['ngaylaphd']) ? "selected" : "";
                        ?>
                            <option value="<?php echo $i ?>" <?php echo $selected; ?>>Ngày <?php echo $i; ?></option>
                        <?php }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Chu kỳ thu tiền</label>
                    <select name="chuky" id="" class="form-select">
                        <!--<option value="">Chọn chu kỳ</option>-->
                        <?php
                        for ($i = 1; $i < 7; $i += 2) {
                            $selectChuky = ($i == $roomDetail['chuky']) ? 'selected' : "";
                        ?>
                            <option value="<?php echo $i ?>" <?php echo $selectChuky; ?>><?php echo $i; ?> tháng</option>
                        <?php }
                        ?>
                    </select>
                </div>
            </div>
            <div class="from-group">
                <div class="btn-row">
                    <a style="margin-right: 20px;" href="<?php echo getLinkAdmin('room') ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-circle-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-edit"></i> Cập nhật
                    </button>

                </div>
            </div>
        </form>

    </div>
</div>


<?php
layout('footer', 'admin');
