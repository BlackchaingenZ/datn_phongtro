
<!-- Main content -->
<div class="">
  <section class="content">
    <div class="container-fluid">
      <div class="menu__list">
        <!-- Item 1 -->
        <a href="<?php echo getLinkAdmin('room') ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/apartment.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý phòng</p>
          </div>
        </a>

        <!-- Item 2 -->
        <a href="<?php echo getLinkAdmin('area') ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/area.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý khu vực</p>
          </div>
        </a>
        <!-- Item 3 -->
        <a href="<?php echo getLinkAdmin('equipment'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/equipment.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý cơ sở vật chất</p>
          </div>
        </a>

        <!-- Item 4 -->
        <a href="<?php echo getLinkAdmin('cost'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/value.png" class="menu__item-image" alt="">
            <p class="menu__item-title"> Quản lý Bảng giá</p>
          </div>
        </a>

        <!-- Item 5 -->
        <a href="<?php echo getLinkAdmin('tenant') ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/clients.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý khách thuê</p>
          </div>
        </a>

        <!-- Item 6 -->
        <a href="<?php echo getLinkAdmin('contract'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/contracts.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý hợp đồng</p>
          </div>
        </a>

        <!-- Item 7 -->
        <a href="<?php echo getLinkAdmin('services'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/services.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý dịch vụ</p>
          </div>
        </a>

        <!-- Item 8 -->
        <a href="<?php echo getLinkAdmin('bill'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/receipt.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý phiếu thu</p>
          </div>
        </a>
        <a href="<?php echo getLinkAdmin('receipt'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/payment.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý phiếu chi</p>
          </div>
        </a>


        <!-- Item 9 -->
        <a href="<?php echo getLinkAdmin('sumary'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/sum.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Báo cáo thu chi</p>
          </div>
        </a>

        <!-- Item 10 -->

        <a href="<?php echo getLinkAdmin('sum'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/report.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Báo cáo tổng hợp</p>
          </div>
        </a>
        <!-- Item 11 -->
        <a href="<?php echo getLinkAdmin('users'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Người dùng hệ thống</p>
          </div>
        </a>

        <!-- <a href="<?php echo getLinkAdmin('city'); ?>" class="link__menu ">
          <div class="menu__item">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/user.png" class="menu__item-image" alt="">
            <p class="menu__item-title">Quản lý thiết bị 1</p>
          </div>
        </a> -->

      </div>
    </div>
    <div class="container-box text-center">
      <h1 class="m-0 text-dark"><?php echo $data['pageTitle']; ?></h1>
    </div>
  </section>
</div>