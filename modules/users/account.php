<?php

if(!defined('_INCODE'))
die('Access denied...');

$data = [
    'pageTitle' => 'Thông tin tài khoản'
];

$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);  
setFlashData('userDetail', $userDetail);

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);
// Xử lý cập nhật

if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    
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
              redirect('?module=users&action=account');

          }else {
              setFlashData('msg', 'Lỗi hệ thống, bạn không thể đổi mật khẩu');
              setFlashData('msg_type', 'err');
              redirect('?module=users&action=account');
          }

    }else {
         // Có lỗi xảy ra
    setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
    setFlashData('msg_type', 'err');
    setFlashData('errors', $errors);
    }

    redirect('?module=users&action=account');
}


$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');

?>
        <div id="MessageFlash">
            <?php getMsg($msg, $msgType);?> 
        </div>
    <div class="container">
        <div class="account"> 
            <div class="account-top">
                <div class="account-top__info">
                    <img src="https://img.freepik.com/premium-photo/simple-smile-happy-man-digital-portrait-bright-red-background_96461-13322.jpg?w=740" alt="" class="account-avt">
                    <!-- <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/avatar3.png" class="account-avt"> -->
                    <div class="account-name">
                        <div class="account-fullname"><?php echo $userDetail['fullname'] ?></div>
                        <p class="account-active"><i class="fa-regular fa-circle-check"></i>Đang hoạt động</p>
                    </div>
                </div>
            </div>
            <div class="account-bot">
                <div class="account-bot__left">
                    <img src="https://img.freepik.com/free-vector/reset-password-concept-illustration_114360-7896.jpg?t=st=1728897207~exp=1728900807~hmac=e6893b42034e2342f8f2c90fcc68ae4ea803a35c7588ca3b271d6aa6d88844ab&w=740" class="bot-left_image" alt="">
                </div>
                <div class="account-bot__right">
                    <div class="account_action">
                        <div class="account-action_left">
                            <p class="accout-action_foget">Đổi mật khẩu</p>
                            <p style="color: black; font-size: 14px">Thay đổi mật khẩu đăng nhập tài khoản</p>
                        </div>
                    </div>
                    <hr />
                    <!-- <div style="background: #ffe2e5; color: #f64e60; padding: 20px 20px"  class="alert alert-danger alert-dismissible fade show alert-hover" role="alert">
                        <div style="display: flex; gap: 20px">
                            <div >
                                <i class="fa-solid fa-circle-info"></i>
                            </div>
                            <p>Nên thay đổi mật khẩu thường xuyên để đảm bảo an toàn cho tài khoản của bạn!</p>
                        </div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div> -->
                    <form class="form-account" style="margin-top: 30px" action="" method="post">                          
                            <div class="form-group">
                                <label style="width: 200px; font-size: 14px; color: black; font-weight: 400;" for="">Mật khẩu mới</label>
                                <input style="border: none; background: #f3f6f9; width: 300px; border-radius: 8px; padding: 0 12px" type="password" name="password" class="" placeholder="Mật khẩu mới" required>
                                
                            </div>
                            <div class="form-group">
                                <label style="width: 200px; font-size: 14px; color: black; font-weight: 400;" for="">Xác thực mật khẩu</label>
                                <input style="border: none; background: #f3f6f9; width: 300px; border-radius: 8px; padding: 0 12px" type="password" name="confirm_password" class="" placeholder="Xác nhận mật khẩu" required>
                            </div>
                            <button type="submit" style="margin-left: 205px; background: #a4a4a4; color: #fff; border: none; font-size: 14px; padding: 8px 14px" class="btn-secondary">Thực hiện</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php

layout('footer', 'admin');
?>

