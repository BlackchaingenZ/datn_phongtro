<?php
if(!isLogin()) {
    redirect('admin/?module=auth&action=login');
} 

$data = [
    'pageTitle' => 'Tổng quan'
];

$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);  
$roomId = $userDetail['room_id'];


if($userDetail['group_id'] == 7) {
    layout('header', 'admin', $data);
    layout('breadcrumb', 'admin', $data);
} else {
    layout('header-tenant', 'admin', $data);
    layout('sidebar', 'admin', $data);
}




?>

<?php
if($userDetail['group_id'] == 7) {
    layout('navbar', 'admin', $data);
}
?>
<?php 
if($userDetail['group_id'] == 7) {
    ?>
        <div class="container-fluid">
            <div class="box-content dashboard-content">
                <div class="content-left">

                    <div class="total-room">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/content-left-icon.svg" alt="">
                            </div>
                            <p class="total-desc">Tổng số phòng</p>
                        </div>
                        <?php $totalRoom = getRows("SELECT id FROM room") ?>
                        <p class="total-count"><?php echo $totalRoom ?></p>
                    </div>
                    
                    <div class="content-left-child">
                        <div class="child-one">
                            <div class="content-left-title">
                                <div class="content-left-icon">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/st.svg" alt="">
                                </div>
                                <p class="total-desc">Tổng số phòng đang cho thuê</p>
                            </div>
                            <?php $totalRoomThue = getRows("SELECT id FROM room WHERE trangthai=1") ?>
                            <?php $ratio1 = ($totalRoomThue / $totalRoom) * 100; ?>
                            <?php $ratio1 = number_format($ratio1, 2) ?>
                            <p class="total-count"><?php echo $totalRoomThue ?> <span style="font-size: 16px">(<?php echo $ratio1 ?>%)</span></p>
                            <a href=""><div class="dashboard-link"></div></a>
                        </div>

                        <div class="child-two">
                            <div class="content-left-title">
                                <div class="content-left-icon">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/st.svg" alt="">
                                </div>
                                <p class="total-desc">Tổng số phòng đang trống</p>
                            </div>
                            <?php $totalRoomTrong = getRows("SELECT id FROM room WHERE trangthai=0") ?>
                            <?php $ratio2 = ($totalRoomTrong / $totalRoom) * 100; ?>
                            <?php $ratio2 = number_format($ratio2, 2) ?>
                            <p class="total-count"><?php echo $totalRoomTrong ?> <span style="font-size: 16px">(<?php echo $ratio2 ?>%)</span></p>
                            <div class="dashboard-link"><a href=""></a></div>
                        </div>

                    </div>


                    <div class="content-left-child">
                        <div class="child-three">
                            <div class="content-left-title">
                                <div class="content-left-icon">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/st.svg" alt="">
                                </div>
                                <p class="total-desc">Tổng số phòng đang trong hạn hợp đồng</p>
                            </div>
                            <?php $contractTotal = getRows("SELECT id From contract") ?>
                            <?php $contractPass = getRows("SELECT id From contract where trangthaihopdong = 1") ?>
                            <?php $ratioContract1 = ($contractPass / $contractTotal) * 100; ?>
                            <?php $ratioContract1 = number_format($ratioContract1, 2) ?>
                            <p class="total-count"><?php echo $contractPass ?> <span style="font-size: 16px">(<?php echo $ratioContract1 ?>%)</span></p>
                            <a href=""><div class="dashboard-link"></div></a>
                        </div>

                        <div class="child-four">
                            <div class="content-left-title">
                                <div class="content-left-icon">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/st.svg" alt="">
                                </div>
                                <p class="total-desc">Tổng số phòng đã hết hạn hợp đồng</p>
                            </div>
                            <?php $contractFail = getRows("SELECT id From contract where trangthaihopdong = 0") ?>
                            <?php $ratioContract2 = ($contractFail / $contractTotal) * 100; ?>
                            <?php $ratioContract2 = number_format($ratioContract2, 2) ?>
                            <p class="total-count"><?php echo $contractFail ?> <span style="font-size: 16px">(<?php echo $ratioContract2 ?>%)</span></p>
                            <div class="dashboard-link"><a href=""></a></div>
                        </div>

                    </div>

                </div>

                <div class="content-right">
                        <div class="child-five">
                            <div class="content-left-title">
                                <div class="content-left-icon background-icon">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/st.svg" alt="">
                                </div>
                                <p class="total-desc">Tổng số khách thuê</p>
                            </div>
                            <?php $toTalTenant = getRows("SELECT id FROM tenant"); ?>
                            <p class="total-count"><?php echo $toTalTenant ?></p>
                            <a href=""><div class="dashboard-link"></div></a>
                        </div>

                        <div class="child-six">
                            <div class="content-left-title">
                                <div class="content-left-icon background-icon">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/st.svg" alt="">
                                </div>
                                <?php
                                    $listAllcontract = getRaw("SELECT *, contract.id, tenphong, tenkhach, giathue, tiencoc, contract.ngayvao as ngayvaoo, contract.ngayra as thoihanhopdong, zalo FROM contract 
                                    INNER JOIN room ON contract.room_id = room.id
                                    INNER JOIN tenant ON contract.tenant_id = tenant.id");
                                    
                                    
                                    // Danh sách các hợp đồng sắp hết hạn
                                    $expiringContracts = [];
                                    
                                    // Thêm các hợp đồng sắp hết hạn vào danh sách
                                    $countContract = 0;
                                    foreach ($listAllcontract as $contract) {
                                        $daysUntilExpiration = getContractStatus($contract['thoihanhopdong']);
                                        if ($daysUntilExpiration == "Sắp hết hạn") {
                                            $expiringContracts[] = $contract;
                                            $countContract++;
                                        }
                                    }
                                    
                                ?>
                                <p class="total-desc">Số phòng sắp hết hạn hợp đồng</p>
                            </div>
                            <?php 
                                $ratio3 = ($countContract / $contractTotal) * 100; 
                                $ratio3 = number_format($ratio3, 2);
                            ?>
                            <p class="total-count"><?php echo $countContract; ?><span style="font-size: 16px">(<?php echo $ratio3 ?>%)</span></p>
                            <a href=""><div class="dashboard-link"></div></a>
                        </div>

                        <div class="child-seven">
                            <div class="content-left-title">
                                <div class="content-left-icon background-icon">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/st.svg" alt="">
                                </div>
                                <p class="total-desc">Đang cập nhật</p>
                            </div>
                            <p class="total-count">...</p>
                        </div>
                        
                </div>
            </div>
        </div>
    <?php
} else {
    $billCount = getRows("SELECT id FROM bill WHERE room_id = $roomId");
    if($billCount > 0) {
        $billNear = firstRaw("SELECT * FROM bill WHERE room_id = $roomId ORDER BY create_at DESC LIMIT 1");
        $id = $billNear['id'];  
        $date = firstRaw("SELECT MONTH(create_at) AS month, YEAR(create_at) AS year FROM bill WHERE id=$id");
        $tenantId = $billNear['tenant_id'];
    
        $tenantDetail = firstRaw("SELECT * FROM tenant WHERE id = $tenantId");
        $roomtDetail = firstRaw("SELECT * FROM room WHERE id = $roomId");
        $msg =getFlashData('msg');
        $msgType = getFlashData('msg_type');
        ?>
            <!-- <body style="display: flex; justify-content: center; margin-top: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7fafc;"> -->
    <h3 style="padding: 20px 0 20px 20px">Hóa đơn thu tiền nhà T<?php echo $date['month'] ?>/<?php echo $date['year'] ?> - <?php echo $billNear['trangthaihoadon'] == 0 ? '<span class="btn-kyhopdong-err">Chưa thanh toán</span>' : '<span class="btn-kyhopdong-suc">Đã thanh toán</span>' ?></h3>
    <div class="bill-content" style="margin: 0 auto;width: 60%; height: auto; background: #fff; box-shadow: 1px 1px 10px #ccc; text-align: center; padding: 50px 20px; line-height: 1.2;">
        <img style="width: 150px; " src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logo-final.png" alt="">
        <h2 style="font-size: 28px; margin: 10px 0;">Hóa đơn tiền thuê nhà</h2>
        <h4 style="margin-top: 10px; margin-bottom: 15px">Tháng <?php echo $date['month'] ?>/<?php echo $date['year'] ?></h4>
        <p style="font-size: 14px;">Địa chỉ: 597 - Nguyễn Bỉnh Khiêm, Đằng Lâm, Hải An, Hải Phòng</p>
        <p>Mã hóa đơn: <b style="color: red; font-size: 18px"><?php echo $billNear['mahoadon'] ?></b></p>
        <div class="rowOne" style="display: flex; justify-content: space-around;">
            <p style="font-size: 14px; margin: 0;">Kính gửi: <b><?php echo $tenantDetail['tenkhach'] ?></b></p>
            <p style="font-size: 14px; margin: 0">Số điện thoại: <b>0<?php echo $tenantDetail['sdt'] ?></b></p>
        </div>
        <div class="rowTwo" style="display: flex; justify-content: space-around; margin-top: 10px;">
            <p style="font-size: 14px;">Đơn vị: <b><?php echo $roomtDetail['tenphong'] ?></b></b></p>
            <p style="font-size: 14px;">Lý do thu tiền: <b>Thu tiền hàng tháng</b></p>
        </div>

        <table border="1" cellspacing="0" width="100%" cellpadding="10" style="text-align: start;">
            <tr>
                <td><b>Khoản thu</b></td>
                <td><b>Chi tiết</b></td>
                <td><b>Thành tiền</b></td>
            </tr>
            <tr>
                 <td style="font-size: 14px;"><b>Tiền phòng</b></td>
                <td><?php echo $billNear['chuky'] == 0 ? '0' : $billNear['chuky'] ?>tháng x <?php echo number_format($roomtDetail['giathue'], 0, ',', '.') ?> đ + <?php echo $billNear['songayle'] ? $billNear['songayle']: '0' ?> ngày lẻ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billNear['tienphong'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tiền điện</b></td>
                <td>Tính tiền: (Số cũ: <?php echo $billNear['sodiencu'] ?> - Số mới: <?php echo $billNear['sodienmoi'] ?>) x 4.000đ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billNear['tiendien'], 0, ',', '.') ?> đ</b></td>
                
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tiền nước</b></td>
                <td>Tính tiền: (Số cũ: <?php echo $billNear['sonuoccu'] ?> - Số mới: <?php echo $billNear['sonuocmoi'] ?>) x 20.000đ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billNear['tiennuoc'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tiền rác (người)</b></td>
                <td>Tính tiền: <?php echo $billNear['songuoi'] ?> x 10.000đ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billNear['tienrac'], 0, ',', '.') ?>đ</b></td>
            </tr>
            
            <tr>
                <td style="font-size: 14px;"><b>Tiền Wifi</b></td>
                <td>Tính tiền: <?php echo $billNear['chuky'] ?> x 50.000đ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billNear['tienmang'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Nợ cũ</b></td>
                <td><b><?php echo number_format($billNear['nocu'], 0, ',', '.') ?> đ</b></td>
                <td style="font-size: 16px;"><b><?php echo number_format($billNear['nocu'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tổng tiền</b></td>
                <td colspan="2" style="text-align: right; font-size: 18px; color: #dc3545;"><b><?php echo number_format($billNear['tongtien'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Thanh toán</b></td>
                <td colspan="2">
                   <div style="display: flex; gap: 50px">
                        <img style="width: 200px; height: 200px;" src="https://jeju.com.vn/wp-content/uploads/2020/05/vnpay-qr-23-06-2020-2.jpg" alt="">
                        <div>
                            <p style="color: red"><i><b>Lưu ý:</b></i></p>
                            <p>Nội dung thanh toán: <b><i>Mã hóa đơn + Tên phòng</i></b></p>
                        </div>
                   </div>
                </td>
            </tr>
        </table>

    </div>
    
<!-- </body> -->
</html>
        <?php
          
        ?>
        <div id="MessageFlash">          
            <?php getMsg($msg, $msgType);?>          
        </div>
        <?php
    } else {
        echo '<img style="width: 70%" src="https://i.pinimg.com/564x/7d/dc/51/7ddc51549b1d53cb4ffe482f872e4c11.jpg" />';
    }
}

layout('footer', 'admin');