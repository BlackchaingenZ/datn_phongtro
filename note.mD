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