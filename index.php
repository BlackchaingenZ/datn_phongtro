<?php
session_start();
ob_start();
require_once 'config.php';

//Import phpmailer lib
require_once 'includes/phpmailer/PHPMailer.php';
require_once 'includes/phpmailer/Exception.php';
require_once 'includes/phpmailer/SMTP.php';
require_once 'includes/pdo_connection.php';
require_once 'includes/functions.php';
require_once 'includes/connect.php';
require_once 'includes/database.php';
require_once 'includes/session.php';

$module = _MODULE_DEFAULT; //Biến chứa tên module (chức năng lớn).//
$action = _ACTION_DEFAULT; //Biến chứa tên action (hành động trong module).//
//_MODULE_DEFAULT và _ACTION_DEFAULT: Hằng số chứa giá trị mặc định, được định nghĩa trong config.php.//

//Kiểm Tra và Cập Nhật Module và Action Từ URL//
//Xây Dựng Đường Dẫn Tệp và Nạp Module//

/*
Kiểm tra module và action từ URL:
Nếu URL chứa ?module=... và ?action=..., giá trị được lấy từ $_GET.
Hàm trim() loại bỏ khoảng trắng dư thừa.
Điều kiện is_string() đảm bảo đầu vào là chuỗi, tránh lỗi hoặc tấn công bảo mật.*/
if (!empty($_GET['action'])) {
    if (is_string($_GET['action'])) {
        $action = trim($_GET['action']);
    }
}

/*$path: Tạo đường dẫn đến tệp xử lý tương ứng với module và action, ví dụ:
Với ?module=user&action=login, đường dẫn là modules/user/login.php.
file_exists($path):
Kiểm tra xem tệp có tồn tại không.
Nếu tồn tại, nạp tệp.
Nếu không, nạp tệp lỗi 404 từ modules/errors/404.php.
if (!empty($_GET['module'])){
    if (is_string($_GET['module'])){
        $module = trim($_GET['module']);
    }
}
*/
$path = 'modules/' . $module . '/' . $action . '.php';

if (file_exists($path)) {
    require_once $path;
} else {
    require_once 'modules/errors/404.php';
}
