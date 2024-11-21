<?php

$body = getBody();
$id = $_GET['id'];

// Lấy chi tiết phiếu thu từ bảng receipt
$receiptDetail = firstRaw("
    SELECT 
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
    <div class="receipt-content" style="width: 60%; height: auto; background: #fff; box-shadow: 1px 1px 10px #ccc; text-align: center; padding: 50px 20px; line-height: 1.2;">
        <img style="width: 150px;" src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/logomain.png" alt="Logo">
        <h2 style="font-size: 28px; margin: 10px 0;">Phiếu Thu Phòng Trọ Thảo Nguyên</h2>
        <h3 style="margin-top: 10px;">Ngày <?php echo date('d/m/Y'); ?></h3>
        <p style="font-size: 14px;">Địa chỉ: 56 - Nam Pháp, Ngô Quyền, Hải Phòng</p>
        <p>Loại phiếu: <b style="color: red; font-size: 18px;"><?php echo htmlspecialchars($receiptDetail['tendanhmuc'], ENT_QUOTES, 'UTF-8'); ?></b></p>
        <div class="rowTwo" style="display: flex; justify-content: space-around; margin-top: 10px;">
            <p style="font-size: 14px;"><b><?php echo htmlspecialchars($receiptDetail['tenphong'], ENT_QUOTES, 'UTF-8'); ?> - Khách hàng: <?php echo htmlspecialchars($receiptDetail['tenkhach'], ENT_QUOTES, 'UTF-8'); ?></b></p>

        </div>

        <table border="1" cellspacing="0" width="100%" cellpadding="10" style="text-align: start; margin-top: 20px;">
            <tr>
                <td><b>STT</b></td>
                <td><b>Thông tin</b></td>
                <td><b>Chi tiết</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>1</b></td>
                <td>Số tiền thu</td>
                <td style="font-size: 16px;"><b><?php echo number_format($receiptDetail['sotien'], 0, ',', '.') ?> đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>2</b></td>
                <td>Ghi chú</td>
                <td style="font-size: 16px;"><b><?php echo htmlspecialchars($receiptDetail['ghichu'], ENT_QUOTES, 'UTF-8'); ?></b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>3</b></td>
                <td>Ngày thu</td>
                <td style="font-size: 16px;"><b><?php echo htmlspecialchars(getDateFormat($receiptDetail['ngaythu'], 'd-m-Y'), ENT_QUOTES, 'UTF-8'); ?></b></td>
            </tr>
            <tr>
                <td style="font-size: 14px;"><b>4</b></td>
                <td>Phương thức thanh toán</td>
                <td style="font-size: 16px;">
                    <b>
                        <?php
                        if ($receiptDetail['phuongthuc'] == 1) {
                            echo "Chuyển khoản";
                        } else {
                            echo "Tiền mặt"; // Hoặc giá trị thay thế khác
                        }
                        ?>
                    </b>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>