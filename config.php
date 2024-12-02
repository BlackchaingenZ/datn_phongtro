<?php
//File này chứa các hằng số cấu hình
/*
date_default_timezone_set(): Thiết lập múi giờ mặc định cho ứng dụng.
'Asia/Ho_Chi_Minh': Múi giờ của Việt Nam. Điều này đảm bảo thời gian hiển thị trong hệ thống khớp với múi giờ địa phương.*/
date_default_timezone_set('Asia/Ho_Chi_Minh');

//Thiết lập hằng số cho client
/*
_MODULE_DEFAULT: Module mặc định khi không có module được chỉ định trong URL. Ở đây, module mặc định là dashboard.
_ACTION_DEFAULT: Action mặc định khi không có action được chỉ định trong URL. Action mặc định là lists.
_INCODE: Biến flag để kiểm tra quyền truy cập trực tiếp:
Các file quan trọng trong hệ thống có thể kiểm tra giá trị if (!defined('_INCODE')) exit; để ngăn chặn truy cập trực tiếp. */
const _MODULE_DEFAULT = 'dashboard'; //Module mặc định
const _ACTION_DEFAULT = 'lists'; //Action mặc định
const _INCODE = true; //Ngăn chặn hành vi truy cập trực tiếp vào file

//Thiết lập host
/*
_WEB_HOST_ROOT:
Địa chỉ gốc của website.
Sử dụng $_SERVER['HTTP_HOST'] để lấy tên miền hoặc địa chỉ IP hiện tại.
/datn: Thư mục chứa ứng dụng.
_WEB_HOST_ADMIN_TEMPLATE:
Đường dẫn đến thư mục chứa giao diện quản trị admin (templates/admin).
Giúp tái sử dụng đường dẫn và dễ thay đổi khi cần.

 */
define('_WEB_HOST_ROOT', 'http://' . $_SERVER['HTTP_HOST'] . '/datn'); //Địa chỉ trang chủ
define('_WEB_HOST_ADMIN_TEMPLATE', _WEB_HOST_ROOT . '/templates/admin');

//Thiết lập path
/*_WEB_PATH_ROOT:
Đường dẫn tuyệt đối tới thư mục chứa file hiện tại.
Sử dụng __DIR__, một hằng số PHP trả về đường dẫn của file đang thực thi.
_WEB_PATH_TEMPLATE:
Đường dẫn tuyệt đối tới thư mục templates.
Giúp xác định vị trí lưu trữ giao diện để nạp đúng file.
 */
define('_WEB_PATH_ROOT', __DIR__);
define('_WEB_PATH_TEMPLATE', _WEB_PATH_ROOT . '/templates');

//Thiết lập kết nối database

const _HOST = 'localhost';
const _USER = 'root';
const _PASS = '123456'; //Xampp => pass='';
const _DB = 'datn';
const _DRIVER = 'mysql';

//Thiết lập số lượng bản ghi hiển thị trên 1 trang
/*_PER_PAGE: Số lượng bản ghi hiển thị trên mỗi trang khi phân trang.
Ví dụ: Nếu tổng cộng có 50 bản ghi và _PER_PAGE = 10, hệ thống sẽ chia thành 5 trang.*/
const _PER_PAGE = 10;
