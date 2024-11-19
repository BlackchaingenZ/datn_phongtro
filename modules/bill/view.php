<?php

$body = getBody();
$id = $_GET['id'];

$billDetail  = firstRaw("SELECT * FROM bill WHERE id=$id");
$date = firstRaw("SELECT MONTH(create_at) AS month, YEAR(create_at) AS year FROM bill WHERE id=$id");
$roomId = $billDetail['room_id'];

$roomtDetail = firstRaw("
    SELECT room.*, cost.giathue, 
           GROUP_CONCAT(tenant.tenkhach SEPARATOR ', ') AS tenkhach
    FROM room
    JOIN cost_room ON room.id = cost_room.room_id
    JOIN cost ON cost_room.cost_id = cost.id
    LEFT JOIN tenant ON room.id = tenant.room_id
    LEFT JOIN contract_tenant ON tenant.id = contract_tenant.tenant_id_1
    LEFT JOIN contract ON contract_tenant.contract_id_1 = contract.id
    WHERE room.id = $roomId
      AND contract.trangthaihopdong = 1
    GROUP BY room.id
");





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
        <h2 style="font-size: 28px; margin: 10px 0;">Hóa đơn tiền thuê phòng trọ Thảo Nguyên</h2>
        <h3 style="margin-top: 10px;">Tháng <?php echo $date['month'] ?>/<?php echo $date['year'] ?></h3>
        <p style="font-size: 14px;">Địa chỉ: 56 - Nam Pháp, Ngô Quyền, Hải Phòng</p>
        <p>Mã hóa đơn: <b style="color: red; font-size: 18px"><?php echo $billDetail['mahoadon'] ?></b></p>
        <div class="rowTwo" style="display: flex; justify-content: space-around; margin-top: 0px;">
            <p style="font-size: 14px;">Tên khách: <b><?php echo $roomtDetail['tenkhach'] ?></b></p>
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
                <td><?php echo $billDetail['chuky'] == 0 ? '0' : $billDetail['chuky'] ?> tháng x <?php echo number_format($roomtDetail['giathue'], 0, ',', '.') ?> đ + <?php echo $billDetail['songayle'] ? $billDetail['songayle'] : '0' ?> ngày lẻ</td>
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
                <td style="font-size: 14px;"><b>Cộng thêm</b></td>
                <td><b><?php echo number_format($billDetail['nocu'], 0, ',', '.') ?> đ</b></td>
                <td style="font-size: 16px;"><b><?php echo number_format($billDetail['nocu'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Tổng tiền</b></td>
                <td colspan="2" style="text-align: right; font-size: 18px; color: #dc3545;"><b><?php echo number_format($billDetail['tongtien'], 0, ',', '.') ?> đ</b></td>
            </tr>

            <tr style="background: #4caf5021">
                <td style="font-size: 14px;"><b>Đã thu</b></td>
                <td colspan="2" style="text-align: right; font-size: 18px; color: #258e0c;"><b><?php echo number_format($billDetail['sotiendatra'], 0, ',', '.') ?> đ</b></td>
            </tr>

            <tr>
                <td style="font-size: 14px;"><b>Còn lại</b></td>
                <td colspan="2" style="text-align: right; font-size: 18px; color: #dc3545;"><b><?php echo number_format($billDetail['sotienconthieu'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>Thanh toán</b></td>
                <td colspan="2">
                    <div style="display: flex; gap: 50px">
                        <img style="width: 200px; height: 200px;" src="/datn/templates/admin/assets/img/QR.jpg" alt="">
                        <div>
                            <p style="color: red"><i><b>Lưu ý:</b></i></p>
                            <p>Nội dung chuyển khoản: <b><i>"THANH TOAN + MÃ HOÁ ĐƠN"</i></b></p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>