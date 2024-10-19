<?php
if (!defined('_INCODE')) die('Access Deined...');
/*
 * File này chứa chức năng đăng nhập
 * */

$data = [
    'pageTitle' => 'Forgot password'
];

layout('header-login','admin', $data);

//Xử lý đăng nhập
if (isPost()){
    $body = getBody();
    if (!empty($body['email'])){
        $email = $body['email'];
        $queryUser = firstRaw("SELECT id FROM users WHERE email='$email'");

        if (!empty($queryUser)){

            $user_id = $queryUser['id'];

            //Tạo forget_token
            $forget_token = sha1(uniqid().time());

            $dataUpdate = [
                'forget_token' => $forget_token
            ];

            $updateStatus = update('users', $dataUpdate, "id=$user_id");

            if ($updateStatus){

                //Tạo link khôi phục
                $link = _WEB_HOST_ROOT.'?module=auth&action=reset&token='.$forget_token;

                //Thiết lập gửi email
                $subject = 'Yêu cầu khôi phục mật khẩu';
                $content = 'Chào bạn: '.$email.'<br />';
                $content .= '<img src="https://img.freepik.com/free-vector/cyber-security-concept_23-2148542913.jpg?t=st=1728728102~exp=1728731702~hmac=c950572d4022957c7ca461bde3ab26f5afa4510e05f903bb89686ebf8159018b&w=740" />'.'<br />';
                $content .= 'Chúng tôi nhận được yêu cầu khôi phục mật khẩu từ bạn. Vui lòng click vào link sau để khôi phục'.'<br />';
                $content .= $link.'<br />';
                $content .= 'Trân trọng !';

                //Tiến hành gửi email
                $senStatus = sendMail($email, $subject, $content);
                if (!empty($senStatus)){
                    setFlashData('msg', 'Mời bạn vui lòng kiểm tra email !');
                    setFlashData('msg_type', 'suc');
                }
            }

        }else{
            setFlashData('msg', 'Địa chỉ email không tồn tại trong hệ thống');
            setFlashData('msg_type', 'err');
        }
    }

    redirect('?module=auth&action=for');
}


$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$old = getFlashData('old');
?>

<body id="body-login">
        <div id="MessageFlash">
            <?php getMsg($msg, $msgType);?> 
        </div>
    <div class="col-3" style="margin: 20px auto;">
        <div class="login">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logomain.png" class="logo-login" alt="">
        <p class="text-center title-login">Khôi phục lại mật khẩu</p>
        <p class="text-center" style="color: #000; margin-bottom: 20px">Hãy nhập địa chỉ email đã đăng ký, chúng tôi sẽ gửi link khôi phục. Quá trình này có thể mất vài phút.</p>



        <form action="" method="post">
            
            <div class="form-group">
                <label for="">Email</label> <br />
                <input type="email" name="email" class="" placeholder="Email" value="<?php echo old('email', $old); ?>">
            </div>
            <button type="submit" class="btn-login">Gửi yêu cầu</button>
            <button type="button" class="btn-login" onclick="window.location.href='http://localhost:85/datn/?module=auth&action=login';">Trở về trang trước</button>

        </form>
        </div>
    </div>
</body>

<?php

layout('footer-login', 'admin');

