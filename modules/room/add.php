<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Tạo phòng mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


if (isPost()) {

    $body = getBody();
    $errors = [];

    if (empty(trim($body['tenphong']))) {
        $errors['tenphong']['required'] = '** Bạn chưa nhập tên phòng!';
    } else {
        if (strlen(trim($body['tenphong'])) <= 5) {
            $errors['tenphong']['min'] = '** Tên phòng phải lớn hơn 5 ký tự!';
        }
    }

    if (empty(trim($body['dientich']))) {
        $errors['dientich']['required'] = '** Bạn chưa nhập diện tích phòng!';
    }

    if (empty(trim($body['tiencoc']))) {
        $errors['tiencoc']['required'] = '** Bạn chưa nhập giá tiền cọc!';
    }

    if (empty(trim($body['soluongtoida']))) {
        $errors['soluongtoida']['required'] = '** Bạn chưa nhập số lượng người tối đa';
    }
    
    if (empty($errors)) {

        $dataInsert = [
            'tenphong' => $body['tenphong'],
            'image' => $body['image'],
            'dientich' => $body['dientich'],
            'tiencoc' => $body['tiencoc'],
            'ngayvao' => $body['ngayvao'],
            'ngayra' => $body['ngayra'],
            'soluongtoida' => $body['soluongtoida'],
        ];

        $insertStatus = insert('room', $dataInsert);
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin phòng trọ thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=room');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=room&action=add');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
        redirect('?module=room&action=add');
    }
}
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>
<?php
layout('navbar', 'admin', $data);
?>

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

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
                            <button type="button" class="btn btn-secondary btn-sm choose-image"><i class="fa fa-upload"></i></button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="">Tên phòng <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên phòng" name="tenphong" id="" class="form-control" value="<?php echo old('tenphong', $old); ?>">
                    <?php echo form_error('tenphong', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Diện tích <span style="color: red">*</span></label>
                    <input type="text" placeholder="Diện tích (m2)" name="dientich" id="" class="form-control" value="<?php echo old('dientich', $old); ?>" oninput="validateNumber(this)">
                    <?php echo form_error('dientich', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>
                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, '');
                    }
                </script>
            </div>

            <div class="col-5">
                <div class="form-group">
                    <label for="">Số lượng người tối đa <span style="color: red">*</span></label>
                    <input type="text" placeholder="Số lượng tối đa" name="soluongtoida" id="" class="form-control" value="<?php echo old('soluongtoida', $old); ?>" oninput="validateNumber(this)">
                    <?php echo form_error('soluongtoida', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>
                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, '');
                    }
                </script>

                <div class="form-group">
                    <label for="">Giá tiền cọc <span style="color: red">*</span></label>
                    <input type="text" placeholder="Giá cọc (đ)" name="tiencoc" id="" class="form-control" value="<?php echo old('tiencoc', $old); ?>" oninput="validateNumber(this)">
                    <?php echo form_error('tiencoc', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>
                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, '');
                    }
                </script>

            </div>
            <div class="from-group">
                <a style="margin-right: 20px " href="<?php echo getLinkAdmin('room') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Thêm phòng</button>
            </div>
    </div>
    </form>

</div>
</div>


<?php
layout('footer', 'admin');
