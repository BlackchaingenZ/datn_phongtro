<?php

$body = getBody();
$id = $_GET['id'];

// Lấy chi tiết phiếu thu từ bảng receipt
$receiptDetail = firstRaw("
    SELECT 
        bill.thang AS thang,
        tenant.sdt AS sdt,
        tenant.diachi AS diachi,
        area.tenkhuvuc AS tenkhuvuc,
        receipt.id, 
        receipt.room_id, 
        room.tenphong AS tenphong, 
        receipt.sotien, 
        receipt.ghichu, 
        receipt.ngaythu, 
        receipt.phuongthuc, 
        receipt.danhmucthu_id, 
        category_collect.tendanhmuc, 
        receipt.bill_id, 
        receipt.contract_id, 
         GROUP_CONCAT(tenant.tenkhach SEPARATOR ', ') AS tenkhach
    FROM 
        receipt
    LEFT JOIN 
        category_collect 
    ON 
        receipt.danhmucthu_id = category_collect.id
    LEFT JOIN 
        room 
    ON 
        receipt.room_id = room.id
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
    LEFT JOIN
        bill
    ON 
        receipt.bill_id = bill.id
    WHERE 
        receipt.id = $id
");



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Thu</title>
</head>

<body style="display: flex; justify-content: center; margin-top: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7fafc;">
    <div class="receipt-content" style="width: 30%; height: auto; background: #fff; box-shadow: 1px 1px 10px #ccc; text-align: center; padding: 50px 20px; line-height: 1.2;">
        <img style="width: 150px;" src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logomain.png" alt="Logo">
        <h2 style="font-size: 28px; margin: 10px 0;">Phòng Trọ Thảo Nguyên</h2>
        <p style="font-size: 14px;">Địa chỉ: 56 - Nam Pháp, Ngô Quyền, Hải Phòng</p>
        <p style="font-size: 18px;">Phiếu <?php echo $receiptDetail['tendanhmuc']; ?> <?php echo $receiptDetail['thang']; ?></p>
        <div style="margin-top: 20px; text-align: start;">
        <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;">Tên KH: <b><?php echo htmlspecialchars($receiptDetail['tenkhach'], ENT_QUOTES, 'UTF-8'); ?></b></p>
            </div>
            <div style="margin-bottom: 10px;">
            <p style="font-size: 14px; font-weight: normal;">Phòng: <?php echo htmlspecialchars($receiptDetail['tenphong'], ENT_QUOTES, 'UTF-8'); ?> - Khu vực: <?php echo htmlspecialchars($receiptDetail['tenkhuvuc'], ENT_QUOTES, 'UTF-8'); ?></p>

            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Số điện thoại: <?php echo htmlspecialchars($receiptDetail['sdt'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Địa chỉ: <?php echo htmlspecialchars($receiptDetail['diachi'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Số tiền thu: <?php echo number_format($receiptDetail['sotien'], 0, ',', '.'); ?> đ</p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Ghi chú: <?php echo htmlspecialchars($receiptDetail['ghichu'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Ngày thu: <?php echo htmlspecialchars(getDateFormat($receiptDetail['ngaythu'], 'd-m-Y'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 14px;font-weight: normal;">Phương thức thanh toán: <?php
                                                                            if ($receiptDetail['phuongthuc'] == 1) {
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
                    <strong>Xác nhận của người thu tiền </strong><br>
                    <i>Ký và ghi rõ họ tên</i>
                    <div style="padding: 10px; height: 150px; overflow: hidden;">
                        <span></span>
                    </div>
                </div>
                <div>
                    <strong>Xác nhận của người nộp tiền </strong><br>
                    <i>Ký và ghi rõ họ tên</i>
                    <div style="padding: 10px; height: 150px; overflow: hidden;">
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
</body>

</html>