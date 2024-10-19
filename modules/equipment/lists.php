<?php

if (!defined('_INCODE'))
    die('Access denied...');

$data = [
    'pageTitle' => 'Quản lý cơ sở vật chất'
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
                <!-- Danh mục cơ sở vật chất -->
                <div class="collect-item">
                    <div class="service-item_left">
                        <div class="service-item_icon">
                            <a href="<?php echo getLinkAdmin('equipment', 'listequipment'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/service-icon.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Danh mục cơ sở vật chất</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('equipment', 'listequipment'); ?>">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/edit.png" class="image__equipment-img" alt="">
                        </a>
                    </div>
                </div>

                <!-- Phân bổ cơ sở vật chất -->
                <div class="collect-item">
                    <div class="service-item_left">
                        <div class="service-item_icon">
                            <a href="<?php echo getLinkAdmin('equipment', 'listdistribute'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/service-icon.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Phân bổ cơ sở vật chất</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('equipment', 'listdistribute'); ?>">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/edit.png" class="image__equipment-img" alt="">
                        </a>
                    </div>
                </div>
            </div>



        </div>
    </div>
    <div>
    </div>

    <?php

    layout('footer', 'admin');
    ?>