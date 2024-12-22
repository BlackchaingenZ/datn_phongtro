if (empty(trim($body['password']))) {
    $errors['password']['required'] = '** Bạn chưa nhập mật khẩu';
} elseif (!is_numeric($body['password'])) {
    $errors['password']['numeric'] = '** Mật khẩu chỉ được chứa số';
}



if (empty(trim($body['password']))) {
    $errors['password']['required'] = '** Bạn chưa nhập mật khẩu';
} elseif (!ctype_alpha($body['password'])) {
    $errors['password']['alpha'] = '** Mật khẩu chỉ được chứa các chữ cái (a-z, A-Z)';
}


if (empty(trim($body['tenphong']))) {
        $errors['tenphong']['required'] = '** Bạn chưa nhập tên phòng!';
} else {
        if (strlen(trim($body['tenphong'])) >10) {
            $errors['tenphong']['max'] = '** Tên phòng đối đa 10 ký tự';
    }
}
///
<div class="form-group">
                <label for="">Mật khẩu</label><br />            
                <input 
        type="password" 
        name="password" 
        class="" 
        placeholder="Mật khẩu" 
        maxlength="6" 
        oninput="validatePasswordLength(this)"
    >
    <small id="passwordHelp" style="color: red; display: none;">Mật khẩu phải đủ 6 ký tự!</small>
            </div>


//
    $ghichu = $_POST['ghichu'] ?? null;

    // Kiểm tra nếu ghi chú vượt quá 20 ký tự
    if (!empty($ghichu) && strlen($ghichu) > 20) {
        setFlashData('msg', 'Ghi chú không được vượt quá 20 ký tự.');
        setFlashData('msg_type', 'err');
        redirect('?module=contract&action=add'); // Quay lại trang thêm hợp đồng
        exit; // Ngừng xử lý
    }

    // Nếu ghi chú trống, gán giá trị mặc định
    if (empty(trim($ghichu))) {
        $ghichu = 'Bỏ trống';
    }

//
    <td style="text-align: center;background-color:blue"><b><?php echo $item['tenphong']; ?></b></td>
//
    <table class="table table-dark table-striped-columns">

//
                    <div class="form-group">
                    <label for="">Giá thiết bị <span style="color: red">*</span></label>
                    <input type="text" name="giathietbi" class="form-control" value="<?php echo old('giathietbi', $old); ?>"oninput="validateNumber(this)">
                    <?php echo form_error('giathietbi', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>

                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, ''); 
                         input.value = input.value.replace(/[^a-zA-Z\s]/g, '');
                    }
                </script>

                echo '<span style = "color: red">'. "Trống".'</span>';


if (empty(trim($body['dientich']))) {
    $errors['dientich']['required'] = '** Bạn chưa nhập diện tích phòng!';
} elseif (!is_numeric($body['dientich']) || $body['dientich'] <= 0) {
    $errors['dientich']['invalid'] = '** Diện tích phải là một số dương!';
} elseif ($body['dientich'] >= 100) {
    $errors['dientich']['max'] = '** Diện tích phải nhỏ hơn 100!';
}

GROUP_CONCAT(
    CASE 
        WHEN equipment_room.soluongcap > 0 THEN equipment_room.soluongcap 
        ELSE NULL 
    END
    SEPARATOR ', '
) AS soluong
