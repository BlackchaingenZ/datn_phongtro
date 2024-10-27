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
                <!-- Danh mục khu vực -->
                <div class="collect-item">
                    <div class="service-item_left">
                        <div class="service-item_icon">
                            <a href="<?php echo getLinkAdmin('area', 'listarea'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/area.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Thêm khu vực</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('area', 'listarea'); ?>">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/edit.png" class="image__equipment-img" alt="">
                        </a>
                    </div>
                </div>

                <!-- Phân bổ khu vực -->
                <div class="collect-item">
                    <div class="service-item_left">
                        <div class="service-item_icon">
                            <a href="<?php echo getLinkAdmin('area', 'applyarea'); ?>">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/area.svg" alt="">

                            </a>
                        </div>
                        <div>
                            <h6>Áp dụng</h6>
                            <i>Đang áp dụng cho hệ thống</i>
                        </div>
                    </div>
                    <div class="service-item_right">
                        <a class="edit" href="<?php echo getLinkAdmin('area', 'applyarea'); ?>">
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