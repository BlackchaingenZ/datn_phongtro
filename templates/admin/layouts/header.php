<?php
// Kiểm tra đăng nhập
if(!isLogin()) { // Khi CSDL không còn dữ liệu trùng khớp thì tự động chuyển hướng sang trang Login
  redirect('?module=auth&action=login');
}else {
  $userId = isLogin()['user_id'];
  $userDetail = getUserInfo($userId); // Lấy thông tin người dùng
}


?>
<!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $data['pageTitle']; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bbootstrap 4 -->
    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/css/adminlte.min.css">

    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/js/bootstrap.min.js">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/plugins/summernote/summernote-bs4.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <script type="text/javascript" src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/ckfinder/ckfinder.js"></script>
 
    <!-- CSS -->
    <link type="text/css" rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/css/style.css?ver=<?php echo rand(); ?>"/>
    <link type="text/css" rel="stylesheet" href="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/PHPExcel.php?ver=<?php echo rand(); ?>"/>

    <!-- AJAX -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://kit.fontawesome.com/98638255fc.js" crossorigin="anonymous"></script>

    
  </head>
  <body class="hold-transition sidebar-mini layout-fixed">

    <header class="header">
        <div class="top-bar">
            <!-- Logo -->
            <a href="<?php echo _WEB_HOST_ROOT.'?module=dashboard' ?>" class="logo">
              <img
                  src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logomain.png"
                  alt=""
                  class="logo__image"
              />
              <p class="logo__title">Phòng Trọ Thảo Nguyên</p>
            </a>

            <div class="nav__list">
                <a href="<?php echo getLinkAdmin('users','account'); ?>" class="nav__item">
                  <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/account.svg" alt="">
                  <span>Tài khoản</span>
                </a>

                <a href="<?php echo getLinkAdmin('auth','logout'); ?>" class="nav__item">
                  <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logout.svg" alt="">
                  <span>Đăng xuất</span>
                </a>
            </div>
        </div>
    </header>


