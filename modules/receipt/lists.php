<?php

if (!defined('_INCODE'))
    die('Access denied...');

$data = [
    'pageTitle' => 'Quản lý phiếu chi'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>

<?php
layout('navbar', 'admin', $data);
?>

<div class="container-fluid">

    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>
    <div class="box-content box-service">
        <div class="collect-left">
            <div class="collect-left_top">
                <div>
                    <h3>Các danh mục chính </h3>
                    <p></p>
                </div>
            </div>
            <div class="collect-list">
                <div class="collect-item">
                    <div class="service-item_left">
                        <div class="service-item_icon">
                            <a href="<?php echo getLinkAdmin('spend', 'lists'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/payment.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Danh mục chi</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('spend', 'lists'); ?>">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/edit.png" class="image__equipment-img" alt="">
                        </a>
                    </div>
                </div>
                <div class="collect-item">
                    <div class="service-item_left">
                        <div class="service-item_icon">
                            <a href="<?php echo getLinkAdmin('payment', 'lists'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/payment.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Phiếu Chi</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('payment', 'lists'); ?>">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/edit.png" class="image__equipment-img" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

layout('footer', 'admin');
?>