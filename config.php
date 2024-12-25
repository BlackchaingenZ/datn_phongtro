<?php

date_default_timezone_set('Asia/Ho_Chi_Minh');


const _MODULE_DEFAULT = 'dashboard'; 
const _ACTION_DEFAULT = 'lists'; 
const _INCODE = true; 

define('_WEB_HOST_ROOT', 'http://' . $_SERVER['HTTP_HOST'] . '/datn'); 
define('_WEB_HOST_ADMIN_TEMPLATE', _WEB_HOST_ROOT . '/templates/admin');


define('_WEB_PATH_ROOT', __DIR__);
define('_WEB_PATH_TEMPLATE', _WEB_PATH_ROOT . '/templates');

//Thiết lập kết nối database

const _HOST = 'localhost';
const _USER = 'root';
const _PASS = '123456'; //Xampp => pass='';
const _DB = 'datn';
const _DRIVER = 'mysql';

const _PER_PAGE = 10;
