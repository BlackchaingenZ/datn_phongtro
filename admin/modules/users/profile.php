<?php
$data = [
    'pageTitle' => 'Cập nhật thông tin cá nhân'
];

layout('header-tenant', 'admin', $data);
layout('sidebar', 'admin', $data);

$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId); 
setFlashData('userDetail', $userDetail);


// Xử lý cập nhật

if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    
    // Valide họ tên: Bắt buộc phải nhập, >=5 ký tự
    if(empty(trim($body['fullname']))) {
        $errors['fullname']['required'] = '** Bạn chưa nhập họ tên!';
    }else {
        if(strlen(trim($body['fullname'])) <= 5) {
        $errors['fullname']['min'] = '** Họ tên phải lớn hơn 5 ký tự!';
        }
    }

   // Validate Email : Bắt buộc phải nhập, định dạng email, k trùng nhau

   if(empty(trim($body['email']))) {
     $errors['email']['required'] = '** Bạn chưa nhập Email!';
   }else {
    //Kiểm tra eamil có hợp lệ không
    if (!isEmail(trim($body['email']))) {
        $errors['email']['isEmail'] = '** Email không hợp lệ!';
    }else {
        //Kiểm tra Email có tồn tại trong DB không
        $email = trim($body['email']);
        $sql = "SELECT id FROM users WHERE email='$email' AND id<>$userId";
        if(getRows($sql) > 0) {
            $errors['email']['unique'] = '** Địa chỉ email đã tồn tại'; 
        }
    }
   }

   if (empty($errors)) {
        // Không có lỗi
        $dataUpdate = [
            'email' => $body['email'],
            'fullname' => $body['fullname'],
        ];


        $condition = "id=$userId";
        $updateStatus = update('users', $dataUpdate, $condition);
        if ($updateStatus) {
            setFlashData('msg', 'Cập nhật thông tin người dùng thành công');
            setFlashData('msg_type', 'suc');
        }else {
        setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
        setFlashData('msg_type', 'err');
        }

    }else {
         // Có lỗi xảy ra
    setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
    setFlashData('msg_type', 'err');
    setFlashData('errors', $errors);
    setFlashData('old', $body); 
    }

    
    redirect('admin/?module=users&action=profile');
}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
$userDetail = getFlashData('userDetail');

if (!empty($userDetail) && empty($old)) {
    $old = $userDetail;
}



?>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">

      <div id="MessageFlash">
            <?php getMsg($msg, $msgType);?> 
        </div>
            <form action="" method="post">
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label for="fullname">Họ và tên</label>
                            <input type="text" name="fullname" id="fullname" class="form-control" value="<?php echo old('fullname', $old); ?>">
                            <?php echo form_error('fullname', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" class="form-control" value="<?php echo old('email', $old); ?>">
                            <?php echo form_error('email', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                    </div>             
                </div>

                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </form>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

<?php
layout('footer', 'admin');
