<?php

$body = getBody();
$id = $_GET['id'];

// Lấy chi tiết phiếu chi từ bảng receipt
$paymentDetail = firstRaw("
    SELECT 
        area.tenkhuvuc AS tenkhuvuc,
        payment.id, 
        payment.room_id, 
        room.tenphong AS tenphong, 
        payment.sotien, 
        payment.ghichu, 
        payment.ngaychi, 
        payment.phuongthuc, 
        payment.danhmucchi_id, 
        category_spend.tendanhmuc
    FROM 
        payment
    LEFT JOIN 
        category_spend 
    ON 
        payment.danhmucchi_id = category_spend.id
    LEFT JOIN 
        room 
    ON 
        payment.room_id = room.id
    LEFT JOIN 
        tenant 
    ON 
        room.id = tenant.room_id
        LEFT JOIN 
        area_room 
    ON 
        room.id = area_room.room_id
    LEFT JOIN 
        area 
    ON 
        area_room.area_id = area.id
    WHERE 
        payment.id = $id
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Chi</title>
</head>

<body style="display: flex; justify-content: center; margin-top: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7fafc;">
    <div class="receipt-content" style="width: 30%; height: auto; background: #fff; box-shadow: 1px 1px 10px #ccc; text-align: center; padding: 50px 20px; line-height: 1.2;">
        <img style="width: 150px;" src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logomain.png" alt="Logo">
        <h2 style="font-size: 28px; margin: 10px 0;">Phòng Trọ Thảo Nguyên</h2>
        <!-- <h3 style="margin-top: 10px;">Ngày <?php echo date('d/m/Y'); ?></h3> -->
        <p style="font-size: 14px;">Địa chỉ: 56 - Nam Pháp, Ngô Quyền, Hải Phòng</p>
        <p style="font-size: 18px;">Phiếu <?php echo $paymentDetail['tendanhmuc']; ?></p>

        <div style="margin-top: 20px; text-align: start;">
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px; font-weight: normal;">Họ tên: Người nhận / Cơ sở: ..........................................................................</p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px; font-weight: normal;">Số điện thoại:: .....................................................................</p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px; font-weight: normal;">Địa chỉ: .................................................................................</p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px; font-weight: normal;">Phòng: <?php echo htmlspecialchars($paymentDetail['tenphong'], ENT_QUOTES, 'UTF-8'); ?> - Khu vực: <?php echo htmlspecialchars($paymentDetail['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Số tiền chi: <?php echo number_format($paymentDetail['sotien'], 0, ',', '.'); ?> đ</p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Ghi chú: <?php echo htmlspecialchars($paymentDetail['ghichu'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Ngày chi: <?php echo htmlspecialchars(getDateFormat($paymentDetail['ngaychi'], 'd-m-Y'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Phương thức thanh toán: <?php
                                                                                        if ($paymentDetail['phuongthuc'] == 1) {
                                                                                            echo "Chuyển khoản";
                                                                                        } else {
                                                                                            echo "Tiền mặt";
                                                                                        }
                                                                                        ?></p>
            </div>
        </div>
        <div style="font-size: 12pt; margin: 40px 0">
            <p style="text-align: right;"><i>........, Ngày...... Tháng...... năm 20.........</i></p>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                <!-- BÊN xác nhận -->
                <div>
                    <strong>Xác nhận của người chi tiền </strong><br>
                    <i>Ký và ghi rõ họ tên</i>
                    <div style="padding: 10px; height: 150px; overflow: hidden;">
                        <span></span>
                    </div>
                </div>
                <div>
                    <strong>Xác nhận của người nhận tiền </strong><br>
                    <i>Ký và ghi rõ họ tên</i>
                    <div style="padding: 10px; height: 150px; overflow: hidden;">
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>