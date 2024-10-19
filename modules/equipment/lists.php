<?php

if(!defined('_INCODE'))
die('Access denied...');

$data = [
    'pageTitle' => 'Quản lý cơ sở vật chất'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>

<?php
layout('navbar', 'admin', $data);
?>

<div class="container-fluid">

    <div id="MessageFlash">          
        <?php getMsg($msg, $msgType);?>          
    </div>



    <div class="box-content box-service">
        <div class="collect-left">
            <div class="collect-left_top">
                <div>
                    <h3>Các danh mục chính </h3>
                    <p></p>
                </div>
            </div>
            <div class="category-container">
        <!-- Gọi tệp cụ thể trong thư mục equipment -->
        <a href="<?php echo getLinkAdmin('equipment', 'listequipment'); ?>" class="category">
            Danh mục cơ sở vật chất
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/facilities.png" class="image__equipment-img" alt="">
        </a>
        <!-- Gọi tệp cụ thể trong thư mục allocate -->
        <a href="<?php echo getLinkAdmin('equipment', 'listdistribute'); ?>" class="category">
            Phân bổ cơ sở vật chất
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/facilities.png" class="image__equipment-img" alt="">
        </a>
    </div>
    </div>
        </div>
    <div>
</div>

<?php

layout('footer', 'admin');
?>