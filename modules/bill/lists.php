<?php

if (!defined('_INCODE'))
    die('Access denied...');

$data = [
    'pageTitle' => 'Quản lý Khu vực'
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
                            <a href="<?php echo getLinkAdmin('bill', 'bills'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/bill.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Hoá đơn</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('bill', 'bills'); ?>">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/edit.png" class="image__equipment-img" alt="">
                        </a>
                    </div>
                </div>


                <div class="collect-item">
                    <div class="service-item_left">
                        <div class="service-item_icon">
                            <a href="<?php echo getLinkAdmin('collect', 'lists'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/bill.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Danh mục thu</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('collect', 'lists'); ?>">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/edit.png" class="image__equipment-img" alt="">
                        </a>
                    </div>
                </div>
                <div class="collect-item">
                    <div class="service-item_left">
                        <div class="service-item_icon">
                            <a href="<?php echo getLinkAdmin('receipt', 'lists'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/bill.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Phiếu Thu</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('receipt', 'receipts'); ?>">
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