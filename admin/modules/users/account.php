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
              redirect('admin/?module=users&action=account');

          }else {
              setFlashData('msg', 'Lỗi hệ thống, bạn không thể đổi mật khẩu');
              setFlashData('msg_type', 'err');
              redirect('/admin/?module=users&action=account');
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
                    <img src="https://quanlytro.me/images/avatar.png" alt="" class="account-avt">
                    <div class="account-name">
                        <div class="account-fullname"><?php echo $userDetail['fullname'] ?></div>
                        <p class="account-active"><i class="fa-regular fa-circle-check"></i>Đang hoạt động</p>
                    </div>
                </div>
            </div>
            <div class="account-bot">
                <div class="account-bot__left">
                    <img src="https://quanlytro.me/images/home_feature/feature-3-thanh-toan-online-doc.jpg" class="bot-left_image" alt="">
                </div>
                <div class="account-bot__right">
                    <div class="account_action">
                        <div class="account-action_left">
                            <p class="accout-action_foget">Đổi mật khẩu</p>
                            <p style="color: #b5b5c3; font-size: 14px">Thay đổi mật khẩu đăng nhập tài khoản</p>
                        </div>
                    </div>
                    <hr />
                    <div style="background: #ffe2e5; color: #f64e60; padding: 20px 20px"  class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div style="display: flex; gap: 20px">
                            <div >
                                <i class="fa-solid fa-circle-info"></i>
                            </div>
                            <p>Bạn nên thay đổi mật khẩu định kỳ để đảm bảo an toàn hơn cho tài khoản của bạn!</p>
                        </div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form class="form-account" style="margin-top: 30px" action="" method="post">                          
                            <div class="form-group">
                                <label style="width: 200px; font-size: 14px; color: #3F4254; font-weight: 400;" for="">Mật khẩu mới</label>
                                <input style="border: none; background: #f3f6f9; width: 300px; border-radius: 8px; padding: 0 12px" type="password" name="password" class="" placeholder="New password">
                                
                            </div>
                            <div class="form-group">
                                <label style="width: 200px; font-size: 14px; color: #3F4254; font-weight: 400;" for="">Xác thực mật khẩu</label>
                                <input style="border: none; background: #f3f6f9; width: 300px; border-radius: 8px; padding: 0 12px" type="password" name="confirm_password" class="" placeholder="Verify password">
                            </div>
                            <button type="submit" style="margin-left: 205px; background: #1bc5bd; color: #fff; border: none; font-size: 14px; padding: 8px 14px" class="btn">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php

layout('footer', 'admin');
?>

