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
//Biến chứa tên module (chức năng lớn)
//Biến chứa tên action (hành động trong module)
//_MODULE_DEFAULT và _ACTION_DEFAULT: Hằng số chứa giá trị mặc định, được định nghĩa trong config.php
//Nếu không có module hoặc action được truyền vào URL, sẽ sử dụng giá trị mặc định
$module = _MODULE_DEFAULT;
$action = _ACTION_DEFAULT;
//Kiểm tra xem có module và action được truyền vào URL không
//Nếu có, gán giá trị mới cho biến $module và $action
//Hàm trim(): Loại bỏ khoảng trắng ở đầu và cuối chuỗi
//Hàm is_string(): Kiểm tra xem biến có phải kiểu string không
//Hàm empty(): Kiểm tra xem biến có rỗng không
//$_GET: Mảng chứa các biến được truyền vào URL

if (!empty($_GET['module'])){
    if (is_string($_GET['module'])){
        $module = trim($_GET['module']);
    }
}
//Kiểm tra action
//Nếu action không được truyền vào URL, sẽ sử dụng action mặc định
//Nếu có, gán giá trị mới cho biến $action
//Hàm trim(): Loại bỏ khoảng trắng ở đầu và cuối chuỗi
//Hàm is_string(): Kiểm tra xem biến có phải kiểu string không
//Hàm empty(): Kiểm tra xem biến có rỗng không
//$_GET: Mảng chứa các biến được truyền vào URL
//Hàm file_exists(): Kiểm tra xem file có tồn tại không
//Hàm require_once(): Nạp file vào chương trình
//Nếu file tồn tại, nạp file đó vào chương trình
//Nếu không tồn tại, nạp file 404.php trong thư mục errors vào chương trình
//404.php: File hiển thị thông báo lỗi khi truy cập vào module không tồn tại
if (!empty($_GET['action'])){
    if (is_string($_GET['action'])){
        $action = trim($_GET['action']);
    }
}

$path = 'modules/'.$module.'/'.$action.'.php';

if (file_exists($path)){
    require_once $path;
}else{
    require_once 'modules/errors/404.php';
}