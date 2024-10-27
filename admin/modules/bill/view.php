<?php
    
    $body = getBody();
    $id = $_GET['id'];

    $billDetail  = firstRaw("SELECT * FROM bill WHERE id=$id");
    $date = firstRaw("SELECT MONTH(create_at) AS month, YEAR(create_at) AS year FROM bill WHERE id=$id");
    $tenantId = $billDetail['tenant_id'];
    $roomId = $billDetail['room_id'];

    $tenantDetail = firstRaw("SELECT * FROM tenant WHERE id = $tenantId");
    $roomtDetail = firstRaw("SELECT * FROM room WHERE id = $roomId");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn thu tiền hàng tháng</title>
</head>
<body style="display: flex; justify-content: center; margin-top: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7fafc;">
    <div class="bill-content" style="width: 60%; height: auto; background: #fff; box-shadow: 1px 1px 10px #ccc; text-align: center; padding: 50px 20px; line-height: 1.2;">
        <img style="width: 150px; " src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logo-final.png" alt="">
        <h2 style="font-size: 28px; margin: 10px 0;">Hóa đơn tiền thuê nhà</h2>
        <h3 style="margin-top: 10px;">Tháng <?php echo $date['month'] ?>/<?php echo $date['year'] ?></h3>
        <p style="font-size: 14px;">Địa chỉ: 597 - Nguyễn Bỉnh Khiêm, Đằng Lâm, Hải An, Hải Phòng</p>
        <p>Mã hóa đơn: <b style="color: red; font-size: 18px"><?php echo $billDetail['mahoadon'] ?></b></p>
        <div class="rowOne" style="display: flex; justify-content: space-around;">
            <p style="font-size: 14px; margin: 0;">Kính gửi: <b><?php echo $tenantDetail['tenkhach'] ?></b></p>
            <p style="font-size: 14px; margin: 0">Số điện thoại: <b>0<?php echo $tenantDetail['sdt'] ?></b></p>
        </div>
        <div class="rowTwo" style="display: flex; justify-content: space-around; margin-top: 0px;">
            <p style="font-size: 14px;">Đơn vị: <b><?php echo $roomtDetail['tenphong'] ?></b></p>
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
                <td><?php echo $billDetail['chuky'] == 0 ? '0' : $billDetail['chuky'] ?>tháng x <?php echo number_format($roomtDetail['giathue'], 0, ',', '.') ?> đ + <?php echo $billDetail['songayle'] ? $billDetail['songayle']: '0' ?> ngày lẻ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billDetail['tienphong'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tiền điện (KWh)</b></td>
                <td>Tính tiền: (Số cũ: <?php echo $billDetail['sodiencu'] ?> - Số mới: <?php echo $billDetail['sodienmoi'] ?>) x 4.000đ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billDetail['tiendien'], 0, ',', '.') ?> đ</b></td>
                
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tiền nước</b></td>
                <td>Tính tiền: (Số cũ: <?php echo $billDetail['sonuoccu'] ?> - Số mới: <?php echo $billDetail['sonuocmoi'] ?>) x 20.000đ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billDetail['tiennuoc'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tiền rác (người)</b></td>
                <td>Tính tiền: <?php echo $billDetail['songuoi'] ?> người x 10.000đ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billDetail['tienrac'], 0, ',', '.') ?> đ</b></td>
            </tr>
            
            <tr>
                <td style="font-size: 14px;"><b>Tiền Wifi</b></td>
                <td>Tính tiền: <?php echo $billDetail['chuky'] ?> tháng x 50.000đ</td>
                <td style="font-size: 16px;"><b><?php echo number_format($billDetail['tienmang'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Nợ cũ</b></td>
                <td><b><?php echo number_format($billDetail['nocu'], 0, ',', '.') ?> đ</b></td>
                <td style="font-size: 16px;"><b><?php echo number_format($billDetail['nocu'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tổng tiền</b></td>
                <td colspan="2" style="text-align: right; font-size: 18px; color: #dc3545;"><b><?php echo number_format($billDetail['tongtien'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Thanh toán</b></td>
                <td colspan="2">
                   <div style="display: flex; gap: 50px">
                        <img style="width: 200px; height: 200px;" src="https://jeju.com.vn/wp-content/uploads/2020/05/vnpay-qr-23-06-2020-2.jpg" alt="">
                        <div>
                            <p style="color: red"><i><b>Lưu ý:</b></i></p>
                            <p>Nội dung thanh toán: <b><i>Mã hóa đơn + Tên phòng + Tháng</i></b></p>
                        </div>
                   </div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>