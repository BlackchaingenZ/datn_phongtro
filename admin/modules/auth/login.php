<?php
if (!defined('_INCODE')) die('Access Deined...');
/*
 * File này chứa chức năng đăng nhập
 * */

$data = [
    'pageTitle' => 'Đăng nhập hệ thống'
];

layout('header-login','admin', $data);

//Xử lý đăng nhập
if (isPost()){
    $body = getBody();
    if (!empty(trim($body['email'])) && !empty(trim($body['password']))){
        //Kiểm tra đăng nhập
        $email = $body['email'];
        $password = $body['password'];

        //Truy vấn lấy thông tin user theo email
        $userQuery = firstRaw("SELECT id, password FROM users WHERE email='$email' AND status=1");

        if (!empty($userQuery)){
            $passwordHash = $userQuery['password'];
            $user_id = $userQuery['id'];
            if (password_verify($password, $passwordHash)){

                //Tạo token login
                $tokenLogin = sha1(uniqid().time());

                //Insert dữ liệu vào bảng login_token
                $dataToken = [
                    'user_id' => $user_id,
                    'token' => $tokenLogin,
                    'create_at' => date('Y-m-d H:i:s')
                ];

                $insertTokenStatus = insert('login_token', $dataToken);
                if ($insertTokenStatus){
                    //Insert token thành công       
                    //Lưu loginToken vào session
                    setSession('loginToken', $tokenLogin);
                    //Chuyển hướng qua trang quản lý users
                    redirect('admin/?module=');
                }

            }else{
                setFlashData('msg', '** Mật khẩu không chính xác!');
                setFlashData('msg_type', 'err');
                setFlashData('old', $body);
               
            }
        }else{
            setFlashData('msg', '** Email chưa được kích hoạt!');
            setFlashData('msg_type', 'err');
            setFlashData('old', $body);
           
        }
    }else{
        setFlashData('msg', '** Vui lòng kiểm tra email và mật khẩu!');
        setFlashData('msg_type', 'err');
        setFlashData('old', $body);
       
    }

    redirect('admin/?module=auth&action=login');
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
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logo-final.png" class="logo-login" alt="">
        <p class="text-center title-login">HỆ THỐNG QUẢN LÝ PHÒNG TRỌ</p>
        <p class="text-center" style="color: #000; margin-bottom: 20px">Hệ thống quản lý phòng trọ cho thuê</p>



        <form action="" method="post">
            
            <div class="form-group">
                <label for="">Email</label> <br />
                <input type="email" name="email" class="" placeholder="Email" value="<?php echo old('email', $old); ?>">
            </div>
            <div class="form-group">
                <label for="">Mật khẩu</label><br />
                <input type="password" name="password" class="" placeholder="Mật khẩu">
            </div>
            <button type="submit" class="btn-login">Đăng nhập</button>
            <hr>
            <p class="text-center"><a href="?module=auth&action=for">Quên mật khẩu?</a></p>
        </form>
        </div>
    </div>
</body>

<?php

layout('footer-login', 'admin');

