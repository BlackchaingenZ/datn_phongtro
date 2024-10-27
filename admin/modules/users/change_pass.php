<?php
$data = [
    'pageTitle' => 'Đổi mật khẩu'
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
    
    // validate mật khẩu
    if(empty(trim($body['password']))) {
        $errors['password']['required'] = '** Bạn chưa nhập mật khẩu !';
    }else {
        if(strlen($body['password']) < 6) {
            $errors['password']['min'] = '** Mật khẩu phải lớn hơn 6 ký tự !';
        }
    }
      //Validate nập lại Password: Bắt buộc phải nhập, giống Password
      if(empty(trim($body['confirm_password']))) {
        $errors['confirm_password']['required'] = '** Bạn chưa xác nhận lại mật khẩu !';
    }else{
        if(trim($body['password']) != trim($body['confirm_password'])) {
            $errors['confirm_password']['match'] = '** Xác nhận lại mật khẩu chưa trùng khớp';
        }
    }

   if (empty($errors)) {
       
          // Xử lý update mật khẩu
          $passwordHash = password_hash($body['password'], PASSWORD_DEFAULT);
          $dataUpdate = [
              'password' => $passwordHash,
              'forget_token' => null,
          ];
          $updateStatus = update('users', $dataUpdate, "id=$userId");
          if($updateStatus) {
              setFlashData('msg', 'Thay đổi mật khẩu thành công');
              setFlashData('msg_type', 'suc');
              redirect('admin/?module=users&action=change_pass');

          }else {
              setFlashData('msg', 'Lỗi hệ thống, bạn không thể đổi mật khẩu');
              setFlashData('msg_type', 'err');
              redirect('/admin/?module=users&action=change_pass');
          }

    }else {
         // Có lỗi xảy ra
    setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
    setFlashData('msg_type', 'err');
    setFlashData('errors', $errors);
    }

    
    redirect('?module=users&action=change_pass');
}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');

?>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">

         <div id="MessageFlash">
            <?php getMsg($msg, $msgType);?> 
        </div>
            <form action="" method="post">
             
                    <div class="col-6">
                        <div class="form-group">
                            <label for="password">Mật khẩu mới</label>
                            <input type="password" name="password" id="password" class="form-control"  value="">
                            <?php echo form_error('password', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                    </div>               

                    <div class="col-6">
                        <div class="form-group">
                            <label for="confirm_pass">Xác nhận lại mật khẩu</label>
                            <input type="password" name="confirm_password" id="confirm_pass" class="form-control" value="">
                            <?php echo form_error('confirm_password', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                    </div>  
                <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
            </form>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

<?php
layout('footer', 'admin');
