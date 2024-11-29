 <?php
  $userId = isLogin()['user_id'];
  $userDetail = getUserInfo($userId);

  // Kiểm tra phân quyền
  $userId = isLogin()['user_id'];
  $userDetail = getUserInfo($userId);
  $roomId  = $userDetail['room_id'];

  ?>


 <!-- Main Sidebar Container -->
 <aside class="main-sidebar sidebar-dark-primary elevation-4">
   <!-- Brand Logo -->
   <a href="<?php echo _WEB_HOST_ROOT . '/?module=dashboard'; ?>" class="brand-link">
     <span class="brand-text font-weight-light text-uppercase">Màn hình chính</span>
   </a>

   <!-- Sidebar -->
   <div class="sidebar">
     <!-- Sidebar user panel (optional) -->
     <div class="user-panel mt-3 pb-3 mb-3 d-flex">
       <div class="image">
         <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/profile.png" class="img-circle elevation-2" alt="User Image">
       </div>
       <div class="info">
         <a href="#" class="d-block"><?php echo $userDetail['fullname'];  ?></a>
       </div>
     </div>

     <!-- Sidebar Menu -->
     <nav class="mt-2">
       <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
         <li class="nav-item">
           <a href="<?php echo  _WEB_HOST_ROOT . '/?module=dashboard' ?>" class="nav-link  <?php echo (activeMenuSidebar('')) ? 'active' : false;  ?>">
             <i class="nav-icon fas fa-home"></i>
             <p>
               Tổng quan
             </p>
           </a>
         </li>

         <li class="nav-item has-treeview <?php echo activeMenuSidebar('bill') ? 'menu-open' : false; ?>">
           <a href="#" class="nav-link <?php echo activeMenuSidebar('bill') ? 'active' : false; ?>">
             <i class="nav-icon fas fa-solid fa-graduation-cap"></i>
             <p>
               <?php $num = getRows("SELECT id FROM bill WHERE room_id = $roomId AND trangthaihoadon = 0") ?>
               Quản lý hóa đơn
               <i class="right fas fa-angle-left"></i>
               <span class="badge badge-danger"><?php echo $num ?></span>
             </p>
           </a>
           <ul class="nav nav-treeview">

             <li class="nav-item">
               <a href="<?php echo getLinkAdmin('bill', 'history'); ?>" class="nav-link">
                 <i class="far fa-circle nav-icon"></i>
                 <p>Lịch sử hóa đơn</p>
               </a>
             </li>
           </ul>
         </li>

         <li class="nav-item has-treeview <?php echo activeMenuSidebar('contract') ? 'menu-open' : false; ?>">
           <a href="#" class="nav-link <?php echo activeMenuSidebar('contract') ? 'active' : false; ?>">
             <i class="nav-icon fas fa-solid fa-graduation-cap"></i>
             <p>
               Quản lý hợp đồng
               <i class="right fas fa-angle-left"></i>
             </p>
           </a>
           <ul class="nav nav-treeview">

             <li class="nav-item">
               <a href="<?php echo getLinkAdmin('contract', 'view-client'); ?>" class="nav-link">
                 <i class="far fa-circle nav-icon"></i>
                 <p>Xem hợp đồng</p>
               </a>
             </li>
           </ul>
         </li>
       </ul>
     </nav>
     <!-- /.sidebar-menu -->
   </div>
   <!-- /.sidebar -->
 </aside>

 <div class="content-wrapper">