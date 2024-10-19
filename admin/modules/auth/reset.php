<?php
if (!defined('_INCODE')) die('Access Deined...');

/*File này chứa chức năng đặt lại mật khẩu*/
layout('header-login', 'admin');
echo '<div class="container "><br/>';
$token = getBody()['token'];

if (!empty($token)){

    //Truy vấn kiểm tra token với database
    $tokenQuery = firstRaw("SELECT id, email FROM users WHERE forget_token='$token'");

    if (!empty($tokenQuery)){
        $user_id = $tokenQuery['id'];
        $email = $tokenQuery['email'];

        if (isPost()){

            $body = getBody();

            $errors = []; //Mảng lưu trữ các lỗi

            //Validate mật khẩu: Bắt buộc phải nhập, >=8 ký tự
            if (empty(trim($body['password']))){
                $errors['password']['required'] = '** Mật khẩu bắt buộc phải nhập!';
            }else{
                if (strlen(trim($body['password'])) < 6){
                    $errors['password']['min'] = '** Mật khẩu không được nhỏ hơn 6 ký tự!';
                }
            }

            //Validate nhập lại mật khẩu: Bắt buộc phải nhập, giống trường mật khẩu
            if (empty(trim($body['confirm_password']))){
                $errors['confirm_password']['required'] = '** Xác nhận mật khẩu không được để trống!';
            }else{
                if (trim($body['password'])!=trim($body['confirm_password'])){
                    $errors['confirm_password']['match'] = '** Mật khẩu không khớp nhau!';
                }
            }

            if (empty($errors)){
                //xử lý update mật khẩu
                $passwordHash = password_hash($body['password'], PASSWORD_DEFAULT);
                
                $dateUpdate = [
                    'password' => $passwordHash,
                    'forget_token' => null,
                ];
                $updateStatus = update('users', $dateUpdate, "id=$user_id");
                if ($updateStatus){

                    setFlashData('msg', 'Thay đổi mật khẩu thành công');
                    setFlashData('msg_type', 'suc');

                    //Gửi email thông báo khi đổi xong
                    $subject = 'Bạn vừa đổi mật khẩu';
                    $content = 'Chúc mừng bạn đã đổi mật khẩu thành công!';
                    sendMail($email, $subject, $content);

                    redirect('admin/?module=auth&action=login');
                }else{
                    setFlashData('msg', 'Lỗi! Bạn không thể đổi mật khẩu');
                    setFlashData('msg_type', 'err');

                    redirect('admin/?module=auth&action=reset&token='.$token);
                }

            }
            else{
                // setFlashData('msg', 'Vui lòng kiểm tra dữ liệu nhập vào');
                // setFlashData('msg_type', 'err');
                setFlashData('errors', $errors);
                redirect('admin/?module=auth&action=reset&token='.$token);
            }

            //redirect('?module=auth&action=reset&token='.$token);
        } //End isPost()

        $msg = getFlashData('msg');
        $msgType = getFlashData('msg_type');
        $errors = getFlashData('errors');

        ?>
        <!-- <div class="row text-left">
            <div class="col-3" style="margin: 20px auto;">
               <div class="reset">
                <h3 class="text-center text-uppercase" style="margin-bottom: 20px;">Đặt lại mật khẩu</h3>
                    <form action="" method="post">

                        <div class="form-group">
                            <label for="">Mật khẩu mới</label>
                            <input type="password" name="password" class="form-control" placeholder="Mật khẩu mới...">
                            <?php echo form_error('password', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Nhập lại mật khẩu</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới...">
                            <?php echo form_error('confirm_password', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Xác nhận</button>
                        <hr>
                        <p class="text-center"><a href="?module=auth&action=login">Đăng nhập</a></p>
                        <input type="hidden" name="token" value="<?php echo $token; ?>">
                    </form>
               </div>
            </div>
        </div> -->

        <body id="body-login">
            <div id="MessageFlash">
                <?php getMsg($msg, $msgType);?> 
            </div>
            <div class="col-3" style="margin: 20px auto;">
                <div class="login">
                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logo-final.png" class="logo-login" alt="">
                    <p class="text-center title-login">ĐỔI MẬT KHẨU</p>
                    <p class="text-center" style="color: #000; margin-bottom: 20px">Hệ thống quản lý phòng trọ cho thuê</p>

                    <form style="text-align: left; margin-top: 25px" action="" method="post">     
                        <div class="form-group">
                            <label for="">Mật khẩu mới</label> <br />
                            <input type="password" name="password" placeholder="Mật khẩu mới">
                            <?php echo form_error('password', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                        <div class="form-group">
                            <label for="">Xác nhận mật khẩu</label> <br />
                            <input type="password" name="confirm_password" class="" placeholder="Nhập lại mật khẩu">
                            <?php echo form_error('confirm_password', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                        <button type="submit" class="btn-login">Xác nhận</button>
                        <hr />
                        <input type="hidden" name="token" value="<?php echo $token; ?>">
                    </form>
                </div>
            </div>
        </body>
        <?php

    }else{
        getMsg('Liên kết không tồn tại hoặc đã hết hạn', 'danger');
    }
}else{
    getMsg('Liên kết không tồn tại hoặc đã hết hạn', 'danger');
}
echo '</div>';
layout('header-footer', 'admin');