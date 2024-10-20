<?php
//File này chứa các hằng số cấu hình
date_default_timezone_set('Asia/Ho_Chi_Minh');

//Thiết lập hằng số cho client
const _MODULE_DEFAULT = 'dashboard'; //Module mặc định
const _ACTION_DEFAULT = 'lists'; //Action mặc định

const _INCODE = true; //Ngăn chặn hành vi truy cập trực tiếp vào file

//Thiết lập host
define('_WEB_HOST_ROOT', 'http://'.$_SERVER['HTTP_HOST'].'/datn'); //Địa chỉ trang chủ

define('_WEB_HOST_ADMIN_TEMPLATE', _WEB_HOST_ROOT.'/templates/admin');

//Thiết lập path
define('_WEB_PATH_ROOT', __DIR__);
define('_WEB_PATH_TEMPLATE', _WEB_PATH_ROOT.'/templates');

//Thiết lập kết nối database

const _HOST = 'localhost';
const _USER = 'root';
const _PASS = '123456'; //Xampp => pass='';
const _DB = 'datn';
const _DRIVER = 'mysql';

//Thiết lập số lượng bản ghi hiển thị trên 1 trang
const _PER_PAGE = 10;