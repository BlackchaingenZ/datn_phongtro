<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Tạo thành phố mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


if (isPost()) {
    // Validate form
    $body = getBody(); 
    $errors = [];  


    if (empty(trim($body['tenthanhpho']))) {
        $errors['tenthanhpho']['required'] = '** Bạn chưa nhập tên thành phố!';
    } else {
        if (strlen(trim($body['tenthanhpho'])) <= 5) {
            $errors['tenthanhpho']['min'] = '** Tên phòng phải lớn hơn 5 ký tự!';
        }
    }
    if (empty(trim($body['matp']))) {
        $errors['matp']['required'] = '** Bạn chưa nhập diện mã thành phố!';
    }
    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataInsert = [
            'tenthanhpho' => $body['tenthanhpho'],
            'matp' => $body['matp'],
        ];

        $insertStatus = insert('thanhpho', $dataInsert);
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin thành phố thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=city');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=city&action=add');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  
        redirect('?module=city&action=add');
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

                <div class="form-group">
                    <label for="">Tên thành phố <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên thành phố" name="tenthanhpho" id="" class="form-control" value="<?php echo old('tenthanhpho', $old); ?>">
                    <?php echo form_error('tenthanhpho', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Mã thành phố <span style="color: red">*</span></label>
                    <input type="text" placeholder="mã thành phố" name="matp" id="" class="form-control" value="<?php echo old('matp', $old); ?>" oninput="validateNumber(this)">
                    <?php echo form_error('matp', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>

                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, '');
                    }
                </script>
            </div>

            <div class="from-group">
                <a style="margin-right: 20px " href="<?php echo getLinkAdmin('city') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Thêm thành phố</button>
            </div>
    </div>
    </form>

</div>
</div>
<?php
layout('footer', 'admin');